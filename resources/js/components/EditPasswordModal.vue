<template>
    <Dialog
        v-model:visible="isVisible"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        class="base-modal"
        dismissable-mask
        header="Change Password"
        modal
        responsive
    >
        <Form v-slot="$form" :resolver="passwordResolver" validate-on-value-update @submit="handleSubmit">
            <div class="space-y-4">
                <div class="flex flex-col gap-1 mt-1">
                    <FloatLabel variant="on">
                        <Password
                            id="currentPassword"
                            :disabled="loading"
                            :feedback="false"
                            fluid
                            name="currentPassword"
                            toggle-mask
                        />
                        <label for="currentPassword">Current Password</label>
                    </FloatLabel>
                    <Message v-if="$form.currentPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.currentPassword.error.message }}
                    </Message>
                </div>

                <hr/>

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <Password
                            id="newPassword"
                            :disabled="loading"
                            fluid
                            name="newPassword"
                            toggle-mask
                        />
                        <label for="newPassword">New Password</label>
                    </FloatLabel>
                    <Message v-if="$form.newPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.newPassword.error.message }}
                    </Message>
                </div>

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <Password
                            id="confirmPassword"
                            :disabled="loading"
                            :feedback="false"
                            fluid
                            name="confirmPassword"
                            toggle-mask
                        />
                        <label for="confirmPassword">Confirm New Password</label>
                    </FloatLabel>
                    <Message v-if="$form.confirmPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.confirmPassword.error.message }}
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
                        icon="pi pi-save"
                        label="Save"
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
import Password from 'primevue/password'
import Button from 'primevue/button'
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
const passwordSchema = z.object({
    currentPassword: z.string().min(1, 'Current password is required'),
    newPassword: z.string().min(8, 'Password must be at least 8 characters'),
    confirmPassword: z.string().min(1, 'Please confirm your password')
}).refine(data => data.newPassword === data.confirmPassword, {
    message: "Confirmation password doesn't match new password",
    path: ['confirmPassword']
}).refine(data => data.newPassword !== data.currentPassword, {
    message: "New password must be different from current password",
    path: ['newPassword']
})
const passwordResolver = zodResolver(passwordSchema)

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

    const values = Object.keys(event.states).reduce((acc, key) => {
        acc[key] = event.states[key].value
        return acc
    }, {} as Record<string, any>)

    loading.value = true

    try {
        const success = await authStore.updatePassword({
            current_password: values.currentPassword,
            new_password: values.newPassword,
            new_password_confirmation: values.confirmPassword
        })

        if (success) {
            emit('update:visible', false)
        }
    } catch (error) {
        console.error('Password update failed:', error)
    }

    loading.value = false
}

const handleCancel = () => {
    emit('update:visible', false)
}
</script>
