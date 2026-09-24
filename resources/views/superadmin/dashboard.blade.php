<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStaff Super Admin | Master SaaS Control</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

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
                ● ሰርቨር በመስራት ላይ (Clever Cloud MySQL)
            </span>
        </div>
    </nav>

    <main class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500 text-emerald-300 rounded-2xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Global Metrics -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                <p class="text-xs text-slate-400">ጠቅላላ ድርጅቶች (Companies)</p>
                <p class="text-2xl font-black text-white mt-1">{{ $totalCompanies }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                <p class="text-xs text-slate-400">ንቁ ድርጅቶች (Active)</p>
                <p class="text-2xl font-black text-emerald-400 mt-1">{{ $activeCompanies }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                <p class="text-xs text-slate-400">የተመዘገቡ ሰራተኞች</p>
                <p class="text-2xl font-black text-blue-400 mt-1">{{ $totalEmployees }}</p>
            </div>
            <div class="bg-slate-900 border border-slate-800 p-5 rounded-3xl">
                <p class="text-xs text-slate-400">የማስታወቂያ እይታዎች (Ad Views)</p>
                <p class="text-2xl font-black text-amber-400 mt-1">{{ $totalAdViews }} <span class="text-xs text-slate-400 font-normal">({{ $totalAdClicks }} Clicks)</span></p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Companies List Table -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        🏢 የተመዘገቡ ድርጅቶች ዝርዝር እና የቁጥጥር ሰሌዳ
                    </h3>
                    <span class="text-xs text-slate-400">{{ $companies->count() }} ድርጅቶች</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ድርጅት</th>
                                <th class="p-3">ልዩ ሊንክ (SaaS Portal)</th>
                                <th class="p-3">አድሚን ስልክ/PIN</th>
                                <th class="p-3">ሰራተኞች</th>
                                <th class="p-3">ሁኔታ / እርምጃ</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($companies as $comp)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-bold text-white">
                                        {{ $comp->company_name }}
                                    </td>
                                    <td class="p-3">
                                        <a href="{{ url('/c/' . $comp->slug) }}" target="_blank" class="text-blue-400 hover:underline font-mono text-[11px]">
                                            /c/{{ $comp->slug }} ↗
                                        </a>
                                    </td>
                                    <td class="p-3 font-mono text-slate-300">
                                        {{ $comp->admin_phone }} <span class="text-slate-500">({{ $comp->admin_pin }})</span>
                                    </td>
                                    <td class="p-3 text-slate-300 font-bold">
                                        {{ $comp->employees_count }}
                                    </td>
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
                                    <td colspan="5" class="p-6 text-center text-slate-500">እስካሁን የተመዘገበ ድርጅት የለም።</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- New Company Registration -->
            <div class="space-y-6">

                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-bold text-white mb-3 flex items-center gap-1.5">
                        ➕ አዲስ ድርጅት መመዝገቢያ (Generate Link)
                    </h3>
                    <form action="{{ route('superadmin.company.store') }}" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block text-slate-400 mb-1">የድርጅቱ ስም</label>
                            <input type="text" name="company_name" placeholder="ለምሳሌ፡ ፀሐይ ባንክ" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">የአድሚን ስልክ ቁጥር</label>
                            <input type="tel" name="admin_phone" placeholder="0911223344" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">የአድሚን ሚስጥር PIN (4-6 አሃዝ)</label>
                            <input type="text" name="admin_pin" placeholder="1234" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-xl transition">
                            ድርጅቱን ፍጠርና ሊንክ አውጣ
                        </button>
                    </form>
                </div>

            </div>

        </div>

        <!-- Ad Network Management Section (የማስታወቂያ ሰሌዳ) -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-6">
            <div class="flex flex-wrap justify-between items-center gap-2">
                <div>
                    <h3 class="text-base font-bold text-white flex items-center gap-2">
                        📢 የማስታወቂያ ሰሌዳ አስተዳደር (Ad Network - የገቢ ምንጭ)
                    </h3>
                    <p class="text-xs text-slate-400">እዚህ የሚጫኑ ማስታወቂያዎች በሁሉም ድርጅቶች ሰራተኞች ዳሽቦርድ ላይ ይታያሉ።</p>
                </div>
            </div>

            <!-- Add New Ad Form -->
            <form action="{{ route('superadmin.ad.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-3 bg-slate-800/40 p-4 rounded-2xl border border-slate-700/60 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">የማስታወቂያው ርዕስ</label>
                    <input type="text" name="title" placeholder="ለምሳሌ፡ ቴሌብር ሱፐርአፕ" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">የባነር ፎቶ Link (Image URL)</label>
                    <input type="url" name="banner_image" placeholder="https://example.com/banner.jpg" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">የሚወስደው ሊንክ (Target URL)</label>
                    <input type="url" name="target_url" placeholder="https://telebirr.et" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 font-bold text-white rounded-xl transition">
                        ➕ ማስታወቂያውን ለጥፍ
                    </button>
                </div>
            </form>

            <!-- Active Ads Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="p-3">ቅድመ እይታ</th>
                            <th class="p-3">ርዕስ</th>
                            <th class="p-3">ኢላማ ሊንክ</th>
                            <th class="p-3">እይታ / ክሊክ</th>
                            <th class="p-3">ሁኔታ</th>
                            <th class="p-3">እርምጃ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($ads as $ad)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3">
                                    <img src="{{ $ad->banner_image }}" class="h-10 w-24 object-cover rounded-lg border border-slate-700" alt="ad">
                                </td>
                                <td class="p-3 font-bold text-white">{{ $ad->title }}</td>
                                <td class="p-3 font-mono text-blue-400">
                                    <a href="{{ $ad->target_url }}" target="_blank" class="hover:underline">{{ Str::limit($ad->target_url, 30) }}</a>
                                </td>
                                <td class="p-3 text-slate-300">
                                    <span class="font-bold text-amber-400">{{ $ad->views_count }}</span> እይታ / <span class="font-bold text-emerald-400">{{ $ad->clicks_count }}</span> ክሊክ
                                </td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $ad->is_active ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-700 text-slate-400' }}">
                                        {{ $ad->is_active ? 'የሚታይ' : 'የቆመ' }}
                                    </span>
                                </td>
                                <td class="p-3 flex items-center gap-2">
                                    <form action="{{ route('superadmin.ad.toggle', $ad->id) }}" method="POST">
                                        @csrf
                                        <button class="text-xs text-amber-400 hover:underline">
                                            {{ $ad->is_active ? 'አቁም' : 'አሳይ' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('superadmin.ad.delete', $ad->id) }}" method="POST">
                                        @csrf
                                        <button class="text-xs text-rose-400 hover:underline ml-2">አጥፋ</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-4 text-center text-slate-500">ምንም የተለጠፈ ማስታወቂያ የለም።</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </main>

</body>
</html>
