<script setup>
import { Head, router } from '@inertiajs/vue3';
import { computed, onMounted, ref, watch } from 'vue';
import CategoryTabs from '../Components/Menu/CategoryTabs.vue';
import OrderSummary from '../Components/Menu/OrderSummary.vue';
import ProductCard from '../Components/Menu/ProductCard.vue';

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

const categories = ref([]);
const products = ref([]);
const activeCategory = ref(null);
const selectedItems = ref({});
const isLoading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const tableInfo = ref(null);
const selectedLanguage = ref(localStorage.getItem('tableflow-language') ?? 'es');

const copy = {
    en: {
        orderTotal: 'Order Total',
        item: 'item selected',
        items: 'items selected',
        callWaiter: 'Call Waiter',
        completeOrder: 'Complete Order',
        submitting: 'Submitting...',
        closeBill: 'Close Bill',
        waiterCalled: 'Waiter has been notified.',
        closeBillMessage: 'Please ask your waiter to close the bill.',
        noDishes: 'No dishes available',
        noDishesHint: 'Check back soon or choose another category.',
    },
    es: {
        orderTotal: 'Total de la Orden',
        item: 'artículo seleccionado',
        items: 'artículos seleccionados',
        callWaiter: 'Llamar Mesero',
        completeOrder: 'Completar Orden',
        submitting: 'Enviando...',
        closeBill: 'Cerrar Cuenta',
        waiterCalled: 'El mesero ha sido notificado.',
        closeBillMessage: 'Solicita al mesero cerrar la cuenta.',
        noDishes: 'No hay platos disponibles',
        noDishesHint: 'Vuelve pronto o elige otra categoría.',
    },
};

const restaurantName = computed(() => props.tenant?.name ?? 'LUMIÈRE DINING');
const pageTitle = computed(() => `${restaurantName.value} | Menu`);
const t = computed(() => copy[selectedLanguage.value] ?? copy.es);

const selectedList = computed(() => Object.values(selectedItems.value));

const itemCount = computed(() => {
    return selectedList.value.reduce((sum, item) => sum + item.quantity, 0);
});

const orderTotal = computed(() => {
    return selectedList.value.reduce((sum, item) => {
        return sum + (Number(item.product.price) * item.quantity);
    }, 0);
});

const visibleSections = computed(() => {
    const grouped = categories.value
        .map((category) => ({
            category,
            products: products.value.filter((product) => product.category_id === category.id),
        }))
        .filter((section) => section.products.length > 0);

    if (activeCategory.value === null) {
        return grouped;
    }

    return grouped.filter((section) => section.category.id === activeCategory.value);
});

const getQuantity = (productId) => selectedItems.value[productId]?.quantity ?? 0;

const layoutForCategory = (category) => {
    return ['platos-fuertes', 'main-courses'].includes(category.slug) ? 'horizontal' : 'vertical';
};

const gridClassForCategory = (category) => {
    return layoutForCategory(category) === 'horizontal'
        ? 'grid grid-cols-1 gap-gutter xl:grid-cols-2'
        : 'grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3';
};

const fetchJson = async (url) => {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('Unable to load menu data.');
    }

    return response.json();
};

const loadCategories = async () => {
    const payload = await fetchJson('/api/menu/categories');
    categories.value = payload.data ?? [];
};

const loadProducts = async (categoryId = null) => {
    const query = categoryId ? `?category_id=${categoryId}` : '';
    const payload = await fetchJson(`/api/menu/products${query}`);
    products.value = payload.data ?? [];
};

const loadTable = async () => {
    if (!props.tableUuid) {
        return;
    }

    const payload = await fetchJson(`/api/tables/${props.tableUuid}`);
    tableInfo.value = payload.data ?? null;
};

const initializeMenu = async () => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await Promise.all([
            loadCategories(),
            loadProducts(activeCategory.value),
            loadTable(),
        ]);
    } catch (error) {
        errorMessage.value = error.message ?? 'Unable to load menu.';
    } finally {
        isLoading.value = false;
    }
};

const addItem = (product) => {
    successMessage.value = '';

    const current = selectedItems.value[product.id];

    selectedItems.value = {
        ...selectedItems.value,
        [product.id]: {
            product,
            quantity: (current?.quantity ?? 0) + 1,
        },
    };
};

