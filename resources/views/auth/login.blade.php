<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Arsip KPU Bali</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom Tailwind colors sesuai KPU */
        .bg-kpu-red { background-color: #e60000; }
        .text-kpu-red { color: #e60000; }
        .login-page-bg {
            background:
                linear-gradient(135deg, rgba(116, 0, 0, 0.18), rgba(255, 255, 255, 0.08)),
                url("{{ asset('login-bg-sinar-bali.png') }}?v=2") right bottom / cover no-repeat;
        }
        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(14px);
        }
        .login-logo {
            width: 132px;
            height: 132px;
            object-fit: contain;
            border-radius: 32px;
            box-shadow:
                0 18px 35px rgba(139, 0, 0, 0.18),
                0 0 30px rgba(255, 36, 112, 0.16);
        }
    </style>
</head>
<body class="bg-gray-100">

    <!-- Background keren -->
    <div class="relative min-h-screen flex items-center justify-center login-page-bg">
        <div class="absolute inset-0 bg-gradient-to-br from-red-950/20 via-white/5 to-red-900/20"></div>

        <!-- Card Login -->
        <div class="relative login-card rounded-xl shadow-xl w-full max-w-md p-8 z-10">
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="text-center mb-6">
                <!-- Logo KPU -->
                <img src="{{ asset('logo-sinar-v2-kotak.png') }}?v=1" alt="Logo SINAR KPU Bali V2" class="mx-auto login-logo">
                <h1 class="text-2xl font-bold mt-4 text-gray-800">Sistem Informasi Arsip <br> KPU Provinsi Bali</h1>
                <p class="text-gray-500 mt-1">Masukkan akun Anda untuk masuk</p>
            </div>

            <!-- Form Login -->
            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="block text-gray-700 font-medium">Email</label>
                    <input id="email" name="email" type="email" required autofocus
                        class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-kpu-red focus:border-kpu-red">
                </div>

                <!-- <div>
                    <label for="password" class="block text-gray-700 font-medium">Password</label>
                    <input id="password" name="password" type="password" required
                        class="w-full mt-2 p-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-kpu-red focus:border-kpu-red">
                </div> -->

                <div class="relative">
                    <label for="password" class="block text-gray-700 font-medium">Password</label>
                    
                    <input id="password" name="password" type="password" required
                        class="w-full mt-2 p-3 pr-12 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-kpu-red focus:border-kpu-red">

                    <!-- Icon show/hide -->
                    {{-- <button type="button" onclick="togglePassword()" 
                        class="absolute right-3 top-10 text-gray-500">
                        👁️
                    </button> --}}
                </div>

                <div class="flex items-center justify-between">
                    <label class="inline-flex items-center text-gray-700">
                        <input type="checkbox" name="remember" class="form-checkbox">
                        <span class="ml-2 text-sm">Ingat Saya</span>
                    </label>
                    <a href="#" class="text-kpu-red text-sm hover:underline">Lupa password?</a>
                </div>

                <button type="submit"
                    class="w-full bg-kpu-red text-white font-semibold p-3 rounded-lg hover:bg-red-800 transition">
                    Masuk
                </button>
            </form>
        </div>
    </div>
<script>
    function togglePassword() {
        const password = document.getElementById("password");
        password.type = password.type === "password" ? "text" : "password";
    }
</script>
</body>
</html>
