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

    <main class="max-w-lg mx-auto p-4 space-y-5" x-data="attendanceHandler()">

        <!-- Calendar Card -->
        <div class="bg-gradient-to-r from-blue-900/60 to-indigo-900/60 border border-blue-700/40 rounded-2xl p-4 shadow-lg flex items-center justify-between">
            <div>
                <span class="text-xs text-blue-300 uppercase tracking-wider font-semibold">የዛሬ ቀን (ዓ.ም / G.C)</span>
                <p class="text-lg font-bold text-white mt-0.5">{{ $todayEc }}</p>
                <p class="text-xs text-slate-300">{{ $todayGc }}</p>
            </div>
            <div class="text-right">
                <span class="text-xs px-2.5 py-1 bg-blue-500/20 border border-blue-400/40 text-blue-300 rounded-full font-mono">
                    {{ $setting->allowed_radius_meters }}m Geofenced
                </span>
            </div>
        </div>

        <!-- Notification Message Box -->
        <div x-show="message" x-cloak 
             :class="isSuccess ? 'bg-emerald-500/20 border-emerald-500 text-emerald-200' : 'bg-rose-500/20 border-rose-500 text-rose-200'"
             class="p-3.5 rounded-xl border text-sm font-medium transition duration-300 text-center"
             x-text="message">
        </div>

        @if(session('success'))
            <div class="p-3 bg-emerald-500/20 border border-emerald-500 text-emerald-200 rounded-xl text-xs text-center">
                {{ session('success') }}
            </div>
        @endif

        <!-- Check-in / Check-out Punch Card -->
        <div class="bg-slate-800 border border-slate-700 rounded-2xl p-5 shadow-xl text-center space-y-4">
            <h3 class="text-sm font-semibold text-slate-300">የዕለቱ አቴንዳንስ (GPS መቆጣጠሪያ)</h3>

            <div class="grid grid-cols-2 gap-3 py-2">
                <div class="bg-slate-700/50 p-3 rounded-xl border border-slate-600/50">
                    <p class="text-[11px] text-slate-400">የመግቢያ ሰዓት (Check-in)</p>
                    <p class="text-base font-bold text-emerald-400 mt-1">
                        {{ $todayAttendance && $todayAttendance->check_in_at ? $todayAttendance->check_in_at->format('h:i A') : '--:--' }}
                    </p>
                </div>
                <div class="bg-slate-700/50 p-3 rounded-xl border border-slate-600/50">
                    <p class="text-[11px] text-slate-400">የመውጫ ሰዓት (Check-out)</p>
                    <p class="text-base font-bold text-amber-400 mt-1">
                        {{ $todayAttendance && $todayAttendance->check_out_at ? $todayAttendance->check_out_at->format('h:i A') : '--:--' }}
                    </p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="space-y-2">
                @if(!$todayAttendance || !$todayAttendance->check_in_at)
                    <button @click="punch('check-in')" :disabled="loading"
                            class="w-full py-4 bg-emerald-600 hover:bg-emerald-500 disabled:opacity-50 text-white font-bold text-lg rounded-2xl shadow-lg shadow-emerald-600/30 flex items-center justify-center space-x-2 transition">
                        <span x-show="!loading">📍 አሁን ገብቻለሁ (Check-in)</span>
                        <span x-show="loading" class="animate-pulse">ቦታዎን በማረጋገጥ ላይ...</span>
                    </button>
                @elseif(!$todayAttendance->check_out_at)
                    <button @click="punch('check-out')" :disabled="loading"
                            class="w-full py-4 bg-amber-600 hover:bg-amber-500 disabled:opacity-50 text-white font-bold text-lg rounded-2xl shadow-lg shadow-amber-600/30 flex items-center justify-center space-x-2 transition">
                        <span x-show="!loading">🚪 ስራ ጨርሻለሁ ውጣ (Check-out)</span>
                        <span x-show="loading" class="animate-pulse">ቦታዎን በማረጋገጥ ላይ...</span>
                    </button>
                @else
                    <div class="py-3 px-4 bg-blue-900/30 border border-blue-500/30 text-blue-300 rounded-xl text-sm font-semibold">
                        ✅ የዛሬውን የስራ ቀን አጠናቀዋል! እናመሰግናለን።
                    </div>
                @endif
            </div>

            <p class="text-[11px] text-slate-500">
                ⚠️ ማሳሰቢያ፡ Check-in/out ለማድረግ የስልክዎ GPS መብራት አለበት፤ ከድርጅቱ በ 100 ሜትር ክልል ውስጥ መሆን ይኖርብዎታል።
            </p>
        </div>

        <!-- Leave Request Accordion -->
        <div x-data="{ open: false }" class="bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md">
            <button @click="open = !open" class="w-full flex justify-between items-center text-left">
                <span class="text-sm font-semibold text-white flex items-center gap-2">
                    📝 ከቤት ሆነው ፈቃድ መጠየቂያ (Leave Request)
                </span>
                <span class="text-slate-400 font-bold" x-text="open ? '−' : '+'"></span>
            </button>

            <div x-show="open" x-cloak class="mt-4 pt-3 border-t border-slate-700 space-y-3">
                <form action="{{ route('employee.leave.submit') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-slate-300 mb-1">የፈቃድ አይነት</label>
                        <select name="leave_type" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-sm text-white focus:outline-none">
                            <option value="የህመም ፈቃድ">የህመም ፈቃድ (Sick Leave)</option>
                            <option value="አስቸኳይ ጉዳይ">አስቸኳይ ጉዳይ (Emergency)</option>
                            <option value="የዓመት ፈቃድ">የዓመት ፈቃድ (Annual Leave)</option>
                            <option value="ሌላ">ሌላ (Other)</option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">የሚጀምርበት ቀን</label>
                            <input type="date" name="start_date_gc" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white">
                        </div>
                        <div>
                            <label class="block text-xs text-slate-300 mb-1">የሚያበቃበት ቀን</label>
                            <input type="date" name="end_date_gc" required class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-300 mb-1">ምክንያት</label>
                        <textarea name="reason" rows="2" required placeholder="የፈቃድዎን ምክንያት በአጭሩ ይግለጹ..." class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-xl text-xs text-white placeholder-slate-400"></textarea>
                    </div>

                    <div>
                        <label class="block text-xs text-slate-300 mb-1">ማስረጃ ካለ (Optional - Image/PDF)</label>
                        <input type="file" name="attachment" accept="image/*,.pdf" class="w-full text-xs text-slate-400 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-blue-600 file:text-white file:text-xs">
                    </div>

                    <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-500 text-white rounded-xl text-xs font-bold transition">
                        የፈቃድ ጥያቄውን ላክ
                    </button>
                </form>
            </div>
        </div>

        <!-- Direct Admin Announcements -->
        @if($announcements->count() > 0)
            <div class="bg-slate-800 border border-slate-700 rounded-2xl p-4 shadow-md space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 flex items-center gap-1.5">
                    📢 ከአስተዳዳሪው የተላለፉ ማስታወቂያዎች
                </h4>
                @foreach($announcements as $notice)
                    <div class="p-3 bg-slate-700/50 border-l-4 {{ $notice->priority === 'urgent' ? 'border-rose-500' : 'border-blue-500' }} rounded-r-xl">
                        <div class="flex justify-between items-center mb-1">
                            <span class="text-xs font-bold text-white">{{ $notice->title }}</span>
                            <span class="text-[10px] text-slate-400">{{ $notice->date_ec }}</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">{{ $notice->message }}</p>
                    </div>
                @endforeach
            </div>
        @endif

    </main>

    <!-- Client-side Geolocation GPS Script -->
    <script>
        function attendanceHandler() {
            return {
                loading: false,
                message: '',
                isSuccess: false,

                punch(action) {
                    if (!navigator.geolocation) {
                        this.isSuccess = false;
                        this.message = "ስልክዎ ጂፒኤስ (Geolocation) አይደግፍም!";
                        return;
                    }

                    this.loading = true;
                    this.message = "የጂፒኤስ መረጃዎን በማንበብ ላይ... እባክዎ ትንሽ ይጠብቁ";

                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            const endpoint = action === 'check-in' 
                                ? "{{ route('employee.checkin') }}" 
                                : "{{ route('employee.checkout') }}";

                            fetch(endpoint, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({ latitude: lat, longitude: lng })
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
                                this.isSuccess = false;
                                this.message = "ግንኙነት ተቋርጧል። እባክዎ እንደገና ይሞክሩ።";
                            });
                        },
                        (error) => {
                            this.loading = false;
                            this.isSuccess = false;
                            switch(error.code) {
                                case error.PERMISSION_DENIED:
                                    this.message = "እባክዎ የስልክዎን Location Permission ያብሩ!";
                                    break;
                                case error.POSITION_UNAVAILABLE:
                                    this.message = "የቦታ መረጃ ማግኘት አልተቻለም።";
                                    break;
                                case error.TIMEOUT:
                                    this.message = "ጂፒኤስ ለመያዝ ሰዓት አልቋል፤ እባክዎ በድጋሚ ይጫኑ።";
                                    break;
                            }
                        },
                        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
                    );
                }
            }
        }
    </script>
</body>
</html>
