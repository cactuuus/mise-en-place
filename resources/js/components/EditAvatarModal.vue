<template>
    <Dialog
        id="avatar-modal"
        v-model:visible="isVisible"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        class="base-modal"
        dismissable-mask
        header="Edit Avatar"
        modal
        responsive
    >
        <div class="grid grid-cols-[1.5fr_2fr] place-items-center gap-4">
            <!-- Avatar Preview -->
            <Image
                :src="avatarPreviewUrl || currentAvatarUrl || '/images/avatar-placeholder.svg'"
                image-class="w-full min-w-[90px] max-w-[200px] mx-auto rounded-full aspect-square object-cover border-4 border-dashed outline-offset-2 border-surface-300 dark:border-surface-500"
            />
            <!-- File Upload -->
            <FileUpload
                ref="fileUploadRef"
                :file-limit="1"
                :max-file-size="2097152"
                :show-cancel-button="false"
                :show-upload-button="false"
                accept="image/*"
                choose-icon="pi pi-upload"
                choose-label="Upload New"
                mode="basic"
                @remove="handleAvatarRemove"
                @select="handleAvatarSelect"
            >
            </FileUpload>
        </div>

        <Message v-if="authStore.errorMessage" severity="error">
            {{ authStore.errorMessage }}
        </Message>

        <!-- Action buttons -->
        <div class="button-container">
            <Button
                label="Cancel"
                outlined
                severity="secondary"
                @click="handleCancel"
            />
            <Button
                :disabled="loading || !selectedAvatar"
                :loading="loading"
                icon="pi pi-save"
                label="Save"
                @click="handleSave"
            />
        </div>
    </Dialog>
</template>

<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import FileUpload from 'primevue/fileupload'
import Image from 'primevue/image'
import Message from "primevue/message";

interface Props {
    visible: boolean
    currentAvatarUrl?: string | null
}

interface Emits {
    'update:visible': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const loading = ref(false)
const selectedAvatar = ref<File | null>(null)
const avatarPreviewUrl = ref<string | null>(null)
const fileUploadRef = ref()

// Computed properties
const isVisible = computed({
    get: () => props.visible,
    set: (value: boolean) => emit('update:visible', value)
})

// Clear state when modal opens/closes
watch(isVisible, (isOpen) => {
    if (!isOpen) {
        clearAvatarSelection()
        authStore.errorMessage = ''
    }
})

// Avatar handling functions
const handleAvatarSelect = (event: { files: File[] }) => {
    const file = event.files[0]
    if (!file) return

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

const handleSave = async () => {
    if (!selectedAvatar.value) return

    loading.value = true

    try {
        const success = await authStore.updateAvatar(selectedAvatar.value)

        if (success) {
            emit('update:visible', false)
        }
    } catch (error) {
        console.error('Avatar update failed:', error)
    }

    loading.value = false
}

const handleCancel = () => {
    emit('update:visible', false)
}
</script>
