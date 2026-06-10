<script setup>
import { computed } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['toggle-item', 'mark-ready']);

const timeElapsed = computed(() => {
    return `${Math.floor(props.order.minutes_elapsed)} min`;
});

const urgencyLabel = computed(() => {
    return {
        urgent: 'URGENT',
        medium: 'Wait Limit',
        new: 'New',
    }[props.order.urgency];
});

const urgencyStyles = computed(() => {
    return {
        urgent: {
            border: 'border-l-4 border-error',
            badge: 'bg-error text-white',
            button: 'bg-red-900 hover:bg-red-800 text-white',
        },
        medium: {
            border: 'border-l-4 border-orange-500',
            badge: 'bg-orange-500 text-white',
            button: 'bg-orange-700 hover:bg-orange-600 text-white',
        },
        new: {
            border: 'border-l-4 border-deep-navy',
            badge: 'bg-slate-text text-white',
            button: 'bg-deep-navy hover:bg-primary-container text-white',
        },
    }[props.order.urgency];
});

const cookingMethodClass = (method) => {
    const classes = {
        GRILL: 'bg-deep-navy text-white',
        HOT: 'bg-error text-white',
        COLD: 'bg-blue-500 text-white',
        SAUCE: 'bg-orange-700 text-white',
        PREP: 'bg-slate-text text-white',
    };
    return classes[method] || 'bg-slate-text text-white';
};
</script>

<template>
    <div
        :class="[
            'bg-white rounded-lg p-4 shadow-sm transition-all',
            urgencyStyles.border,
        ]"
    >
        <div class="flex justify-between items-start mb-3">
            <div>
                <h3 class="font-headline-md text-headline-md text-deep-navy">
                    Table {{ order.table.number }}
                </h3>
                <p class="text-slate-text text-label-md mt-0.5">
                    {{ order.order_number }}
                </p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold text-deep-navy">
                    {{ timeElapsed }}
                </div>
                <span
                    :class="[
                        'inline-block text-label-md px-2 py-1 rounded mt-1',
                        urgencyStyles.badge,
                    ]"
                >
                    {{ urgencyLabel }}
                </span>
            </div>
        </div>

        <div class="space-y-4 mb-5">
            <div
                v-for="item in order.items"
                :key="item.id"
                :class="[
                    'flex items-start gap-4 rounded-lg px-3 py-3 transition-colors',
                    item.is_ready
                        ? 'border border-green-200 bg-green-50/80'
                        : 'bg-soft-blue-gray/40',
                ]"
            >
                <input
                    type="checkbox"
                    :checked="item.is_ready"
                    @change="emit('toggle-item', item.id)"
                    class="mt-1.5 h-6 w-6 shrink-0 rounded border-slate-text accent-green-600 cursor-pointer"
                />
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-3">
                        <span
                            :class="[
                                'font-headline-md text-headline-md font-semibold leading-tight',
                                item.is_ready
                                    ? 'text-slate-text line-through decoration-2'
                                    : 'text-deep-navy',
                            ]"
                        >
                            {{ item.product.name }}
                            <span
                                v-if="item.quantity > 1"
                                :class="[
                                    'font-body-lg text-body-lg',
                                    item.is_ready ? 'text-slate-text/70' : 'text-slate-text',
                                ]"
                            >
                                (x{{ item.quantity }})
                            </span>
                        </span>
                        <span
                            v-if="item.cooking_method"
                            :class="[
                                'text-label-lg px-2.5 py-1 rounded shrink-0',
                                item.is_ready
                                    ? 'bg-slate-text/20 text-slate-text line-through'
                                    : cookingMethodClass(item.cooking_method),
                            ]"
                        >
                            {{ item.cooking_method }}
                        </span>
                    </div>
                    <p
                        v-if="item.notes"
                        :class="[
                            'mt-1.5 font-body-lg text-body-lg',
                            item.is_ready
                                ? 'text-slate-text/70 line-through'
                                : 'text-slate-text',
                        ]"
                    >
                        {{ item.notes }}
                    </p>
                </div>
            </div>
        </div>

        <button
            @click="emit('mark-ready', order.id)"
            :class="[
                'w-full py-3 rounded-lg font-label-lg',
                'flex items-center justify-center gap-2',
                'transition-colors',
                urgencyStyles.button,
            ]"
        >
            <svg
                class="w-5 h-5"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
            >
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M5 13l4 4L19 7"
                />
            </svg>
            Mark Order as Ready
        </button>
    </div>
</template>
