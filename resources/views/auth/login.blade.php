<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-full items-center justify-center bg-blue-950 px-4 py-10 font-sans antialiased">
    <div class="grid w-full max-w-4xl overflow-hidden rounded-2xl bg-white shadow-2xl lg:grid-cols-2">
        {{-- Panel kiri --}}
        <div class="hidden flex-col justify-between bg-gradient-to-br from-blue-600 to-blue-900 p-10 text-white lg:flex">
            <div>
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/15 text-2xl font-bold">A</div>
                <h1 class="mt-6 text-3xl font-bold leading-tight">Sistem Absensi Karyawan</h1>
                <p class="mt-3 text-sm text-blue-100">Kelola data karyawan, lokasi absensi, QR Code, dan jadwal kerja dalam satu platform berbasis web.</p>
            </div>
            <p class="text-xs text-blue-200">&copy; {{ date('Y') }} {{ config('app.name') }}. Semua hak dilindungi.</p>
        </div>

        {{-- Panel form --}}
        <div class="p-8 sm:p-12">
            <div class="mb-8 flex items-center gap-3 lg:hidden">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-blue-600 text-lg font-bold text-white">A</div>
                <p class="font-bold text-gray-800">{{ config('app.name') }}</p>
            </div>

            <h2 class="text-2xl font-bold text-gray-900">Login Admin</h2>
            <p class="mt-1 text-sm text-gray-500">Masuk menggunakan akun Admin Anda.</p>

            @if (session('success'))
                <div class="mt-4 rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('login.attempt') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="mb-1 block text-sm font-semibold text-gray-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="nama@perusahaan.com">
                    @error('email')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="mb-1 block text-sm font-semibold text-gray-700">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        placeholder="••••••••">
                    @error('password')
                        <p class="mt-1 text-xs font-medium text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600">
                        <input type="checkbox" name="remember" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Ingat saya
                    </label>
                </div>

                <button type="submit"
                    class="w-full rounded-lg bg-blue-600 px-4 py-3 text-sm font-bold text-white transition hover:bg-blue-700 focus:ring-2 focus:ring-blue-300">
                    Masuk
                </button>
            </form>

            <div class="mt-6 rounded-lg border border-blue-100 bg-blue-50 p-3 text-xs text-blue-800">
                <p class="font-semibold">Akun demo Admin</p>
                <p>Email: <code class="font-mono">admin@gmail.com</code></p>
                <p>Password: <code class="font-mono">12345678</code></p>
            </div>
        </div>
    </div>
</body>
</html>