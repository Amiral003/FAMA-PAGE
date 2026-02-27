<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title inertia>{{ config('app.name', 'FAMA - Portail Officiel') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    @vite('resources/js/app.js')
</head>
<body>
    <div id="app"></div>
</body>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Sélectionne tous les liens à l'intérieur du contenu du post
        const links = document.querySelectorAll('.text-content a');
        links.forEach(link => {
            link.setAttribute('target', '_blank');
            link.setAttribute('rel', 'noopener noreferrer'); // Sécurité critique
        });
    });
</script>
</html>
