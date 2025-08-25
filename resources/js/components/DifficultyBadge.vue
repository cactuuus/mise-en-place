<template>
    <Tag
        :severity="difficulty.severity"
        :value="difficulty.label"
        class="difficulty-tag"
    />
</template>

<script lang="ts" setup>
import Tag from 'primevue/tag'
import {DifficultyLevel} from '@/types/recipe'

defineProps<{
    difficulty: DifficultyLevel
}>()
</script>
