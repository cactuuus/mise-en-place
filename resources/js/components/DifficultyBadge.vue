<!-- js/components/DifficultyBadge.vue -->
<template>
    <div
        :class="`bg-${difficulty.color}-50 text-${difficulty.color}-600 dark:bg-${difficulty.color}-900 dark:text-${difficulty.color}-200 ring-${difficulty.color}-600/10 dark:ring-${difficulty.color}-200/10`"
        class="tag font-medium ring-inset ring-1"
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
