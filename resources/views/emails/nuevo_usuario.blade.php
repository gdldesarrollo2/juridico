<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso al sistema jurídico</title>
</head>
<body style="font-family: Arial, sans-serif; color:#111827;">
    <h2>Hola {{ $user->name }},</h2>

    <p>Se ha creado una cuenta para ti en el sistema jurídico.</p>

    <p><strong>Datos de acceso:</strong></p>
    <ul>
        <li><strong>Usuario (email):</strong> {{ $user->email }}</li>
        <li><strong>Contraseña temporal:</strong> {{ $password }}</li>
        <li><strong>Rol asignado:</strong> {{ $role }}</li>
    </ul>

    <p>
        Puedes ingresar al sistema en el siguiente enlace:<br>
        <a href="{{ $loginUrl }}">{{ $loginUrl }}</a>
    </p>

    <p style="font-size: 12px; color:#6b7280;">
        Te recomendamos cambiar tu contraseña después de iniciar sesión por primera vez.
    </p>

    <p>Saludos,<br>Equipo de Jurídico</p>
</body>
</html>
