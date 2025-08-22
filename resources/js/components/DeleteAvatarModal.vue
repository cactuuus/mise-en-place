<template>
    <BaseModal
        :error-message="authStore.errorMessage"
        :on-submit="onSubmit"
        :on-visibility-change="onVisibilityChange"
        :visible="visible"
        submit-icon="pi pi-trash"
        submit-label="Delete Avatar"
        submit-severity="danger"
        title="Delete Avatar"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default>
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
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import {useAuthStore} from '@/stores/auth'
import BaseModal from '@/baseComponents/baseModal.vue'

interface Props {
    visible: boolean
}

interface Emits {
    'update:visible': [value: boolean]
}

defineProps<Props>()
const emit = defineEmits<Emits>()
const authStore = useAuthStore()

// Events
const onVisibilityChange = (isOpen: boolean) => {
    if (!isOpen) {
        authStore.errorMessage = ''
    }
}

const onSubmit = async () => {
    return await authStore.deleteAvatar()
}
</script>
