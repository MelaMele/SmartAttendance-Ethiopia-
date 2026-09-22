<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Services\EthiopianCalendarService;
use App\Services\GeoService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeePortalController extends Controller
{
    // የመግቢያ ገጽ ማሳያ
    public function showLogin()
    {
        if (session()->has('employee_id')) {
            return redirect()->route('employee.dashboard');
        }
        return view('employee.login');
    }

    // በስልክና በሚስጥር ኮድ ማረጋገጫ
    public function login(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'access_code'  => 'required',
        ]);

        $employee = Employee::where('phone_number', $request->phone_number)
            ->where('access_code', $request->access_code)
            ->where('is_active', true)
            ->first();

        if (!$employee) {
            return back()->with('error', 'የተሳሳተ ስልክ ቁጥር ወይም የይለፍ ኮድ! እባክዎ እንደገና ይሞክሩ።');
        }

        // ሰራተኛውን በ Session ውስጥ ማስቀመጥ
        session(['employee_id' => $employee->id, 'employee_name' => $employee->full_name]);

        return redirect()->route('employee.dashboard')->with('success', "እንኳን ደህና መጡ፣ {$employee->full_name}!");
    }

    // የሰራተኛው ዋና ዳሽቦርድ
    public function dashboard()
    {
        $employeeId = session('employee_id');
        if (!$employeeId) {
            return redirect()->route('employee.login');
        }

        $employee = Employee::findOrFail($employeeId);
        $setting = CompanySetting::first() ?? new CompanySetting();
        $todayGc = Carbon::today('Africa/Addis_Ababa')->toDateString();
        $todayEc = EthiopianCalendarService::todayText();

        // የዛሬው አቴንዳንስ ካለ ማምጣት
        $todayAttendance = Attendance::where('employee_id', $employee->id)
            ->where('date_gc', $todayGc)
            ->first();

        // የቅርብ ጊዜ ማስታወቂያዎች
        $announcements = Announcement::latest()->take(3)->get();

        // የሰራተኛው ያለፉት 5 አቴንዳንሶች
        $recentAttendances = Attendance::where('employee_id', $employee->id)
            ->latest('date_gc')
            ->take(5)
            ->get();

        return view('employee.dashboard', compact(
            'employee',
            'setting',
            'todayGc',
            'todayEc',
            'todayAttendance',
            'announcements',
            'recentAttendances'
        ));
    }

    // Check-in (100 ሜትር ውስጥ መሆናቸውን አረጋግጦ መመዝገብ)
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = session('employee_id');
        $setting = CompanySetting::first() ?? CompanySetting::create([
            'company_name' => 'Mela Solution',
            'latitude' => 9.030000,
            'longitude' => 38.740000,
            'allowed_radius_meters' => 100,
        ]);

        // የርቀት ስሌት በሜትር
        $distance = GeoService::calculateDistanceInMeters(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->allowed_radius_meters) {
            return response()->json([
                'success' => false,
                'message' => "ከተፈቀደው 100 ሜትር ክልል ውጭ ነዎት! አሁን ከድርጅቱ በ {$distance} ሜትር ርቀት ላይ ይገኛሉ።"
            ], 422);
        }

        $now = Carbon::now('Africa/Addis_Ababa');
        $todayGc = $now->toDateString();
        $todayEc = EthiopianCalendarService::todayFormatted();

        // አርፍዷል ወይስ በሰዓቱ ነው?
        $workStartTime = Carbon::parse($setting->work_start_time ?? '08:30:00');
        $status = $now->format('H:i:s') > $workStartTime->format('H:i:s') ? 'late' : 'present';

        $attendance = Attendance::firstOrCreate(
            ['employee_id' => $employeeId, 'date_gc' => $todayGc],
            [
                'date_ec' => $todayEc,
                'check_in_at' => $now,
                'check_in_lat' => $request->latitude,
                'check_in_lng' => $request->longitude,
                'check_in_distance_meters' => $distance,
                'status' => $status
            ]
        );

        return response()->json([
            'success' => true,
            'message' => "Check-in በተሳካ ሁኔታ ተመዝግቧል! (ከቢሮው በ {$distance} ሜትር ርቀት ላይ ነዎት)",
            'time' => $now->format('h:i A')
        ]);
    }

    // Check-out (የስራ መውጫ ሰዓት መመዝገብ)
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $employeeId = session('employee_id');
        $setting = CompanySetting::first();

        $distance = GeoService::calculateDistanceInMeters(
            $request->latitude,
            $request->longitude,
            $setting->latitude,
            $setting->longitude
        );

        if ($distance > $setting->allowed_radius_meters) {
            return response()->json([
                'success' => false,
                'message' => "Check-out ለማድረግ በ 100 ሜትር ክልል ውስጥ መሆን አለብዎት! (አሁን በ {$distance} ሜትር ርቀት ላይ ነዎት)"
            ], 422);
        }

        $now = Carbon::now('Africa/Addis_Ababa');
        $todayGc = $now->toDateString();

        $attendance = Attendance::where('employee_id', $employeeId)
            ->where('date_gc', $todayGc)
            ->first();

        if (!$attendance) {
            return response()->json([
                'success' => false,
                'message' => "መጀመሪያ ዛሬ ጠዋት Check-in አላደረጉም!"
            ], 400);
        }

        $attendance->update([
            'check_out_at' => $now,
            'check_out_lat' => $request->latitude,
            'check_out_lng' => $request->longitude,
            'check_out_distance_meters' => $distance,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Check-out በተሳካ ሁኔታ ተከናውኗል! ደህና ይደሩ።",
            'time' => $now->format('h:i A')
        ]);
    }

    // ከቤት ሆነው ፈቃድ የመጠየቂያ ፎርም መቀበያ (No GPS restriction)
    public function submitLeave(Request $request)
    {
        $request->validate([
            'leave_type'    => 'required|string',
            'start_date_gc' => 'required|date',
            'end_date_gc'   => 'required|date|after_or_equal:start_date_gc',
            'reason'        => 'required|string',
            'attachment'    => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096'
        ]);

        $filePath = null;
        if ($request->hasFile('attachment')) {
            $filePath = $request->file('attachment')->store('leave_attachments', 'public');
        }

        LeaveRequest::create([
            'employee_id'   => session('employee_id'),
            'leave_type'    => $request->leave_type,
            'start_date_gc' => $request->start_date_gc,
            'start_date_ec' => EthiopianCalendarService::fromGregorian($request->start_date_gc)['formatted_text'],
            'end_date_gc'   => $request->end_date_gc,
            'end_date_ec'   => EthiopianCalendarService::fromGregorian($request->end_date_gc)['formatted_text'],
            'reason'        => $request->reason,
            'attachment'    => $filePath,
            'status'        => 'pending'
        ]);

        return back()->with('success', 'የፈቃድ ጥያቄዎ ለአስተዳዳሪው ተልኳል! ውሳኔውን እዚህ ገጽ ላይ መከታተል ይችላሉ።');
    }

    // መውጫ (Logout)
    public function logout()
    {
        session()->forget(['employee_id', 'employee_name']);
        return redirect()->route('employee.login');
    }
}