const removeItem = (productId) => {
    successMessage.value = '';

    const current = selectedItems.value[productId];

    if (!current) {
        return;
    }

    if (current.quantity <= 1) {
        const next = { ...selectedItems.value };
        delete next[productId];
        selectedItems.value = next;
        return;
    }

    selectedItems.value = {
        ...selectedItems.value,
        [productId]: {
            ...current,
            quantity: current.quantity - 1,
        },
    };
};

const completeOrder = async () => {
    if (!props.tableUuid) {
        errorMessage.value = 'Table reference is missing.';
        return;
    }

    if (itemCount.value === 0) {
        return;
    }

    isSubmitting.value = true;
    errorMessage.value = '';
    successMessage.value = '';

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
                items: selectedList.value.map((item) => ({
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

            throw new Error(validationMessage ?? 'Unable to complete order.');
        }

        selectedItems.value = {};
        successMessage.value = payload.message ?? t.value.completeOrder;
    } catch (error) {
        errorMessage.value = error.message ?? 'Unable to complete order.';
    } finally {
        isSubmitting.value = false;
    }
};

const callWaiter = () => {
    successMessage.value = t.value.waiterCalled;
};

const closeBill = () => {
    successMessage.value = t.value.closeBillMessage;
};

const toggleLanguage = () => {
    selectedLanguage.value = selectedLanguage.value === 'es' ? 'en' : 'es';
    localStorage.setItem('tableflow-language', selectedLanguage.value);
};

watch(activeCategory, async (categoryId) => {
    isLoading.value = true;
    errorMessage.value = '';

    try {
        await loadProducts(categoryId);
    } catch (error) {
        errorMessage.value = error.message ?? 'Unable to load products.';
    } finally {
        isLoading.value = false;
    }
});

onMounted(() => {
    if (!props.tableUuid) {
        router.visit(route('tenant.welcome'));
        return;
    }

    initializeMenu();
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
                            v-if="tableInfo"
                            class="mt-1 font-label-md text-label-md uppercase tracking-[0.2em] text-slate-text"
                        >
                            Mesa {{ tableInfo.number }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-outline-variant text-deep-navy transition hover:bg-soft-blue-gray/40"
                            :title="selectedLanguage === 'es' ? 'Switch to English' : 'Cambiar a Español'"
                            @click="toggleLanguage"
                        >
                            <span class="material-symbols-outlined text-base">translate</span>
                        </button>
                    </div>
                </div>

                <div class="mt-4">
                    <CategoryTabs
                        :categories="categories"
                        :active-category="activeCategory"
                        @select="activeCategory = $event"
                    />
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-container-max-width px-margin-mobile py-stack-lg md:px-margin-desktop">
            <p
                v-if="errorMessage"
                class="mb-stack-md rounded-lg border border-error/20 bg-error-container px-4 py-3 font-body-md text-body-md text-on-error-container"
            >
                {{ errorMessage }}
            </p>

            <div
                v-if="isLoading"
                class="space-y-stack-lg"
            >
                <div
                    v-for="index in 2"
                    :key="index"
                    class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3"
                >
                    <div
                        v-for="card in 3"
                        :key="card"
                        class="h-80 animate-pulse rounded-lg bg-soft-blue-gray"
                    />
                </div>
            </div>

            <div
                v-else-if="visibleSections.length === 0"
                class="rounded-lg border border-soft-blue-gray bg-soft-blue-gray/30 px-6 py-12 text-center"
            >
                <p class="font-headline-md text-headline-md text-deep-navy">
                    {{ t.noDishes }}
                </p>
                <p class="mt-2 font-body-md text-body-md text-slate-text">
                    {{ t.noDishesHint }}
                </p>
            </div>

            <div
                v-else
                class="space-y-stack-lg"
            >
                <section
                    v-for="section in visibleSections"
                    :key="section.category.id"
                    class="space-y-stack-md"
                >
                    <h2 class="font-headline-md text-headline-md text-deep-navy">
                        {{ section.category.name }}
                    </h2>

                    <div :class="gridClassForCategory(section.category)">
                        <ProductCard
                            v-for="product in section.products"
                            :key="product.id"
                            :product="product"
                            :quantity="getQuantity(product.id)"
                            :layout="layoutForCategory(section.category)"
                            @add="addItem"
                            @remove="removeItem"
                        />
                    </div>
                </section>
            </div>
        </main>

        <OrderSummary
            :item-count="itemCount"
            :total="orderTotal"
            :is-submitting="isSubmitting"
            :success-message="successMessage"
            :labels="t"
            @complete="completeOrder"
            @call-waiter="callWaiter"
            @close-bill="closeBill"
        />
    </div>
</template>
