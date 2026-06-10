<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
        validator: (value) => ['optimal', 'busy', 'critical'].includes(value),
    },
    delayedCount: {
        type: Number,
        default: 0,
    },
});

const statusConfig = computed(() => {
    return {
        optimal: {
            color: 'bg-green-500',
            label: 'Optimal',
        },
        busy: {
            color: 'bg-yellow-500',
            label: 'Busy',
        },
        critical: {
            color: 'bg-error',
            label: 'Critical',
        },
    }[props.status];
});
</script>

<template>
    <div class="flex items-center gap-2">
        <span :class="['w-2 h-2 rounded-full', statusConfig.color]" />
        <span class="text-white font-body-md">
            Kitchen Status: {{ statusConfig.label }}
        </span>
    </div>
</template>
