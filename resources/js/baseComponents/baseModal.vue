<template>
    <Dialog
        :class="['base-modal', modalClass]"
        :closable="false"
        :close-on-escape="false"
        :draggable="false"
        :header="title"
        :visible="visible"
        dismissable-mask
        modal
        responsive
        @update:visible="emit('update:visible', $event)"
    >
        <!-- Form wrapper - always present but resolver is optional -->
        <Form
            v-slot="$form"
            :initial-values="initialValues"
            :resolver="resolver"
            validate-on-value-update
            @submit="handleFormSubmit"
        >
            <div class="space-y-4">
                <!-- Main content slot -->
                <div class="modal-content">
                    <slot :form="$form" :loading="loading"/>
                </div>

                <!-- Button container -->
                <div class="button-container">
                    <Button
                        label="Cancel"
                        outlined
                        severity="secondary"
                        @click="handleCancel"
                    />
                    <Button
                        :disabled="loading || (resolver && !$form.valid)"
                        :icon="submitIcon"
                        :label="submitLabel"
                        :loading="loading"
                        :severity="submitSeverity"
                        :type="resolver ? 'submit' : 'button'"
                        @click="resolver ? undefined : handleNonFormSubmit()"
                    />
                </div>
            </div>
        </Form>
    </Dialog>
</template>

<script lang="ts" setup>
import {computed, ref, watch} from 'vue'
import Dialog from 'primevue/dialog'
import Button from 'primevue/button'
import {Form} from '@primevue/forms'
import {zodResolver} from '@primevue/forms/resolvers/zod'

interface Props {
    visible: boolean
    title: string
    schema?: any
    loading?: boolean
    onSubmit?: (formData?: any) => Promise<boolean>
    submitLabel?: string
    submitIcon?: string
    submitSeverity?: 'primary' | 'secondary' | 'success' | 'info' | 'warn' | 'help' | 'danger' | 'contrast'
    disabled?: boolean
    modalClass?: string
    initialValues?: Record<string, any>
    onVisibilityChange?: (isOpen: boolean) => void
}

interface Emits {
    'update:visible': [value: boolean]
    'cancel': []
}

const props = withDefaults(defineProps<Props>(), {
    submitLabel: 'Save',
    submitIcon: 'pi pi-save',
    submitSeverity: 'primary',
    disabled: false,
    modalClass: '',
    initialValues: () => ({})
})

const emit = defineEmits<Emits>()

const loading = ref(false)

// Computed properties
const isVisible = computed({
    get: () => props.visible,
    set: (value: boolean) => emit('update:visible', value)
})
const resolver = computed(() => {
    return props.schema ? zodResolver(props.schema) : undefined
})

// Watch for visibility changes to trigger custom cleanup
watch(isVisible, (isOpen) => {
    if (props.onVisibilityChange) {
        props.onVisibilityChange(isOpen)
    }
})

// Event handlers
const handleFormSubmit = async (event: { valid: boolean; states: Record<string, any> }) => {
    if (!event.valid || !props.onSubmit) return

    const formData = Object.keys(event.states).reduce((acc, key) => {
        acc[key] = event.states[key].value
        return acc
    }, {} as Record<string, any>)

    loading.value = true
    try {
        const success = await props.onSubmit(formData)
        if (success) {
            emit('update:visible', false)
        }
    } finally {
        loading.value = false
    }
}

const handleNonFormSubmit = async () => {
    if (!props.onSubmit) return

    loading.value = true
    try {
        const success = await props.onSubmit()
        if (success) {
            emit('update:visible', false)
        }
    } finally {
        loading.value = false
    }
}

const handleCancel = () => {
    emit('cancel')
    emit('update:visible', false)
}
</script>
