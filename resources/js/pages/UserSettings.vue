<template>
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-surface-900 dark:text-surface-0 mb-6">Account settings</h1>

        <!-- Profile Information Display -->
        <Panel>
            <template #header>
                <div class="flex items-center gap-2">
                    <i class="pi pi-user text-primary"></i>
                    Profile Information
                </div>
            </template>

            <div class="space-y-6">
                <!-- Avatar Section -->
                <div
                    class="flex items-center justify-between p-4 border border-surface-200 dark:border-surface-700 rounded-lg">
                    <div class="flex items-center gap-4">
                        <Avatar
                            :image="authStore.user?.avatar_urls?.small || '/images/avatar-placeholder.svg'"
                            shape="circle"
                            size="xlarge"
                        />
                        <div>
                            <p class="font-medium text-surface-900 dark:text-surface-0">Profile Picture</p>
                            <p class="text-sm text-surface-600 dark:text-surface-400">
                                JPG, PNG, or WebP. Max 2MB.
                            </p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <Button
                            icon="pi pi-pencil"
                            label="Edit"
                            outlined
                            size="small"
                            @click="showAvatarModal = true"
                        />
                        <Button
                            v-if="authStore.user?.avatar_urls?.small"
                            icon="pi pi-trash"
                            label="Delete"
                            outlined
                            severity="danger"
                            size="small"
                            @click="showDeleteAvatarModal = true"
                        />
                    </div>
                </div>

                <!-- Name Section -->
                <div
                    class="flex items-center justify-between p-4 border border-surface-200 dark:border-surface-700 rounded-lg">
                    <div>
                        <p class="font-medium text-surface-900 dark:text-surface-0">Name</p>
                        <p class="text-surface-600 dark:text-surface-400">{{ authStore.user?.name }}</p>
                    </div>
                    <Button
                        icon="pi pi-pencil"
                        label="Edit"
                        outlined
                        size="small"
                        @click="showNameModal = true"
                    />
                </div>

                <!-- Email Section -->
                <div
                    class="flex items-center justify-between p-4 border border-surface-200 dark:border-surface-700 rounded-lg opacity-60">
                    <div>
                        <p class="font-medium text-surface-900 dark:text-surface-0">Email Address</p>
                        <p class="text-surface-600 dark:text-surface-400">{{ authStore.user?.email }}</p>
                    </div>
                    <Button
                        disabled
                        icon="pi pi-lock"
                        label="Locked"
                        outlined
                        size="small"
                    />
                </div>

                <!-- Password Section -->
                <div
                    class="flex items-center justify-between p-4 border border-surface-200 dark:border-surface-700 rounded-lg">
                    <div>
                        <p class="font-medium text-surface-900 dark:text-surface-0">Password</p>
                        <p class="text-surface-600 dark:text-surface-400">••••••••••••</p>
                    </div>
                    <Button
                        icon="pi pi-key"
                        label="Change"
                        outlined
                        size="small"
                        @click="showPasswordModal = true"
                    />
                </div>
            </div>
        </Panel>

        <!-- Danger Zone -->
        <Card class="mt-6">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-exclamation-triangle text-red-500"></i>
                    Danger Zone
                </div>
            </template>
            <template #content>
                <div class="space-y-3">
                    <p class="text-sm text-surface-600 dark:text-surface-400">
                        Once you delete your account, all of your data will be permanently removed.
                    </p>
                    <Button
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

    <!-- Edit Name Modal -->
    <Dialog
        v-model:visible="showNameModal"
        :closable="false"
        :draggable="false"
        class="w-full max-w-md mx-3"
        header="Edit Name"
        modal
    >
        <Form v-slot="$form" :initial-values="{ name: authStore.user?.name }" :resolver="nameResolver"
              validate-on-value-update @submit="handleNameUpdate">
            <div class="space-y-4">
                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <InputText
                            id="name"
                            :disabled="nameLoading"
                            fluid
                            name="name"
                            type="text"
                        />
                        <label for="name">Full Name</label>
                    </FloatLabel>
                    <Message v-if="$form.name?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.name.error.message }}
                    </Message>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <Button
                        label="Cancel"
                        outlined
                        @click="showNameModal = false"
                    />
                    <Button
                        :disabled="nameLoading || !$form.valid"
                        :loading="nameLoading"
                        icon="pi pi-save"
                        label="Save Changes"
                        type="submit"
                    />
                </div>
            </div>
        </Form>
    </Dialog>

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
            <div class="space-y-4">
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

    <!-- Avatar Upload Modal -->
    <Dialog
        v-model:visible="showAvatarModal"
        :closable="false"
        :draggable="false"
        class="w-full max-w-md mx-3"
        header="Edit Avatar"
        modal
    >
        <div class="space-y-6">
            <!-- Avatar Preview -->
            <div class="text-center">
                <Avatar
                    :image="avatarPreviewUrl || authStore.user?.avatar_urls?.large || '/images/avatar-placeholder.svg'"
                    class="mb-4"
                    shape="circle"
                    size="xlarge"
                />
                <p class="text-sm text-surface-600 dark:text-surface-400">
                    {{ selectedAvatar ? 'New avatar selected' : 'Current avatar' }}
                </p>
            </div>

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
                choose-label="Choose New Avatar"
                @remove="handleAvatarRemove"
                @select="handleAvatarSelect"
            >
                <template #empty>
                    <div class="flex flex-col items-center gap-2 py-4">
                        <i class="pi pi-cloud-upload text-2xl text-surface-400"></i>
                        <p class="text-sm text-surface-600 dark:text-surface-400">
                            Drag and drop an image here or click to browse
                        </p>
                    </div>
                </template>
            </FileUpload>

            <!-- Clear selection button -->
            <div v-if="selectedAvatar" class="text-center">
                <Button
                    icon="pi pi-times"
                    label="Clear Selection"
                    outlined
                    severity="secondary"
                    size="small"
                    @click="clearAvatarSelection"
                />
            </div>

            <!-- Action buttons -->
            <div class="flex justify-end gap-2 pt-4">
                <Button
                    label="Cancel"
                    outlined
                    @click="cancelAvatarEdit"
                />
                <Button
                    :disabled="avatarLoading || !selectedAvatar"
                    :loading="avatarLoading"
                    icon="pi pi-save"
                    label="Save Avatar"
                    @click="handleAvatarSave"
                />
            </div>
        </div>
    </Dialog>

    <!-- Delete Avatar Confirmation Modal -->
    <Dialog
        v-model:visible="showDeleteAvatarModal"
        :closable="false"
        :draggable="false"
        class="w-full max-w-md mx-3"
        header="Delete Avatar"
        modal
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

            <div class="flex justify-end gap-2 pt-4">
                <Button
                    label="Cancel"
                    outlined
                    @click="showDeleteAvatarModal = false"
                />
                <Button
                    :loading="deleteAvatarLoading"
                    icon="pi pi-trash"
                    label="Delete Avatar"
                    severity="danger"
                    @click="handleAvatarDelete"
                />
            </div>
        </div>
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
import Panel from 'primevue/panel'
import Button from 'primevue/button'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'
import Password from 'primevue/password'
import Dialog from 'primevue/dialog'
import Message from 'primevue/message'
import FileUpload from 'primevue/fileupload'
import {Form} from '@primevue/forms'
import {z} from 'zod'
import {zodResolver} from '@primevue/forms/resolvers/zod'
import Avatar from "primevue/avatar"

