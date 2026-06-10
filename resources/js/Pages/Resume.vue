<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const props = defineProps({
    tableUuid: {
        type: String,
        default: null,
    },
    tenant: {
        type: Object,
        default: null,
    },
});

const bill = ref(null);
const isLoading = ref(true);
const isClosing = ref(false);
const errorMessage = ref('');
const selectedLanguage = ref(localStorage.getItem('tableflow-language') ?? 'es');

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
];

const copy = {
    en: {
        reviewBill: 'Review Your Bill',
        tableLabel: 'Table',
        subtotal: 'Subtotal',
        serviceCharge: 'Service Charge',
        salesTax: 'Sales Tax',
        totalAmount: 'Total Amount',
        qty: 'Qty',
        footerMessage: 'Please see your server for payment or to request a printed copy. We hope you enjoyed your experience at',
        closeAccount: 'Close Account',
        closingAccount: 'Processing...',
        returnToMenu: 'Return to Menu',
        closeError: 'Unable to close account.',
        noItems: 'No active orders for this table.',
        loadError: 'Unable to load bill.',
    },
    es: {
        reviewBill: 'Revisa tu Cuenta',
        tableLabel: 'Mesa',
        subtotal: 'Subtotal',
        serviceCharge: 'Cargo por Servicio',
        salesTax: 'Impuesto',
        totalAmount: 'Total a Pagar',
        qty: 'Cant',
        footerMessage: 'Consulta con tu mesero para el pago o solicita una copia impresa. Esperamos que hayas disfrutado tu experiencia en',
        closeAccount: 'Cerrar Cuenta',
        closingAccount: 'Procesando...',
        returnToMenu: 'Volver al Menú',
        closeError: 'No se pudo cerrar la cuenta.',
        noItems: 'No hay órdenes activas para esta mesa.',
        loadError: 'No se pudo cargar la cuenta.',
    },
};

const restaurantName = computed(() => props.tenant?.name ?? 'LUMIÈRE DINING');
const pageTitle = computed(() => `${restaurantName.value} | ${t.value.reviewBill}`);
const t = computed(() => copy[selectedLanguage.value] ?? copy.es);

const billDate = computed(() => {
    const date = bill.value?.generated_at ? new Date(bill.value.generated_at) : new Date();

    return new Intl.DateTimeFormat(selectedLanguage.value === 'es' ? 'es-ES' : 'en-US', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    }).format(date);
});

const tableSubtitle = computed(() => {
    if (!bill.value?.table) {
        return '';
    }

    return `${t.value.tableLabel} ${bill.value.table.number} • ${billDate.value}`;
});

const footerMessage = computed(() => `${t.value.footerMessage} ${restaurantName.value}.`);

const currencyFormatter = computed(() => {
    return new Intl.NumberFormat(selectedLanguage.value === 'es' ? 'es-ES' : 'en-US', {
        style: 'currency',
        currency: 'USD',
    });
});

const formatMoney = (amount) => currencyFormatter.value.format(Number(amount ?? 0));

const formatRatePercent = (rate) => {
    const percent = Number(rate ?? 0) * 100;

    return `${parseFloat(percent.toFixed(4))}%`;
};

const showServiceCharge = computed(() => Number(bill.value?.service_charge_rate ?? 0) > 0);

const showTax = computed(() => Number(bill.value?.tax_rate ?? 0) > 0);

const serviceChargeLabel = computed(() => {
    if (!showServiceCharge.value) {
        return t.value.serviceCharge;
    }

    return `${t.value.serviceCharge} (${formatRatePercent(bill.value.service_charge_rate)})`;
});

const salesTaxLabel = computed(() => {
    if (!showTax.value) {
        return t.value.salesTax;
    }

    return `${t.value.salesTax} (${formatRatePercent(bill.value.tax_rate)})`;
});

const fetchJson = async (url) => {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error(t.value.loadError);
    }

    return response.json();
};

