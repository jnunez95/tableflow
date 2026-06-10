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

const mobileButtonClass = (isActive) => [
    'w-full min-h-11 rounded-lg px-3 py-2 font-label-lg text-label-lg font-semibold transition active:scale-95',
    isActive
        ? 'bg-terracotta-accent text-white'
        : 'border border-outline-variant bg-white text-deep-navy',
];
</script>

<template>
    <nav class="hidden items-center gap-6 md:flex lg:gap-8">
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

    <div class="grid grid-cols-2 gap-2 md:hidden">
        <button
            type="button"
            :class="mobileButtonClass(activeCategory === null)"
            @click="$emit('select', null)"
        >
            All
        </button>

        <button
            v-for="category in categories"
            :key="category.id"
            type="button"
            :class="mobileButtonClass(activeCategory === category.id)"
            @click="$emit('select', category.id)"
        >
            {{ category.name }}
        </button>
    </div>
</template>
