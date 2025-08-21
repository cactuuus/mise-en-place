<template>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0 mb-6">Account settings</h1>

        <!-- Profile Section -->
        <Panel>
            <template #header>
                <div class="flex items-center gap-2">
                    <i class="pi pi-user text-primary"></i>
                    Profile Information
                </div>
            </template>
            <div class="space-y-6">
                <!-- Avatar Section -->
                <div class="flex items-start gap-4">
                    <div class="flex flex-col items-center gap-3">
                        <!-- Current or preview avatar -->
                        <Image
                            :src="avatarPreviewUrl || authStore.user?.avatar_urls?.large || '/images/avatar-placeholder.svg'"
                            class="max-w-56"
                            shape="circle"
                        >
                        </Image>

                        <!-- File Upload -->
                        <FileUpload
                            ref="fileUploadRef"
                            :file-limit="1"
                            :max-file-size="2097152"
                            :multiple="false"
                            :show-cancel-button="false"
                            :show-upload-button="false"
                            accept="image/*"
                            choose-icon="pi pi-upload"
                            choose-label="Upload Avatar"
                            @remove="handleAvatarRemove"
                            @select="handleAvatarSelect"
                        >
                            <template #empty>
                                <div class="flex flex-col items-center gap-2">
                                    <i class="pi pi-cloud-upload text-4xl text-surface-400"></i>
                                    <p class="text-sm text-surface-600 dark:text-surface-400">
                                        Drag and drop files here to upload.
                                    </p>
                                </div>
                            </template>
                        </FileUpload>

                        <!-- Clear selection button -->
                        <Button
                            v-if="selectedAvatar"
                            icon="pi pi-times"
                            label="Clear Selection"
                            outlined
                            severity="secondary"
                            size="small"
                            @click="clearAvatarSelection"
                        />
                    </div>
                    <div class="flex-1">
                        <p class="text-sm text-surface-600 dark:text-surface-400 mb-2">
                            Upload a profile picture. Recommended size is 500x500 pixels.
                            JPG, PNG, or WebP files are accepted.
                        </p>
                        <div class="text-xs text-surface-500 dark:text-surface-500">
                            Maximum file size: 2MB
                        </div>
                        <div v-if="selectedAvatar" class="text-xs text-primary mt-1">
                            New avatar selected: {{ selectedAvatar.name }}
                        </div>
                    </div>
                </div>

                <Divider/>

                <!-- Profile Form -->
                <Form
                    v-slot="$form"
                    :initial-values="{ name: authStore.user?.name }"
                    :resolver="profileResolver" validate-on-value-update
                    @submit="handleProfileUpdate"
                >
                    <div class="form-content space-y-4">
                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <InputText
                                    id="name"
                                    :disabled="profileLoading" fluid
                                    name="name"
                                    type="text"
                                />
                                <label for="name">Name</label>
                            </FloatLabel>
                            <Message v-if="$form.name?.invalid" severity="error" size="small" variant="simple">
                                {{ $form.name.error.message }}
                            </Message>
                        </div>

                        <div class="flex justify-end gap-2">
                            <Button
                                :disabled="profileLoading || !$form.valid"
                                :loading="profileLoading"
                                icon="pi pi-save"
                                label="Save Changes"
                                type="submit"
                            />
                        </div>
                    </div>
                </Form>
            </div>
        </Panel>

        <!-- Account Actions -->
        <div class="space-y-4">
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-shield text-primary"></i>
                        Security
                    </div>
                </template>
                <template #content>
                    <div class="space-y-3">
                        <Button
                            class="w-full justify-start"
                            icon="pi pi-key"
                            label="Change Password"
                            outlined
                            @click="showPasswordModal = true"
                        />
                    </div>
                </template>
            </Card>

            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-trash text-red-500"></i>
                        Danger Zone
                    </div>
                </template>
                <template #content>
                    <div class="space-y-3">
                        <p class="text-sm text-surface-600 dark:text-surface-400">
                            Once you delete your account, all of your data will be permanently removed.
                        </p>
                        <Button
                            class="w-full justify-start"
                            icon="pi pi-trash"
                            label="Delete Account"
                            outlined
                            severity="danger"
                            @click="showDeleteModal = true"
                        />
                    </div>
                </template>
            </Card>
        </div>
    </div>

    <!-- Change Password Modal -->
    <Dialog
        v-model:visible="showPasswordModal"
        :closable="false"
        :draggable="false"
        class="w-full max-w-md mx-3"
        header="Change Password"
        modal
    >
        <Form v-slot="$form" :resolver="passwordResolver" validate-on-value-update @submit="handlePasswordChange">
            <div class="form-content space-y-4">
                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <Password
                            id="currentPassword"
                            :disabled="passwordLoading"
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

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <Password
                            id="newPassword"
                            :disabled="passwordLoading"
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
                            :disabled="passwordLoading"
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

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        outlined
                        @click="showPasswordModal = false"
                    />
                    <Button
                        :disabled="passwordLoading || !$form.valid"
                        :loading="passwordLoading"
                        icon="pi pi-save"
                        label="Change Password"
                        type="submit"
                    />
                </div>
            </div>
        </Form>
    </Dialog>

    <!-- Delete Account Modal -->
    <Dialog
        v-model:visible="showDeleteModal"
        :closable="false"
        :draggable="false"
        class="w-full max-w-md mx-3"
        header="Delete Account"
        modal
    >
        <Form v-slot="$form" :resolver="deleteResolver" validate-on-value-update @submit="handleAccountDelete">
            <div class="form-content space-y-4">
                <div
                    class="flex items-center gap-3 p-4 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-200 dark:border-red-800">
                    <i class="pi pi-exclamation-triangle text-red-500 text-xl"></i>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-200">This action cannot be undone</p>
                        <p class="text-sm text-red-600 dark:text-red-300">All your recipes and data will be
                            permanently deleted.</p>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <InputText
                            id="confirmDelete"
                            :disabled="deleteLoading"
                            fluid
                            name="confirmation"
                            placeholder="Type 'DELETE' to confirm"
                        />
                        <label for="confirmDelete">Type 'DELETE' to confirm</label>
                    </FloatLabel>
                    <Message v-if="$form.confirmation?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.confirmation.error.message }}
                    </Message>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        outlined
                        @click="showDeleteModal = false"
                    />
                    <Button
                        :disabled="deleteLoading || !$form.valid"
                        :loading="deleteLoading"
                        icon="pi pi-trash"
                        label="Delete Account"
                        severity="danger"
                        type="submit"
                    />
                </div>
            </div>
        </Form>
    </Dialog>
