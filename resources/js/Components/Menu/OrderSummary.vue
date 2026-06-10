<script setup>
import { computed } from 'vue';

const props = defineProps({
    itemCount: {
        type: Number,
        default: 0,
    },
    total: {
        type: Number,
        default: 0,
    },
    isSubmitting: {
        type: Boolean,
        default: false,
    },
    successMessage: {
        type: String,
        default: '',
    },
    labels: {
        type: Object,
        required: true,
    },
});

defineEmits(['complete', 'close-bill']);

const formattedTotal = computed(() => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(props.total);
});
</script>

<template>
    <div class="menu-action-bar fixed inset-x-0 bottom-0 z-30 border-t border-soft-blue-gray px-margin-mobile py-4 md:px-margin-desktop">
        <div class="mx-auto flex max-w-container-max-width flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-white shadow-ambient">
                    <span class="material-symbols-outlined text-deep-navy">shopping_cart</span>
                </div>
                <div>
                    <p class="font-label-md text-label-md uppercase tracking-[0.2em] text-slate-text">
                        {{ labels.orderTotal }}
                    </p>
                    <p class="font-headline-md text-headline-md text-deep-navy">
                        {{ formattedTotal }}
                    </p>
                    <p
                        v-if="successMessage"
                        class="mt-1 font-body-md text-body-md text-deep-navy"
                    >
                        {{ successMessage }}
                    </p>
                    <p
                        v-else-if="itemCount > 0"
                        class="mt-1 font-body-md text-body-md text-slate-text"
                    >
                        {{ itemCount }} {{ itemCount === 1 ? labels.item : labels.items }}
                    </p>
                </div>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center sm:justify-end">
                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-terracotta-accent px-6 py-3 font-label-lg text-label-lg uppercase tracking-wider text-white transition hover:opacity-90 active:scale-95 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="itemCount === 0 || isSubmitting"
                    @click="$emit('complete')"
                >
                    <span class="material-symbols-outlined text-base">play_arrow</span>
                    {{ isSubmitting ? labels.submitting : labels.completeOrder }}
                </button>

                <button
                    type="button"
                    class="inline-flex items-center justify-center gap-2 rounded-lg border border-outline-variant bg-white px-5 py-3 font-label-lg text-label-lg text-deep-navy transition hover:bg-soft-blue-gray/30 active:scale-95"
                    @click="$emit('close-bill')"
                >
                    <span class="material-symbols-outlined text-base">receipt_long</span>
                    {{ labels.closeBill }}
                </button>
            </div>
        </div>
    </div>
</template>

<style scoped>
.menu-action-bar {
    background: rgba(232, 237, 247, 0.95);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    box-shadow: 0 -8px 24px rgba(30, 46, 66, 0.05);
}
</style>
