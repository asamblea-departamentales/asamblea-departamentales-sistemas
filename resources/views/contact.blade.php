<!DOCTYPE html>
<html>
<head>
    <title>Contacto</title>
</head>
<body>
    <h1>Formulario de Contacto</h1>
    <form method="POST" action="{{ route('contact.submit') }}">
        @csrf
        <!-- campos del formulario -->
    </form>
</body>
</html>
