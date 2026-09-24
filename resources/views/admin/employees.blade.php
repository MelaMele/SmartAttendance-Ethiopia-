<!DOCTYPE html>
<html lang="am">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartStaff Admin | ሰራተኞች ማስተዳደሪያ</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Ethiopic:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Noto Sans Ethiopic', sans-serif; }</style>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">

    <nav class="bg-slate-900 border-b border-slate-800 px-6 py-4 flex justify-between items-center">
        <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-blue-400 hover:text-blue-300">
            ← ወደ ዋና ዳሽቦርድ ተመለስ
        </a>
        <h1 class="text-base font-bold text-white">የሰራተኞች ዝርዝር እና ማስተዳደሪያ</h1>
    </nav>

    <main class="max-w-6xl mx-auto p-6 space-y-6">

        @if(session('success'))
            <div class="p-4 bg-emerald-500/20 border border-emerald-500 text-emerald-300 rounded-2xl text-xs font-semibold">
                {{ session('success') }}
            </div>
        @endif

        <!-- Add Employee Form -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl">
            <h2 class="text-sm font-bold text-white mb-4">➕ አዲስ ሰራተኛ መመዝገቢያ</h2>
            <form action="{{ route('admin.employee.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 text-xs">
                @csrf
                <div>
                    <label class="block text-slate-400 mb-1">ሙሉ ስም</label>
                    <input type="text" name="full_name" placeholder="ሙሉ ስም" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">ስልክ ቁጥር</label>
                    <input type="tel" name="phone_number" placeholder="0911223344" required class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">ዲፓርትመንት</label>
                    <input type="text" name="department" placeholder="ለምሳሌ፡ IT" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                </div>
                <div>
                    <label class="block text-slate-400 mb-1">የስራ መደብ</label>
                    <input type="text" name="position" placeholder="ለምሳሌ፡ Developer" class="w-full px-3 py-2 bg-slate-800 border border-slate-700 rounded-xl text-white">
                </div>
                <div class="md:col-span-4 text-right">
                    <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 font-bold text-white rounded-xl transition">
                        ሰራተኛውን መዝግብ (ኮድ አመንጭ)
                    </button>
                </div>
            </form>
        </div>

        <!-- Employees List Table with Edit & Delete Actions -->
        <div class="bg-slate-900 border border-slate-800 rounded-3xl p-6 shadow-xl space-y-4">
            <h3 class="text-sm font-bold text-white">የተመዘገቡ ሰራተኞች ዝርዝር</h3>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-800/60 text-slate-400 uppercase text-[10px]">
                        <tr>
                            <th class="p-3">ስም</th>
                            <th class="p-3">ስልክ</th>
                            <th class="p-3">ክፍል / መደብ</th>
                            <th class="p-3">የይለፍ ኮድ</th>
                            <th class="p-3">እርምጃዎች</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($employees as $emp)
                            <tr class="hover:bg-slate-800/40 transition">
                                <td class="p-3 font-bold text-white">{{ $emp->full_name }}</td>
                                <td class="p-3 font-mono text-slate-300">{{ $emp->phone_number }}</td>
                                <td class="p-3 text-slate-400">{{ $emp->department ?? '-' }} / {{ $emp->position ?? '-' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 bg-blue-900/40 border border-blue-600/40 text-blue-300 rounded font-mono font-bold">
                                        {{ $emp->access_code }}
                                    </span>
                                </td>
                                <td class="p-3 flex items-center gap-2">
                                    <!-- Edit Trigger (Simple Prompt or Form) -->
                                    <button onclick="editEmployee({{ $emp->id }}, '{{ $emp->full_name }}', '{{ $emp->phone_number }}', '{{ $emp->access_code }}')"
                                            class="px-2.5 py-1 bg-amber-600/20 hover:bg-amber-600 text-amber-300 hover:text-white rounded text-[11px] font-bold transition">
                                        ✏️ አስተካክል
                                    </button>

                                    <!-- Delete Employee Button -->
                                    <form action="{{ route('admin.employee.delete', $emp->id) }}" method="POST" onsubmit="return confirm('እርግጠኛ ነዎት ይህ ሰራተኛ ከሲስተሙ ይሰረዝ?')">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 bg-rose-600/20 hover:bg-rose-600 text-rose-300 hover:text-white rounded text-[11px] font-bold transition">
                                            🗑️ አጥፋ
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-6 text-center text-slate-500">ምንም ሰራተኛ አልተመዘገበም።</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">
                {{ $employees->links() }}
            </div>
        </div>

    </main>

    <!-- Hidden Edit Form for Easy Update -->
    <form id="editForm" method="POST" style="display:none;">
        @csrf
        <input type="hidden" name="full_name" id="editName">
        <input type="hidden" name="phone_number" id="editPhone">
        <input type="hidden" name="access_code" id="editCode">
    </form>

    <script>
        function editEmployee(id, name, phone, code) {
            const newName = prompt("አዲስ ሙሉ ስም፡", name);
            if (!newName) return;
            const newPhone = prompt("አዲስ ስልክ ቁጥር፡", phone);
            if (!newPhone) return;
            const newCode = prompt("አዲስ የይለፍ ኮድ፡", code);
            if (!newCode) return;

            const form = document.getElementById('editForm');
            form.action = `/admin/employees/${id}/update`;
            document.getElementById('editName').value = newName;
            document.getElementById('editPhone').value = newPhone;
            document.getElementById('editCode').value = newCode;
            form.submit();
        }
    </script>
</body>
</html>
