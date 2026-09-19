<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 — Halaman Tidak Ditemukan</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen items-center justify-center bg-gray-100 p-6 font-sans text-gray-900 antialiased">
    <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 text-center shadow-sm">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-blue-100 text-3xl font-black text-blue-600">404</div>
        <h1 class="mt-4 text-xl font-bold text-gray-900">Halaman Tidak Ditemukan</h1>
        <p class="mt-2 text-sm text-gray-500">Halaman yang Anda cari tidak tersedia atau telah dipindahkan.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="{{ route('login') }}" class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-bold text-white transition hover:bg-blue-700">Login</a>
            <a href="{{ url('/') }}" class="rounded-lg border border-gray-300 px-5 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">Beranda</a>
        </div>
    </div>
</body>
</html>