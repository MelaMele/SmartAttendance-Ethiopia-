<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ad;
use App\Models\Employee;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    // የሱፐር አድሚን ዋና ዳሽቦርድ
    public function dashboard()
    {
        $companies = Company::withCount('employees')->latest()->get();
        $ads = Ad::latest()->get();

        $totalCompanies = $companies->count();
        $activeCompanies = $companies->where('status', 'active')->count();
        $totalEmployees = Employee::count();
        $totalAdViews = $ads->sum('views_count');
        $totalAdClicks = $ads->sum('clicks_count');

        return view('superadmin.dashboard', compact(
            'companies', 'ads', 'totalCompanies',
            'activeCompanies', 'totalEmployees', 'totalAdViews', 'totalAdClicks'
        ));
    }

    // አዲስ ድርጅት መመዝገብና ልዩ ሊንክ ማመንጨት
    public function storeCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'admin_phone'  => 'required|unique:companies,admin_phone',
            'admin_pin'    => 'required|min:4|max:10',
        ]);

        $slug = Str::slug($request->company_name);
        // ስሙ ከተደጋገመ ልዩ ቁጥር ማከል
        if (Company::where('slug', $slug)->exists()) {
            $slug = $slug . '-' . rand(100, 999);
        }

        $company = Company::create([
            'company_name'          => $request->company_name,
            'slug'                  => $slug,
            'admin_phone'           => $request->admin_phone,
            'admin_pin'             => $request->admin_pin,
            'latitude'              => 9.030000,
            'longitude'             => 38.740000,
            'allowed_radius_meters' => 100,
            'status'                => 'active',
        ]);

        return back()->with('success', "ድርጅቱ ተመዝግቧል! የተፈጠረለት ሊንክ፡ " . url("/c/{$company->slug}"));
    }

    // በአንድ ክሊክ ድርጅትን ማገድ ወይም መክፈት (1-Click Suspend / Activate)
    public function toggleCompanyStatus($id)
    {
        $company = Company::findOrFail($id);
        $newStatus = $company->status === 'active' ? 'suspended' : 'active';
        $company->update(['status' => $newStatus]);

        $msg = $newStatus === 'suspended' ? "ድርጅቱ ({$company->company_name}) ታግዷል (Suspended)!" : "ድርጅቱ ({$company->company_name}) ነጻ ሆኗል (Activated)!";
        return back()->with('success', $msg);
    }

    // አዲስ ማስታወቂያ መጫን (Ad Network Creator)
    public function storeAd(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'banner_image' => 'required|url', // የምስል ሊንክ
            'target_url'   => 'required|url',  // ተጠቃሚው ጠቅ ሲያደርግ የሚሄድበት
            'placement'    => 'required|in:employee_dashboard,admin_dashboard,all',
        ]);

        Ad::create([
            'title'        => $request->title,
            'banner_image' => $request->banner_image,
            'target_url'   => $request->target_url,
            'placement'    => $request->placement,
            'is_active'    => true,
        ]);

        return back()->with('success', 'አዲስ ማስታወቂያ በተሳካ ሁኔታ ተለጥፏል!');
    }

    // ማስታወቂያ ማጥፋት ወይም ማቆም
    public function toggleAdStatus($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->update(['is_active' => !$ad->is_active]);
        return back()->with('success', 'የማስታወቂያው ሁኔታ ተቀይሯል!');
    }

    public function deleteAd($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();
        return back()->with('success', 'ማስታወቂያው ተሰርዟል!');
    }
}
