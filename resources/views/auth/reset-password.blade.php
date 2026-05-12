<x-guest-layout :card="false">
    <div class="fixed inset-0 z-0 bg-[#fafafa]"></div>

    <div class="relative z-10 flex min-h-screen items-center justify-center px-4 py-6">
        <div class="w-full max-w-[420px]">
            <div class="rounded-2xl border border-gray-200 bg-white px-8 py-8 shadow-sm sm:px-10 sm:py-10">
                {{-- Header --}}
                <div class="mb-6">
                    <h1 class="text-xl font-semibold tracking-tight text-gray-900">
                        Create new password
                    </h1>
                    <p class="mt-2 text-sm leading-relaxed text-gray-500">
                        Choose a strong password to secure your account.
                    </p>
                </div>

                <form method="POST" action="{{ route('password.store') }}" id="resetForm">
                    @csrf

                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    {{-- Email (readonly) --}}
                    <div>
                        <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Email address
                        </label>
                        <input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $request->email) }}"
                            required
                            readonly
                            class="block w-full cursor-not-allowed rounded-lg border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-500 outline-none"
                        />
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    {{-- New Password --}}
                    <div class="mt-4">
                        <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">
                            New password
                        </label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autofocus
                            autocomplete="new-password"
                            placeholder="Enter new password"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-[#8a0b4e] focus:ring-1 focus:ring-[#8a0b4e]"
                        />
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    {{-- Confirm Password --}}
                    <div class="mt-4">
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">
                            Confirm password
                        </label>
                        <input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            placeholder="Confirm new password"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 outline-none transition focus:border-[#8a0b4e] focus:ring-1 focus:ring-[#8a0b4e]"
                        />
                        <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1.5" />
                    </div>

                    {{-- Submit --}}
                    <div class="mt-6">
                        <button
                            type="submit"
                            id="submitBtn"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#8a0b4e] px-4 py-2.5 text-sm font-medium text-white transition hover:bg-[#6f083f] disabled:opacity-50"
                        >
                            <span id="btnText">Reset password</span>
                            <svg id="btnSpinner" class="hidden h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </button>
                    </div>

                    {{-- Back --}}
                    <div class="mt-3 text-center">
                        <a
                            href="{{ route('login') }}"
                            class="inline-flex items-center gap-1.5 text-xs text-gray-400 transition hover:text-[#8a0b4e]"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m10 19-7-7m0 0 7-7m-7 7h18"/>
                            </svg>
                            Back to login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('resetForm')?.addEventListener('submit', function () {
            const btn = document.getElementById('submitBtn');
            btn && (btn.disabled = true);

            document.getElementById('btnText')?.classList.add('hidden');
            document.getElementById('btnSpinner')?.classList.remove('hidden');
        });
    </script>
</x-guest-layout>