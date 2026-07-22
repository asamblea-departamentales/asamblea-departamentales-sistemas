<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Acceso Restringido — {{ config('app.name') }}</title>
    <link rel="icon" href="{{ asset('images/logo-asamblea1.png') }}">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f9fafb;
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1), 0 1px 2px rgba(0,0,0,0.06);
            padding: 3rem 2.5rem;
            max-width: 28rem;
            width: 90%;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            object-fit: contain;
        }
        .icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: #fef2f2;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-circle svg {
            width: 40px;
            height: 40px;
            color: #dc2626;
        }
        h1 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            color: #111827;
        }
        .message {
            color: #6b7280;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 0.5rem;
        }
        .detail {
            color: #9ca3af;
            font-size: 0.85rem;
            line-height: 1.5;
            margin-bottom: 2rem;
        }
        .btn {
            display: inline-block;
            background: #0A2C65;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 0.5rem;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn:hover {
            background: #081f4d;
        }
        .footer {
            margin-top: 2rem;
            color: #d1d5db;
            font-size: 0.75rem;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon-circle">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        </div>

        <h1>Acceso Restringido</h1>

        <p class="message">
            Tu cuenta no tiene acceso al sistema.
        </p>

        <p class="detail">
            Pedile al administrador que te cree un usuario o que active tu cuenta.
        </p>

        <a href="{{ route('filament.admin.auth.login') }}" class="btn">
            Iniciar Sesión
        </a>

        <div class="footer">
            {{ config('app.name') }} &mdash; Asamblea Legislativa de El Salvador
        </div>
    </div>
</body>
</html>
