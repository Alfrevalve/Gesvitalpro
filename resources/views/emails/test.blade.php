<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .container {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 20px;
            margin-top: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #4e73df;
            margin: 0;
            padding: 0;
        }
        .content {
            background-color: white;
            border-radius: 5px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .footer {
            text-align: center;
            font-size: 0.9em;
            color: #6c757d;
        }
        .details {
            margin: 20px 0;
            padding: 15px;
            background-color: #e9ecef;
            border-radius: 5px;
        }
        .details p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Prueba de Email Exitosa</h1>
        </div>

        <div class="content">
            <p>Este es un email de prueba enviado desde GesVitalPro para verificar la configuración del servidor de correo.</p>
            
            <div class="details">
                <p><strong>Fecha y Hora:</strong> {{ $timestamp }}</p>
                <p><strong>Servidor SMTP:</strong> {{ $server }}</p>
                <p><strong>Puerto:</strong> {{ $port }}</p>
                <p><strong>Encriptación:</strong> {{ $encryption ?? 'Ninguna' }}</p>
            </div>

            <p>Si has recibido este email, significa que la configuración de correo se ha realizado correctamente.</p>
        </div>

        <div class="footer">
            <p>Este es un email automático, por favor no responder.</p>
            <p>&copy; {{ date('Y') }} GesVitalPro. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
