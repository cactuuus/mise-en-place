<template>
    <BaseModal
        :initial-values="{ name: initialName || '' }"
        :on-submit="onSubmit"
        :schema="sectionSchema"
        :title="mode === 'create' ? 'Create Section' : 'Edit Section'"
        :visible="visible"
        submit-icon="pi pi-check"
        submit-label="Save"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
            <div class="flex flex-col gap-1 mt-1">
                <FloatLabel variant="on">
                    <InputText
                        id="name"
                        :disabled="loading"
                        fluid
                        name="name"
                        type="text"
                    />
                    <label for="name">Section Name</label>
                </FloatLabel>
                <Message v-if="form.name?.invalid" severity="error" size="small" variant="simple">
                    {{ form.name.error.message }}
                </Message>
            </div>
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import BaseModal from '@/baseComponents/baseModal.vue'
import InputText from 'primevue/inputtext'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {z} from 'zod'

interface Props {
    visible: boolean
    mode: 'create' | 'edit'
    initialName?: string
}

interface Emits {
    'update:visible': [value: boolean]
    'submit': [data: { name: string }]
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const sectionSchema = z.object({
    name: z.string()
        .min(1, 'Section name is required')
        .max(255, 'Section name must be less than 255 characters')
})

const onSubmit = async (formData: { name: string }) => {
    emit('submit', {name: formData.name.trim()})
    return true
}
</script>
