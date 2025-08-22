<template>
    <Dialog
        v-model:visible="isVisible"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        class="base-modal"
        dismissable-mask
        header="Edit Name"
        modal
        responsive
    >
        <Form
            v-slot="$form"
            :initial-values="{ name: initialName }"
            :resolver="nameResolver"
            validate-on-value-update
            @submit="handleSubmit"
        >
            <div class="space-y-4">
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
                    <Message v-if="$form.name?.invalid" severity="error" size="small" variant="simple">
                        {{ $form.name.error.message }}
                    </Message>
                </div>

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
import InputText from 'primevue/inputtext'
import Button from 'primevue/button'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {Form} from '@primevue/forms'
import {z} from 'zod'
import {zodResolver} from '@primevue/forms/resolvers/zod'

interface Props {
    visible: boolean
    initialName?: string
}

interface Emits {
    'update:visible': [value: boolean]
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

const authStore = useAuthStore()
const loading = ref(false)

// Form validation schema
const nameSchema = z.object({
    name: z
        .string()
        .min(1, 'Name is required')
        .max(255, 'Name must be less than 255 characters')
})
const nameResolver = zodResolver(nameSchema)

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
        const success = await authStore.updateName(values.name.trim())

        if (success) {
            emit('update:visible', false)
        }
    } catch (error) {
        console.error('Name update failed:', error)
    }

    loading.value = false
}

const handleCancel = () => {
    emit('update:visible', false)
}
</script>
