<script setup>
import { Form, Head, Link } from '@inertiajs/vue3';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';
</script>

<template>
    <Head title="Register" />

    <div class="relative min-h-screen w-full overflow-hidden bg-[#070c18] text-slate-100">
        <!-- Dot grid background -->
        <div
            class="pointer-events-none absolute inset-0 opacity-40"
            style="background-image: radial-gradient(circle, rgba(99,102,241,0.35) 1px, transparent 1px); background-size: 28px 28px;"
        ></div>

        <div class="relative grid min-h-screen lg:grid-cols-2">
            <!-- ── Left branding panel ── -->
            <div class="hidden flex-col justify-between border-r border-slate-800/50 bg-[#060a14]/90 p-12 lg:flex">
                <!-- Logo -->
                <div class="flex items-center gap-2.5">
                    <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-300">
                        <svg class="h-5 w-5" viewBox="0 0 24 24">
                            <path d="M13.2 2.6 6.4 13.4h4.7l-1.3 8.1 6.8-10.8h-4.7z" fill="currentColor" />
                        </svg>
                    </span>
                    <span class="font-[Manrope] text-xl font-bold tracking-tight">Run Fast</span>
                </div>

                <!-- Headline -->
                <div class="space-y-5">
                    <h2 class="font-[Manrope] text-4xl font-bold leading-[1.15] tracking-tight text-white xl:text-5xl">
                        Accelerate your<br />workflow with<br />precision.
                    </h2>
                    <p class="max-w-xs text-sm leading-relaxed text-slate-400">
                        Join thousands of teams scaling their operations on the most reliable performance-driven SaaS platform. Experience high-speed data delivery and seamless user integration.
                    </p>
                </div>

                <!-- Feature badges -->
                <div class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 rounded-full border border-slate-700/60 bg-slate-800/40 px-4 py-2 text-xs font-medium tracking-wide text-slate-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-indigo-400"></span>
                        ULTRA LOW LATENCY
                    </div>
                    <div class="flex items-center gap-2 rounded-full border border-slate-700/60 bg-slate-800/40 px-4 py-2 text-xs font-medium tracking-wide text-slate-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        ENTERPRISE GRADE
                    </div>
                </div>
            </div>

            <!-- ── Right form panel ── -->
            <div class="flex items-center justify-center px-6 py-12">
                <div class="w-full max-w-sm space-y-6">

                    <!-- Mobile logo -->
                    <div class="flex items-center justify-center gap-2.5 lg:hidden">
                        <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-500/20 text-indigo-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24">
                                <path d="M13.2 2.6 6.4 13.4h4.7l-1.3 8.1 6.8-10.8h-4.7z" fill="currentColor" />
                            </svg>
                        </span>
                        <span class="font-[Manrope] text-xl font-bold tracking-tight">Run Fast</span>
                    </div>

                    <!-- Form card -->
                    <div class="rounded-2xl border border-slate-700/50 bg-[#0d1525] p-8 shadow-[0_32px_64px_rgba(0,0,0,0.6)]">
                        <div class="mb-7">
                            <h1 class="font-[Manrope] text-2xl font-bold text-white">Create Account</h1>
                            <p class="mt-1.5 text-sm text-slate-400">
                                Start your 14-day free trial today.
                            </p>
                        </div>

                        <Form
                            v-bind="store.form()"
                            :reset-on-success="['password', 'password_confirmation']"
                            v-slot="{ errors, processing }"
                            class="flex flex-col gap-5"
                        >
                            <!-- Full Name -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="name"
                                    class="font-[JetBrains_Mono] text-[10px] uppercase tracking-[0.1em] text-slate-400"
                                >Full Name</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    name="name"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="name"
                                    placeholder="John Doe"
                                    class="rounded-lg border border-slate-700/60 bg-slate-900/70 px-3.5 py-2.5 text-slate-100 placeholder:text-slate-600 focus-visible:ring-2 focus-visible:ring-indigo-500/50"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <!-- Email -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="email"
                                    class="font-[JetBrains_Mono] text-[10px] uppercase tracking-[0.1em] text-slate-400"
                                >Email Address</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    name="email"
                                    required
                                    :tabindex="2"
                                    autocomplete="email"
                                    placeholder="john@company.com"
                                    class="rounded-lg border border-slate-700/60 bg-slate-900/70 px-3.5 py-2.5 text-slate-100 placeholder:text-slate-600 focus-visible:ring-2 focus-visible:ring-indigo-500/50"
                                />
                                <InputError :message="errors.email" />
                            </div>

                            <!-- Password -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="password"
                                    class="font-[JetBrains_Mono] text-[10px] uppercase tracking-[0.1em] text-slate-400"
                                >Password</Label>
                                <PasswordInput
                                    id="password"
                                    name="password"
                                    required
                                    :tabindex="3"
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="rounded-lg border border-slate-700/60 bg-slate-900/70 px-3.5 py-2.5 text-slate-100 placeholder:text-slate-600 focus-visible:ring-2 focus-visible:ring-indigo-500/50"
                                />
                                <InputError :message="errors.password" />
                            </div>

                            <!-- Confirm Password -->
                            <div class="flex flex-col gap-1.5">
                                <Label
                                    for="password_confirmation"
                                    class="font-[JetBrains_Mono] text-[10px] uppercase tracking-[0.1em] text-slate-400"
                                >Confirm Password</Label>
                                <PasswordInput
                                    id="password_confirmation"
                                    name="password_confirmation"
                                    required
                                    :tabindex="4"
                                    autocomplete="new-password"
                                    placeholder="••••••••"
                                    class="rounded-lg border border-slate-700/60 bg-slate-900/70 px-3.5 py-2.5 text-slate-100 placeholder:text-slate-600 focus-visible:ring-2 focus-visible:ring-indigo-500/50"
                                />
                                <InputError :message="errors.password_confirmation" />
                            </div>

                            <!-- Terms -->
                            <div class="flex items-start gap-2.5">
                                <Checkbox id="terms" name="terms" :tabindex="5" class="mt-0.5" />
                                <Label for="terms" class="text-sm leading-relaxed text-slate-400">
                                    I agree to the
                                    <a href="#" class="text-indigo-300 transition hover:text-indigo-200">Terms of Service</a>
                                    and
                                    <a href="#" class="text-indigo-300 transition hover:text-indigo-200">Privacy Policy</a>.
                                </Label>
                            </div>

                            <!-- Submit -->
                            <Button
                                type="submit"
                                class="w-full rounded-lg bg-indigo-500 py-2.5 text-sm font-semibold text-white shadow-[0_0_28px_rgba(99,102,241,0.45)] transition hover:bg-indigo-400"
                                :tabindex="6"
                                :disabled="processing"
                                data-test="register-user-button"
                            >
                                <Spinner v-if="processing" />
                                Sign Up &rarr;
                            </Button>

                            <!-- Login link -->
                            <div class="border-t border-slate-700/50 pt-4">
                                <p class="text-center text-sm text-slate-400">
                                    Already have an account?
                                    <Link :href="login()" class="ml-1 font-medium text-indigo-300 transition hover:text-indigo-200" :tabindex="7">
                                        Log In
                                    </Link>
                                </p>
                            </div>

                            <!-- Divider -->
                            <div class="grid grid-cols-[1fr_auto_1fr] items-center gap-3">
                                <span class="h-px bg-slate-700/60"></span>
                                <span class="font-[JetBrains_Mono] text-[10px] tracking-[0.1em] text-slate-500">OR REGISTER WITH</span>
                                <span class="h-px bg-slate-700/60"></span>
                            </div>

                            <!-- Social buttons -->
                            <div class="grid grid-cols-2 gap-3">
                                <button
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-slate-700/60 bg-slate-800/30 px-3 py-2 text-sm text-slate-300 transition hover:bg-slate-800/70"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24">
                                        <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="currentColor" />
                                        <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="currentColor" />
                                        <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z" fill="currentColor" />
                                        <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 12-4.53z" fill="currentColor" />
                                    </svg>
                                    Google
                                </button>
                                <button
                                    type="button"
                                    class="flex items-center justify-center gap-2 rounded-lg border border-slate-700/60 bg-slate-800/30 px-3 py-2 text-sm text-slate-300 transition hover:bg-slate-800/70"
                                >
                                    <svg class="h-4 w-4" viewBox="0 0 24 24">
                                        <path d="M12 2C6.477 2 2 6.477 2 12c0 4.419 2.865 8.166 6.839 9.489.5.092.682-.217.682-.482 0-.237-.008-.866-.013-1.7-2.782.603-3.369-1.341-3.369-1.341-.454-1.152-1.11-1.459-1.11-1.459-.908-.62.069-.608.069-.608 1.003.07 1.531 1.03 1.531 1.03.892 1.529 2.341 1.087 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.11-4.555-4.943 0-1.091.39-1.984 1.029-2.683-.103-.253-.446-1.27.098-2.647 0 0 .84-.269 2.75 1.025A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.294 2.747-1.025 2.747-1.025.546 1.377.202 2.394.1 2.647.64.699 1.028 1.592 1.028 2.683 0 3.842-2.339 4.687-4.566 4.935.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482C19.138 20.161 22 16.416 22 12c0-5.523-4.477-10-10-10z" fill="currentColor" />
                                    </svg>
                                    GitHub
                                </button>
                            </div>
                        </Form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
