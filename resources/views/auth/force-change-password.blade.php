<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Changer le mot de passe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white rounded-2xl shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-2">Changer votre mot de passe</h1>
        <p class="text-sm text-gray-600 mb-6">
            Pour des raisons de sécurité, vous devez définir un nouveau mot de passe avant de continuer.
        </p>

        @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 text-red-700 p-3 text-sm">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('password.force.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium mb-1">Nouveau mot de passe</label>
                <input
                    type="password"
                    name="password"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                >
            </div>

            <div>
                <label class="block text-sm font-medium mb-1">Confirmation du mot de passe</label>
                <input
                    type="password"
                    name="password_confirmation"
                    required
                    class="w-full rounded-lg border border-gray-300 px-3 py-2"
                >
            </div>

            <button
                type="submit"
                class="w-full rounded-lg bg-blue-600 text-white py-2 font-medium hover:bg-blue-700"
            >
                Mettre à jour
            </button>
        </form>
    </div>
</body>
</html>