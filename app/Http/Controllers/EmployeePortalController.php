<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Company;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Models\Ad;
use App\Services\EthiopianCalendarService;
use App\Services\GeoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    public function showLogin()
    {
        if (session('employee_id')) {
            return redirect()->route('employee.dashboard');
        }
        return view('employee.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'access_code'  => 'required',
        ]);

        $rawPhone = trim($request->phone_number);
        $cleanPhone = substr(preg_replace('/[^0-9]/', '', $rawPhone), -9);

        $query = Employee::where(function ($q) use ($cleanPhone) {
                $q->where('phone_number', 'LIKE', '%' . $cleanPhone)
                  ->orWhere('phone_number', 'LIKE', '%0' . $cleanPhone);
            })
            ->where('access_code', trim($request->access_code))
            ->where('is_active', true);

        // ተጠቃሚው በኩባንያ ሊንክ (/c/{slug}) በኩል ከመጣ በዚያ ኩባንያ ብቻ መፈለግ
        if (session('current_company_id')) {
            $query->where('company_id', session('current_company_id'));
        }

        $employee = $query->first();

        if (!$employee) {
            return back()->with('error', 'የተሳሳተ ስልክ ቁጥር ወይም የይለፍ ኮድ!')->withInput();
        }

        session([
            'employee_id'        => $employee->id,
            'employee_name'      => $employee->full_name,
            'current_company_id' => $employee->company_id,
        ]);
        session()->save();

        return redirect()->route('employee.dashboard');
    }

    public function dashboard()
    {
        $employeeId = session('employee_id');
        if (!$employeeId) {
            return redirect()->route('employee.login');
        }

        $employee = Employee::find($employeeId);
        if (!$employee) {
            session()->forget(['employee_id', 'employee_name', 'current_company_id']);
            return redirect()->route('employee.login');
        }

        // የኩባንያውን ቅንብር መፈለግ (በመጀመሪያ ከ Company ሞዴል፣ ካልተገኘ ከ CompanySetting)
        $setting = null;
        if ($employee->company_id) {
            $setting = Company::find($employee->company_id);
        }
        if (!$setting) {
            $setting = CompanySetting::first() ?? (object)[
                'company_name'          => 'Mela Solution',
                'latitude'              => 9.030000,
                'longitude'             => 38.740000,
                'allowed_radius_meters' => 100,
                'work_start_time'       => '08:30:00',
                'work_end_time'         => '17:00:00',
            ];
        }

        $todayGc = Carbon::today('Africa/Addis_Ababa')->toDateString();
        $todayEc = EthiopianCalendarService::todayText();

        // የዛሬ የአቴንዳንስ መረጃ
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date_gc', $todayGc)
            ->first();

        // ማስታወቂያዎች (ለዚህ ሰራተኛ ተለይቶ ወይም ለሁሉም የተላከ)
        $announcements = Announcement::where(function ($query) use ($employee) {
                $query->whereNull('employee_id')
                      ->orWhere('employee_id', $employee->id);
            })
            ->when($employee->company_id, function($q) use ($employee) {
                $q->where(function($sub) use ($employee) {
                    $sub->where('company_id', $employee->company_id)
                        ->orWhereNull('company_id');
                });
            })
            ->latest()
            ->take(5)
            ->get();

        // የፈቃድ ጥያቄዎች ሁኔታ
        $myLeaves = LeaveRequest::where('employee_id', $employee->id)
            ->latest()
            ->take(5)
            ->get();

        // በሰራተኞች ገጽ ላይ ለሚታዩ ማስታወቂያዎች እይታን (Views Count) መቁጠር
        Ad::where('is_active', true)
            ->whereIn('placement', ['employee_dashboard', 'all'])
            ->increment('views_count');

        return view('employee.dashboard', compact(
            'employee', 'setting', 'todayGc', 'todayEc',
            'todayAttendance', 'announcements', 'myLeaves'
        ));
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = session('employee_id');
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'እባክዎ መጀመሪያ ይግቡ!'], 401);
        }

        $setting = null;
        if ($employee->company_id) {
            $setting = Company::find($employee->company_id);
        }
        if (!$setting) {
            $setting = CompanySetting::first() ?? (object)[
                'latitude'              => 9.030000,
                'longitude'             => 38.740000,
                'allowed_radius_meters' => 100,
                'work_start_time'       => '08:30:00',
            ];
        }

        $distance = GeoService::calculateDistanceInMeters(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        $allowedRadius = $setting->allowed_radius_meters ?? 100;
        if ($distance > $allowedRadius) {
            return response()->json([
                'success' => false,
                'message' => "ከተፈቀደው {$allowedRadius} ሜትር ክልል ውጭ ነዎት! (አሁን በ {$distance} ሜትር ርቀት ላይ ነዎት)"
            ], 422);
        }

        $now = Carbon::now('Africa/Addis_Ababa');
        $todayGc = $now->toDateString();
        $ethData = EthiopianCalendarService::fromGregorian($todayGc);

        $workStartTime = Carbon::parse($setting->work_start_time ?? '08:30:00');
        $status = $now->format('H:i:s') > $workStartTime->format('H:i:s') ? 'late' : 'present';

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employee->id, 'date_gc' => $todayGc],
            [
                'company_id'               => $employee->company_id,
                'eth_month'                => $ethData['month'] ?? 1,
                'eth_year'                 => $ethData['year'] ?? 2017,
                'date_ec'                  => $ethData['formatted_text'] ?? EthiopianCalendarService::todayFormatted(),
                'check_in_at'              => $now,
                'check_in_lat'             => $request->latitude,
                'check_in_lng'             => $request->longitude,
                'check_in_distance_meters' => $distance,
                'status'                   => $status
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Check-in በተሳካ ሁኔታ ተመዝግቧል! (" . ($status === 'late' ? 'አርፍደዋል' : 'በሰዓቱ ገብተዋል') . ")",
            'time'    => $now->format('h:i A')
        ]);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude'     => 'required|numeric',
            'longitude'    => 'required|numeric',
            'early_reason' => 'nullable|string'
        ]);

        $employeeId = session('employee_id');
        $employee = Employee::find($employeeId);

        if (!$employee) {
            return response()->json(['success' => false, 'message' => 'እባክዎ መጀመሪያ ይግቡ!'], 401);
        }

        $setting = null;
        if ($employee->company_id) {
            $setting = Company::find($employee->company_id);
        }
        if (!$setting) {
            $setting = CompanySetting::first() ?? (object)[
                'latitude'              => 9.030000,
                'longitude'             => 38.740000,
                'allowed_radius_meters' => 100,
            ];
        }

        $distance = GeoService::calculateDistanceInMeters(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        $allowedRadius = $setting->allowed_radius_meters ?? 100;
        if ($distance > $allowedRadius) {
            return response()->json([
                'success' => false,
                'message' => "Check-out ለማድረግ በ {$allowedRadius} ሜትር ክልል ውስጥ መሆን አለብዎት! (አሁን በ {$distance} ሜትር ርቀት ላይ ነዎት)"
            ], 422);
        }

        $now = Carbon::now('Africa/Addis_Ababa');
        $todayGc = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employee->id)
            ->where('date_gc', $todayGc)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => "ዛሬ ጠዋት Check-in አልተደረገም!"
            ], 400);
        }

        $attendance->update([
            'check_out_at'              => $now,
            'check_out_lat'             => $request->latitude,
            'check_out_lng'             => $request->longitude,
            'check_out_distance_meters' => $distance,
            'early_leave_reason'        => $request->early_reason,
            'early_leave_approved'      => $request->early_reason ? false : null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Check-out በተሳካ ሁኔታ ተመዝግቧል! ደህና ይደሩ።",
            'time'    => $now->format('h:i A')
        ]);
    }

    public function submitLeave(Request $request)
    {
        $request->validate([
            'leave_type'    => 'required|string',
            'start_date_gc' => 'required|date',
            'end_date_gc'   => 'required|date',
            'reason'        => 'required|string',
        ]);

        $employee = Employee::findOrFail(session('employee_id'));

        LeaveRequest::create([
            'employee_id'   => $employee->id,
            'company_id'    => $employee->company_id,
            'leave_type'    => $request->leave_type,
            'start_date_gc' => $request->start_date_gc,
            'start_date_ec' => EthiopianCalendarService::fromGregorian($request->start_date_gc)['formatted_text'] ?? $request->start_date_gc,
            'end_date_gc'   => $request->end_date_gc,
            'end_date_ec'   => EthiopianCalendarService::fromGregorian($request->end_date_gc)['formatted_text'] ?? $request->end_date_gc,
            'reason'        => $request->reason,
            'status'        => 'pending'
        ]);

        return back()->with('success', 'የፈቃድ ጥያቄዎ ለአስተዳዳሪው ተልኳል! ሁኔታውን ከታች መከታተል ይችላሉ።');
    }

    public function logout()
    {
        session()->forget(['employee_id', 'employee_name', 'current_company_id']);
        return redirect()->route('employee.login');
    }
}
