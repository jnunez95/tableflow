<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    tableUuid: {
        type: String,
        default: null,
    },
    tenant: {
        type: Object,
        default: null,
    },
    items: {
        type: Array,
        default: () => [],
    },
});

const cartItems = ref([]);
const tableInfo = ref(null);
const isLoading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const selectedLanguage = ref(localStorage.getItem('tableflow-language') ?? 'es');

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
];

const copy = {
    en: {
        verifyOrder: 'Verify Your Order',
        reviewSelection: 'Review and modify your selection before sending to the kitchen.',
        tableLabel: 'Table',
        quantity: 'Qty',
        item: 'item',
        items: 'items',
        remove: 'Remove',
        subtotal: 'Subtotal',
        totalAmount: 'Total',
        confirmOrder: 'Confirm Order',
        submitting: 'Submitting...',
        backToMenu: 'Back to Menu',
        emptyCart: 'Your cart is empty',
        emptyCartHint: 'Add items from the menu to continue.',
        loadError: 'Unable to load table information.',
        submitError: 'Unable to confirm order.',
        orderSuccess: 'Order placed successfully.',
    },
    es: {
        verifyOrder: 'Verifica tu Orden',
        reviewSelection: 'Revisa y modifica tu selección antes de enviar a cocina.',
        tableLabel: 'Mesa',
        quantity: 'Cant',
        item: 'artículo',
        items: 'artículos',
        remove: 'Eliminar',
        subtotal: 'Subtotal',
        totalAmount: 'Total',
        confirmOrder: 'Confirmar Orden',
        submitting: 'Enviando...',
        backToMenu: 'Volver al Menú',
        emptyCart: 'Tu carrito está vacío',
        emptyCartHint: 'Agrega artículos desde el menú para continuar.',
        loadError: 'No se pudo cargar la información de la mesa.',
        submitError: 'No se pudo confirmar la orden.',
        orderSuccess: 'Orden enviada exitosamente.',
    },
};

const placeholderImage = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';

const restaurantName = computed(() => props.tenant?.name ?? 'LUMIÈRE DINING');
const pageTitle = computed(() => `${restaurantName.value} | ${t.value.verifyOrder}`);
const t = computed(() => copy[selectedLanguage.value] ?? copy.es);

const cartStorageKey = computed(() => `tableflow-cart-${props.tableUuid}`);

const currencyFormatter = computed(() => {
    return new Intl.NumberFormat(selectedLanguage.value === 'es' ? 'es-ES' : 'en-US', {
        style: 'currency',
        currency: 'USD',
    });
});

const formatMoney = (amount) => currencyFormatter.value.format(Number(amount ?? 0));

const itemCount = computed(() => {
    return cartItems.value.reduce((sum, item) => sum + item.quantity, 0);
});

const orderSubtotal = computed(() => {
    return cartItems.value.reduce((sum, item) => {
        return sum + (Number(item.product.price) * item.quantity);
    }, 0);
});

const tableSubtitle = computed(() => {
    if (!tableInfo.value) {
        return '';
    }

    return `${t.value.tableLabel} ${tableInfo.value.number}`;
});

const normalizeItems = (items) => {
    return items
        .filter((item) => item?.product && item.quantity > 0)
        .map((item) => ({
            product_id: item.product_id ?? item.product.id,
            product: item.product,
            quantity: item.quantity,
        }));
};

const persistCart = () => {
    if (!props.tableUuid) {
        return;
    }

    const payload = cartItems.value.reduce((accumulator, item) => {
        accumulator[item.product.id] = {
            product: item.product,
            quantity: item.quantity,
        };

        return accumulator;
    }, {});

    localStorage.setItem(cartStorageKey.value, JSON.stringify(payload));
};

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

const loadTable = async () => {
    if (!props.tableUuid) {
        return;
    }

    const payload = await fetchJson(`/api/tables/${props.tableUuid}`);
    tableInfo.value = payload.data ?? null;
};

const increaseQuantity = (productId) => {
    errorMessage.value = '';

    cartItems.value = cartItems.value.map((item) => {
        if (item.product.id !== productId) {
            return item;
        }

        return {
            ...item,
            quantity: item.quantity + 1,
        };
    });
};

