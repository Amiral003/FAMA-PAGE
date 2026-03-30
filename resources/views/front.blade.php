<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">

    <!-- ✅ LA LIGNE QUI MANQUE -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ config('app.name', 'FAMA - Portail Officiel') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">

    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"></div>
</body>
</html>