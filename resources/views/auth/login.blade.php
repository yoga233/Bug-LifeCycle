<x-guest-layout :card="false">
    <div class="w-full max-w-5xl overflow-hidden rounded-2xl bg-white shadow-xl shadow-[#8a0b4e]/20">
        <div class="grid grid-cols-1 md:grid-cols-2 items-stretch">
            <!-- Panel Publik (tanpa login) -->
            <section class="relative flex h-full w-full flex-col overflow-hidden bg-[#8a0b4e] px-8 py-10 text-white md:px-10 md:py-12">
                {{-- Decorative Background Elements --}}
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    {{-- Photo/gradient background --}}
                    <div class="absolute inset-0 opacity-20" style="background-image: url('{{ asset('images/auth/login-left.webp') }}'); background-size: cover; background-position: center;"></div>

                    {{-- Darken overlay --}}
                    <div class="absolute inset-0 bg-[#3b031f]/45"></div>

                    {{-- Dot grid for premium texture --}}
                    <div class="absolute inset-0 opacity-[0.12] bg-[radial-gradient(circle_at_1px_1px,rgba(255,255,255,0.4)_1px,transparent_0)] [background-size:20px_20px]"></div>

                    {{-- Subtle highlight --}}
                    <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-0 w-64 h-64 bg-[#d48ab0]/20 rounded-full blur-3xl"></div>
                </div>

                {{-- Content --}}
                <div class="relative z-10 mx-auto flex h-full w-full max-w-sm flex-col justify-between">
                    {{-- TOP --}}
                    <div class="space-y-6">
                        <div class="flex items-center gap-3 text-xs font-semibold uppercase tracking-widest text-[#f5dbe8]/90">
                            <span class="h-px w-10 bg-white/30"></span>
                            <span>Client Portal</span>
                        </div>

                        <div class="space-y-2">
                            <h2 class="text-3xl font-bold leading-tight tracking-tight text-white md:text-4xl">
                                Ticket Service Center
                            </h2>
                            <p class="text-lg font-light text-[#f3d8e6]">
                                PRANALA BLMS
                            </p>
                        </div>

                        <div class="border-l-2 border-white/30 pl-4">
                            <p class="text-sm leading-relaxed text-[#f8e9f1]/90">
                                Report technical issues or track your application fix progress through this portal.
                                <br><br>
                                <span class="font-semibold text-white">No login required.</span>
                                Use your <span class="font-semibold text-white">Ticket Number</span> for instant and transparent access.
                            </p>
                        </div>
                    </div>

                    {{-- BOTTOM CTA --}}
                    <div class="mt-8">
                        <a
                            href="{{ route('client.landing') }}"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-white px-5 py-3.5 text-sm font-semibold text-[#8a0b4e] shadow-lg shadow-[#8a0b4e]/20 transition hover:bg-[#f9edf3] hover:shadow-xl focus:outline-none focus:ring-2 focus:ring-[#f5dbe8]"
                        >
                            <span>Access Service Portal</span>
                            <x-lucide name="arrow-right" class="h-4 w-4" />
                        </a>

                        <p class="mt-3 text-xs text-[#f0cfe0]/80">
                            *You will be redirected to the bug reporting page.
                        </p>
                    </div>
                </div>
            </section>

            <!-- Panel Internal (login) -->
            <section class="bg-white px-8 py-10 md:px-10 md:py-12 flex flex-col justify-center">
                <div class="mx-auto w-full max-w-sm">
                    <div class="flex items-center gap-2 text-xs font-semibold text-slate-500">
                        <x-lucide name="lock" class="h-4 w-4" />
                        <span>Internal Access (Employees)</span>
                    </div>

                    <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">
                        Sign in to PRANALA BLMS
                    </h1>
                    <p class="mt-1 text-sm text-slate-600">
                        Use your company account to access the internal dashboard.
                    </p>

                    <x-auth-session-status class="mt-6" :status="session('status')" />

                    <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                        @csrf

                        <div>
                            <x-input-label for="email" value="Email" class="text-xs font-medium text-slate-600" />
                            <x-text-input
                                id="email"
                                class="mt-2 block w-full rounded-xl px-3 py-2.5 focus:!border-[#8a0b4e] focus:!ring-[#8a0b4e]"
                                type="email"
                                name="email"
                                :value="old('email')"
                                required
                                autofocus
                                autocomplete="username"
                            />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="password" value="Password" class="text-xs font-medium text-slate-600" />
                            <x-text-input
                                id="password"
                                class="mt-2 block w-full rounded-xl px-3 py-2.5 focus:!border-[#8a0b4e] focus:!ring-[#8a0b4e]"
                                type="password"
                                name="password"
                                required
                                autocomplete="current-password"
                            />
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>

                        <label for="remember_me" class="flex items-center gap-2 pt-1 text-sm text-slate-600">
                            <input
                                id="remember_me"
                                type="checkbox"
                                class="rounded border-slate-300 text-[#8a0b4e] shadow-sm focus:ring-[#8a0b4e]"
                                name="remember"
                            >
                            <span>Remember Me</span>
                        </label>

                        <div class="space-y-1 pt-1">
                            <div>
                                <x-primary-button class="w-full justify-center rounded-xl py-3 text-sm normal-case tracking-normal !bg-[#8a0b4e] hover:!bg-[#730a41] focus:!bg-[#730a41] active:!bg-[#5d0834] focus:!ring-[#8a0b4e]">
                                    Sign In
                                </x-primary-button>
                            </div>

                            @if (Route::has('password.request'))
                                <div class="text-center">
                                    <a
                                        class="text-xs font-medium text-slate-500 hover:text-[#8a0b4e] underline-offset-4 hover:underline"
                                        href="{{ route('password.request') }}"
                                    >
                                        Forgot password?
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </div>
</x-guest-layout>