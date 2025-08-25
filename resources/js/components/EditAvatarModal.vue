<template>
    <BaseModal
        :on-submit="onSubmit"
        :on-visibility-change="onVisibilityChange"
        :visible="visible"
        submit-icon="pi pi-save"
        submit-label="Save"
        title="Edit Avatar"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ loading }">
            <div class="grid grid-cols-[1.5fr_2fr] place-items-center gap-4">
                <!-- Avatar Preview -->
                <Image
                    :src="avatarPreviewUrl || currentAvatarUrl || '/images/avatar-placeholder.svg'"
                    image-class="w-full min-w-[90px] max-w-[200px] mx-auto rounded-full aspect-square object-cover border-4 border-dashed outline-offset-2 border-surface-300 dark:border-surface-500"
                />
                <!-- File Upload -->
                <FileUpload
                    ref="fileUploadRef"
                    :disabled="loading"
                    :file-limit="1"
                    :max-file-size="MAX_FILESIZE"
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
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {updateAvatar} from "@/services/userService.ts";
import BaseModal from '@/baseComponents/baseModal.vue'
import FileUpload from 'primevue/fileupload'
import Image from 'primevue/image'

const MAX_FILESIZE = 5242880 // 5MB
interface Props {
    visible: boolean
    currentAvatarUrl?: string | null
}

interface Emits {
    'update:visible': [value: boolean]
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const selectedAvatar = ref<File | null>(null)
const avatarPreviewUrl = ref<string | null>(null)
const fileUploadRef = ref()


// Avatar handling functions
const handleAvatarSelect = (event: { files: File[] }) => {
    const file = event.files[0]
    if (!file) return

    selectedAvatar.value = file
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
    if (fileUploadRef.value) {
        fileUploadRef.value.clear()
    }
}

// Events
const onVisibilityChange = (isOpen: boolean) => {
    if (!isOpen) {
        clearAvatarSelection()
    }
}

const onSubmit = async () => {
    if (!selectedAvatar.value) return false
    return await updateAvatar(selectedAvatar.value)
}
</script>
