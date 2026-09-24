<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ad;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        $companies = Company::withCount('employees')->latest()->get();
        $ads = Ad::latest()->get();

        $totalCompanies = $companies->count();
        $activeCompanies = $companies->where('status', 'active')->count();
        $totalEmployees = Employee::count();
        $totalAdViews = $ads->sum('views_count');
        $totalAdClicks = $ads->sum('clicks_count');

        // ለቀጥታ ተንቀሳቃሽ ሰሌዳ (Live Carousel)
        $activeAds = Ad::where('is_active', true)->latest()->get();

        return view('superadmin.dashboard', compact(
            'companies', 'ads', 'activeAds', 'totalCompanies',
            'activeCompanies', 'totalEmployees', 'totalAdViews', 'totalAdClicks'
        ));
    }

    public function storeCompany(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'admin_phone'  => 'required|unique:companies,admin_phone',
            'admin_pin'    => 'required|min:4|max:10',
        ]);

        $slug = Str::slug($request->company_name);
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

    public function toggleCompanyStatus($id)
    {
        $company = Company::findOrFail($id);
        $newStatus = $company->status === 'active' ? 'suspended' : 'active';
        $company->update(['status' => $newStatus]);

        $msg = $newStatus === 'suspended' ? "ድርጅቱ ({$company->company_name}) ታግዷል (Suspended)!" : "ድርጅቱ ({$company->company_name}) ነጻ ሆኗል (Activated)!";
        return back()->with('success', $msg);
    }

    // አዲስ ፖስተር ከስልክ/ኮምፒውተር Upload ማድረጊያ
    public function storeAd(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'target_url'   => 'required|url',
            'placement'    => 'required|in:employee_dashboard,admin_dashboard,all',
            'expiry_date'  => 'nullable|date',
            'ad_file'      => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:3072', // እስከ 3MB
            'banner_image' => 'nullable|url',
        ]);

        $imageUrl = $request->banner_image;

        // ፋይል ከስልክ/ፒሲ ከተጫነ ወደ Base64 ይቀየራል (በ Vercel ላይ ዘላቂ ሆኖ እንዲቆይ)
        if ($request->hasFile('ad_file')) {
            $file = $request->file('ad_file');
            $imageContent = file_get_contents($file->getRealPath());
            $mimeType = $file->getMimeType();
            $imageUrl = 'data:' . $mimeType . ';base64,' . base64_encode($imageContent);
        }

        if (!$imageUrl) {
            return back()->with('error', 'እባክዎ የማስታወቂያ ፎቶ ይምረጡ ወይም የምስል ሊንክ ያስገቡ!');
        }

        Ad::create([
            'title'        => $request->title,
            'banner_image' => $imageUrl,
            'target_url'   => $request->target_url,
            'placement'    => $request->placement,
            'is_active'    => true,
        ]);

        return back()->with('success', 'አዲስ ፖስተር በተሳካ ሁኔታ ተጭኗል!');
    }

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
