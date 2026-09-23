<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStaff | የሰራተኞች መግቢያ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm bg-slate-800/90 border border-slate-700/80 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-xl">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-gradient-to-tr from-blue-600 to-indigo-600 rounded-2xl mb-3 shadow-lg shadow-blue-500/30">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white tracking-wide">SmartStaff</h1>
            <p class="text-xs text-slate-400 mt-1">የሰራተኞች መግቢያ ፖርታል</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 bg-rose-500/20 border border-rose-500/50 text-rose-300 rounded-xl text-xs text-center font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('employee.login.submit') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">ስልክ ቁጥር</label>
                <input type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="0911223344" required
                       class="w-full px-4 py-3 bg-slate-700/60 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">የይለፍ ኮድ (Access Code)</label>
                <input type="password" name="access_code" placeholder="••••••" maxlength="10" required
                       class="w-full px-4 py-3 bg-slate-700/60 border border-slate-600 rounded-xl text-white tracking-widest text-center text-lg placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" 
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-bold rounded-xl transition duration-200 shadow-lg shadow-blue-600/30 text-sm">
                ግባ / Login
            </button>
        </form>

        <div class="mt-8 text-center pt-2">
            <p class="text-[11px] text-slate-500">Mela Solution &copy; 2017/2025</p>
        </div>
    </div>

</body>
</html>
