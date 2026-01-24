<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>GesInventario - Sistema Inteligente de Inventario</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        /* RESET COMPLETO - FORZAR ESTILOS */
        html, body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100%;
            height: 100%;
            font-family: 'Inter', sans-serif !important;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            overflow-x: hidden;
        }

        * {
            box-sizing: border-box;
        }

        /* Gradientes */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            position: relative;
        }

        .gradient-text {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Animaciones de iconos flotantes */
        @keyframes float {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-25px) rotate(3deg);
            }
        }

        @keyframes float-delayed {
            0%, 100% {
                transform: translateY(0px) rotate(0deg);
            }
            50% {
                transform: translateY(-20px) rotate(-3deg);
            }
        }

        @keyframes float-slow {
            0%, 100% {
                transform: translateY(0px) scale(1);
            }
            50% {
                transform: translateY(-15px) scale(1.03);
            }
        }

        .floating-icon-1 {
            animation: float 5s ease-in-out infinite;
        }

        .floating-icon-2 {
            animation: float-delayed 6s ease-in-out infinite;
        }

        .floating-icon-3 {
            animation: float-slow 7s ease-in-out infinite;
        }

        /* Cards con hover */
        .card-hover {
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .card-hover:hover {
            transform: translateY(-12px) scale(1.02);
            box-shadow: 0 30px 60px rgba(102, 126, 234, 0.3);
        }

        /* Números animados */
        .stat-number {
            font-size: 3rem;
            font-weight: 800;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            animation: pulse-glow 2s ease-in-out infinite;
        }

        @keyframes pulse-glow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        /* Iconos con brillo */
        .icon-glow {
            transition: all 0.3s ease;
        }

        .card-hover:hover .icon-glow {
            transform: scale(1.15);
            filter: drop-shadow(0 0 20px rgba(102, 126, 234, 0.6));
        }

        /* Botones */
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
        }

        /* Scroll suave */
        html {
            scroll-behavior: smooth;
        }

        /* Patrón decorativo */
        .hero-pattern {
            background-image:
                radial-gradient(circle at 20% 30%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        }

        /* Altura mínima */
        .min-h-screen {
            min-height: 100vh;
        }

        /* Hover scale */
        .hover\:scale-105:hover {
            transform: scale(1.05);
        }

        /* Animación pulso */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .animate-pulse {
            animation: pulse 1.5s ease-in-out infinite;
        }

        /* Forzar fondo blanco en secciones específicas */
        .bg-white-forced {
            background-color: white !important;
        }
    </style>
</head>
<body>

    <!-- ============================================ -->
    <!-- NAVBAR -->
    <!-- ============================================ -->
    <nav class="bg-white/95 backdrop-blur-md shadow-sm fixed w-full top-0 z-50 border-b border-gray-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex items-center">
                    <span class="text-2xl font-extrabold gradient-text">GesInventario</span>
                </div>

                <div class="flex items-center space-x-3">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-5 py-2.5 btn-primary text-white rounded-lg font-semibold shadow-lg">
                                <i class="fas fa-tachometer-alt mr-2"></i>
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-4 py-2 text-gray-700 hover:text-purple-600 transition font-medium">
                                <i class="fas fa-sign-in-alt mr-2"></i>
                                Iniciar Sesión
                            </a>

                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="inline-flex items-center px-5 py-2.5 btn-primary text-white rounded-lg font-semibold shadow-lg">
                                    <i class="fas fa-user-plus mr-2"></i>
                                    Registrarse
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </nav>

    <!-- ============================================ -->
    <!-- HERO SECTION CON GRADIENTE FORZADO -->
    <!-- ============================================ -->
    <section class="gradient-bg hero-pattern pt-24 pb-0 px-4 relative overflow-hidden min-h-screen flex items-center">
        <div class="max-w-7xl mx-auto w-full">
            <div class="grid lg:grid-cols-2 gap-16 items-center py-16">

                <!-- Contenido Textual -->
                <div class="text-white z-10">
                    <div class="inline-block px-4 py-2 bg-white/10 backdrop-blur-sm rounded-full mb-6">
                        <span class="text-sm font-semibold">Sistema Inteligente de Inventario</span>
                    </div>

                    <h1 class="text-5xl md:text-6xl font-extrabold mb-6 leading-tight">
                        Gestiona tu
                        <span class="block text-white mt-2">Inventario de</span>
                        <span class="block text-yellow-300 mt-2">Forma Inteligente</span>
                    </h1>

                    <p class="text-xl mb-8 text-purple-100 leading-relaxed">
                        Controla, organiza y optimiza todos tus bienes patrimoniales desde una plataforma moderna,
                        potente y fácil de usar. <strong class="text-white">Todo en un solo lugar.</strong>
                    </p>

                    <div class="flex flex-wrap gap-4 mb-12">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-8 py-4 bg-white text-purple-600 rounded-xl hover:bg-gray-100 transition shadow-2xl font-bold text-lg hover:scale-105">
                                <i class="fas fa-rocket mr-3"></i>
                                Ir al Sistema
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center px-8 py-4 bg-white text-purple-600 rounded-xl hover:bg-gray-100 transition shadow-2xl font-bold text-lg hover:scale-105">
                                <i class="fas fa-sign-in-alt mr-3"></i>
                                Comenzar Ahora
                            </a>
                        @endauth

                        <a href="#features" class="inline-flex items-center px-8 py-4 border-2 border-white text-white rounded-xl hover:bg-white hover:text-purple-600 transition font-bold text-lg hover:scale-105">
                            <i class="fas fa-arrow-down mr-3"></i>
                            Ver Más
                        </a>
                    </div>


                </div>

                <!-- Iconos Animados 3D -->
                <div class="hidden lg:block relative h-[600px]">

                    <div class="absolute inset-0 grid grid-cols-3 gap-6 p-8">

                        <!-- Fila 1 -->
                        <div class="floating-icon-1 flex items-center justify-center">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-box text-white text-5xl"></i>
                            </div>
                        </div>

                        <div class="floating-icon-2 flex items-center justify-center" style="animation-delay: 0.5s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-chart-line text-white text-5xl"></i>
                            </div>
                        </div>

                        <div class="floating-icon-3 flex items-center justify-center" style="animation-delay: 1s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-qrcode text-white text-5xl"></i>
                            </div>
                        </div>

                        <!-- Fila 2 -->
                        <div class="floating-icon-2 flex items-center justify-center" style="animation-delay: 0.3s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-clipboard-check text-white text-5xl"></i>
                            </div>
                        </div>

                        <!-- ICONO CENTRAL GRANDE -->
                        <div class="floating-icon-1 flex items-center justify-center scale-125">
                            <div class="bg-gradient-to-br from-yellow-400 to-yellow-500 p-10 rounded-3xl shadow-2xl border-4 border-white/50 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-warehouse text-purple-900 text-6xl"></i>
                            </div>
                        </div>

                        <div class="floating-icon-3 flex items-center justify-center" style="animation-delay: 0.7s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-map-marker-alt text-white text-5xl"></i>
                            </div>
                        </div>

                        <!-- Fila 3 -->
                        <div class="floating-icon-1 flex items-center justify-center" style="animation-delay: 1.2s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-users text-white text-5xl"></i>
                            </div>
                        </div>

                        <div class="floating-icon-2 flex items-center justify-center" style="animation-delay: 0.9s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-shield-alt text-white text-5xl"></i>
                            </div>
                        </div>

                        <div class="floating-icon-3 flex items-center justify-center" style="animation-delay: 0.4s;">
                            <div class="bg-white/20 backdrop-blur-md p-8 rounded-3xl shadow-2xl border-2 border-white/30 hover:scale-110 transition-transform cursor-pointer">
                                <i class="fas fa-file-export text-white text-5xl"></i>
                            </div>
                        </div>

                    </div>

                    <!-- Resplandor -->
                    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-yellow-300/20 rounded-full blur-3xl pointer-events-none"></div>
                </div>

            </div>
        </div>

        <!-- Ondas inferiores -->
        <div class="absolute bottom-0 left-0 right-0 pointer-events-none">
            <svg viewBox="0 0 1440 120" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full">
                <path d="M0 120L60 105C120 90 240 60 360 45C480 30 600 30 720 37.5C840 45 960 60 1080 67.5C1200 75 1320 75 1380 75L1440 75V120H1380C1320 120 1200 120 1080 120C960 120 840 120 720 120C600 120 480 120 360 120C240 120 120 120 60 120H0Z" fill="white"/>
            </svg>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- ESTADÍSTICAS -->
    <!-- ============================================ -->
    <section class="py-16 bg-white-forced">
        <div class="max-w-6xl mx-auto px-4">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div class="p-6">
                    <div class="stat-number">{{ \App\Models\Bien::count() }}</div>
                    <p class="text-gray-600 font-semibold mt-2">Bienes</p>
                    <p class="text-gray-400 text-sm">Registrados</p>
                </div>
                <div class="p-6">
                    <div class="stat-number">{{ \App\Models\Movimiento::count() }}</div>
                    <p class="text-gray-600 font-semibold mt-2">Movimientos</p>
                    <p class="text-gray-400 text-sm">Realizados</p>
                </div>
                <div class="p-6">
                    <div class="stat-number">{{ \App\Models\Ubicacion::count() }}</div>
                    <p class="text-gray-600 font-semibold mt-2">Ubicaciones</p>
                    <p class="text-gray-400 text-sm">Activas</p>
                </div>
                <div class="p-6">
                    <div class="stat-number">{{ \App\Models\User::count() }}</div>
                    <p class="text-gray-600 font-semibold mt-2">Usuarios</p>
                    <p class="text-gray-400 text-sm">Activos</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- MÓDULOS -->
    <!-- ============================================ -->
    <section id="features" class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4">

            <div class="text-center mb-16">
                <span class="inline-block px-4 py-2 bg-purple-100 text-purple-700 rounded-full font-semibold text-sm mb-4">
                    MÓDULOS PRINCIPALES
                </span>
                <h2 class="text-4xl md:text-5xl font-extrabold text-gray-800 mb-4">
                    Todo lo que <span class="gradient-text">necesitas</span>
                </h2>
                <p class="text-xl text-gray-600">Funcionalidades completas para gestionar tu inventario</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">

                <!-- Gestión de Bienes -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-blue-400 to-blue-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-box text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Gestión de Bienes</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Control total de tus activos con códigos patrimoniales, categorías y estados actualizados en tiempo real.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Códigos QR automáticos
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Categorización inteligente
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Estados en tiempo real
                        </li>
                    </ul>
                </div>

                <!-- Movimientos -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-purple-400 to-purple-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-exchange-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Movimientos</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Rastrea cada traslado, asignación o mantenimiento con historial completo y trazabilidad total.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Historial detallado
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Tipos personalizables
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Trazabilidad 100%
                        </li>
                    </ul>
                </div>

                <!-- Ubicaciones -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-map-marker-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Ubicaciones</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Organización espacial por sedes, ambientes y pisos para localizar cualquier bien al instante.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Multi-sede
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Búsqueda rápida
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Mapas visuales
                        </li>
                    </ul>
                </div>

                <!-- Responsables -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-user-tie text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Responsables</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Asignación clara de responsabilidades con control de quién tiene qué y dónde.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            DNI y cargos
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Múltiples asignaciones
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Historial completo
                        </li>
                    </ul>
                </div>

                <!-- Reportes -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-red-400 to-red-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-chart-bar text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Reportes</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Análisis completos y exportación de datos en múltiples formatos para mejor toma de decisiones.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Excel/PDF
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Gráficos visuales
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Filtros avanzados
                        </li>
                    </ul>
                </div>

                <!-- Seguridad -->
                <div class="bg-white rounded-2xl p-8 shadow-lg card-hover border-2 border-transparent hover:border-purple-200">
                    <div class="bg-gradient-to-br from-indigo-400 to-indigo-600 w-16 h-16 rounded-xl flex items-center justify-center mb-6 icon-glow">
                        <i class="fas fa-shield-alt text-white text-2xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-3">Seguridad</h3>
                    <p class="text-gray-600 mb-4 leading-relaxed">
                        Protección total con roles, permisos granulares y auditoría de cada acción en el sistema.
                    </p>
                    <ul class="space-y-2 text-sm text-gray-600">
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Roles personalizados
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Auditoría total
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-check-circle text-green-500 mr-2"></i>
                            Datos seguros
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- CALL TO ACTION -->
    <!-- ============================================ -->
    <section class="gradient-bg py-20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-72 h-72 bg-white rounded-full blur-3xl"></div>
            <div class="absolute bottom-10 right-10 w-96 h-96 bg-white rounded-full blur-3xl"></div>
        </div>

        <div class="max-w-4xl mx-auto text-center px-4 relative z-10">
            <div class="inline-block px-6 py-3 bg-white/20 backdrop-blur-sm rounded-full mb-6">
                <span class="text-white font-semibold">Comienza en segundos</span>
            </div>

            <h2 class="text-4xl md:text-5xl font-extrabold text-white mb-6">
                ¿Listo para transformar tu gestión?
            </h2>
            <p class="text-xl text-purple-100 mb-10 leading-relaxed">
                Únete a las instituciones que ya optimizan su inventario con GesInventario.
                Sin complicaciones, sin límites.
            </p>

            @guest
                <a href="{{ route('register') }}" class="inline-flex items-center px-10 py-5 bg-white text-purple-600 rounded-xl hover:bg-gray-100 transition shadow-2xl font-extrabold text-xl hover:scale-105">
                    <i class="fas fa-rocket mr-3"></i>
                    Empezar Gratis
                </a>
            @else
                <a href="{{ url('/dashboard') }}" class="inline-flex items-center px-10 py-5 bg-white text-purple-600 rounded-xl hover:bg-gray-100 transition shadow-2xl font-extrabold text-xl hover:scale-105">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Ir al Dashboard
                </a>
            @endguest

            <p class="text-purple-100 mt-6 text-sm">
                ✓ Sin tarjeta de crédito &nbsp;&nbsp;|&nbsp;&nbsp; ✓ Configuración en 2 minutos &nbsp;&nbsp;|&nbsp;&nbsp; ✓ Soporte 24/7
            </p>
        </div>
    </section>

    <!-- ============================================ -->
    <!-- FOOTER -->
    <!-- ============================================ -->
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="max-w-7xl mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-8">

                <div>
                    <h3 class="text-white text-2xl font-extrabold mb-4">
                        <span class="gradient-text">GesInventario</span>
                    </h3>
                    <p class="text-sm leading-relaxed">
                        Solución profesional para la gestión inteligente de inventarios.
                        Desarrollado con las mejores prácticas de ingeniería de software.
                    </p>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4">Producto</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="#features" class="hover:text-white transition">Módulos</a></li>
                        <li><a href="#" class="hover:text-white transition">Características</a></li>
                        <li><a href="#" class="hover:text-white transition">Precios</a></li>
                        <li><a href="#" class="hover:text-white transition">Actualizaciones</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4">Empresa</h4>
                    <ul class="space-y-2 text-sm">
                        <li><a href="{{ route('login') }}" class="hover:text-white transition">Iniciar Sesión</a></li>
                        @if (Route::has('register'))
                            <li><a href="{{ route('register') }}" class="hover:text-white transition">Registrarse</a></li>
                        @endif
                        <li><a href="#" class="hover:text-white transition">Soporte</a></li>
                        <li><a href="#" class="hover:text-white transition">Documentación</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="text-white font-bold mb-4">Stack Tecnológico</h4>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-3 py-1 bg-gray-800 rounded-lg text-xs hover:bg-gray-700 transition cursor-pointer">
                            <i class="fab fa-laravel text-red-400"></i> Laravel 10
                        </span>
                        <span class="px-3 py-1 bg-gray-800 rounded-lg text-xs hover:bg-gray-700 transition cursor-pointer">
                            <i class="fab fa-php text-indigo-400"></i> PHP 8.2
                        </span>
                        <span class="px-3 py-1 bg-gray-800 rounded-lg text-xs hover:bg-gray-700 transition cursor-pointer">
                            <i class="fas fa-database text-blue-400"></i> PostgreSQL
                        </span>
                        <span class="px-3 py-1 bg-gray-800 rounded-lg text-xs hover:bg-gray-700 transition cursor-pointer">
                            <i class="fab fa-js text-yellow-400"></i> JavaScript
                        </span>
                    </div>
                </div>

            </div>

            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
                <p>&copy; {{ date('Y') }} GesInventario. Todos los derechos reservados.</p>
                <p>
                    <i class="fas fa-code text-purple-500"></i>
                    Desarrollado por ingenieros apasionados
                </p>
                <p class="text-xs">
                    Laravel {{ Illuminate\Foundation\Application::VERSION }} | PHP {{ PHP_VERSION }}
                </p>
            </div>
        </div>
    </footer>

</body>
</html>
