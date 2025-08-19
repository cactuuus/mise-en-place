<!-- js/components/DifficultyBadge.vue -->
<template>
    <div
        :class="`bg-${color}-100 text-${color}-800 dark:bg-${color}-900 dark:text-${color}-200`"
        class="px-2 py-1 rounded-full text-xs font-medium"
    >
        {{ getDifficultyLabel(difficulty) }}
    </div>
</template>

<script lang="ts" setup>
import {DifficultyLevel, getDifficultyColor, getDifficultyLabel} from '@/types/recipe'
import {computed} from "vue";

const props = defineProps<{
    difficulty: DifficultyLevel
}>()

const color = computed(() => {
    return getDifficultyColor(props.difficulty)
})

</script>
