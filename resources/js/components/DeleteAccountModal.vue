<template>
    <BaseModal
        :on-submit="onSubmit"
        :schema="deleteSchema"
        :visible="visible"
        modal-class="!max-w-md"
        submit-icon="pi pi-trash"
        submit-label="Confirm Deletion"
        submit-severity="danger"
        title="Delete Account"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
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
                            :disabled="loading"
                            fluid
                            name="confirmation"
                        />
                        <label for="confirmDelete">Type 'DELETE' to confirm</label>
                    </FloatLabel>
                    <Message v-if="form.confirmation?.invalid" severity="error" size="small" variant="simple">
                        {{ form.confirmation.error.message }}
                    </Message>
                </div>
            </div>
        </template>
    </BaseModal>
</template>

<script lang="ts" setup>
import {deleteAccount} from "@/services/userService.ts";
import BaseModal from '@/baseComponents/baseModal.vue'
import InputText from 'primevue/inputtext'
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

const deleteSchema = z.object({
    confirmation: z.string().refine(val => val === 'DELETE', {
        message: "You must type 'DELETE' to confirm"
    })
})

const onSubmit = async () => {
    return await deleteAccount()
}
</script>
