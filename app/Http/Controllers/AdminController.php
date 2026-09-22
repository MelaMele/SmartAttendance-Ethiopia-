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
use Illuminate\Support\Str;

class AdminController extends Controller
{
    // ዋናው የአድሚን ዳሽቦርድ
    public function dashboard(Request $request)
    {
        $todayGc = Carbon::today('Africa/Addis_Ababa')->toDateString();
        $todayEc = EthiopianCalendarService::todayText();

        $setting = CompanySetting::first() ?? CompanySetting::create([
            'company_name' => 'Mela Solution',
            'latitude' => 9.030000,
            'longitude' => 38.740000,
            'allowed_radius_meters' => 100,
        ]);

        $totalEmployees = Employee::where('is_active', true)->count();

        // የዛሬ አቴንዳንስ ስታቲስቲክስ
        $todayAttendances = Attendance::with('employee')
            ->where('date_gc', $todayGc)
            ->get();

        $presentCount = $todayAttendances->where('status', 'present')->count();
        $lateCount = $todayAttendances->where('status', 'late')->count();
        $checkedInCount = $todayAttendances->count();
        $absentCount = max(0, $totalEmployees - $checkedInCount);

        // የመጡ የፈቃድ ጥያቄዎች
        $pendingLeaves = LeaveRequest::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->get();

        // የቅርብ ማስታወቂያዎች
        $announcements = Announcement::latest()->take(5)->get();

        return view('admin.dashboard', compact(
            'todayGc', 'todayEc', 'setting', 'totalEmployees',
            'presentCount', 'lateCount', 'absentCount',
            'todayAttendances', 'pendingLeaves', 'announcements'
        ));
    }

    // የሰራተኞች ዝርዝር እና መመዝገቢያ ገጽ
    public function employees()
    {
        $employees = Employee::latest()->paginate(15);
        return view('admin.employees', compact('employees'));
    }

    // አዲስ ሰራተኛ መመዝገብ
    public function storeEmployee(Request $request)
    {
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'phone_number' => 'required|unique:employees,phone_number',
            'department'   => 'nullable|string',
            'position'     => 'nullable|string',
        ]);

        // 6 አሃዝ የይለፍ ኮድ በዘፈቀደ ማመንጨት (Random 6-digit Code)
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

    // የድርጅት የጂፒኤስ አጥር (100 ሜትር) ማስተካከያ
    public function updateGeofence(Request $request)
    {
        $request->validate([
            'company_name'          => 'required|string',
            'latitude'              => 'required|numeric',
            'longitude'             => 'required|numeric',
            'allowed_radius_meters' => 'required|integer|min:10|max:1000',
            'work_start_time'       => 'required',
        ]);

        $setting = CompanySetting::first() ?? new CompanySetting();
        $setting->fill($request->all());
        $setting->save();

        return back()->with('success', 'የጂፒኤስ አጥር እና የስራ ሰዓት ቅንብር በተሳካ ሁኔታ ተቀይሯል!');
    }

    // የፈቃድ ጥያቄ መወሰን (መፍቀድ / መከልከል)
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

        return back()->with('success', "የፈቃድ ጥያቄው ውሳኔ ተመዝግቧል ({$request->status})!");
    }

    // አዲስ አጠቃላይ ማስታወቂያ መለጠፍ
    public function postAnnouncement(Request $request)
    {
        $request->validate([
            'title'    => 'required|string|max:255',
            'message'  => 'required|string',
            'priority' => 'required|in:normal,urgent,info'
        ]);

        $today = Carbon::today('Africa/Addis_Ababa');

        Announcement::create([
            'title'    => $request->title,
            'message'  => $request->message,
            'priority' => $request->priority,
            'date_gc'  => $today->toDateString(),
            'date_ec'  => EthiopianCalendarService::todayFormatted()
        ]);

        return back()->with('success', 'ማስታወቂያው ለሁሉም ሰራተኞች ዳሽቦርድ ተላልፏል!');
    }

    // ሪፖርት ማውረጃ (CSV/Excel)
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

        $columns = ['የሰራተኛ ስም', 'ስልክ ቁጥር', 'ቀን (ዓ.ም)', 'ቀን (G.C)', 'የመግቢያ ሰዓት', 'Check-in ርቀት (ሜትር)', 'የመውጫ ሰዓት', 'ሁኔታ'];

        $callback = function() use($attendances, $columns) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM ለግዕዝ ፊደላት እንዳይዛቡ
            fputcsv($file, $columns);

            foreach ($attendances as $row) {
                fputcsv($file, [
                    $row->employee->full_name ?? '',
                    $row->employee->phone_number ?? '',
                    $row->date_ec,
                    $row->date_gc->toDateString(),
                    $row->check_in_at ? $row->check_in_at->format('h:i A') : '-',
                    $row->check_in_distance_meters ?? '-',
                    $row->check_out_at ? $row->check_out_at->format('h:i A') : '-',
                    $row->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
