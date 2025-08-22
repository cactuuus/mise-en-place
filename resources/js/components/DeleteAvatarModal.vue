<template>
    <Dialog
        v-model:visible="isVisible"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        class="base-modal"
        dismissable-mask
        header="Delete Avatar"
        modal
        responsive
    >
        <div class="space-y-4">
            <div
                class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                <i class="pi pi-exclamation-triangle text-red-500 text-xl"></i>
                <div>
                    <p class="font-semibold text-red-800 dark:text-red-200">Remove profile picture?</p>
                    <p class="text-sm text-red-600 dark:text-red-300">
                        Your avatar will be set to the default placeholder.
                    </p>
                </div>
            </div>

            <Message v-if="authStore.errorMessage" severity="error">
                {{ authStore.errorMessage }}
            </Message>

            <div class="button-container">
                <Button
                    label="Cancel"
                    outlined
                    severity="secondary"
                    @click="handleCancel"
                />
                <Button
                    :loading="loading"
                    icon="pi pi-trash"
                    label="Delete Avatar"
                    severity="danger"
                    @click="handleDelete"
                />
            </div>
        </div>
    </Dialog>
</template>

<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import Message from 'primevue/message'

interface Props {
    visible: boolean
}

interface Emits {
    'update:visible': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const loading = ref(false)

// Computed properties
const isVisible = computed({
    get: () => props.visible,
    set: (value: boolean) => emit('update:visible', value)
})

// Clear auth errors when modal opens/closes
watch(isVisible, (isOpen) => {
    if (!isOpen) {
        authStore.errorMessage = ''
    }
})

// Event handlers
const handleDelete = async () => {
    loading.value = true

    try {
        const success = await authStore.deleteAvatar()

        if (success) {
            emit('update:visible', false)
        }
    } catch (error) {
        console.error('Avatar deletion failed:', error)
    }

    loading.value = false
}

const handleCancel = () => {
    emit('update:visible', false)
}
</script>
