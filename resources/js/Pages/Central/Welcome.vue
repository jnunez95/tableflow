<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <Head title="TableFlow" />

    <div class="min-h-screen bg-deep-navy text-white">
        <div class="mx-auto flex min-h-screen max-w-5xl flex-col justify-center px-margin-mobile py-16 md:px-margin-desktop">
            <p class="font-label-lg text-label-lg uppercase tracking-[0.2em] text-white/60">
                Restaurant Management Platform
            </p>
            <h1 class="mt-4 font-display-lg text-display-lg">
                TableFlow
            </h1>
            <p class="mt-6 max-w-2xl font-body-lg text-body-lg text-white/80">
                Automate your restaurant business flow with multi-tenant digital dining experiences,
                powered by Laravel 13, Inertia, and Filament.
            </p>

            <div class="mt-10 flex flex-wrap gap-4">
                <Link
                    v-if="canRegister"
                    :href="route('register')"
                    class="rounded-lg bg-terracotta-accent px-6 py-3 font-label-lg text-label-lg text-white transition hover:opacity-90"
                >
                    Register Restaurant
                </Link>
                <Link
                    v-if="canLogin"
                    :href="route('login')"
                    class="rounded-lg border border-white/20 px-6 py-3 font-label-lg text-label-lg text-white transition hover:bg-white/10"
                >
                    Sign In
                </Link>
                <a
                    href="/admin"
                    class="rounded-lg border border-white/20 px-6 py-3 font-label-lg text-label-lg text-white transition hover:bg-white/10"
                >
                    Admin Panel
                </a>
            </div>

            <p class="mt-12 text-sm text-white/40">
                Laravel v{{ laravelVersion }} · PHP v{{ phpVersion }}
            </p>
        </div>
    </div>
</template>
