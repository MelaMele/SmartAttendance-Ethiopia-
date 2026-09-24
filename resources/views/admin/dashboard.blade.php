<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStaff Admin | ዋና መቆጣጠሪያ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <!-- Top Admin Bar -->
    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex flex-wrap justify-between items-center gap-4">
        <div class="flex items-center gap-3">
            <span class="p-2 bg-blue-600 rounded-xl font-black text-white text-lg">M</span>
            <div>
                <h1 class="text-base font-bold text-white leading-tight">SmartStaff Admin Portal</h1>
                <p class="text-xs text-blue-400">{{ $setting->company_name }} • Geofenced ({{ $setting->allowed_radius_meters }}m)</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.employees') }}" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-xl border border-slate-700 transition">
                👥 ሰራተኞች ማስተዳደሪያ ({{ $totalEmployees }})
            </a>
            <a href="{{ route('admin.attendance.export.monthly', ['month' => $selectedMonth]) }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                📥 የወር ማጠቃለያ ሪፖርት አውርድ (Excel)
            </a>
        </div>
    </nav>

    <!-- Sponsored Ad Network Banner (የገቢ ምንጭ ማስታወቂያ ሰሌዳ) -->
    @if(isset($adminAd) && $adminAd)
        <div class="max-w-7xl mx-auto px-6 pt-4">
            <div class="bg-slate-900 border border-slate-800 rounded-2xl p-2.5 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="px-2 py-0.5 bg-amber-500/20 text-amber-300 text-[10px] font-bold rounded">የተደገፈ / Ad</span>
                    <span class="text-xs font-semibold text-white">{{ $adminAd->title }}</span>
                </div>
                <a href="{{ route('ad.click', $adminAd->id) }}" target="_blank" class="px-3 py-1 bg-blue-600 hover:bg-blue-500 text-white rounded-lg text-xs font-bold transition">
                    ይመልከቱ ↗
                </a>
            </div>
        </div>
    @endif

    <main class="max-w-7xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500 text-emerald-300 rounded-2xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Summary Banner -->
        <div class="bg-gradient-to-r from-blue-900/50 via-indigo-900/40 to-slate-900 border border-blue-800/40 rounded-3xl p-6 flex flex-wrap justify-between items-center gap-4">
            <div>
                <span class="text-xs text-blue-300 uppercase tracking-widest font-bold">የዕለቱ ቀን ቅንጅት</span>
                <h2 class="text-2xl font-black text-white mt-1">{{ $todayEc }}</h2>
                <p class="text-xs text-slate-400">{{ $todayGc }} (Gregorian)</p>
            </div>
            <div class="flex flex-wrap gap-3 text-center">
                <div class="bg-slate-800/70 border border-slate-700 px-4 py-2.5 rounded-2xl">
                    <p class="text-[11px] text-slate-400">የገቡ (Present)</p>
                    <p class="text-xl font-bold text-emerald-400">{{ $presentCount }}</p>
                </div>
                <div class="bg-slate-800/70 border border-slate-700 px-4 py-2.5 rounded-2xl">
                    <p class="text-[11px] text-slate-400">ያረፈዱ (Late)</p>
                    <p class="text-xl font-bold text-amber-400">{{ $lateCount }}</p>
                </div>
                <div class="bg-slate-800/70 border border-slate-700 px-4 py-2.5 rounded-2xl">
                    <p class="text-[11px] text-slate-400">በፈቃድ ላይ (Leave)</p>
                    <p class="text-xl font-bold text-blue-400">{{ $onLeaveCount }}</p>
                </div>
                <div class="bg-slate-800/70 border border-slate-700 px-4 py-2.5 rounded-2xl">
                    <p class="text-[11px] text-slate-400">ያልገቡ (Absent)</p>
                    <p class="text-xl font-bold text-rose-400">{{ $absentCount }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Today's Attendance Table with Status Changer -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        📍 የዛሬ የቀጥታ አቴንዳንስ (ሁኔታ መቀየሪያ ፖፕ-አፕ ያለው)
                    </h3>
                    <span class="text-xs text-slate-400">{{ $todayAttendances->count() }} የተመዘገቡ</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ሰራተኛ</th>
                                <th class="p-3">መግቢያ</th>
                                <th class="p-3">መውጫ</th>
                                <th class="p-3">ሁኔታ (ቀይር)</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-800">
                            @forelse($todayAttendances as $att)
                                <tr class="hover:bg-slate-800/40 transition">
                                    <td class="p-3 font-semibold text-white">
                                        {{ $att->employee->full_name }}
                                        <span class="block text-[10px] text-slate-400 font-normal">{{ $att->employee->phone_number }}</span>
                                    </td>
                                    <td class="p-3 text-emerald-400 font-mono">{{ $att->check_in_at ? $att->check_in_at->format('h:i A') : '-' }}</td>
                                    <td class="p-3 text-amber-400 font-mono">{{ $att->check_out_at ? $att->check_out_at->format('h:i A') : '-' }}</td>
                                    <td class="p-3">
                                        <!-- Quick Status Dropdown (Present, Late, Absent, Permission) -->
                                        <form action="{{ route('admin.attendance.status', $att->id) }}" method="POST" class="inline">
                                            @csrf
                                            <select name="status" onchange="this.form.submit()"
                                                    class="px-2 py-1 bg-slate-800 border border-slate-700 rounded-lg text-xs font-bold 
                                                    {{ $att->status === 'present' ? 'text-emerald-400' : ($att->status === 'late' ? 'text-amber-400' : ($att->status === 'absent' ? 'text-rose-400' : 'text-blue-400')) }}">
                                                <option value="present" {{ $att->status === 'present' ? 'selected' : '' }}>Present (በሰዓቱ)</option>
                                                <option value="late" {{ $att->status === 'late' ? 'selected' : '' }}>Late (አርፍዷል)</option>
                                                <option value="absent" {{ $att->status === 'absent' ? 'selected' : '' }}>Absent (ቀሪ)</option>
                                                <option value="on_leave" {{ $att->status === 'on_leave' ? 'selected' : '' }}>Permission (ፈቃድ)</option>
                                            </select>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-6 text-center text-slate-500">ለዛሬ እስካሁን የገባ ሰራተኛ የለም።</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- 1-Click GPS & Messaging Controls -->
            <div class="space-y-6">

                <!-- 1-Click Auto GPS Capture -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-bold text-white mb-2">🌐 የ 100m ጂፒኤስ እና የስራ ሰዓት</h3>
                    <div class="mb-3 p-2.5 bg-blue-950/50 border border-blue-800/60 rounded-xl">
                        <button type="button" onclick="captureAdminLocation()" id="gpsCaptureBtn"
                                class="w-full py-2 bg-emerald-600 hover:bg-emerald-500 text-white font-bold rounded-lg text-xs transition">
                            📍 ያለሁበትን ቦታ እንደ ድርጅቱ GPS ውሰድ (1-Click)
                        </button>
                    </div>

                    <form action="{{ route('admin.geofence.update') }}" method="POST" class="space-y-2 text-xs">
                        @csrf
                        <input type="hidden" name="company_name" value="{{ $setting->company_name }}">
                        <div class="grid grid-cols-2 gap-2">
                            <input type="text" id="adminLat" name="latitude" value="{{ $setting->latitude }}" required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white font-mono" placeholder="Lat">
                            <input type="text" id="adminLng" name="longitude" value="{{ $setting->longitude }}" required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white font-mono" placeholder="Lng">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <input type="time" name="work_start_time" value="{{ $setting->work_start_time }}" required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white">
                            <input type="time" name="work_end_time" value="{{ $setting->work_end_time }}" required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white">
                        </div>
                        <input type="number" name="allowed_radius_meters" value="{{ $setting->allowed_radius_meters }}" required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white font-mono" placeholder="ራዲየስ (ሜትር)">
                        <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-lg transition">
                            ቅንብሩን መዝግብ / Save
                        </button>
                    </form>
                </div>

                <!-- 1-on-1 & Broadcast Messaging -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-bold text-white mb-2">💬 መልእክት መላኪያ</h3>
                    <form action="{{ route('admin.announcement.post') }}" method="POST" class="space-y-2 text-xs">
                        @csrf
                        <select name="employee_id" class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white">
                            <option value="all">📢 ለሁሉም ሰራተኞች</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">👤 {{ $emp->full_name }}</option>
                            @endforeach
                        </select>
                        <input type="text" name="title" placeholder="ርዕስ..." required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white">
                        <textarea name="message" rows="2" placeholder="መልእክት..." required class="w-full px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-white"></textarea>
                        <button type="submit" class="w-full py-2 bg-indigo-600 hover:bg-indigo-500 font-bold text-white rounded-lg transition">
                            መልእክቱን ላክ
                        </button>
                    </form>
                </div>

            </div>

        </div>

        <!-- ከመስከረም - ጳጉሜን የወርሃዊ ማህደር ፎልደሮች (Month Folders 1-13) -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white flex items-center gap-2">
                📁 ከመስከረም - ጳጉሜን የወርሃዊ መልእክቶች ማህደር (Archive Folders)
            </h3>

            <!-- Months Folder Tabs -->
            @php
                $ethMonths = [
                    1 => 'መስከረም', 2 => 'ጥቅምት', 3 => 'ህዳር', 4 => 'ታህሳስ',
                    5 => 'ጥር', 6 => 'የካቲት', 7 => 'መጋቢት', 8 => 'ሚያዚያ',
                    9 => 'ግንቦት', 10 => 'ሰኔ', 11 => 'ሐምሌ', 12 => 'ነሐሴ', 13 => 'ጳጉሜን'
                ];
            @endphp
            <div class="flex flex-wrap gap-2 pb-2 border-b border-slate-800">
                @foreach($ethMonths as $mNum => $mName)
                    <a href="{{ route('admin.dashboard', ['month' => $mNum]) }}"
                       class="px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5
                       {{ $selectedMonth == $mNum ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'bg-slate-800 text-slate-400 hover:text-white' }}">
                        📁 {{ $mName }}
                    </a>
                @endforeach
            </div>

            <!-- Filtered Month Announcements -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                @forelse($monthAnnouncements as $mAnn)
                    <div class="p-3 bg-slate-800/60 border border-slate-700/60 rounded-xl space-y-1">
                        <div class="flex justify-between items-center">
                            <span class="font-bold text-white text-xs">{{ $mAnn->title }}</span>
                            <span class="text-[10px] text-slate-400">{{ $mAnn->date_ec }}</span>
                        </div>
                        <p class="text-xs text-slate-300">{{ $mAnn->message }}</p>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-3 col-span-3">በዚህ ወር የተመዘገበ መልእክት የለም።</p>
                @endforelse
            </div>
        </div>

    </main>

    <script>
        function captureAdminLocation() {
            if (!navigator.geolocation) return alert("ጂፒኤስ አይደገፍም!");
            navigator.geolocation.getCurrentPosition((pos) => {
                document.getElementById('adminLat').value = pos.coords.latitude.toFixed(8);
                document.getElementById('adminLng').value = pos.coords.longitude.toFixed(8);
                alert("✓ ቦታዎ ተይዟል! አሁን 'ቅንብሩን መዝግብ' ይጫኑ");
            });
        }
    </script>
</body>
</html>
