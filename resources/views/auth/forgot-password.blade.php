<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Recuperar Contraseña - GesInventario</title>

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

        /* Animación de pulso para el icono */
        @keyframes pulse-icon {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .pulse-icon {
            animation: pulse-icon 2s ease-in-out infinite;
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
                    <div class="bg-white/20 backdrop-blur-md p-6 rounded-2xl inline-block pulse-icon">
                        <i class="fas fa-key text-white text-6xl"></i>
                    </div>
                </div>

                <h1 class="text-5xl font-extrabold mb-6 leading-tight">
                    ¿Olvidaste tu<br/>
                    <span class="text-yellow-300">contraseña?</span>
                </h1>

                <p class="text-xl text-purple-100 mb-8 leading-relaxed">
                    No te preocupes, te enviaremos un enlace seguro a tu correo electrónico para
                    que puedas restablecer tu contraseña fácilmente.
                </p>

                <div class="space-y-4">
                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-envelope-open-text text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Revisa tu correo</h3>
                            <p class="text-purple-100 text-sm">Te enviaremos un enlace de recuperación</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-shield-alt text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Proceso Seguro</h3>
                            <p class="text-purple-100 text-sm">El enlace expira en 60 minutos por seguridad</p>
                        </div>
                    </div>

                    <div class="flex items-start">
                        <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg mr-4">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg">Rápido y Fácil</h3>
                            <p class="text-purple-100 text-sm">Recupera el acceso en menos de 2 minutos</p>
                        </div>
                    </div>
                </div>

                <div class="mt-8 p-4 bg-white/10 backdrop-blur-sm rounded-lg border border-white/20">
                    <p class="text-sm text-purple-100">
                        <i class="fas fa-info-circle mr-2"></i>
                        <strong>Nota:</strong> Si no recibes el correo en unos minutos, revisa tu carpeta de spam.
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
                        <i class="fas fa-lock-open text-purple-600 text-3xl"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Recuperar Contraseña</h2>
                    <p class="text-gray-600">
                        Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
                    </p>
                </div>

                <!-- Session Status (mensaje de éxito) -->
                @if (session('status'))
                    <div class="mb-6 p-4 bg-green-50 border-l-4 border-green-500 rounded-lg">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 text-xl mr-3 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-semibold text-green-800 mb-1">¡Correo enviado!</p>
                                <p class="text-sm text-green-700">{{ session('status') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
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
                            class="input-field w-full px-4 py-3 border-2 border-gray-300 rounded-lg transition duration-200"
                            placeholder="correo@ejemplo.com"
                        />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500">
                            <i class="fas fa-info-circle mr-1"></i>
                            Usa el mismo correo con el que te registraste
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <button
                        type="submit"
                        class="btn-gradient w-full py-3 px-6 text-white font-bold rounded-lg shadow-lg"
                    >
                        <i class="fas fa-paper-plane mr-2"></i>
                        Enviar Enlace de Recuperación
                    </button>

                    <!-- Back to Login -->
                    <div class="text-center pt-4">
                        <p class="text-gray-600">
                            ¿Ya recordaste tu contraseña?
                            <a href="{{ route('login') }}" class="text-purple-600 hover:text-purple-700 font-semibold">
                                <i class="fas fa-sign-in-alt mr-1"></i>
                                Iniciar Sesión
                            </a>
                        </p>
                    </div>
                </form>

                <!-- Footer Info -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <div class="space-y-3">
                        <div class="flex items-start text-sm">
                            <i class="fas fa-question-circle text-purple-600 mr-2 mt-0.5"></i>
                            <div>
                                <p class="text-gray-700 font-semibold">¿Aún no tienes cuenta?</p>
                                <a href="{{ route('register') }}" class="text-purple-600 hover:text-purple-700 font-medium">
                                    Regístrate aquí
                                </a>
                            </div>
                        </div>

                        <div class="flex items-start text-sm">
                            <i class="fas fa-headset text-purple-600 mr-2 mt-0.5"></i>
                            <div>
                                <p class="text-gray-700 font-semibold">¿Necesitas ayuda?</p>
                                <a href="#" class="text-purple-600 hover:text-purple-700 font-medium">
                                    Contactar soporte técnico
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script>
        // Auto-focus en el input después de 500ms (mejor UX)
        setTimeout(() => {
            document.getElementById('email').focus();
        }, 500);

        // Mostrar mensaje temporal después de enviar
        @if (session('status'))
            setTimeout(() => {
                const statusDiv = document.querySelector('.bg-green-50');
                if (statusDiv) {
                    statusDiv.style.transition = 'opacity 0.5s';
                    statusDiv.style.opacity = '0';
                    setTimeout(() => {
                        statusDiv.remove();
                    }, 500);
                }
            }, 8000); // Desaparece después de 8 segundos
        @endif
    </script>

</body>
</html>
