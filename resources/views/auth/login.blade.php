<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión - GesInventario</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            height: 100%;
            font-family: 'Inter', sans-serif !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animación de entrada */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Input focus */
        .input-field:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Botón gradiente */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Checkbox personalizado */
        .custom-checkbox:checked {
            background-color: #667eea;
            border-color: #667eea;
        }

        /* Decoración flotante */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <!-- Decoración de fondo -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 left-20 w-64 h-64 bg-white/10 rounded-full blur-3xl floating" style="animation-delay: 0s;"></div>
        <div class="absolute bottom-20 right-20 w-96 h-96 bg-white/10 rounded-full blur-3xl floating" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 w-72 h-72 bg-white/5 rounded-full blur-3xl floating" style="animation-delay: 2s;"></div>
    </div>

    <!-- Contenedor Principal -->
    <div class="w-full max-w-5xl relative z-10">
        <div class="grid md:grid-cols-2 gap-8 items-center">

            <!-- Lado Izquierdo: Información -->
            <div class="hidden md:block text-white fade-in-up">
                <a href="{{ url('/') }}" class="inline-flex items-center mb-8 text-white/80 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al inicio
                </a>

                <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                    ¡Bienvenido de<br/>
                    nuevo a<br/>
                    <span class="text-yellow-300">GesInventario</span>
                </h1>

                <p class="text-xl text-purple-100 mb-8 leading-relaxed">
                    Inicia sesión para acceder a tu panel de control y gestionar tu inventario de forma inteligente.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-boxes text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Control Total</h3>
                            <p class="text-purple-100 text-sm">Gestiona todos tus bienes patrimoniales</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-chart-line text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Reportes en Tiempo Real</h3>
                            <p class="text-purple-100 text-sm">Estadísticas y análisis actualizados</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-mobile-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Acceso desde cualquier lugar</h3>
                            <p class="text-purple-100 text-sm">Compatible con todos tus dispositivos</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lado Derecho: Formulario -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 fade-in-up" style="animation-delay: 0.2s;">

                <!-- Logo Mobile -->
                <div class="md:hidden text-center mb-6">
                    <h2 class="text-3xl font-extrabold gradient-text">GesInventario</h2>
                </div>

                <div class="text-center mb-8">
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Iniciar Sesión</h2>
                    <p class="text-gray-600">Ingresa tus credenciales para continuar</p>
                </div>

                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-800">
                            <i class="fas fa-check-circle mr-2"></i>
                            {{ session('status') }}
                        </p>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-purple-600 mr-2"></i>
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200"
                            placeholder="correo@ejemplo.com"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-purple-600 mr-2"></i>
                            Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                                class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200 pr-12"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onclick="togglePassword()"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="inline-flex items-center cursor-pointer">
                            <input
                                id="remember_me"
                                type="checkbox"
                                name="remember"
                                class="custom-checkbox w-4 h-4 text-purple-600 border-gray-300 rounded focus:ring-purple-500"
                            >
                            <span class="ml-2 text-sm text-gray-600 font-medium">Recordarme</span>
                        </label>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="text-sm text-purple-600 hover:text-purple-700 font-semibold">
                                ¿Olvidaste tu contraseña?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn-gradient w-full py-3 px-6 text-white font-bold rounded-lg shadow-lg"
                    >
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Iniciar Sesión
                    </button>

                    <!-- Register Link -->
                    <div class="text-center pt-4">
                        <p class="text-gray-600">
                            ¿No tienes una cuenta?
                            <a href="{{ route('register') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                                Regístrate aquí
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Footer Info -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center">
                    <p class="text-xs text-gray-500 mb-2">¿Problemas para iniciar sesión?</p>
                    <a href="#" class="text-sm text-purple-600 hover:text-purple-700 font-semibold">
                        <i class="fas fa-headset mr-1"></i>
                        Contactar soporte
                    </a>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('password-eye');

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>

</body>
</html>
