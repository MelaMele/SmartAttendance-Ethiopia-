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

        $msg = $newStatus === 'suspended' 
            ? "ድርጅቱ ({$company->company_name}) ታግዷል (Suspended)!" 
            : "ድርጅቱ ({$company->company_name}) ነጻ ሆኗል (Activated)!";

        return back()->with('success', $msg);
    }

    // አዲስ ፖስተር መጫኛ (GD ሳያስፈልገው 100% Fail-Proof Base64 Data URI)
    public function storeAd(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'target_url'   => 'nullable|string',
            'phone_number' => 'nullable|string',
            'placement'    => 'required|in:employee_dashboard,admin_dashboard,all',
            'ad_file'      => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'banner_image' => 'nullable|string',
        ]);

        $imageUrl = $request->banner_image;

        // ፎቶ ከተሰጠ ወደ Base64 ይቀየራል (ለ Vercel Serverless አስተማማኝ ነው)
        if ($request->hasFile('ad_file')) {
            $file = $request->file('ad_file');
            $mime = $file->getMimeType();
            $data = file_get_contents($file->getRealPath());
            $imageUrl = 'data:' . $mime . ';base64,' . base64_encode($data);
        }

        if (!$imageUrl) {
            return back()->with('error', 'እባክዎ የማስታወቂያ ፎቶ ይምረጡ ወይም ሊንክ ያስገቡ!');
        }

        $targetAction = $request->target_url;
        if ($request->filled('phone_number')) {
            $targetAction = 'tel:' . preg_replace('/[^0-9+]/', '', $request->phone_number);
        }

        Ad::create([
            'title'        => $request->title,
            'banner_image' => $imageUrl,
            'target_url'   => $targetAction ?: '#',
            'placement'    => $request->placement,
            'is_active'    => true,
            'views_count'  => 0,
            'clicks_count' => 0,
        ]);

        return back()->with('success', 'አዲስ ፖስተር በተሳካ ሁኔታ ተጭኗል!');
    }

    // ማስታወቂያ ማስተካከል (Edit / Update Ad)
    public function updateAd(Request $request, $id)
    {
        $ad = Ad::findOrFail($id);

        $request->validate([
            'title'        => 'required|string|max:255',
            'target_url'   => 'nullable|string',
            'phone_number' => 'nullable|string',
            'placement'    => 'required|in:employee_dashboard,admin_dashboard,all',
            'ad_file'      => 'nullable|file|mimes:jpeg,png,jpg,webp,gif|max:5120',
        ]);

        $updateData = [
            'title'     => $request->title,
            'placement' => $request->placement,
        ];

        // አዲስ ፎቶ ከመረጠ ብቻ ምስሉ ይቀየራል
        if ($request->hasFile('ad_file')) {
            $file = $request->file('ad_file');
            $mime = $file->getMimeType();
            $data = file_get_contents($file->getRealPath());
            $updateData['banner_image'] = 'data:' . $mime . ';base64,' . base64_encode($data);
        }

        if ($request->filled('phone_number')) {
            $updateData['target_url'] = 'tel:' . preg_replace('/[^0-9+]/', '', $request->phone_number);
        } elseif ($request->filled('target_url')) {
            $updateData['target_url'] = $request->target_url;
        }

        $ad->update($updateData);

        return back()->with('success', 'ማስታወቂያው በተሳካ ሁኔታ ተስተካክሏል!');
    }

    public function toggleAdStatus($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->update(['is_active' => !$ad->is_active]);
        $statusText = $ad->is_active ? 'ነቁ ሆኗል' : 'ቆሟል';
        return back()->with('success', "የማስታወቂያው ሁኔታ ({$statusText}) ተቀይሯል!");
    }

    public function deleteAd($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();
        return back()->with('success', 'ማስታወቂያው በቋሚነት ተሰርዟል!');
    }
}