</template>

<script lang="ts" setup>
import {ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth'
import {useToast} from 'primevue/usetoast'
import Card from 'primevue/card'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'
import Password from 'primevue/password'
import Dialog from 'primevue/dialog'
import Divider from 'primevue/divider'
import Panel from 'primevue/panel'
import Image from 'primevue/image'
import Message from 'primevue/message'
import FileUpload from 'primevue/fileupload'
import {Form} from '@primevue/forms'
import {z} from 'zod'
import {zodResolver} from '@primevue/forms/resolvers/zod'

// Stores and composables
const authStore = useAuthStore()
const toast = useToast()

// Refs for modals
const showPasswordModal = ref(false)
const showDeleteModal = ref(false)

// Avatar handling
const selectedAvatar = ref<File | null>(null)
const avatarPreviewUrl = ref<string | null>(null)
const fileUploadRef = ref()

// Loading states
const profileLoading = ref(false)
const passwordLoading = ref(false)
const deleteLoading = ref(false)

// Zod schemas
const profileSchema = z.object({
    name: z.string().min(1, 'Name is required').max(255, 'Name must be less than 255 characters')
})

const passwordSchema = z.object({
    currentPassword: z.string().min(1, 'Current password is required'),
    newPassword: z.string().min(8, 'Password must be at least 8 characters'),
    confirmPassword: z.string().min(1, 'Please confirm your password')
}).refine(data => data.newPassword === data.confirmPassword, {
    message: "Passwords don't match",
    path: ['confirmPassword']
})

const deleteSchema = z.object({
    confirmation: z.string().refine(val => val === 'DELETE', {
        message: "You must type 'DELETE' to confirm"
    })
})

// Form resolvers
const profileResolver = zodResolver(profileSchema)
const passwordResolver = zodResolver(passwordSchema)
const deleteResolver = zodResolver(deleteSchema)

// Clear auth errors when modals are closed
watch(showPasswordModal, (isOpen) => {
    if (!isOpen) {
        authStore.errorMessage = ''
    }
})

// Functions
const handleAvatarSelect = (event: { files: File[] }) => {
    const file = event.files[0]
    if (!file) return

    // Validate file size (2MB) - FileUpload should handle this but let's be safe
    if (file.size > 2 * 1024 * 1024) {
        toast.add({
            severity: 'error',
            summary: 'File too large',
            detail: 'Please select an image smaller than 2MB',
            life: 5000
        })
        return
    }

    // Validate file type
    if (!file.type.startsWith('image/')) {
        toast.add({
            severity: 'error',
            summary: 'Invalid file type',
            detail: 'Please select an image file (JPG, PNG, or WebP)',
            life: 5000
        })
        return
    }

    selectedAvatar.value = file

    // Create preview URL
    if (avatarPreviewUrl.value) {
        URL.revokeObjectURL(avatarPreviewUrl.value)
    }
    avatarPreviewUrl.value = URL.createObjectURL(file)
}

const handleAvatarRemove = () => {
    clearAvatarSelection()
}

const clearAvatarSelection = () => {
    selectedAvatar.value = null
    if (avatarPreviewUrl.value) {
        URL.revokeObjectURL(avatarPreviewUrl.value)
        avatarPreviewUrl.value = null
    }
    // Clear the file upload component
    if (fileUploadRef.value) {
        fileUploadRef.value.clear()
    }
}

const handleProfileUpdate = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return
    profileLoading.value = true

    try {
        const values = Object.keys(event.states).reduce((acc, key) => {
            acc[key] = event.states[key].value
            return acc
        }, {} as Record<string, any>)

        console.log('Form values:', values) // Debug what we get from the form

        const formData = new FormData()
        formData.append('name', values.name.trim())

        if (selectedAvatar.value) {
            formData.append('avatar', selectedAvatar.value)
        }

        // Debug what's in FormData
        console.log('FormData contents:')
        for (let [key, value] of formData.entries()) {
            console.log(key, value)

        }

        const success = await authStore.updateProfile(formData)

        if (success) {
            clearAvatarSelection()
        }
    } catch (error) {
        console.error('Profile update failed:', error)
    }

    profileLoading.value = false
}

const handlePasswordChange = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return

    passwordLoading.value = true

    try {
        // Extract values from states
        const values = Object.keys(event.states).reduce((acc, key) => {
            acc[key] = event.states[key].value
            return acc
        }, {} as Record<string, any>)

        const success = await authStore.updatePassword({
            current_password: values.currentPassword,
            new_password: values.newPassword,
            new_password_confirmation: values.confirmPassword
        })

        if (success) {
            showPasswordModal.value = false
        }
    } catch (error) {
        console.error('Password update failed:', error)
    }

    passwordLoading.value = false
}

const handleAccountDelete = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return

    deleteLoading.value = true

    try {
        // Extract values from states
        const values = Object.keys(event.states).reduce((acc, key) => {
            acc[key] = event.states[key].value
            return acc
        }, {} as Record<string, any>)

        const success = await authStore.deleteAccount({
            confirmation: values.confirmation
        })

        if (success) {
            // User will be redirected by the auth store
            showDeleteModal.value = false
        }
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Deletion Failed',
            detail: 'Failed to delete account. Please try again.',
            life: 5000
        })
    } finally {
        deleteLoading.value = false
    }
}
</script>
