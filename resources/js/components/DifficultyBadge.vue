<template>
    <div
        :class="badgeClasses"
    >
        {{ getLabel(difficulty) }}
    </div>
</template>

<script lang="ts" setup>
import {computed} from 'vue'

const props = defineProps<{
    difficulty?: number
}>()

const getLabel = (value: number): string => {
    switch (value) {
        case 1:
            return 'Easy'
        case 2:
            return 'Medium'
        case 3:
            return 'Hard'
        default:
            console.error('unrecognized difficulty value: ' + value)
    }
}

const badgeClasses = computed(() => {
    const base = 'px-2 py-1 rounded-full text-xs font-medium'

    switch (props.difficulty) {
        case 1:
            return `${base} bg-green-100 text-green-800`
        case 2:
            return `${base} bg-yellow-100 text-yellow-800`
        case 3:
            return `${base} bg-red-100 text-red-800`
    }
})
</script>
