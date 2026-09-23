<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\CompanySetting;
use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\Announcement;
use App\Services\EthiopianCalendarService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $todayGc = Carbon::today('Africa/Addis_Ababa')->toDateString();
        $todayEc = EthiopianCalendarService::todayText();

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

        // ዛሬ በጸደቀ ፈቃድ ላይ ያሉ ሰራተኞች
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

        $announcements = Announcement::with('employee')->latest()->take(6)->get();

        return view('admin.dashboard', compact(
            'todayGc', 'todayEc', 'setting', 'employees', 'totalEmployees',
            'presentCount', 'lateCount', 'onLeaveCount', 'absentCount',
            'todayAttendances', 'onLeaveToday', 'pendingLeaves', 'announcements'
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

        return back()->with('success', "የፈቃድ ጥያቄው ውሳኔ ተመዝግቧል!");
    }

    // ቀድሞ የመውጣት ምክንያት ማጽደቅ
    public function approveEarlyLeave($id)
    {
        $attendance = Attendance::findOrFail($id);
        $attendance->update(['early_leave_approved' => true]);
        return back()->with('success', 'ቀድሞ የመውጣት ጥያቄው ጸድቋል!');
    }

    // ማስታወቂያ ወይም ለእያንዳንዱ ሰራተኛ ለብቻው መልእክት መላኪያ
    public function postAnnouncement(Request $request)
    {
        $request->validate([
            'title'       => 'required|string|max:255',
            'message'     => 'required|string',
            'employee_id' => 'nullable', // ባዶ ከሆነ ለሁሉም፣ ቁጥር ከሆነ ለተመረጠው
            'priority'    => 'required|in:normal,urgent,info'
        ]);

        $today = Carbon::today('Africa/Addis_Ababa');

        Announcement::create([
            'title'       => $request->title,
            'message'     => $request->message,
            'priority'    => $request->priority,
            'employee_id' => $request->employee_id === 'all' ? null : $request->employee_id,
            'date_gc'     => $today->toDateString(),
            'date_ec'     => EthiopianCalendarService::todayFormatted()
        ]);

        $target = $request->employee_id === 'all' || empty($request->employee_id) ? "ለሁሉም ሰራተኞች" : "ለተመረጠው ሰራተኛ";
        return back()->with('success', "መልእክቱ {$target} በተሳካ ሁኔታ ተላልፏል!");
    }

    public function exportAttendanceCsv()
    {
        $fileName = 'attendance_report_' . date('Y-m-d') . '.csv';
        $attendances = Attendance::with('employee')->latest()->get();

        $headers = [
            "Content-type"        => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['የሰራተኛ ስም', 'ስልክ ቁጥር', 'ቀን (ዓ.ም)', 'የመግቢያ ሰዓት', 'ርቀት (ሜ)', 'የመውጫ ሰዓት', 'ቀድሞ የወጣበት ምክንያት', 'ሁኔታ'];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($attendances as $row) {
                fputcsv($file, [
                    $row->employee->full_name ?? '',
                    $row->employee->phone_number ?? '',
                    $row->date_ec,
                    $row->check_in_at ? $row->check_in_at->format('h:i A') : '-',
                    $row->check_in_distance_meters ?? '-',
                    $row->check_out_at ? $row->check_out_at->format('h:i A') : '-',
                    $row->early_leave_reason ?? '-',
                    $row->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
