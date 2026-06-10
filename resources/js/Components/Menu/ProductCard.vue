<script setup>
const props = defineProps({
    product: {
        type: Object,
        required: true,
    },
    quantity: {
        type: Number,
        default: 0,
    },
    layout: {
        type: String,
        default: 'vertical',
        validator: (value) => ['vertical', 'horizontal'].includes(value),
    },
});

defineEmits(['add', 'remove']);

const placeholderImage = 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?auto=format&fit=crop&w=800&q=80';

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD',
    }).format(Number(price));
};

const stripHtml = (value) => {
    if (!value) {
        return '';
    }

    return value.replace(/<[^>]*>/g, '');
};

const primaryTag = () => props.product.dietary_tags?.[0] ?? null;
</script>

<template>
    <article
        class="overflow-hidden rounded-lg border border-soft-blue-gray bg-surface-container-lowest transition-shadow hover:shadow-ambient"
        :class="layout === 'horizontal'
            ? 'flex flex-col sm:flex-row'
            : 'flex h-full flex-col'"
    >
        <div
            class="relative shrink-0 overflow-hidden bg-soft-blue-gray"
            :class="layout === 'horizontal'
                ? 'aspect-[4/3] w-full sm:aspect-auto sm:w-2/5 sm:min-h-[220px]'
                : 'aspect-[4/3]'"
        >
            <img
                :src="product.image || placeholderImage"
                :alt="product.name"
                class="h-full w-full object-cover"
                @error="($event.target).src = placeholderImage"
            >

            <span
                v-if="primaryTag()"
                class="absolute left-3 top-3 rounded bg-deep-navy/80 px-3 py-1 font-label-md text-label-md uppercase tracking-[0.2em] text-white backdrop-blur-sm"
            >
                {{ primaryTag() }}
            </span>
        </div>

        <div
            class="flex flex-1 flex-col p-4"
            :class="layout === 'horizontal' ? 'justify-between sm:p-6' : ''"
        >
            <div>
            <div
                class="flex items-start justify-between gap-3"
                :class="layout === 'horizontal' ? 'flex-col sm:flex-row sm:items-center' : ''"
            >
                <h3 class="font-headline-md text-headline-md text-deep-navy">
                    {{ product.name }}
                </h3>
                <div class="flex shrink-0 items-center gap-3">
                    <span class="font-label-lg text-label-lg text-terracotta-accent">
                        {{ formatPrice(product.price) }}
                    </span>

                    <div
                        v-if="layout === 'horizontal'"
                        class="flex items-center gap-2"
                    >
                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent text-terracotta-accent transition hover:bg-terracotta-accent/10 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="quantity === 0"
                            @click="$emit('remove', product.id)"
                        >
                            <span class="material-symbols-outlined text-xl">remove</span>
                        </button>

                        <span class="min-w-[2rem] text-center font-label-lg text-label-lg text-deep-navy">
                            {{ quantity }}
                        </span>

                        <button
                            type="button"
                            class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent bg-terracotta-accent text-white transition hover:opacity-90 active:scale-95"
                            @click="$emit('add', product)"
                        >
                            <span class="material-symbols-outlined text-xl">add</span>
                        </button>
                    </div>
                </div>
            </div>

                <p
                    v-if="stripHtml(product.description)"
                    class="mt-2 font-body-md text-body-md text-slate-text"
                    :class="layout === 'horizontal' ? 'line-clamp-3' : 'line-clamp-2'"
                >
                    {{ stripHtml(product.description) }}
                </p>

                <div
                    v-if="product.dietary_tags?.length > 1"
                    class="mt-3 flex flex-wrap gap-2"
                >
                    <span
                        v-for="tag in product.dietary_tags.slice(1)"
                        :key="tag"
                        class="rounded-full bg-deep-navy/10 px-3 py-1 font-label-md text-label-md uppercase tracking-[0.2em] text-deep-navy"
                    >
                        {{ tag }}
                    </span>
                </div>
            </div>

            <div
                v-if="layout !== 'horizontal'"
                class="mt-4 flex items-center justify-end gap-2 pt-stack-sm"
            >
                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent text-terracotta-accent transition hover:bg-terracotta-accent/10 active:scale-95 disabled:cursor-not-allowed disabled:opacity-40"
                    :disabled="quantity === 0"
                    @click="$emit('remove', product.id)"
                >
                    <span class="material-symbols-outlined text-xl">remove</span>
                </button>

                <span class="min-w-[2rem] text-center font-label-lg text-label-lg text-deep-navy">
                    {{ quantity }}
                </span>

                <button
                    type="button"
                    class="flex h-10 w-10 items-center justify-center rounded-lg border border-terracotta-accent bg-terracotta-accent text-white transition hover:opacity-90 active:scale-95"
                    @click="$emit('add', product)"
                >
                    <span class="material-symbols-outlined text-xl">add</span>
                </button>
            </div>
        </div>
    </article>
</template>