const decreaseQuantity = (productId) => {
    errorMessage.value = '';

    const current = cartItems.value.find((item) => item.product.id === productId);

    if (!current) {
        return;
    }

    if (current.quantity <= 1) {
        removeItem(productId);
        return;
    }

    cartItems.value = cartItems.value.map((item) => {
        if (item.product.id !== productId) {
            return item;
        }

        return {
            ...item,
            quantity: item.quantity - 1,
        };
    });
};

const removeItem = (productId) => {
    errorMessage.value = '';
    cartItems.value = cartItems.value.filter((item) => item.product.id !== productId);
};

const returnToMenu = () => {
    persistCart();
    router.visit(route('tenant.menu', { table: props.tableUuid }));
};

const confirmOrder = async () => {
    if (!props.tableUuid || itemCount.value === 0 || isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const response = await fetch('/api/orders', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
            },
            body: JSON.stringify({
                table_uuid: props.tableUuid,
                items: cartItems.value.map((item) => ({
                    product_id: item.product.id,
                    quantity: item.quantity,
                })),
            }),
        });

        const payload = await response.json();

        if (!response.ok) {
            const validationMessage = payload?.errors
                ? Object.values(payload.errors).flat()[0]
                : payload?.message;

            throw new Error(validationMessage ?? t.value.submitError);
        }

        localStorage.removeItem(cartStorageKey.value);
        localStorage.setItem('tableflow-order-success-message', payload.message ?? t.value.orderSuccess);

        router.visit(route('tenant.menu', { table: props.tableUuid }));
    } catch (error) {
        errorMessage.value = error.message ?? t.value.submitError;
        isSubmitting.value = false;
    }
};

watch(selectedLanguage, (language) => {
    localStorage.setItem('tableflow-language', language);
});

watch(cartItems, () => {
    persistCart();
}, { deep: true });

