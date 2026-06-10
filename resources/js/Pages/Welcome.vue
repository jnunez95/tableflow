<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    tenant: {
        type: Object,
        default: null,
    },
    tableUuid: {
        type: String,
        default: null,
    },
});

const selectedLanguage = ref(localStorage.getItem('tableflow-language') ?? 'es');
const isVisible = ref(false);

const heroImage = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCt51RRF8Sux4nomaaSnQflNJAHC50vtVWKm9qW0sCISUUqgc5L56qjI_jxsO-xBNv6pgIzei15NfOCrIqCotL8Xy0U-lLbzKArcAUCUNYDFuGuKJTTDYuRqFr9fZNfL2F2XjrewFCr-JNz7K_jv9fLOC5oyMmIYEFgp73HSp1GvwHrMTOQ5hsHkAAuQJtRB2TGHoRlgJ4-clSNXha7sQmU0_McAjdIpM8ASmfVJdoQS91CgccZiDFyCJJMDMjJ-VdeUTVIMOBFWfGP';

const languages = [
    { code: 'en', label: 'English', subtitle: 'English' },
    { code: 'es', label: 'Español', subtitle: 'Spanish' },
];

const copy = {
    en: {
        pageTitle: 'Welcome',
        tagline: 'Welcome to Excellence',
        headline: 'An Artful Culinary Journey',
        description: 'Experience a meticulously curated menu designed for the discerning palate.',
        beginOrder: 'Begin Order',
        selectLanguage: 'Select Language',
        privacyPolicy: 'Privacy Policy',
        termsOfService: 'Terms of Service',
        accessibility: 'Accessibility',
    },
    es: {
        pageTitle: 'Bienvenida',
        tagline: 'Bienvenido a la Excelencia',
        headline: 'Un Viaje Culinario con Arte',
        description: 'Disfruta de un menú cuidadosamente curado para el paladar más exigente.',
        beginOrder: 'Comenzar Orden',
        selectLanguage: 'Seleccionar Idioma',
        privacyPolicy: 'Política de Privacidad',
        termsOfService: 'Términos de Servicio',
        accessibility: 'Accesibilidad',
    },
};

const restaurantName = props.tenant?.name ?? 'LUMIÈRE DINING';

const t = computed(() => copy[selectedLanguage.value] ?? copy.es);

const pageTitle = computed(() => `${restaurantName} | ${t.value.pageTitle}`);

const beginOrder = () => {
    if (!props.tableUuid) {
        return;
    }

    router.visit(route('tenant.menu', { table: props.tableUuid }));
};

onMounted(() => {
    window.setTimeout(() => {
        isVisible.value = true;
    }, 100);

    document.querySelectorAll('[data-haptic]').forEach((button) => {
        button.addEventListener('mousedown', () => {
            button.style.transform = 'scale(0.96)';
        });
        button.addEventListener('mouseup', () => {
            button.style.transform = 'scale(1)';
        });
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'scale(1)';
        });
    });
});
</script>

<template>
    <Head :title="pageTitle" />

    <main class="relative flex h-screen w-full flex-col items-center justify-center overflow-hidden bg-surface font-body-md text-on-surface">
        <div class="absolute inset-0 z-0">
            <img
                :src="heroImage"
                alt="Fine dining table setting"
                class="h-full w-full object-cover"
            >
            <div class="absolute inset-0 hero-gradient" />
        </div>

        <header class="absolute left-0 top-0 z-20 flex h-24 w-full items-center justify-between px-margin-mobile md:px-margin-desktop">
            <div class="font-headline-md text-headline-lg-mobile font-semibold tracking-tight text-white md:text-headline-md">
                {{ restaurantName }}
            </div>
        </header>

        <div class="relative z-10 flex w-full max-w-2xl flex-col items-center gap-stack-lg px-margin-mobile text-center">
            <div
                class="animate-fade-in space-y-4 transition-all duration-[800ms] ease-[cubic-bezier(0.16,1,0.3,1)]"
                :class="isVisible ? 'translate-y-0 opacity-100' : 'translate-y-5 opacity-0'"
            >
                <span class="font-label-lg text-label-lg uppercase tracking-[0.2em] text-white/60">
                    {{ t.tagline }}
                </span>
                <h1 class="font-display-lg text-display-lg leading-tight text-white">
                    {{ t.headline }}
                </h1>
                <p class="mx-auto max-w-md font-body-lg text-body-lg text-white/80">
                    {{ t.description }}
                </p>
            </div>

            <div class="mt-8 flex w-full max-w-sm flex-col gap-stack-md">
                <button
                    type="button"
                    data-haptic
                    class="w-full rounded-lg bg-terracotta-accent px-10 py-5 font-headline-md text-headline-lg text-white shadow-lg transition-all duration-200 hover:opacity-90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="!tableUuid"
                    @click="beginOrder"
                >
                    {{ t.beginOrder }}
                </button>
            </div>

            <div class="glass-panel mt-12 w-full max-w-md rounded-xl p-6">
                <p class="mb-4 font-label-md text-label-md uppercase tracking-widest text-white/70">
                    {{ t.selectLanguage }}
                </p>
                <div class="grid grid-cols-2 gap-3">
                    <button
                        v-for="language in languages"
                        :key="language.code"
                        type="button"
                        data-haptic
                        class="group flex flex-col items-center justify-center rounded-lg border border-white/5 bg-white/10 px-2 py-4 transition-colors hover:bg-white/20"
                        :class="{ 'bg-white/20': selectedLanguage === language.code }"
                        @click="selectedLanguage = language.code; localStorage.setItem('tableflow-language', language.code)"
                    >
                        <span class="font-label-lg text-label-lg text-body-lg text-white">{{ language.label }}</span>
                        <span class="mt-1 text-[10px] uppercase text-white/40 group-hover:text-white/60">
                            {{ language.subtitle }}
                        </span>
                    </button>
                </div>
            </div>
        </div>

        <footer class="absolute bottom-10 left-0 z-10 flex w-full justify-center px-margin-mobile">
            <div class="flex items-center gap-6 font-label-md text-label-md text-white/50">
                <span class="cursor-pointer transition-colors hover:text-white">{{ t.privacyPolicy }}</span>
                <span class="h-1 w-1 rounded-full bg-white/20" />
                <span class="cursor-pointer transition-colors hover:text-white">{{ t.termsOfService }}</span>
                <span class="h-1 w-1 rounded-full bg-white/20" />
                <span class="cursor-pointer transition-colors hover:text-white">{{ t.accessibility }}</span>
            </div>
        </footer>

        <div class="absolute bottom-0 left-0 h-1 w-full bg-gradient-to-r from-transparent via-terracotta-accent to-transparent opacity-50" />
    </main>
</template>
