<template>
    <BaseModal
        :initial-values="{ name: initialName || '', text: initialText || '' }"
        :on-submit="onSubmit"
        :schema="stepSchema"
        :title="mode === 'create' ? 'Create Step' : 'Edit Step'"
        :visible="visible"
        modal-class="!max-w-2xl"
        submit-icon="pi pi-check"
        submit-label="Save"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
            <div class="space-y-4 mt-1">
                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <InputText
                            id="name"
                            :disabled="loading"
                            fluid
                            name="name"
                            type="text"
                        />
                        <label for="name">Step Name (optional)</label>
                    </FloatLabel>
                    <Message v-if="form.name?.invalid" severity="error" size="small" variant="simple">
                        {{ form.name.error.message }}
                    </Message>
                </div>

                <div class="flex flex-col gap-1">
                    <FloatLabel variant="on">
                        <Textarea
                            id="text"
                            :disabled="loading"
                            auto-resize
                            fluid
                            name="text"
                            rows="3"
                        />
                        <label for="text">Step Instructions</label>
                    </FloatLabel>
                    <Message v-if="form.text?.invalid" severity="error" size="small" variant="simple">
                        {{ form.text.error.message }}
                    </Message>
                </div>
            </div>
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import BaseModal from '@/baseComponents/baseModal.vue'
import InputText from 'primevue/inputtext'
import Textarea from 'primevue/textarea'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {z} from 'zod'

interface Props {
    visible: boolean
    mode: 'create' | 'edit'
    initialName?: string
    initialText?: string
}

interface Emits {
    'update:visible': [value: boolean]
    'submit': [data: { name?: string; text: string }]
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const stepSchema = z.object({
    name: z.string().max(50, 'Step name must be less than 50 characters').optional(),
    text: z.string()
        .min(1, 'Step instructions are required')
        .max(1000, 'Step instructions must be less than 1000 characters')
})

const onSubmit = async (formData: { name?: string; text: string }) => {
    emit('submit', {
        name: formData.name?.trim() || undefined,
        text: formData.text.trim()
    })
    return true
}
</script>
