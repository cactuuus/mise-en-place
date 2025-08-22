<template>
    <BaseModal
        :error-message="authStore.errorMessage"
        :on-submit="onSubmit"
        :on-visibility-change="onVisibilityChange"
        :schema="passwordSchema"
        :visible="visible"
        submit-icon="pi pi-save"
        submit-label="Save"
        title="Change Password"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
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
                    <Message v-if="form.currentPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ form.currentPassword.error.message }}
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
                    <Message v-if="form.newPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ form.newPassword.error.message }}
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
                    <Message v-if="form.confirmPassword?.invalid" severity="error" size="small" variant="simple">
                        {{ form.confirmPassword.error.message }}
                    </Message>
                </div>
            </div>
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import {useAuthStore} from '@/stores/auth'
import BaseModal from '@/baseComponents/baseModal.vue'
import Password from 'primevue/password'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {z} from 'zod'

interface Props {
    visible: boolean
}

interface Emits {
    'update:visible': [value: boolean]
}

defineProps<Props>()
const emit = defineEmits<Emits>()
const authStore = useAuthStore()

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

// Events
const onVisibilityChange = (isOpen: boolean) => {
    if (!isOpen) {
        authStore.errorMessage = ''
    }
}

const onSubmit = async (formData: { currentPassword: string; newPassword: string; confirmPassword: string }) => {
    return await authStore.updatePassword({
        current_password: formData.currentPassword,
        new_password: formData.newPassword,
        new_password_confirmation: formData.confirmPassword
    })
}
</script>
