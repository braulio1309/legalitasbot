<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LegalitasBot - Consultor Legal IA | Respuestas Legales Instantaneas</title>
    <script src="https://js.stripe.com/v3/"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Styles -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        {!! $css !!}
    </style>
    <!-- Livewire Styles -->
    @livewireStyles

</head>
<body>
    <!-- Floating particles -->
    <div class="particles" id="particles"></div>

    <div class="container">
        @yield('content')
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts
    <script>
        // JavaScript compartido
        {!! $js !!}
    </script>
    @stack('scripts')
</body>
</html>