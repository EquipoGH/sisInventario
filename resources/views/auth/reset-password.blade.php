<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecer Contraseña - GesInventario</title>

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

        /* Decoración flotante */
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        /* Indicador de fortaleza de contraseña */
        .strength-bar {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        .strength-weak {
            width: 33.33%;
            background-color: #ef4444;
        }

        .strength-medium {
            width: 66.66%;
            background-color: #f59e0b;
        }

        .strength-strong {
            width: 100%;
            background-color: #10b981;
        }

        /* Animación check */
        @keyframes checkPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .check-icon {
            animation: checkPulse 2s ease-in-out infinite;
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
                <a href="{{ route('login') }}" class="inline-flex items-center mb-8 text-white/80 hover:text-white transition">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Volver al login
                </a>

                <div class="mb-8">
                    <div class="bg-white/20 backdrop-blur-md p-6 rounded-2xl inline-block check-icon">
                        <i class="fas fa-shield-alt text-white text-6xl"></i>
                    </div>
                </div>

                <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                    Crea tu nueva<br/>
                    <span class="text-yellow-300">contraseña segura</span>
                </h1>

                <p class="text-xl text-purple-100 mb-8 leading-relaxed">
                    Estás a un paso de recuperar el acceso a tu cuenta.
                    Crea una contraseña fuerte y segura.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-check-circle text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Mínimo 8 caracteres</h3>
                            <p class="text-purple-100 text-sm">Usa una combinación de letras y números</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-lock text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Contraseña única</h3>
                            <p class="text-purple-100 text-sm">No uses la misma de otras plataformas</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-user-shield text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Mantén tu cuenta segura</h3>
                            <p class="text-purple-100 text-sm">No compartas tu contraseña con nadie</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-yellow-500/20 backdrop-blur-sm rounded-lg border border-yellow-300/30">
                    <p class="text-sm text-yellow-100">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Importante:</strong> Este enlace expirará pronto. Completa el proceso ahora.
                    </p>
                </div>
            </div>

            <!-- Lado Derecho: Formulario -->
            <div class="bg-white rounded-2xl shadow-2xl p-8 md:p-10 fade-in-up" style="animation-delay: 0.2s;">

                <!-- Logo Mobile -->
                <div class="md:hidden text-center mb-6">
                    <h2 class="text-3xl font-extrabold gradient-text">GesInventario</h2>
                </div>

                <div class="text-center mb-8">
                    <div class="inline-block p-4 bg-purple-100 rounded-full mb-4">
                        <i class="fas fa-key text-purple-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Restablecer Contraseña</h2>
                    <p class="text-gray-600">
                        Crea una nueva contraseña segura para tu cuenta
                    </p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                    @csrf

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email (readonly) -->
                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-envelope text-purple-600 mr-2"></i>
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            readonly
                            class="w-full px-4 py-3 border-2 border-gray-200 bg-gray-50 rounded-lg text-gray-600 cursor-not-allowed"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- New Password -->
                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-lock text-purple-600 mr-2"></i>
                            Nueva Contraseña
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
                                onkeyup="checkPasswordStrength()"
                            />
                            <button
                                type="button"
                                onclick="togglePassword('password')"
                                class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-gray-700"
                            >
                                <i class="fas fa-eye" id="password-eye"></i>
                            </button>
                        </div>

                        <!-- Barra de fortaleza -->
                        <div class="mt-2 bg-gray-200 rounded-full h-1 overflow-hidden">
                            <div id="strength-bar" class="strength-bar" style="width: 0%;"></div>
                        </div>
                        <p id="strength-text" class="mt-1 text-xs text-gray-500"></p>

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
                            Confirmar Nueva Contraseña
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

                    <!-- Requisitos de contraseña -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <p class="text-xs font-semibold text-blue-800 mb-2">
                            <i class="fas fa-info-circle mr-1"></i>
                            Requisitos de la contraseña:
                        </p>
                        <ul class="text-xs text-blue-700 space-y-1">
                            <li><i class="fas fa-check text-green-600 mr-1"></i> Mínimo 8 caracteres</li>
                            <li><i class="fas fa-check text-green-600 mr-1"></i> Combina letras y números</li>
                            <li><i class="fas fa-check text-green-600 mr-1"></i> Ambas contraseñas deben coincidir</li>
                        </ul>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn-gradient w-full py-3 px-6 text-white font-bold rounded-lg shadow-lg"
                    >
                        <i class="fas fa-check-circle mr-2"></i>
                        Restablecer Contraseña
                    </button>

                    <!-- Back to Login -->
                    <div class="text-center pt-4">
                        <p class="text-gray-600">
                            ¿Problemas para restablecer?
                            <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                                <i class="fas fa-arrow-left mr-1"></i>
                                Volver al login
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Footer Info -->
                <div class="mt-6 pt-6 border-t border-gray-200 text-center text-xs text-gray-500">
                    <p>
                        <i class="fas fa-shield-alt mr-1"></i>
                        Tu contraseña está encriptada y segura
                    </p>
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

        // Check password strength
        function checkPasswordStrength() {
            const password = document.getElementById('password').value;
            const strengthBar = document.getElementById('strength-bar');
            const strengthText = document.getElementById('strength-text');

            let strength = 0;
            let text = '';
            let className = '';

            if (password.length === 0) {
                strengthBar.style.width = '0%';
                strengthText.textContent = '';
                strengthBar.className = 'strength-bar';
                return;
            }

            // Calcular fortaleza
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/\d/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;

            // Aplicar estilos según fortaleza
            if (strength <= 2) {
                className = 'strength-bar strength-weak';
                text = '❌ Contraseña débil';
            } else if (strength <= 3) {
                className = 'strength-bar strength-medium';
                text = '⚠️ Contraseña media';
            } else {
                className = 'strength-bar strength-strong';
                text = '✅ Contraseña fuerte';
            }

            strengthBar.className = className;
            strengthText.textContent = text;
        }

        // Auto-focus en password
        setTimeout(() => {
            document.getElementById('password').focus();
        }, 500);
    </script>

</body>
</html>
