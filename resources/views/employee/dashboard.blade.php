<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ዳሽቦርድ | {{ $employee->full_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen pb-20">

    <!-- Top Bar -->
    <header class="bg-slate-800 border-b border-slate-700 px-4 py-3 sticky top-0 z-30 flex justify-between items-center shadow-md">
        <div>
            <h2 class="text-base font-bold text-white leading-tight">{{ $employee->full_name }}</h2>
            <p class="text-xs text-blue-400">{{ $employee->position ?? 'ሰራተኛ' }} • {{ $employee->department ?? 'General' }}</p>
        </div>
        <form action="{{ route('employee.logout') }}" method="POST">
            @csrf
            <button type="submit" class="px-3 py-1.5 bg-slate-700 hover:bg-rose-600/30 text-rose-300 text-xs rounded-lg transition border border-rose-500/30">
                ውጣ
            </button>
        </form>
    </header>

    <main class="max-w-lg mx-auto p-4 space-y-4" x-data="autoAttendanceHandler()" x-init="initGps()">

        <!-- Ethiopian Calendar Card -->
        <div class="bg-gradient-to-r from-blue-900/60 to-indigo-900/60 border border-blue-700/40 rounded-2xl p-4 shadow-lg flex items-center justify-between">
            <div>
                <span class="text-[11px] text-blue-300 uppercase tracking-wider font-semibold">የዛሬ ቀን (ዓ.ም / G.C)</span>
                <p class="text-lg font-bold text-white mt-0.5">{{ $todayEc }}</p>
                <p class="text-xs text-slate-300">{{ $todayGc }}</p>
            </div>
            <div class="text-right">
                <!-- Auto GPS Live Indicator -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold"
                     :class="gpsStatus === 'ready' ? (withinRadius ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/40' : 'bg-rose-500/20 text-rose-300 border border-rose-500/40') : 'bg-slate-700 text-slate-300'">
                    <span class="w-2 h-2 rounded-full" :class="withinRadius ? 'bg-emerald-400 animate-pulse' : 'bg-rose-400'"></span>
                    <span x-text="gpsLabel">ጂፒኤስ በማንበብ ላይ...</span>
                </div>
            </div>
        </div>

        <!-- Notification Message Box -->
        <div x-show="message" x-cloak 
             :class="isSuccess ? 'bg-emerald-500/20 border-emerald-500 text-emerald-200' : 'bg-rose-500/20 border-rose-500 text-rose-200'"
             class="p-3.5 rounded-xl border text-xs font-semibold transition text-center"
             x-text="message">
        </div>

        @if(session('success'))
            <div class="p-3 bg-emerald-500/20 border border-emerald-500 text-emerald-200 rounded-xl text-xs text-center font-medium">
                {{ session('success') }}
            </div>
        @endif

        <!-- Check-in / Check-out Punch Card -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-xl text-center space-y-4">
            <div class="flex justify-between items-center border-b border-slate-700 pb-3">
                <span class="text-xs text-slate-400">የስራ ሰዓት፡ <b class="text-white">{{ $setting->work_start_time }} - {{ $setting->work_end_time }}</b></span>
                <span class="text-xs text-blue-400 font-mono">100m Geofence</span>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div class="bg-slate-700/50 p-3 rounded-xl border border-slate-600/50">
                    <p class="text-[11px] text-slate-400">መግቢያ (Check-in)</p>
                    <p class="text-base font-bold text-emerald-400 mt-1">
                        {{ $todayAttendance && $todayAttendance->check_in_at ? $todayAttendance->check_in_at->format('h:i A') : '--:--' }}
                    </p>
                </div>
                <div class="bg-slate-700/50 p-3 rounded-xl border border-slate-600/50">
                    <p class="text-[11px] text-slate-400">መውጫ (Check-out)</p>
                    <p class="text-base font-bold text-amber-400 mt-1">
                        {{ $todayAttendance && $todayAttendance->check_out_at ? $todayAttendance->check_out_at->format('h:i A') : '--:--' }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-3">
                @if(!$todayAttendance || !$todayAttendance->check_in_at)
                    <button @click="punch('check-in')" :disabled="loading || !canPunch"
                            class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold text-base rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2 transition">
                        <span x-show="!loading">📍 አሁን ገብቻለሁ (Check-in)</span>
                        <span x-show="loading" class="animate-pulse">በማረጋገጥ ላይ...</span>
                    </button>
                @elseif(!$todayAttendance->check_out_at)
                    <div x-data="{ showEarlyBox: false }">
                        <div x-show="showEarlyBox" class="mb-3 p-3 bg-slate-700 rounded-xl border border-slate-600 text-left space-y-2">
                            <label class="block text-xs text-amber-300 font-semibold">የስራ ሰዓት ከማለቁ በፊት የሚወጡበት ምክንያት፡</label>
                            <input type="text" x-model="earlyReason" placeholder="ለምሳሌ፡ ስራ ጨርሻለሁ / የታመምኩበት ጉዳይ አለ..." class="w-full px-3 py-2 bg-slate-800 border border-slate-600 rounded-lg text-xs text-white">
                        </div>

                        <button @click="handleCheckOutClick()" :disabled="loading || !canPunch"
                                class="w-full py-4 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-white font-bold text-base rounded-2xl shadow-lg shadow-amber-600/30 flex items-center justify-center space-x-2 transition">
                            <span x-show="!loading">🚪 ስራ ጨርሻለሁ ውጣ (Check-out)</span>
                            <span x-show="loading" class="animate-pulse">በማረጋገጥ ላይ...</span>
                        </button>
                    </div>
                @else
                    <div class="py-3 px-4 bg-emerald-900/30 border border-emerald-500/40 text-emerald-300 rounded-xl text-xs font-bold">
                        ✅ የዛሬውን የስራ ቀን በሰላም አጠናቀዋል!
                    </div>
                @endif
            </div>
        </div>

        <!-- Leave Status Tracker Box (ሰራተኛው ፈቃዱ ምላሽ ማግኘቱን የሚያይበት) -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-300 flex items-center gap-1.5">
                📋 የፈቃድ ጥያቄዎቼ ሁኔታ (Leave Status)
            </h3>

            @forelse($myLeaves as $leave)
                <div class="p-3 bg-slate-700/50 rounded-xl border border-slate-600/60 space-y-1.5 text-xs">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-white">{{ $leave->leave_type }}</span>
                        @if($leave->status === 'approved')
                            <span class="px-2 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-[10px]">🟢 ተፈቅዷል (Approved)</span>
                        @elseif($leave->status === 'rejected')
                            <span class="px-2 py-0.5 rounded-full bg-rose-500/20 text-rose-300 font-bold text-[10px]">🔴 ውድቅ ተደርጓል (Rejected)</span>
                        @else
                            <span class="px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 font-bold text-[10px]">🟡 በመጠባበቅ ላይ (Pending)</span>
                        @endif
                    </div>
                    <p class="text-[11px] text-slate-400">ቀን፡ {{ $leave->start_date_ec }} እስከ {{ $leave->end_date_ec }}</p>
                    @if($leave->admin_remark)
                        <div class="p-2 bg-slate-800/80 rounded-lg text-blue-300 text-[11px]">
                            <b>ከአድሚኑ አስተያየት፡</b> {{ $leave->admin_remark }}
                        </div>
                    @endif
                </div>
            @empty
                <p class="text-xs text-slate-500 py-2">እስካሁን የጠየቁት ፈቃድ የለም።</p>
            @endforelse
        </div>

        <!-- Leave Request Box -->
        <div x-data="{ open: false }" class="bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md">
            <button @click="open = !open" class="w-full flex justify-between items-center text-left">
                <span class="text-xs font-bold text-white flex items-center gap-2">
                    📝 አዲስ የፈቃድ ጥያቄ ማቅረቢያ
                </span>
                <span class="text-slate-400 font-bold" x-text="open ? '−' : '+'"></span>
            </button>

            <div x-show="open" x-cloak class="mt-4 pt-3 border-t border-slate-700 space-y-3">
                <form action="{{ route('employee.leave.submit') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">የፈቃድ አይነት</label>
                        <select name="leave_type" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white">
                            <option value="የህመም ፈቃድ">የህመም ፈቃድ</option>
                            <option value="አስቸኳይ ጉዳይ">አስቸኳይ ጉዳይ</option>
                            <option value="የዓመት ፈቃድ">የዓመት ፈቃድ</option>
                            <option value="ሌላ">ሌላ</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">የሚጀምርበት</label>
                            <input type="date" name="start_date_gc" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">የሚያበቃበት</label>
                            <input type="date" name="end_date_gc" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-300 mb-1">ምክንያት</label>
                        <textarea name="reason" rows="2" required placeholder="ምክንያቶን በአጭሩ ይግለጹ..." class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white placeholder-slate-400"></textarea>
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition">
                        የፈቃድ ጥያቄውን ላክ
                    </button>
                </form>
            </div>
        </div>

        <!-- Personal & Broadcast Messages -->
        @if($announcements->count() > 0)
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    📬 ከአስተዳዳሪው የተላኩ መልእክቶች
                </h4>
                @foreach($announcements as $notice)
                    <div class="p-3 bg-slate-700/50 border-l-4 {{ $notice->employee_id ? 'border-amber-500' : 'border-blue-500' }} rounded-r-xl">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-bold text-white flex items-center gap-1">
                                @if($notice->employee_id)
                                    <span class="px-1.5 py-0.5 bg-amber-500/20 text-amber-300 text-[10px] rounded">🔒 የግል መልእክት</span>
                                @endif
                                {{ $notice->title }}
                            </span>
                            <span class="text-[10px] text-slate-400">{{ $notice->date_ec }}</span>
                        </div>
                        <p class="text-xs text-slate-300">{{ $notice->message }}</p>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- Auto GPS & Smart Logic Script -->
    <script>
        function autoAttendanceHandler() {
            return {
                loading: false,
                message: '',
                isSuccess: false,
                userLat: null,
                userLng: null,
                withinRadius: false,
                canPunch: false,
                gpsStatus: 'locating',
                gpsLabel: 'ጂፒኤስ በማንበብ ላይ...',
                earlyReason: '',
                showEarlyInput: false,

                // የድርጅቱ መጋጠሚያዎች
                orgLat: {{ $setting->latitude }},
                orgLng: {{ $setting->longitude }},
                allowedRadius: {{ $setting->allowed_radius_meters }},
                workEndTime: "{{ $setting->work_end_time ?? '17:00:00' }}",

                initGps() {
                    if (!navigator.geolocation) {
                        this.gpsLabel = "GPS አይደገፍም";
                        return;
                    }

                    navigator.geolocation.watchPosition(
                        (pos) => {
                            this.userLat = pos.coords.latitude;
                            this.userLng = pos.coords.longitude;
                            const dist = this.getDistance(this.userLat, this.userLng, this.orgLat, this.orgLng);
                            
                            this.gpsStatus = 'ready';
                            if (dist <= this.allowedRadius) {
                                this.withinRadius = true;
                                this.canPunch = true;
                                this.gpsLabel = `📍 በቢሮ ክልል ውስጥ ነዎት (${dist}m)`;
                            } else {
                                this.withinRadius = false;
                                this.canPunch = false;
                                this.gpsLabel = `⚠️ ከቢሮ ውጭ ነዎት (${dist}m)`;
                            }
                        },
                        (err) => {
                            this.gpsLabel = "የስልክዎን Location ያብሩ";
                        },
                        { enableHighAccuracy: true, timeout: 10000, maximumAge: 5000 }
                    );
                },

                getDistance(lat1, lon1, lat2, lon2) {
                    const R = 6371000;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                    return Math.round(R * c);
                },

                handleCheckOutClick() {
                    const now = new Date();
                    const currentTime = now.toTimeString().split(' ')[0];
                    if (currentTime < this.workEndTime && !this.earlyReason) {
                        const reason = prompt("የስራ ሰዓት ከማለቁ በፊት እየወጡ ነው፤ ምክንያቶን ይጻፉ (ወይም 'ስራ ጨርሻለሁ' ይበሉ)፡");
                        if (!reason) return;
                        this.earlyReason = reason;
                    }
                    this.punch('check-out');
                },

                punch(action) {
                    if (!this.userLat || !this.userLng) {
                        alert("እባክዎ የጂፒኤስ መረጃ እስኪነበብ ጥቂት ሰከንድ ይጠብቁ!");
                        return;
                    }

                    this.loading = true;
                    const endpoint = action === 'check-in' 
                        ? "{{ route('employee.checkin') }}" 
                        : "{{ route('employee.checkout') }}";

                    fetch(endpoint, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ 
                            latitude: this.userLat, 
                            longitude: this.userLng,
                            early_reason: this.earlyReason
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        this.loading = false;
                        this.message = data.message;
                        this.isSuccess = data.success;
                        if (data.success) {
                            setTimeout(() => window.location.reload(), 1500);
                        }
                    })
                    .catch(err => {
                        this.loading = false;
                        this.message = "ግንኙነት ተቋርጧል፤ እባክዎ እንደገና ይሞክሩ።";
                    });
                }
            }
        }
    </script>
</body>
</html>