onMounted(async () => {
    if (!props.tableUuid) {
        router.visit(route('tenant.welcome'));
        return;
    }

    cartItems.value = normalizeItems(props.items);

    if (cartItems.value.length === 0) {
        const storedCart = localStorage.getItem(cartStorageKey.value);

        if (storedCart) {
            try {
                const parsed = JSON.parse(storedCart);
                cartItems.value = normalizeItems(Object.values(parsed));
            } catch {
                cartItems.value = [];
            }
        }
    }

    if (cartItems.value.length === 0) {
        router.visit(route('tenant.menu', { table: props.tableUuid }));
        return;
    }

    isLoading.value = true;

    try {
        await loadTable();
    } catch (error) {
        errorMessage.value = error.message ?? t.value.loadError;
    } finally {
        isLoading.value = false;
    }
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-surface-container-lowest pb-44 font-body-md text-on-surface">
        <header class="sticky top-0 z-20 border-b border-soft-blue-gray bg-surface-container-lowest/95 backdrop-blur-md">
            <div class="mx-auto max-w-container-max-width px-margin-mobile py-4 md:px-margin-desktop">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="truncate font-headline-md text-headline-md text-deep-navy md:font-headline-lg md:text-headline-lg">
                            {{ restaurantName }}
                        </h1>
                        <p
                            v-if="tableSubtitle"
                            class="mt-1 font-label-md text-label-md uppercase tracking-[0.2em] text-slate-text"
                        >
                            {{ tableSubtitle }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <label for="verify-language" class="sr-only">Language</label>
                        <div class="relative">
                            <select
                                id="verify-language"
                                v-model="selectedLanguage"
                                class="min-h-12 min-w-[9.5rem] w-full cursor-pointer appearance-none rounded-lg border border-outline-variant bg-white !bg-none py-2 pl-4 pr-12 font-body-lg text-body-lg text-deep-navy transition hover:bg-soft-blue-gray/40 focus:border-terracotta-accent focus:outline-none focus:ring-2 focus:ring-terracotta-accent/30"
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
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-container-max-width px-margin-mobile py-stack-lg md:px-margin-desktop">
            <div class="mb-stack-lg text-center md:text-left">
                <h2 class="font-headline-lg text-headline-lg text-deep-navy">
                    {{ t.verifyOrder }}
                </h2>
                <p class="mt-2 font-body-md text-body-md text-slate-text">
                    {{ t.reviewSelection }}
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
                v-else-if="cartItems.length === 0"
                class="rounded-xl border border-soft-blue-gray bg-soft-blue-gray/30 px-6 py-12 text-center"
            >
                <p class="font-headline-md text-headline-md text-deep-navy">
                    {{ t.emptyCart }}
                </p>
                <p class="mt-2 font-body-md text-body-md text-slate-text">
                    {{ t.emptyCartHint }}
                </p>
            </div>

            <div
                v-else
                class="overflow-hidden rounded-xl border border-soft-blue-gray bg-white shadow-ambient"
            >
                <div class="divide-y divide-soft-blue-gray">
                    <article
                        v-for="item in cartItems"
                        :key="item.product.id"
                        class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:gap-6 md:p-6"
                    >
                        <div class="h-24 w-full shrink-0 overflow-hidden rounded-lg bg-soft-blue-gray sm:h-20 sm:w-20">
                            <img
                                :src="item.product.image || placeholderImage"
                                :alt="item.product.name"
                                class="h-full w-full object-cover"
                                @error="($event.target).src = placeholderImage"
                            >
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="font-headline-md text-headline-md text-deep-navy">
                                {{ item.product.name }}
                            </h3>
                            <p class="mt-1 font-label-lg text-label-lg text-terracotta-accent">
                                {{ formatMoney(item.product.price) }}
                            </p>
                            <p class="mt-1 font-label-md text-label-md text-slate-text">
                                {{ t.subtotal }}: {{ formatMoney(Number(item.product.price) * item.quantity) }}
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3 sm:justify-end">
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent text-terracotta-accent transition hover:bg-terracotta-accent/10 active:scale-95"
                                    @click="decreaseQuantity(item.product.id)"
                                >
                                    <span class="material-symbols-outlined text-xl">remove</span>
                                </button>

                                <span class="min-w-[2rem] text-center font-label-lg text-label-lg text-deep-navy">
                                    {{ item.quantity }}
                                </span>

                                <button
                                    type="button"
                                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent bg-terracotta-accent text-white transition hover:opacity-90 active:scale-95"
                                    @click="increaseQuantity(item.product.id)"
                                >
                                    <span class="material-symbols-outlined text-xl">add</span>
                                </button>
                            </div>

                            <button
                                type="button"
                                class="inline-flex items-center gap-1 rounded-lg border border-outline-variant px-3 py-2 font-label-md text-label-md text-deep-navy transition hover:bg-soft-blue-gray/30 active:scale-95"
                                @click="removeItem(item.product.id)"
                            >
                                <span class="material-symbols-outlined text-base">delete</span>
                                {{ t.remove }}
                            </button>
                        </div>
                    </article>
                </div>

                <div class="border-t border-soft-blue-gray bg-soft-blue-gray/20 px-4 py-6 md:px-6">
                    <div class="flex items-center justify-between text-deep-navy">
                        <span class="font-headline-md text-headline-md">{{ t.totalAmount }}</span>
                        <span class="text-[28px] font-bold text-terracotta-accent">
                            {{ formatMoney(orderSubtotal) }}
                        </span>
                    </div>
                    <p class="mt-1 text-right font-body-md text-body-md text-slate-text">
                        {{ itemCount }} {{ itemCount === 1 ? t.item : t.items }}
                    </p>
                </div>
            </div>
        </main>

        <div class="fixed inset-x-0 bottom-0 z-30 border-t border-soft-blue-gray bg-surface-container-lowest/95 px-margin-mobile py-4 backdrop-blur-md md:px-margin-desktop">
            <div class="mx-auto flex max-w-container-max-width flex-col gap-3 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-5 py-3 font-label-lg text-label-lg text-deep-navy transition hover:bg-soft-blue-gray/30 active:scale-95"
                    :disabled="isSubmitting"
                    @click="returnToMenu"
                >
                    <span class="material-symbols-outlined text-base">arrow_back</span>
                    {{ t.backToMenu }}
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-terracotta-accent px-6 py-3 font-label-lg text-label-lg uppercase tracking-wider text-white transition hover:opacity-90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="cartItems.length === 0 || isSubmitting"
                    @click="confirmOrder"
                >
                    <span class="material-symbols-outlined text-base">check_circle</span>
                    {{ isSubmitting ? t.submitting : t.confirmOrder }}
                </button>
            </div>
        </div>
    </div>
</template>
