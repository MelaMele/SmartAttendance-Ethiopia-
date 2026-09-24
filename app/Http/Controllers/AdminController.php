<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Models\Ad;
use App\Services\EthiopianCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $todayGc = Carbon::today('Africa/Addis_Ababa')->toDateString();
        $todayEcData = EthiopianCalendarService::fromGregorian($todayGc);
        $todayEc = $todayEcData['formatted_text'];
        $currentEthMonth = $todayEcData['month'];

        $setting = CompanySetting::first() ?? CompanySetting::create([
            'company_name' => 'Mela Solution',
            'latitude' => 9.030000,
            'longitude' => 38.740000,
            'allowed_radius_meters' => 100,
            'work_start_time' => '08:30:00',
            'work_end_time' => '17:00:00',
        ]);

        $employees = Employee::where('is_active', true)->get();
        $totalEmployees = $employees->count();

        // ዛሬ በፈቃድ ላይ ያሉ
        $onLeaveToday = LeaveRequest::with('employee')
            ->where('status', 'approved')
            ->where('start_date_gc', '<=', $todayGc)
            ->where('end_date_gc', '>=', $todayGc)
            ->get();

        $todayAttendances = Attendance::with('employee')
            ->where('date_gc', $todayGc)
            ->get();

        $presentCount = $todayAttendances->where('status', 'present')->count();
        $lateCount = $todayAttendances->where('status', 'late')->count();
        $onLeaveCount = $onLeaveToday->count();
        $absentCount = max(0, $totalEmployees - ($todayAttendances->count() + $onLeaveCount));

        $pendingLeaves = LeaveRequest::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // የተመረጠው የኢትዮጵያ ወር (ፎልደር ማህደር) - በነባሪ የአሁኑ ወር
        $selectedMonth = $request->get('month', $currentEthMonth);
        $monthAnnouncements = Announcement::where('eth_month', $selectedMonth)->latest()->get();

        // ማስታወቂያ ለአድሚን
        $adminAd = Ad::where('is_active', true)
            ->whereIn('placement', ['admin_dashboard', 'all'])
            ->inRandomOrder()
            ->first();
        if ($adminAd) {
            $adminAd->increment('views_count');
        }

        return view('admin.dashboard', compact(
            'todayGc', 'todayEc', 'setting', 'employees', 'totalEmployees',
            'presentCount', 'lateCount', 'onLeaveCount', 'absentCount',
            'todayAttendances', 'onLeaveToday', 'pendingLeaves',
            'monthAnnouncements', 'selectedMonth', 'adminAd'
        ));
    }

    public function employees()
    {
        $employees = Employee::latest()->paginate(15);
        return view('admin.employees', compact('employees'));
    }

    public function storeEmployee(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|unique:employees,phone_number',
            'department'   => 'nullable|string',
            'position'     => 'nullable|string',
        ]);

        $accessCode = rand(100000, 999999);

        Employee::create([
            'full_name'    => $request->full_name,
            'phone_number' => $request->phone_number,
            'access_code'  => $accessCode,
            'department'   => $request->department,
            'position'     => $request->position,
            'is_active'    => true,
        ]);

        return back()->with('success', "ሰራተኛው ተመዝግቧል! የመግቢያ ኮድ፡ {$accessCode}");
    }

    // ሰራተኛ ማስተካከል (Edit Employee)
    public function updateEmployee(Request $request, $id)
    {
        $employee = Employee::findOrFail($id);
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|unique:employees,phone_number,' . $employee->id,
            'department'   => 'nullable|string',
            'position'     => 'nullable|string',
            'access_code'  => 'required|string',
        ]);

        $employee->update($request->all());
        return back()->with('success', 'የሰራተኛው መረጃ በተሳካ ሁኔታ ተስተካክሏል!');
    }

    // ሰራተኛ ሲለቅ ማጥፋት (Delete Employee)
    public function deleteEmployee($id)
    {
        $employee = Employee::findOrFail($id);
        $employee->delete();
        return back()->with('success', 'ሰራተኛው ከሲስተሙ ተሰርዟል!');
    }

    // ፈጣን የሁኔታ መቀየሪያ (Quick Status Update: Present, Late, Absent, Permission)
    public function quickStatusUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:present,late,absent,on_leave'
        ]);

        $attendance = Attendance::findOrFail($id);
        $attendance->update(['status' => $request->status]);

        return back()->with('success', 'የሰራተኛው ሁኔታ ተቀይሯል!');
    }

    // የ 100m ጂፒኤስ ማስተካከያ
    public function updateGeofence(Request $request)
    {
        $request->validate([
            'company_name'          => 'required|string',
            'latitude'              => 'required|numeric',
            'longitude'             => 'required|numeric',
            'allowed_radius_meters' => 'required|integer|min:10|max:1000',
            'work_start_time'       => 'required',
            'work_end_time'         => 'required',
        ]);

        $setting = CompanySetting::first() ?? new CompanySetting();
        $setting->fill($request->all());
        $setting->save();

        return back()->with('success', 'የጂፒኤስ አጥር እና የስራ ሰዓት ቅንብር በተሳካ ሁኔታ ተቀይሯል!');
    }

    public function updateLeaveStatus(Request $request, $id)
    {
        $request->validate([
            'status'       => 'required|in:approved,rejected',
            'admin_remark' => 'nullable|string'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status'       => $request->status,
            'admin_remark' => $request->admin_remark,
        ]);

        return back()->with('success', 'የፈቃድ ጥያቄው ውሳኔ ተመዝግቧል!');
    }

    public function approveEarlyLeave($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update(['early_leave_approved' => true]);
        return back()->with('success', 'ቀድሞ የመውጣት ጥያቄው ጸድቋል!');
    }

    // መልእክት መላክ (ከወር ቁጥር ጋር ወደ ማህደር ማስገባት)
    public function postAnnouncement(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'employee_id' => 'nullable',
            'priority'    => 'required|in:normal,urgent,info'
        ]);

        $today = Carbon::today('Africa/Addis_Ababa');
        $ethData = EthiopianCalendarService::fromGregorian($today);

        Announcement::create([
            'title'       => $request->title,
            'message'     => $request->message,
            'priority'    => $request->priority,
            'employee_id' => $request->employee_id === 'all' ? null : $request->employee_id,
            'eth_month'   => $ethData['month'], // ከመስከረም - ጳጉሜን
            'eth_year'    => $ethData['year'],
            'date_gc'     => $today->toDateString(),
            'date_ec'     => $ethData['formatted_text']
        ]);

        return back()->with('success', 'መልእክቱ ተላልፏል፤ ወደ ወርሃዊ ማህደርም ገብቷል!');
    }

    // የወርሃዊ ማጠቃለያ ሪፖርት (Payroll Monthly Summary Export)
    public function exportMonthlySummaryCsv(Request $request)
    {
        $ethData = EthiopianCalendarService::fromGregorian();
        $month = $request->get('month', $ethData['month']);
        $year = $ethData['year'];

        $fileName = "Monthly_Attendance_Summary_Month_{$month}_{$year}.csv";
        $employees = Employee::where('is_active', true)->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        // ንጹህ የወር ማጠቃለያ አምዶች
        $columns = ['የሰራተኛ ሙሉ ስም', 'ስልክ ቁጥር', 'ክፍል / መደብ', 'የተገኙበት ቀን ብዛት (Present)', 'ያረፈዱበት ቀን ብዛት (Late)', 'በፈቃድ የቆዩበት ቀን (Permission)', 'የቀሩበት ቀን ብዛት (Absent)'];

        $callback = function() use($employees, $month, $year, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM ለግዕዝ ፊደላት
            fputcsv($file, $columns);

            foreach ($employees as $emp) {
                // በወሩ ውስጥ የነበሩ አቴንዳንሶች
                $attendances = Attendance::where('employee_id', $emp->id)
                    ->where('eth_month', $month)
                    ->get();

                $presentDays = $attendances->where('status', 'present')->count();
                $lateDays = $attendances->where('status', 'late')->count();
                $permissionDays = $attendances->where('status', 'on_leave')->count();
                $absentDays = $attendances->where('status', 'absent')->count();

                fputcsv($file, [
                    $emp->full_name,
                    $emp->phone_number,
                    ($emp->department ?? '-') . ' / ' . ($emp->position ?? '-'),
                    $presentDays,
                    $lateDays,
                    $permissionDays,
                    $absentDays,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
