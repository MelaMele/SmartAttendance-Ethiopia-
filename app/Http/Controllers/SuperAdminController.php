<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Ad;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SuperAdminController extends Controller
{
    // ዋና የሱፐር አድሚን ዳሽቦርድ
    public function dashboard()
    {
        $companies = Company::withCount('employees')->latest()->get();
        $ads = Ad::latest()->get();

        $totalCompanies = $companies->count();
        $activeCompanies = $companies->where('status', 'active')->count();
        $totalEmployees = Employee::count();
        $totalAdViews = $ads->sum('views_count');
        $totalAdClicks = $ads->sum('clicks_count');

        // ለቀጥታ ተንቀሳቃሽ ሰሌዳ (Live 4.5s Carousel)
        $activeAds = Ad::where('is_active', true)->latest()->get();

        return view('superadmin.dashboard', compact(
            'companies', 'ads', 'activeAds', 'totalCompanies',
            'activeCompanies', 'totalEmployees', 'totalAdViews', 'totalAdClicks'
        ));
    }

    // አዲስ ድርጅት መመዝገብ
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

    // በ 1-Click ድርጅትን ማገድ ወይም መክፈት
    public function toggleCompanyStatus($id)
    {
        $company = Company::findOrFail($id);
        $newStatus = $company->status === 'active' ? 'suspended' : 'active';
        $company->update(['status' => $newStatus]);

        $msg = $newStatus === 'suspended' ? "ድርጅቱ ({$company->company_name}) ታግዷል (Suspended)!" : "ድርጅቱ ({$company->company_name}) ነጻ ሆኗል (Activated)!";
        return back()->with('success', $msg);
    }

    // አዲስ ፖስተር መጫኛ (ከነ ፎቶ መጨመሪያ/Compressor ሎጂክ ጋር)
    public function storeAd(Request $request)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'target_url'   => 'nullable|string',
            'phone_number' => 'nullable|string',
            'placement'    => 'required|in:employee_dashboard,admin_dashboard,all',
            'ad_file'      => 'nullable|image|max:8192', // እስከ 8MB መቀበል ይችላል፤ ራሱ ያሳንሰዋል
            'banner_image' => 'nullable|url',
        ]);

        $imageUrl = $request->banner_image;

        // ፎቶ ከተሰጠ በ PHP GD አማካኝነት መጠኑን አሳንሶ (Compress) ማስቀመጥ
        if ($request->hasFile('ad_file')) {
            $file = $request->file('ad_file');
            $imageUrl = $this->compressAndConvertToBase64($file);
        }

        if (!$imageUrl) {
            return back()->with('error', 'እባክዎ የማስታወቂያ ፎቶ ይምረጡ!');
        }

        // ስልክ ቁጥር ከተሞላ ቀጥታ መደወያ (tel:...) ማድረግ
        $targetAction = $request->target_url;
        if ($request->filled('phone_number')) {
            $targetAction = 'tel:' . preg_replace('/[^0-9+]/', '', $request->phone_number);
        }

        Ad::create([
            'title'        => $request->title,
            'banner_image' => $imageUrl,
            'target_url'   => $targetAction ?? '#',
            'placement'    => $request->placement,
            'is_active'    => true,
        ]);

        return back()->with('success', 'አዲስ ፖስተር (Compress ተደርጎ መጠኑ በከፍተኛ ሁኔታ ቀንሶ) ተጭኗል!');
    }

    // ማስታወቂያ ማቆም ወይም ማሳየት
    public function toggleAdStatus($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->update(['is_active' => !$ad->is_active]);
        return back()->with('success', 'የማስታወቂያው ሁኔታ ተቀይሯል!');
    }

    // ማስታወቂያ ማጥፋት
    public function deleteAd($id)
    {
        $ad = Ad::findOrFail($id);
        $ad->delete();
        return back()->with('success', 'ማስታወቂያው ተሰርዟል!');
    }

    /**
     * ፎቶውን አሳንሶ (Resize & Compress) ወደ ዝቅተኛ Base64 መቀየሪያ ፈንክሽን
     */
    private function compressAndConvertToBase64($file)
    {
        $filePath = $file->getRealPath();
        $mime = $file->getMimeType();

        // የምስል ምንጭ መፍጠር
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $image = @imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = @imagecreatefrompng($filePath);
                break;
            case 'image/webp':
                $image = @imagecreatefromwebp($filePath);
                break;
            default:
                // GD የማይደግፈው ከሆነ በቀጥታ ማስቀመጥ
                return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }

        if (!$image) {
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($filePath));
        }

        // 1. መጠኑን (Dimension) ወደ ከፍተኛው 700px ማስተካከል
        $origWidth = imagesx($image);
        $origHeight = imagesy($image);
        $maxWidth = 700;

        if ($origWidth > $maxWidth) {
            $newWidth = $maxWidth;
            $newHeight = (int) ($origHeight * ($maxWidth / $origWidth));
        } else {
            $newWidth = $origWidth;
            $newHeight = $origHeight;
        }

        $resizedImage = imagecreatetruecolor($newWidth, $newHeight);

        // ለ PNG የነበረውን Transparency መጠበቅ
        imagealphablending($resizedImage, false);
        imagesavealpha($resizedImage, true);

        imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $origWidth, $origHeight);

        // 2. በጥራት 65% ወደ JPEG ማሳነስ (ፋይሉን እጅግ በጣም ያቀለዋል)
        ob_start();
        imagejpeg($resizedImage, null, 65);
        $compressedData = ob_get_clean();

        // ሚሞሪውን ማጽዳት
        imagedestroy($image);
        imagedestroy($resizedImage);

        return 'data:image/jpeg;base64,' . base64_encode($compressedData);
    }
}