const loadBill = async () => {
    if (!props.tableUuid) {
        return;
    }

    isLoading.value = true;
    errorMessage.value = '';

    try {
        const payload = await fetchJson(`/api/tables/${props.tableUuid}/bill`);
        bill.value = payload.data ?? null;
    } catch (error) {
        errorMessage.value = error.message ?? t.value.loadError;
    } finally {
        isLoading.value = false;
    }
};

const returnToMenu = () => {
    router.visit(route('tenant.menu', { table: props.tableUuid }));
};

const closeAccount = async () => {
    if (isClosing.value || !props.tableUuid) {
        return;
    }

    isClosing.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch(`/api/tables/${props.tableUuid}/close-bill`, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
        });

        const payload = await response.json();

        if (!response.ok) {
            throw new Error(payload?.message ?? t.value.closeError);
        }

        router.visit(route('tenant.menu', { table: props.tableUuid }));
    } catch (error) {
        errorMessage.value = error.message ?? t.value.closeError;
        isClosing.value = false;
    }
};

onMounted(() => {
    if (!props.tableUuid) {
        router.visit(route('tenant.welcome'));
        return;
    }

    loadBill();
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-surface pb-8 font-body-md text-on-surface">
        <header class="fixed top-0 z-50 flex h-16 w-full items-center justify-between bg-surface/80 px-margin-mobile backdrop-blur-md md:px-margin-desktop">
            <div class="truncate font-headline-md text-headline-md text-deep-navy">
                {{ restaurantName }}
            </div>

            <div class="flex items-center gap-2">
                <label for="bill-language" class="sr-only">Language</label>
                <div class="relative">
                    <select
                        id="bill-language"
                        v-model="selectedLanguage"
                        class="min-h-10 min-w-[9.5rem] cursor-pointer appearance-none rounded-lg border border-outline-variant bg-white !bg-none py-2 pl-4 pr-12 font-body-md text-body-md text-deep-navy transition hover:bg-soft-blue-gray/40 focus:border-terracotta-accent focus:outline-none focus:ring-2 focus:ring-terracotta-accent/30"
                    >
                        <option
                            v-for="option in languageOptions"
                            :key="option.value"
                            :value="option.value"
                        >
                            {{ option.label }}
                        </option>
                    </select>
                    <span
                        class="pointer-events-none absolute inset-y-0 right-4 flex items-center text-deep-navy"
                        aria-hidden="true"
                    >
                        <span class="material-symbols-outlined text-xl">expand_more</span>
                    </span>
                </div>
            </div>
        </header>

        <main class="mx-auto min-h-screen max-w-[800px] px-4 pb-8 pt-24 md:px-margin-desktop">
            <div class="mb-stack-lg text-center">
                <h1 class="mb-2 font-headline-lg text-headline-lg-mobile text-deep-navy">
                    {{ t.reviewBill }}
                </h1>
                <p
                    v-if="tableSubtitle"
                    class="font-body-md text-body-md text-slate-text"
                >
                    {{ tableSubtitle }}
                </p>
                <p
                    v-if="tableSubtitle"
                    class="mx-auto mt-4 max-w-md font-body-md text-body-md font-bold text-deep-navy"
                >
                    {{ footerMessage }}
                </p>
            </div>

            <p
                v-if="errorMessage"
                class="mb-stack-md rounded-lg border border-error/20 bg-error-container px-4 py-3 font-body-md text-body-md text-on-error-container"
            >
                {{ errorMessage }}
            </p>

            <div
                v-if="isLoading"
                class="h-96 animate-pulse rounded-xl bg-soft-blue-gray"
            />

            <div
                v-else-if="bill && bill.items.length > 0"
                class="overflow-hidden rounded-xl border border-soft-blue-gray bg-white shadow-ambient"
            >
                <div class="receipt-texture h-2 w-full bg-soft-blue-gray opacity-30" />

                <div class="p-6 md:p-10">
                    <div class="space-y-6">
                        <div
                            v-for="item in bill.items"
                            :key="item.product_id"
                            class="flex items-start justify-between"
                        >
                            <div class="flex-1">
                                <span class="mb-1 block font-label-lg text-label-lg uppercase text-terracotta-accent">
                                    {{ item.category_name }}
                                </span>
                                <h3 class="font-headline-md text-headline-md text-deep-navy">
                                    {{ item.product_name }}
                                </h3>
                                <p
                                    v-if="item.description"
                                    class="font-body-md text-body-md text-slate-text"
                                >
                                    {{ item.description }}
                                </p>
                            </div>
                            <div class="ml-4 text-right">
                                <p class="font-label-lg text-label-lg text-deep-navy">
                                    {{ formatMoney(item.subtotal) }}
                                </p>
                                <p class="font-label-md text-label-md text-outline">
                                    {{ t.qty }}: {{ item.quantity }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="mt-10 space-y-4 border-t border-soft-blue-gray pt-10">
                        <div class="flex items-center justify-between text-slate-text">
                            <span class="font-body-md text-body-md">{{ t.subtotal }}</span>
                            <span class="font-label-lg text-label-lg">{{ formatMoney(bill.subtotal) }}</span>
                        </div>
                        <div
                            v-if="showServiceCharge"
                            class="flex items-center justify-between text-slate-text"
                        >
                            <span class="font-body-md text-body-md">{{ serviceChargeLabel }}</span>
                            <span class="font-label-lg text-label-lg">{{ formatMoney(bill.service_charge) }}</span>
                        </div>
                        <div
                            v-if="showTax"
                            class="flex items-center justify-between text-slate-text"
                        >
                            <span class="font-body-md text-body-md">{{ salesTaxLabel }}</span>
                            <span class="font-label-lg text-label-lg">{{ formatMoney(bill.tax) }}</span>
                        </div>
                        <div class="dotted-line my-6" />
                        <div class="flex items-center justify-between text-deep-navy">
                            <span class="font-headline-md text-headline-md">{{ t.totalAmount }}</span>
                            <span class="text-[32px] font-bold text-terracotta-accent">
                                {{ formatMoney(bill.total) }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="receipt-edge h-4 w-full" />
            </div>

            <div
                v-else-if="bill"
                class="rounded-xl border border-soft-blue-gray bg-soft-blue-gray/30 px-6 py-12 text-center"
            >
                <p class="font-headline-md text-headline-md text-deep-navy">
                    {{ t.noItems }}
                </p>
            </div>

            <div class="mt-stack-lg flex flex-col items-center px-4 text-center">
                <div class="mt-10 flex w-full max-w-sm flex-col gap-4">
                    <button
                        type="button"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-terracotta-accent px-8 py-4 font-headline-md text-headline-md text-white shadow-lg shadow-terracotta-accent/20 transition active:scale-95 disabled:cursor-not-allowed disabled:opacity-80"
                        :disabled="isClosing"
                        @click="closeAccount"
                    >
                        <span
                            v-if="isClosing"
                            class="material-symbols-outlined animate-spin text-xl"
                        >
                            progress_activity
                        </span>
                        {{ isClosing ? t.closingAccount : t.closeAccount }}
                    </button>

                    <button
                        type="button"
                        class="w-full rounded-lg border border-deep-navy px-8 py-4 font-headline-md text-headline-md text-deep-navy transition hover:bg-soft-blue-gray active:scale-95"
                        @click="returnToMenu"
                    >
                        {{ t.returnToMenu }}
                    </button>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>
.receipt-texture {
    background-image: radial-gradient(#dee3ed 0.5px, transparent 0.5px);
    background-size: 8px 8px;
}

.dotted-line {
    border-bottom: 2px dotted #c4c6cd;
}

.receipt-edge {
    background:
        linear-gradient(-45deg, transparent 4px, white 4px),
        linear-gradient(45deg, transparent 4px, white 4px);
    background-repeat: repeat-x;
    background-size: 8px 16px;
}
</style>
