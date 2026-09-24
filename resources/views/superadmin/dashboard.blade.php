<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStaff Super Admin | Ad Network & SaaS Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between">

    <div>
        <!-- Top Navigation -->
        <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex flex-wrap justify-between items-center gap-4">
            <div class="flex items-center gap-3">
                <span class="p-2 bg-gradient-to-tr from-amber-500 to-rose-600 rounded-xl font-black text-white text-lg">👑</span>
                <div>
                    <h1 class="text-base font-bold text-white leading-tight">SmartStaff Super Admin (Mela Solution)</h1>
                    <p class="text-xs text-amber-400">Multi-Company SaaS & Ad Network Manager</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 bg-emerald-500/20 border border-emerald-500/40 text-emerald-300 text-xs rounded-xl font-bold">
                    ● Ad Network Active
                </span>
            </div>
        </nav>

        <main class="max-w-7xl mx-auto p-6 space-y-8">

            @if(session('success'))
                <div class="p-4 bg-emerald-500/20 border border-emerald-500 text-emerald-300 rounded-2xl text-xs font-semibold">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-500/20 border border-rose-500 text-rose-300 rounded-2xl text-xs font-semibold">
                    {{ session('error') }}
                </div>
            @endif

            <!-- Global Metrics -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                    <p class="text-xs text-slate-400">ጠቅላላ ድርጅቶች</p>
                    <p class="text-2xl font-black text-white mt-1">{{ $totalCompanies }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                    <p class="text-xs text-slate-400">ንቁ ድርጅቶች (Active)</p>
                    <p class="text-2xl font-black text-emerald-400 mt-1">{{ $activeCompanies }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                    <p class="text-xs text-slate-400">ጠቅላላ ሰራተኞች</p>
                    <p class="text-2xl font-black text-blue-400 mt-1">{{ $totalEmployees }}</p>
                </div>
                <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                    <p class="text-xs text-slate-400">የማስታወቂያ እይታዎች</p>
                    <p class="text-2xl font-black text-amber-400 mt-1">{{ $totalAdViews }} <span class="text-xs text-slate-400 font-normal">({{ $totalAdClicks }} Clicks)</span></p>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 1. የድርጅቶች አስተዳደር እና መመዝገቢያ (COMPANIES MANAGEMENT & REGISTRATION) -->
            <!-- ============================================================== -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-6">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            🏢 የድርጅቶች አስተዳደር (Multi-Company SaaS & 1-Click Suspend)
                        </h3>
                        <p class="text-xs text-slate-400">አዳዲስ ድርጅቶችን መመዝገብ፣ ሊንክ ማመንጨት እና በአንድ ክሊክ ማገድ/መክፈት</p>
                    </div>
                </div>

                <!-- NEW COMPANY REGISTRATION FORM (መመዝገቢያ ፎርም) -->
                <div class="bg-slate-800/50 border border-slate-700/70 p-5 rounded-2xl">
                    <h4 class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-3">➕ አዲስ ድርጅት መመዝገቢያ (Generate Company Link)</h4>
                    
                    <form action="{{ route('superadmin.company.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                        @csrf
                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">የድርጅቱ ስም</label>
                            <input type="text" name="company_name" placeholder="ለምሳሌ፡ ፀሐይ ኢንሹራንስ" required 
                                   class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white">
                        </div>

                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">የአድሚን ስልክ ቁጥር</label>
                            <input type="tel" name="admin_phone" placeholder="0911223344" required 
                                   class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white">
                        </div>

                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">የአድሚን ሚስጥር PIN (4-6 አሃዝ)</label>
                            <input type="text" name="admin_pin" placeholder="1234" required maxlength="6"
                                   class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono">
                        </div>

                        <div class="flex items-end">
                            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-xl shadow-lg shadow-blue-600/30 transition text-xs">
                                🚀 ድርጅቱን ፍጠርና ሊንክ አውጣ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Companies List Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ድርጅት</th>
                                <th class="p-3">ልዩ ሊንክ (SaaS Portal)</th>
                                <th class="p-3">አድሚን ስልክ / PIN</th>
                                <th class="p-3">ሰራተኞች</th>
                                <th class="p-3">እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($companies as $comp)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-bold text-white">{{ $comp->company_name }}</td>
                                    <td class="p-3">
                                        <a href="{{ url('/c/' . $comp->slug) }}" target="_blank" class="text-blue-400 hover:underline font-mono text-[11px]">
                                            /c/{{ $comp->slug }} ↗
                                        </a>
                                    </td>
                                    <td class="p-3 font-mono text-slate-300">
                                        {{ $comp->admin_phone }} <span class="text-slate-500">({{ $comp->admin_pin }})</span>
                                    </td>
                                    <td class="p-3 font-bold text-slate-200">{{ $comp->employees_count }}</td>
                                    <td class="p-3">
                                        <form action="{{ route('superadmin.company.toggle', $comp->id) }}" method="POST">
                                            @csrf
                                            @if($comp->status === 'active')
                                                <button type="submit" class="px-3 py-1 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white border border-rose-500/40 rounded-lg text-[10px] font-bold transition">
                                                    ⛔ አግድ (Suspend)
                                                </button>
                                            @else
                                                <button type="submit" class="px-3 py-1 bg-emerald-600/20 hover:bg-emerald-600 text-emerald-300 hover:text-white border border-emerald-500/40 rounded-lg text-[10px] font-bold transition">
                                                    ✓ ክፈት (Activate)
                                                </button>
                                            @endif
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-500">እስካሁን የተመዘገበ ድርጅት የለም። ከላይ ባለው ፎርም መመዝገብ ይችላሉ!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 2. የቀጥታ አንቀሳቃሽ ሰሌዳ ቅኝት (LIVE CAROUSEL PREVIEW - 4.5s) -->
            <!-- ============================================================== -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-4"
                 x-data="adCarouselHandler({{ $activeAds->toJson() }})"
                 x-init="startTimer()">

                <div class="flex flex-wrap justify-between items-center gap-2 border-b border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            🎡 የቀጥታ አንቀሳቃሽ ሰሌዳ ቅኝት (Live Carousel Preview)
                        </h3>
                        <p class="text-xs text-slate-400 mt-0.5">በአሁኑ ሰዓት ሰራተኞችና አድሚኖች የሚያዩት ተንቀሳቃሽ ማስታወቂያ፡</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-blue-500/20 text-blue-300 border border-blue-500/40 rounded-full text-xs font-mono font-semibold flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                            በየ 4.5 ሰከንድ ይንሸራተታል
                        </span>
                    </div>
                </div>

                <!-- Carousel Display Window -->
                <div class="relative w-full h-48 md:h-64 bg-slate-950 rounded-2xl overflow-hidden border border-slate-800 flex items-center justify-center">
                    <template x-if="ads.length > 0">
                        <div class="w-full h-full relative group">
                            <img :src="ads[currentIndex].banner_image" 
                                 class="w-full h-full object-cover transition-opacity duration-700 ease-in-out">
                            
                            <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/20 to-transparent flex flex-col justify-end p-5">
                                <span class="px-2 py-0.5 bg-amber-500 text-slate-950 text-[10px] font-black rounded w-fit mb-1">ስፖንሰር</span>
                                <h4 class="text-base font-bold text-white" x-text="ads[currentIndex].title"></h4>
                                <p class="text-xs text-blue-400 font-mono mt-0.5" x-text="ads[currentIndex].target_url"></p>
                            </div>
                        </div>
                    </template>

                    <template x-if="ads.length === 0">
                        <div class="text-center p-6 text-slate-500">
                            <p class="text-sm">ምንም ንቁ ማስታወቂያ የለም። ከስር አዲስ ፖስተር ይጫኑ!</p>
                        </div>
                    </template>

                    <div class="absolute bottom-3 right-4 flex gap-1.5" x-show="ads.length > 1">
                        <template x-for="(item, index) in ads" :key="index">
                            <button @click="setIndex(index)" 
                                    :class="index === currentIndex ? 'bg-amber-400 w-5' : 'bg-slate-600 w-2'" 
                                    class="h-2 rounded-full transition-all duration-300"></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- ============================================================== -->
            <!-- 3. የማስታወቂያዎች አስተዳደር (AD CAMPAIGNS - UPLOAD & MANAGE) -->
            <!-- ============================================================== -->
            <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-2xl space-y-6">
                <div class="flex flex-wrap justify-between items-center gap-2">
                    <div>
                        <h3 class="text-base font-bold text-white flex items-center gap-2">
                            📢 የማስታወቂያዎች አስተዳደር (Ad Campaigns)
                        </h3>
                        <p class="text-xs text-slate-400">ማስታወቂያዎችን በቀጥታ ከስልክዎ ወይም ከኮምፒውተርዎ Upload ያድርጉ</p>
                    </div>
                </div>

                <!-- Upload Ad Form -->
                <div class="bg-slate-800/50 border border-slate-700/70 p-5 rounded-2xl">
                    <h4 class="text-xs font-bold text-amber-300 uppercase tracking-wider mb-3">➕ አዲስ ፖስተር ጫን (Upload Ad)</h4>
                    
                    <form action="{{ route('superadmin.ad.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 text-xs">
                        @csrf
                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">የማስታወቂያ ርዕስ</label>
                            <input type="text" name="title" placeholder="ለምሳሌ፡ ቴሌብር ሱፐርአፕ" required 
                                   class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white">
                        </div>

                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">ፖስተር ከስልክ/ኮምፒውተር ይምረጡ</label>
                            <input type="file" name="ad_file" accept="image/*" required
                                   class="w-full text-xs text-slate-400 file:mr-2 file:py-2 file:px-3 file:rounded-xl file:border-0 file:bg-blue-600 file:text-white file:text-xs file:font-bold hover:file:bg-blue-500 cursor-pointer">
                        </div>

                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">የሚወስደው ሊንክ (Target URL) ወይም ስልክ</label>
                            <input type="text" name="target_url" placeholder="https://... ወይም 0911223344" required 
                                   class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white font-mono">
                        </div>

                        <div>
                            <label class="block text-slate-300 mb-1 font-semibold">ዒላማ (የማሳያ ቦታ)</label>
                            <select name="placement" class="w-full px-3 py-2.5 bg-slate-900 border border-slate-700 rounded-xl text-white">
                                <option value="all">ለሁሉም (ሰራተኞች + አድሚኖች)</option>
                                <option value="employee_dashboard">ለሰራተኞች ዳሽቦርድ ብቻ</option>
                                <option value="admin_dashboard">ለአድሚኖች ዳሽቦርድ ብቻ</option>
                            </select>
                        </div>

                        <div class="md:col-span-4 text-right pt-2 border-t border-slate-700/60">
                            <button type="submit" class="px-6 py-3 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/30 transition text-xs">
                                🚀 አዲስ ፖስተር ጫን (Upload Ad)
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Ad Campaigns Table -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ፖስተር</th>
                                <th class="p-3">ዒላማ</th>
                                <th class="p-3">የጊዜ ገደብ</th>
                                <th class="p-3">እይታ / ክሊክ</th>
                                <th class="p-3">ሁኔታ</th>
                                <th class="p-3">እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($ads as $ad)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $ad->banner_image }}" class="h-12 w-24 object-cover rounded-xl border border-slate-700 shadow-md">
                                            <div>
                                                <p class="font-bold text-white text-xs">{{ $ad->title }}</p>
                                                <a href="{{ $ad->target_url }}" target="_blank" class="text-[10px] text-blue-400 font-mono hover:underline">
                                                    {{ Str::limit($ad->target_url, 25) }} ↗
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-3 text-slate-300">
                                        @if($ad->placement === 'all')
                                            <span class="px-2 py-0.5 rounded bg-blue-500/20 text-blue-300 font-semibold text-[10px]">ለሁሉም</span>
                                        @elseif($ad->placement === 'employee_dashboard')
                                            <span class="px-2 py-0.5 rounded bg-amber-500/20 text-amber-300 font-semibold text-[10px]">ለሰራተኞች</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded bg-purple-500/20 text-purple-300 font-semibold text-[10px]">ለአድሚኖች</span>
                                        @endif
                                    </td>
                                    <td class="p-3 text-slate-400 font-mono text-[11px]">
                                        ያልተገደበ (Unlimited)
                                    </td>
                                    <td class="p-3 text-slate-300">
                                        <span class="font-bold text-amber-400">{{ $ad->views_count }}</span> እይታ / 
                                        <span class="font-bold text-emerald-400">{{ $ad->clicks_count }}</span> ክሊክ
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2.5 py-1 rounded-full text-[10px] font-bold {{ $ad->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
                                            {{ $ad->is_active ? '● በመታየት ላይ' : '○ የቆመ' }}
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex items-center gap-2">
                                            <form action="{{ route('superadmin.ad.toggle', $ad->id) }}" method="POST">
                                                @csrf
                                                <button class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-amber-300 text-[10px] font-bold transition">
                                                    {{ $ad->is_active ? 'አቁም' : 'አሳይ' }}
                                                </button>
                                            </form>
                                            <form action="{{ route('superadmin.ad.delete', $ad->id) }}" method="POST" onsubmit="return confirm('ይህ ማስታወቂያ ይሰረዝ?')">
                                                @csrf
                                                <button class="px-2.5 py-1 rounded bg-rose-950/40 hover:bg-rose-600 text-rose-300 hover:text-white text-[10px] font-bold transition">
                                                    አጥፋ
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="p-6 text-center text-slate-500">ምንም የተመዘገበ ማስታወቂያ የለም።</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- POWERED BY MELA SOLUTION FOOTER -->
    <footer class="text-center py-6 border-t border-slate-800/80 text-xs text-slate-400 mt-8">
        <p class="font-semibold text-slate-300">
            Powered by <span class="text-blue-400 font-bold">Mela Solution</span>
        </p>
        <p class="text-[11px] text-slate-400 mt-0.5">
            📞 ድጋፍና እገዛ፡ <a href="tel:0913064239" class="text-amber-400 hover:underline">0913064239</a> / <a href="tel:0703064239" class="text-amber-400 hover:underline">0703064239</a>
        </p>
    </footer>

    <!-- 4.5s Live Carousel Script -->
    <script>
        function adCarouselHandler(adsList) {
            return {
                ads: adsList,
                currentIndex: 0,
                timer: null,

                startTimer() {
                    if (this.ads.length <= 1) return;
                    this.timer = setInterval(() => {
                        this.currentIndex = (this.currentIndex + 1) % this.ads.length;
                    }, 4500); // 4.5 ሰከንድ
                },

                setIndex(idx) {
                    this.currentIndex = idx;
                    clearInterval(this.timer);
                    this.startTimer();
                }
            }
        }
    </script>
</body>
</html>
