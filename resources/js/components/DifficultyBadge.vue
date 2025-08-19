<!-- js/components/DifficultyBadge.vue -->
<template>
    <div
        :class="`bg-${difficulty.color}-100 text-${difficulty.color}-800 dark:bg-${difficulty.color}-900 dark:text-${difficulty.color}-200`"
        class="px-2 py-1 rounded-full text-xs font-medium"
    >
        {{ difficulty.label }}
    </div>
</template>

<script lang="ts" setup>
import {DifficultyLevel} from '@/types/recipe'

defineProps<{
    difficulty: DifficultyLevel
}>()
</script>
