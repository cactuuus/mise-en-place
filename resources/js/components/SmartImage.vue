<template>
    <div :class="containerClass">
        <!-- Loading skeleton -->
        <div
            v-if="loading"
            :class="['secondary-bg flex items-center justify-center', imageClass]"
        >
            <ProgressSpinner
                animation-duration="2s"
                class="!w-1/3 !max-w-20 !aspect-square !h-auto"
                stroke-width="4"
            />
        </div>

        <!-- Actual image -->
        <img
            v-show="!loading && !error"
            :key="imageKey"
            :alt="alt"
            :class="imageClass"
            :src="src"
            @error="onError"
            @load="onLoad"
        />

        <!-- Error fallback -->
        <div
            v-if="error"
            :class="['bg-red-100 dark:bg-red-700/50 flex items-center justify-center', imageClass]">
            <svg class="!w-1/2 !max-w-20 !aspect-square !h-auto text-red-600 dark:text-red-200" fill="none"
                 stroke="currentColor"
                 viewBox="0 0 24 24">
                <path
                    d="M14.2647 15.9377L12.5473 14.2346C11.758 13.4519 11.3633 13.0605 10.9089 12.9137C10.5092 12.7845 10.079 12.7845 9.67922 12.9137C9.22485 13.0605 8.83017 13.4519 8.04082 14.2346L4.04193 18.2622M14.2647 15.9377L14.606 15.5991C15.412 14.7999 15.8149 14.4003 16.2773 14.2545C16.6839 14.1262 17.1208 14.1312 17.5244 14.2688C17.9832 14.4253 18.3769 14.834 19.1642 15.6515L20 16.5001M14.2647 15.9377L18.22 19.9628M12 4H7.2C6.07989 4 5.51984 4 5.09202 4.21799C4.7157 4.40973 4.40973 4.71569 4.21799 5.09202C4 5.51984 4 6.0799 4 7.2V16.8C4 17.4466 4 17.9066 4.04193 18.2622M4.04193 18.2622C4.07264 18.5226 4.12583 18.7271 4.21799 18.908C4.40973 19.2843 4.7157 19.5903 5.09202 19.782C5.51984 20 6.07989 20 7.2 20H16.8C17.9201 20 18.4802 20 18.908 19.782C19.2843 19.5903 19.5903 19.2843 19.782 18.908C20 18.4802 20 17.9201 20 16.8V12M16 3L18.5 5.5M18.5 5.5L21 8M18.5 5.5L21 3M18.5 5.5L16 8"
                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            </svg>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {onUnmounted, ref, watch} from 'vue'
import ProgressSpinner from "primevue/progressspinner";

interface Props {
    src: string
    alt?: string
    imageClass?: string
    containerClass?: string
    maxRetries?: number
    retryDelay?: number // Initial delay in ms
}

const props = withDefaults(defineProps<Props>(), {
    alt: '',
    imageClass: '',
    containerClass: '',
    maxRetries: 5,
    retryDelay: 1000,
})

const imageKey = ref(0)
const loading = ref(true)
const error = ref(false)
const retryCount = ref(0)
let retryTimeout: NodeJS.Timeout | null = null

const resetState = () => {
    loading.value = true
    error.value = false
    retryCount.value = 0
    if (retryTimeout) {
        clearTimeout(retryTimeout)
        retryTimeout = null
    }
}

const onLoad = () => {
    loading.value = false
    error.value = false
    retryCount.value = 0
}

const onError = () => {
    if (retryCount.value < props.maxRetries) {
        // const delay = props.retryDelay * Math.pow(2, retryCount.value, 5000) // exponential
        const delay = Math.min(props.retryDelay * (retryCount.value + 1), 5000) // linear
        retryCount.value++

        console.log(`SmartImage retry ${retryCount.value}/${props.maxRetries} in ${delay}ms for: ${props.src}`)

        retryTimeout = setTimeout(() => {
            // Force re-render by changing key
            imageKey.value++
        }, delay)
    } else {
        console.log(`SmartImage failed after ${props.maxRetries} retries: ${props.src}`)
        loading.value = false
        error.value = true
    }
}

// Watch src changes
watch(() => props.src, () => {
    resetState()
})

onUnmounted(() => {
    if (retryTimeout) {
        clearTimeout(retryTimeout)
    }
})
</script>
