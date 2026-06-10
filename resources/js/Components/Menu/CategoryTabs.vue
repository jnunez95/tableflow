<script setup>
defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    activeCategory: {
        type: [Number, String, null],
        default: null,
    },
});

defineEmits(['select']);
</script>

<template>
    <nav class="hidden items-center gap-6 lg:gap-8 md:flex">
        <button
            type="button"
            class="min-h-12 border-b-[3px] px-1 py-2 font-headline-md text-headline-md font-semibold transition hover:text-terracotta-accent"
            :class="activeCategory === null
                ? 'border-terracotta-accent text-terracotta-accent'
                : 'border-transparent text-deep-navy'"
            @click="$emit('select', null)"
        >
            All
        </button>

        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            class="min-h-12 border-b-[3px] px-1 py-2 font-headline-md text-headline-md font-semibold transition hover:text-terracotta-accent"
            :class="activeCategory === category.id
                ? 'border-terracotta-accent text-terracotta-accent'
                : 'border-transparent text-deep-navy'"
            @click="$emit('select', category.id)"
        >
            {{ category.name }}
        </button>
    </nav>

    <div class="overflow-x-auto pb-1 md:hidden">
        <div class="flex min-w-max gap-3">
            <button
                type="button"
                class="min-h-12 rounded-full px-6 py-3 font-headline-md text-headline-md font-semibold transition active:scale-95"
                :class="activeCategory === null
                    ? 'bg-terracotta-accent text-white'
                    : 'border-2 border-outline text-deep-navy'"
                @click="$emit('select', null)"
            >
                All
            </button>

            <button
                v-for="category in categories"
                :key="category.id"
                type="button"
                class="min-h-12 rounded-full px-6 py-3 font-headline-md text-headline-md font-semibold transition active:scale-95"
                :class="activeCategory === category.id
                    ? 'bg-terracotta-accent text-white'
                    : 'border-2 border-outline text-deep-navy'"
                @click="$emit('select', category.id)"
            >
                {{ category.name }}
            </button>
        </div>
    </div>
</template>
