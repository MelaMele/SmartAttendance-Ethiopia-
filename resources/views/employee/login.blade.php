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

    <div class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-3xl p-6 sm:p-8 shadow-2xl backdrop-blur-lg">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-600 rounded-2xl mb-3 shadow-lg shadow-blue-500/30">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-wide">SmartStaff Ethiopia</h1>
            <p class="text-sm text-slate-400 mt-1">የ Mela Solution የሰራተኞች አቴንዳንስ መግቢያ</p>
        </div>

        @if(session('error'))
            <div class="mb-4 p-3 bg-rose-500/20 border border-rose-500/40 text-rose-300 rounded-xl text-sm text-center">
                {{ session('error') }}
            </div>
        @endif

        <form action="{{ route('employee.login.submit') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">ስልክ ቁጥር</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm">🇪🇹 +251</span>
                    <input type="tel" name="phone_number" placeholder="911223344" required
                           class="w-full pl-20 pr-4 py-3 bg-slate-700/60 border border-slate-600 rounded-xl text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-300 mb-1">የመግቢያ ኮድ (Access Code)</label>
                <input type="password" name="access_code" placeholder="••••••" maxlength="10" required
                       class="w-full px-4 py-3 bg-slate-700/60 border border-slate-600 rounded-xl text-white tracking-widest text-center text-xl placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>

            <button type="submit" 
                    class="w-full py-3.5 bg-blue-600 hover:bg-blue-500 active:scale-95 text-white font-semibold rounded-xl transition duration-200 shadow-lg shadow-blue-600/40">
                ግባ / Login
            </button>
        </form>

        <div class="mt-8 text-center border-t border-slate-700/60 pt-4">
            <p class="text-xs text-slate-500">Mela Solution &copy; 2017/2025 ዓ.ም</p>
        </div>
    </div>

</body>
</html>
