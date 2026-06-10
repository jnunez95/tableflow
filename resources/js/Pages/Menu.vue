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
const cartStorageKey = computed(() => `tableflow-cart-${props.tableUuid}`);
const isLoading = ref(true);
const isSubmitting = ref(false);
const errorMessage = ref('');
const successMessage = ref('');
const tableInfo = ref(null);
const selectedLanguage = ref(localStorage.getItem('tableflow-language') ?? 'es');

const languageOptions = [
    { value: 'en', label: 'English' },
    { value: 'es', label: 'Spanish' },
];

const copy = {
    en: {
        orderTotal: 'Order Total',
        item: 'item selected',
        items: 'items selected',
        completeOrder: 'Complete Order',
        submitting: 'Submitting...',
        closeBill: 'Close Bill',
        closeBillMessage: 'Please ask your waiter to close the bill.',
        noDishes: 'No dishes available',
        noDishesHint: 'Check back soon or choose another category.',
    },
    es: {
        orderTotal: 'Total de la Orden',
        item: 'artículo seleccionado',
        items: 'artículos seleccionados',
        completeOrder: 'Completar Orden',
        submitting: 'Enviando...',
        closeBill: 'Cerrar Cuenta',
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

const persistCart = () => {
    if (!props.tableUuid) {
        return;
    }

    localStorage.setItem(cartStorageKey.value, JSON.stringify(selectedItems.value));
};

const restoreCart = () => {
    if (!props.tableUuid) {
        return;
    }

    const storedCart = localStorage.getItem(cartStorageKey.value);

    if (!storedCart) {
        return;
    }

    try {
        const parsed = JSON.parse(storedCart);
        selectedItems.value = Object.fromEntries(
            Object.entries(parsed).filter(([, item]) => item?.product && item.quantity > 0),
        );
    } catch {
        selectedItems.value = {};
    }
};

const restoreSuccessMessage = () => {
    const message = localStorage.getItem('tableflow-order-success-message');

    if (!message) {
        return;
    }

    successMessage.value = message;
    localStorage.removeItem('tableflow-order-success-message');
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

    persistCart();
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
        persistCart();
        return;
    }

    selectedItems.value = {
        ...selectedItems.value,
        [productId]: {
            ...current,
            quantity: current.quantity - 1,
        },
    };

    persistCart();
};

const completeOrder = () => {
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
    persistCart();

    router.visit(route('tenant.order.verify'), {
        method: 'post',
        data: {
            table_uuid: props.tableUuid,
            items: selectedList.value.map((item) => ({
                product_id: item.product.id,
                product: item.product,
                quantity: item.quantity,
            })),
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};

const closeBill = () => {
    if (!props.tableUuid) {
        return;
    }

    router.visit(route('tenant.bill', { table: props.tableUuid }));
};

watch(selectedLanguage, (language) => {
    localStorage.setItem('tableflow-language', language);
});

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

    restoreCart();
    restoreSuccessMessage();
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
                        <label for="menu-language" class="sr-only">Language</label>
                        <div class="relative">
                            <select
                                id="menu-language"
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
                    <h2 class="font-headline-lg text-headline-lg text-deep-navy">
                        {{ section.category.name }}
                    </h2>

                    <div class="grid grid-cols-1 gap-gutter sm:grid-cols-2 lg:grid-cols-3">
                        <ProductCard
                            v-for="product in section.products"
                            :key="product.id"
                            :product="product"
                            :quantity="getQuantity(product.id)"
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
            @close-bill="closeBill"
        />
    </div>
</template>
