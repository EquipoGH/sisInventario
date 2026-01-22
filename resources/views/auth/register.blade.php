<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro - GesInventario</title>

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

        /* Input focus personalizado */
        .input-field:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            outline: none;
        }

        /* Botón con gradiente */
        .btn-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        /* Iconos flotantes decorativos */
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
                <a href="{{ route('dashboard') }}" class="inline-flex items-center mb-8 text-white/80 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al inicio
                </a>

                <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                    Únete a<br/>
                    <span class="text-yellow-300">GesInventario</span>
                </h1>

                <p class="text-xl text-purple-100 mb-8 leading-relaxed">
                    Crea tu cuenta y comienza a gestionar tu inventario de forma inteligente.
                    Todo lo que necesitas en una sola plataforma.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Seguro y Confiable</h3>
                            <p class="text-purple-100 text-sm">Tus datos están protegidos con encriptación de nivel empresarial</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-rocket text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Configuración Rápida</h3>
                            <p class="text-purple-100 text-sm">Empieza a usar el sistema en menos de 2 minutos</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-headset text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Soporte 24/7</h3>
                            <p class="text-purple-100 text-sm">Estamos aquí para ayudarte cuando lo necesites</p>
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
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Crear Cuenta</h2>
                    <p class="text-gray-600">Completa tus datos para registrarte</p>
                </div>

                <form method="POST" action="{{ route('register') }}" class="space-y-5">
                    @csrf

                    <!-- DNI -->
                    <div>
                        <label for="dni_usuario" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-id-card text-purple-600 mr-2"></i>
                            DNI
                        </label>
                        <input
                            id="dni_usuario"
                            type="text"
                            name="dni_usuario"
                            value="{{ old('dni_usuario') }}"
                            maxlength="8"
                            required
                            autofocus
                            class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200"
                            placeholder="12345678"
                        />
                        @error('dni_usuario')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user text-purple-600 mr-2"></i>
                            Nombre Completo
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autocomplete="name"
                            class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200"
                            placeholder="Juan Pérez"
                        />
                        @error('name')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

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
                                autocomplete="new-password"
                                class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200 pr-12"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onclick="togglePassword('password')"
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

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-purple-600 mr-2"></i>
                            Confirmar Contraseña
                        </label>
                        <div class="relative">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                required
                                autocomplete="new-password"
                                class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200 pr-12"
                                placeholder="••••••••"
                            />
                            <button
                                type="button"
                                onclick="togglePassword('password_confirmation')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <i class="fas fa-eye" id="password_confirmation-eye"></i>
                            </button>
                        </div>
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn-gradient w-full py-3 px-6 text-white font-bold rounded-lg shadow-lg"
                    >
                        <i class="fas fa-user-plus mr-2"></i>
                        Crear Cuenta
                    </button>

                    <!-- Login Link -->
                    <div class="text-center pt-4">
                        <p class="text-gray-600">
                            ¿Ya tienes una cuenta?
                            <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Footer Info -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center text-xs text-gray-500">
                    <p>Al registrarte, aceptas nuestros términos de servicio y política de privacidad</p>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Toggle password visibility
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(inputId + '-eye');

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

        // Validación en tiempo real del DNI (solo números)
        document.getElementById('dni_usuario').addEventListener('input', function(e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

</body>
</html>
