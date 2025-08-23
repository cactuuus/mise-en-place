<template>
    <Tag
        :severity="difficulty.severity"
        :value="difficulty.label"
    />
</template>

<script lang="ts" setup>
import Tag from 'primevue/tag'
import {DifficultyLevel} from '@/types/recipe'

defineProps<{
    difficulty: DifficultyLevel
}>()
</script>
