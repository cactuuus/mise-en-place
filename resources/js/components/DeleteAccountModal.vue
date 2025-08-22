<template>
    <Dialog
        v-model:visible="isVisible"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        class="base-modal !max-w-md"
        dismissable-mask
        header="Delete Account"
        modal
        responsive
    >
        <Form v-slot="$form" :resolver="deleteResolver" validate-on-value-update @submit="handleSubmit">
            <div class="space-y-4">
                <div
                    class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <i class="pi pi-exclamation-triangle text-red-500 text-xl"></i>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-200">This action cannot be undone</p>
                        <p class="text-sm text-red-600 dark:text-red-300">
                            All your recipes and data will be permanently deleted.
                        </p>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <InputText
                            id="confirmDelete"
                            :disabled="loading"
                            fluid
                            name="confirmation"
                        />
                        <label for="confirmDelete">Type 'DELETE' to confirm</label>
                    </FloatLabel>
                    <Message v-if="$form.confirmation?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.confirmation.error.message }}
                    </Message>
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
                        :disabled="loading || !$form.valid"
                        :loading="loading"
                        icon="pi pi-trash"
                        label="Confirm Deletion"
                        severity="danger"
                        type="submit"
                    />
                </div>
            </div>
        </Form>
    </Dialog>
</template>

<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {Form} from '@primevue/forms'
import {z} from 'zod'
import {zodResolver} from '@primevue/forms/resolvers/zod'

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

// Form validation schema
const deleteSchema = z.object({
    confirmation: z.string().refine(val => val === 'DELETE', {
        message: "You must type 'DELETE' to confirm"
    })
})

const deleteResolver = zodResolver(deleteSchema)

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
const handleSubmit = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return

    loading.value = true

    try {
        const success = await authStore.deleteAccount()

        if (success) {
            emit('update:visible', false)
        }
    } catch (error) {
        console.error('Account deletion failed:', error)
    }

    loading.value = false
}

const handleCancel = () => {
    emit('update:visible', false)
}
</script>