// Stores and composables
const authStore = useAuthStore()
const toast = useToast()

// Modal visibility
const showNameModal = ref(false)
const showPasswordModal = ref(false)
const showAvatarModal = ref(false)
const showDeleteAvatarModal = ref(false)
const showDeleteModal = ref(false)

// Avatar handling
const selectedAvatar = ref<File | null>(null)
const avatarPreviewUrl = ref<string | null>(null)
const fileUploadRef = ref()

// Loading states
const nameLoading = ref(false)
const passwordLoading = ref(false)
const avatarLoading = ref(false)
const deleteAvatarLoading = ref(false)
const deleteLoading = ref(false)

// Zod schemas
const nameSchema = z.object({
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
const nameResolver = zodResolver(nameSchema)
const passwordResolver = zodResolver(passwordSchema)
const deleteResolver = zodResolver(deleteSchema)

// Clear auth errors when modals are closed
watch(showPasswordModal, (isOpen) => {
    if (!isOpen) {
        authStore.errorMessage = ''
    }
})

// Avatar Functions
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

const cancelAvatarEdit = () => {
    clearAvatarSelection()
    showAvatarModal.value = false
}

const handleAvatarSave = async () => {
    if (!selectedAvatar.value) return

    avatarLoading.value = true

    try {
        const success = await authStore.updateAvatar(selectedAvatar.value)

        if (success) {
            clearAvatarSelection()
            showAvatarModal.value = false
        }
    } catch (error) {
        console.error('Avatar update failed:', error)
    }

    avatarLoading.value = false
}

const handleAvatarDelete = async () => {
    deleteAvatarLoading.value = true

    try {
        const success = await authStore.deleteAvatar()

        if (success) {
            showDeleteAvatarModal.value = false
        }
    } catch (error) {
        console.error('Avatar deletion failed:', error)
    }

    deleteAvatarLoading.value = false
}

// Other Functions
const handleNameUpdate = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return
    nameLoading.value = true

    try {
        const values = Object.keys(event.states).reduce((acc, key) => {
            acc[key] = event.states[key].value
            return acc
        }, {} as Record<string, any>)

        const success = await authStore.updateName(values.name.trim())

        if (success) {
            showNameModal.value = false
        }
    } catch (error) {
        console.error('Name update failed:', error)
    }

    nameLoading.value = false
}

const handlePasswordChange = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return
    passwordLoading.value = true

    try {
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
        const values = Object.keys(event.states).reduce((acc, key) => {
            acc[key] = event.states[key].value
            return acc
        }, {} as Record<string, any>)

        const success = await authStore.deleteAccount({
            confirmation: values.confirmation
        })

        if (success) {
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
