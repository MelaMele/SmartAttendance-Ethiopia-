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
                👥 ሰራተኞች ({{ $totalEmployees }})
            </a>
            <a href="{{ route('admin.attendance.export') }}" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-xs font-semibold text-white rounded-xl shadow-lg shadow-emerald-600/30 transition flex items-center gap-1.5">
                📥 ሪፖርት አውርድ (Excel/CSV)
            </a>
        </div>
    </nav>

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

            <!-- Today's Attendance Table -->
            <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-white flex items-center gap-2">
                        📍 የዛሬ የቀጥታ አቴንዳንስ ሙሉ ዝርዝር (Live Attendance)
                    </h3>
                    <span class="text-xs text-slate-400">{{ $todayAttendances->count() }} የተመዘገቡ</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                            <tr>
                                <th class="p-3">ሰራተኛ</th>
                                <th class="p-3">መግቢያ</th>
                                <th class="p-3">ርቀት</th>
                                <th class="p-3">መውጫ / ቀድሞ የወጣበት ምክንያት</th>
                                <th class="p-3">ሁኔታ</th>
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
                                    <td class="p-3 font-mono text-slate-300">{{ $att->check_in_distance_meters }}m</td>
                                    <td class="p-3">
                                        <span class="text-amber-400 font-mono">{{ $att->check_out_at ? $att->check_out_at->format('h:i A') : '-' }}</span>
                                        @if($att->early_leave_reason)
                                            <div class="mt-1 p-1.5 bg-slate-800 border border-slate-700 rounded text-[11px] text-slate-300">
                                                <span>⚠️ ምክንያት፡ {{ $att->early_leave_reason }}</span>
                                                @if(!$att->early_leave_approved)
                                                    <form action="{{ route('admin.early.approve', $att->id) }}" method="POST" class="inline ml-1">
                                                        @csrf
                                                        <button class="text-[10px] text-emerald-400 font-bold underline">አጽድቅ</button>
                                                    </form>
                                                @else
                                                    <span class="text-[10px] text-emerald-400 font-bold ml-1">✓ ጸድቋል</span>
                                                @endif
                                            </div>
                                        @endif
                                    </td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $att->status === 'present' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300' }}">
                                            {{ $att->status === 'present' ? 'በሰዓቱ' : 'አርፍዷል' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="p-6 text-center text-slate-500">ለዛሬ እስካሁን የገባ ሰራተኛ የለም።</td>
                                </tr>
                            @endforelse

                            <!-- በፈቃድ ላይ ያሉ ሰራተኞች ዝርዝር -->
                            @foreach($onLeaveToday as $lv)
                                <tr class="bg-blue-950/20">
                                    <td class="p-3 font-semibold text-white">
                                        {{ $lv->employee->full_name }}
                                        <span class="block text-[10px] text-blue-400 font-normal">በፈቃድ ላይ ({{ $lv->leave_type }})</span>
                                    </td>
                                    <td colspan="3" class="p-3 text-xs text-blue-300 italic">"{{ $lv->reason }}"</td>
                                    <td class="p-3">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-500/20 text-blue-300">በፈቃድ ላይ</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Controls (Messages & GPS) -->
            <div class="space-y-6">

                <!-- 1-on-1 & Broadcast Messaging Box -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-bold text-white mb-3 flex items-center gap-2">
                        💬 መልእክት መላኪያ (ለሁሉም ወይም ለብቻ)
                    </h3>
                    <form action="{{ route('admin.announcement.post') }}" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <label class="block text-slate-400 mb-1">ተቀባይ</label>
                            <select name="employee_id" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                                <option value="all">📢 ለሁሉም ሰራተኞች (Broadcast)</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">👤 {{ $emp->full_name }} ({{ $emp->phone_number }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <input type="text" name="title" placeholder="የመልእክቱ ርዕስ..." required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        </div>
                        <div>
                            <textarea name="message" rows="2" placeholder="መልእክቱን እዚህ ይጻፉ..." required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white"></textarea>
                        </div>
                        <div class="flex justify-between items-center">
                            <select name="priority" class="px-2 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-slate-300 text-xs">
                                <option value="normal">መደበኛ</option>
                                <option value="urgent">አስቸኳይ</option>
                            </select>
                            <button type="submit" class="px-4 py-1.5 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-lg transition">
                                ላክ
                            </button>
                        </div>
                    </form>
                </div>

                <!-- GPS & Shift Timing Settings Form -->
                <div class="bg-slate-900 border border-slate-800 rounded-3xl p-5 shadow-xl">
                    <h3 class="text-xs font-bold text-white mb-3 flex items-center gap-2">
                        🌐 የ 100m ጂፒኤስ እና የስራ ሰዓት ቅንብር
                    </h3>
                    <form action="{{ route('admin.geofence.update') }}" method="POST" class="space-y-3 text-xs">
                        @csrf
                        <div>
                            <input type="text" name="company_name" value="{{ $setting->company_name }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-400 mb-1">ኬክሮስ (Lat)</label>
                                <input type="text" name="latitude" value="{{ $setting->latitude }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-1">ኬንትሮስ (Lng)</label>
                                <input type="text" name="longitude" value="{{ $setting->longitude }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="block text-slate-400 mb-1">መግቢያ ሰዓት</label>
                                <input type="time" name="work_start_time" value="{{ $setting->work_start_time }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                            </div>
                            <div>
                                <label class="block text-slate-400 mb-1">መውጫ ሰዓት</label>
                                <input type="time" name="work_end_time" value="{{ $setting->work_end_time }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                            </div>
                        </div>
                        <div>
                            <label class="block text-slate-400 mb-1">የተፈቀደ ራዲየስ (ሜትር)</label>
                            <input type="number" name="allowed_radius_meters" value="{{ $setting->allowed_radius_meters }}" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white font-mono">
                        </div>
                        <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-xl transition">
                            ቅንብሩን መዝግብ
                        </button>
                    </form>
                </div>

            </div>

        </div>

        <!-- Pending Leave Approvals Section -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <h3 class="text-sm font-bold text-white mb-4 flex items-center gap-2">
                ⏳ ውሳኔ የሚጠብቁ የፈቃድ ጥያቄዎች ({{ $pendingLeaves->count() }})
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($pendingLeaves as $leave)
                    <div class="bg-slate-800/60 border border-slate-700/60 rounded-2xl p-4 space-y-3">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-white text-sm">{{ $leave->employee->full_name }}</h4>
                                <span class="text-xs text-blue-400 font-semibold">{{ $leave->leave_type }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400">{{ $leave->start_date_ec }}</span>
                        </div>
                        <p class="text-xs text-slate-300 italic">"{{ $leave->reason }}"</p>
                        
                        <form action="{{ route('admin.leave.status', $leave->id) }}" method="POST" class="space-y-2 pt-2 border-t border-slate-700">
                            @csrf
                            <input type="text" name="admin_remark" placeholder="አስተያየት ካለ እዚህ ይጻፉ..." class="w-full px-2.5 py-1.5 bg-slate-900 border border-slate-700 rounded-lg text-xs text-white">
                            <div class="flex gap-2">
                                <button type="submit" name="status" value="approved" class="flex-1 py-1.5 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-bold transition">
                                    ፍቀድ
                                </button>
                                <button type="submit" name="status" value="rejected" class="flex-1 py-1.5 bg-rose-600 hover:bg-rose-500 text-white rounded-lg text-xs font-bold transition">
                                    ከልክል
                                </button>
                            </div>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-slate-500 py-3 col-span-3">በአሁኑ ሰዓት ምንም ያልጸደቀ የፈቃድ ጥያቄ የለም።</p>
                @endforelse
            </div>
        </div>

    </main>

</body>
</html>
