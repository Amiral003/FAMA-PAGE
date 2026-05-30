<x-guest-layout>
    <div class="min-h-screen flex items-center justify-center px-4 py-10 bg-[#243125]">
        <div class="w-full max-w-2xl rounded-3xl border border-yellow-500/20 bg-[#1f261b] shadow-2xl p-8">

            <div class="mb-8 text-center">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-yellow-500/10 mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg"
                         class="w-8 h-8 text-yellow-400"
                         fill="none"
                         viewBox="0 0 24 24"
                         stroke="currentColor">
                        <path stroke-linecap="round"
                              stroke-linejoin="round"
                              stroke-width="1.8"
                              d="M12 15v2m0-8h.01M5.07 19H18.93c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>

                <h1 class="text-3xl font-black text-white tracking-tight">
                    Sécurisation du compte
                </h1>

                <p class="mt-3 text-sm leading-6 text-gray-300">
                    Configurez l’authentification à deux facteurs avant
                    d’accéder à l’administration FAMa.
                </p>
            </div>

            <div class="rounded-2xl border border-yellow-500/10 bg-[#182117] p-6">

                @if (! auth()->user()->two_factor_secret)

                    <form method="POST" action="/user/two-factor-authentication">
                        @csrf

                        <button
                            type="submit"
                            class="w-full rounded-2xl bg-yellow-400 hover:bg-yellow-300 transition-all duration-200 px-6 py-4 text-base font-black text-[#172216] shadow-lg"
                        >
                            Activer l’authentification à deux facteurs
                        </button>
                    </form>

                @else

                    <div class="space-y-6">

                        <div>
                            <h2 class="text-lg font-bold text-yellow-400 mb-2">
                                1. Scanner le QR Code
                            </h2>

                            <p class="text-sm text-gray-300 mb-4">
                                Utilisez Google Authenticator, Microsoft Authenticator
                                ou une application TOTP compatible.
                            </p>

                            <div class="flex justify-center rounded-2xl bg-white p-6">
                                {!! auth()->user()->twoFactorQrCodeSvg() !!}
                            </div>
                        </div>

                        <div>
                            <h2 class="text-lg font-bold text-yellow-400 mb-2">
                                2. Entrer le code à 6 chiffres
                            </h2>

                            <form method="POST" action="/user/confirmed-two-factor-authentication">
                                @csrf

                                <input
                                    type="text"
                                    name="code"
                                    inputmode="numeric"
                                    autocomplete="one-time-code"
                                    placeholder="123456"
                                    class="w-full rounded-2xl border border-yellow-500/20 bg-[#243125] text-white px-5 py-4 text-center tracking-[0.5em] text-2xl font-black focus:ring-2 focus:ring-yellow-400 focus:outline-none"
                                >

                                <button
                                    type="submit"
                                    class="mt-5 w-full rounded-2xl bg-green-600 hover:bg-green-500 transition-all duration-200 px-6 py-4 text-base font-black text-white shadow-lg"
                                >
                                    Confirmer et accéder à l’administration
                                </button>
                            </form>
                        </div>

                    </div>

                @endif

            </div>
        </div>
    </div>
</x-guest-layout>