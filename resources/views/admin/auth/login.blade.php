<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login — InsightPress</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Hind Siliguri', sans-serif; }</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-xl p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-slate-900">Insight<span class="text-emerald-600">Press</span></h1>
            <p class="text-sm text-slate-500 mt-1">অ্যাডমিন কন্ট্রোল প্যানেলে লগইন করুন</p>
        </div>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-rose-50 text-rose-600 text-sm">{{ $errors->first() }}</div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">ইমেইল অ্যাড্রেস</label>
                <input type="email" name="email" value="{{ old('email', 'admin@websinsight.com') }}" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">পাসওয়ার্ড</label>
                <input type="password" name="password" value="password123" required class="w-full px-4 py-2.5 rounded-lg border border-slate-300 focus:ring-2 focus:ring-emerald-500 focus:outline-none">
            </div>
            <div class="flex items-center justify-between text-sm">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="remember" checked class="rounded text-emerald-600"> মনে রাখুন
                </label>
            </div>
            <button type="submit" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg transition">
                লগইন করুন
            </button>
        </form>
    </div>
</body>
</html>
