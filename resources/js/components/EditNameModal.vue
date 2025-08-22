<template>
    <BaseModal
        :initial-values="{ name: initialName }"
        :on-submit="onSubmit"
        :schema="nameSchema"
        :visible="visible"
        submit-icon="pi pi-save"
        submit-label="Save"
        title="Edit Name"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
            <div class="flex flex-col gap-1 mt-1">
                <FloatLabel variant="on">
                    <InputText
                        id="name"
                        :disabled="loading"
                        class="w-full"
                        name="name"
                        type="text"
                    />
                    <label for="name">Full Name</label>
                </FloatLabel>
                <Message v-if="form.name?.invalid" severity="error" size="small" variant="simple">
                    {{ form.name.error.message }}
                </Message>
            </div>
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import {updateName} from "@/services/userService.ts";
import BaseModal from '@/baseComponents/baseModal.vue'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {z} from 'zod'

interface Props {
    visible: boolean
    initialName?: string
}

interface Emits {
    'update:visible': [value: boolean]
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const nameSchema = z.object({
    name: z.string()
        .min(1, 'Name is required')
        .max(255, 'Name must be less than 255 characters')
})

const onSubmit = async (formData: { name: string }) => {
    return await updateName(formData.name.trim())
}
</script>
