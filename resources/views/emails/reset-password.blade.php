<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña - GesInventario</title>
    <style>
        body {
            font-family: 'Inter', 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            -webkit-font-smoothing: antialiased;
        }

        .email-wrapper {
            width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 50px 30px;
            text-align: center;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 40px;
        }

        .header h1 {
            color: white;
            margin: 0;
            font-size: 32px;
            font-weight: 800;
        }

        .header p {
            color: rgba(255,255,255,0.9);
            margin: 10px 0 0 0;
            font-size: 16px;
        }

        .content {
            padding: 50px 40px;
        }

        .greeting {
            font-size: 24px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
        }

        .message {
            font-size: 16px;
            line-height: 1.6;
            color: #555;
            margin-bottom: 30px;
        }

        .btn-container {
            text-align: center;
            margin: 40px 0;
        }

        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 16px 50px;
            border-radius: 10px;
            font-weight: bold;
            font-size: 18px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }

        .alert-box {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 30px 0;
            border-radius: 8px;
        }

        .alert-box strong {
            color: #856404;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin: 30px 0;
            border-radius: 8px;
            font-size: 14px;
            color: #0c5460;
        }

        .url-fallback {
            background: #f7f7f7;
            padding: 15px;
            border-radius: 8px;
            word-break: break-all;
            font-size: 13px;
            color: #667eea;
            margin-top: 20px;
        }

        .footer {
            background: #f7f7f7;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .footer p {
            margin: 5px 0;
            font-size: 13px;
            color: #999;
        }

        .footer-links {
            margin-top: 15px;
        }

        .footer-links a {
            color: #667eea;
            text-decoration: none;
            margin: 0 10px;
            font-weight: 600;
        }

        .security-tips {
            background: #f0f0f0;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }

        .security-tips h3 {
            color: #333;
            font-size: 16px;
            margin-top: 0;
        }

        .security-tips ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .security-tips li {
            font-size: 14px;
            color: #666;
            margin: 8px 0;
        }

        /* Responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }

            .content {
                padding: 30px 20px;
            }

            .header {
                padding: 30px 20px;
            }

            .btn {
                padding: 14px 40px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">

            <!-- Header -->
            <div class="header">
                <h1>GesInventario</h1>
                <p>Sistema Inteligente de Gestión de Inventario</p>
            </div>

            <!-- Content -->
            <div class="content">
                <div class="greeting">¡Hola{{ isset($notifiable->name) ? ', ' . explode(' ', $notifiable->name)[0] : '' }}!</div>

                <div class="message">
                    <p>Recibiste este correo porque recibimos una <strong>solicitud de restablecimiento de contraseña</strong> para tu cuenta de GesInventario.</p>

                    <p>Para crear una nueva contraseña segura, haz clic en el botón de abajo:</p>
                </div>

                <!-- Button -->
                <div class="btn-container">
                    <a href="{{ $url }}" class="btn">
                        🔓 Restablecer Contraseña
                    </a>
                </div>

                <!-- Alert -->
                <div class="alert-box">
                    <strong>Importante:</strong> Este enlace de restablecimiento expirará en <strong>60 minutos</strong> por seguridad.
                </div>

                <!-- Info Box -->
                <div class="info-box">
                    <strong>🛡️ ¿No solicitaste esto?</strong><br>
                    Si no solicitaste restablecer tu contraseña, puedes ignorar este mensaje de forma segura. Tu cuenta no ha sido comprometida.
                </div>

                <!-- URL Fallback -->
                <p style="font-size: 14px; color: #666; margin-top: 30px;">
                    Si tienes problemas al hacer clic en el botón "Restablecer Contraseña", copia y pega esta URL en tu navegador:
                </p>
                <div class="url-fallback">
                    {{ $url }}
                </div>

                <!-- Security Tips -->
                <div class="security-tips">
                    <h3>💡 Consejos de Seguridad:</h3>
                    <ul>
                        <li>Usa una contraseña de al menos 8 caracteres</li>
                        <li>Combina letras mayúsculas, minúsculas y números</li>
                        <li>No compartas tu contraseña con nadie</li>
                        <li>Usa una contraseña única para GesInventario</li>
                    </ul>
                </div>
            </div>

            <!-- Footer -->
            <div class="footer">
                <p><strong>GesInventario</strong> - Sistema de Gestión de Inventario</p>
                <p>&copy; {{ date('Y') }} Todos los derechos reservados.</p>

                <div class="footer-links">
                    <a href="{{ url('/') }}">Inicio</a> |
                    <a href="{{ route('login') }}">Iniciar Sesión</a> |
                    <a href="#">Soporte</a>
                </div>

                <p style="margin-top: 20px; font-size: 11px;">
                    Este es un correo automático, por favor no respondas a este mensaje.
                </p>
            </div>

        </div>
    </div>
</body>
</html>
