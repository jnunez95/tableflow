<script setup>
import { Head } from '@inertiajs/vue3';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import OrderCard from '../Components/Kitchen/OrderCard.vue';
import StatusIndicator from '../Components/Kitchen/StatusIndicator.vue';

const props = defineProps({
    tenant: {
        type: Object,
        default: null,
    },
});

const stats = ref({
    active_orders: 0,
    average_time_minutes: 0,
    delayed_count: 0,
    status: 'optimal',
});

const orders = ref([]);
const isLoading = ref(true);
const errorMessage = ref('');
const pollingInterval = ref(null);

const restaurantName = computed(() => props.tenant?.name ?? 'Kitchen Display');
const pageTitle = computed(() => `${restaurantName.value} | Kitchen`);

const formatTime = (minutes) => {
    const safeMinutes = Math.max(0, Number(minutes) || 0);
    const m = Math.floor(safeMinutes);
    const s = Math.floor((safeMinutes - m) * 60);

    return `${m}m ${s}s`;
};

const fetchJson = async (url, options = {}) => {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            ...options.headers,
        },
        ...options,
    });

    if (!response.ok) {
        throw new Error('Unable to fetch kitchen data.');
    }

    return response.json();
};

const fetchDashboard = async () => {
    try {
        errorMessage.value = '';
        const payload = await fetchJson('/api/kitchen/orders');
        
        stats.value = payload.data.stats;
        orders.value = payload.data.orders;
    } catch (error) {
        errorMessage.value = error.message ?? 'Unable to load kitchen data.';
        console.error('Kitchen fetch error:', error);
    } finally {
        isLoading.value = false;
    }
};

const toggleItemReady = async (itemId) => {
    const previousOrders = orders.value;

    orders.value = orders.value.map((order) => ({
        ...order,
        items: order.items.map((item) => (
            item.id === itemId
                ? { ...item, is_ready: !item.is_ready }
                : item
        )),
    }));

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        const response = await fetchJson(`/api/kitchen/items/${itemId}/toggle`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        const updatedItemId = response.data.id;
        const newStatus = response.data.is_ready;

        orders.value = orders.value.map((order) => ({
            ...order,
            items: order.items.map((item) => (
                item.id === updatedItemId
                    ? { ...item, is_ready: newStatus }
                    : item
            )),
        }));
    } catch (error) {
        orders.value = previousOrders;
        errorMessage.value = 'Failed to update item status.';
        console.error('Toggle item error:', error);
    }
};

const markOrderReady = async (orderId) => {
    const previousOrders = orders.value;

    orders.value = orders.value.map((order) => (
        order.id === orderId
            ? {
                ...order,
                items: order.items.map((item) => ({ ...item, is_ready: true })),
            }
            : order
    ));

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

        await fetchJson(`/api/kitchen/orders/${orderId}/ready`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
        });

        orders.value = orders.value.filter((order) => order.id !== orderId);
        stats.value.active_orders = Math.max(0, stats.value.active_orders - 1);

        await fetchDashboard();
    } catch (error) {
        orders.value = previousOrders;
        errorMessage.value = error.message ?? 'Failed to mark order as ready.';
        console.error('Mark ready error:', error);
    }
};

const startPolling = () => {
    pollingInterval.value = setInterval(fetchDashboard, 5000);
};

const stopPolling = () => {
    if (pollingInterval.value) {
        clearInterval(pollingInterval.value);
        pollingInterval.value = null;
    }
};

onMounted(async () => {
    await fetchDashboard();
    startPolling();
});

onUnmounted(() => {
    stopPolling();
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="min-h-screen bg-soft-blue-gray">
        <header class="sticky top-0 z-20 bg-[#1E2E42] text-white shadow-md">
            <div class="px-6 py-4">
                <div class="flex justify-between items-center">
                    <h1 class="font-headline-lg text-headline-lg">
                        {{ restaurantName }}
                    </h1>
                    <StatusIndicator
                        :status="stats.status"
                        :delayed-count="stats.delayed_count"
                    />
                </div>
            </div>
        </header>

        <main>
            <div
                v-if="errorMessage"
                class="mx-6 mt-6 rounded-lg border border-error/20 bg-error-container px-4 py-3 font-body-md text-body-md text-on-error-container"
            >
                {{ errorMessage }}
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-slate-text text-label-md uppercase tracking-wider">
                        ACTIVE ORDERS
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-4xl font-bold text-deep-navy">
                            {{ stats.active_orders }}
                        </div>
                        <div class="text-3xl">
                            🍴
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-slate-text text-label-md uppercase tracking-wider">
                        AVG TIME
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-4xl font-bold text-deep-navy">
                            {{ formatTime(stats.average_time_minutes) }}
                        </div>
                        <div class="text-3xl">
                            ⏱️
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 shadow-sm">
                    <div class="text-slate-text text-label-md uppercase tracking-wider">
                        DELAYED
                    </div>
                    <div class="flex items-center justify-between mt-2">
                        <div class="text-4xl font-bold text-error">
                            {{ stats.delayed_count }}
                        </div>
                        <div class="text-3xl">
                            ⚠️
                        </div>
                    </div>
                </div>
            </div>

            <div
                v-if="isLoading"
                class="px-6 pb-6"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="i in 6"
                        :key="i"
                        class="bg-white rounded-lg p-6 h-64 animate-pulse"
                    />
                </div>
            </div>

            <div
                v-else-if="orders.length === 0"
                class="px-6 pb-6"
            >
                <div class="bg-white rounded-lg p-12 text-center shadow-sm">
                    <p class="font-headline-md text-headline-md text-deep-navy">
                        No active orders
                    </p>
                    <p class="mt-2 font-body-md text-body-md text-slate-text">
                        All orders have been completed. Great work!
                    </p>
                </div>
            </div>

            <div
                v-else
                class="px-6 pb-6"
            >
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <OrderCard
                        v-for="order in orders"
                        :key="order.id"
                        :order="order"
                        @toggle-item="toggleItemReady"
                        @mark-ready="markOrderReady"
                    />
                </div>
            </div>
        </main>
    </div>
</template>
