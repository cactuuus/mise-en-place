<template>
    <BaseModal
        :on-submit="onSubmit"
        :schema="importSchema"
        :visible="visible"
        modal-class="!max-w-md"
        submit-icon="pi pi-check"
        submit-label="Import"
        submit-severity="primary"
        title="Import Recipe from URL"
        @update:visible="emit('update:visible', $event)"
    >
        <template #default="{ form, loading }">
            <div class="flex flex-col gap-1 mt-1">
                <FloatLabel variant="on">
                    <InputText
                        id="importUrl"
                        :disabled="loading"
                        fluid
                        name="importUrl"
                        type="url"
                    />
                    <label for="import-url">Recipe URL</label>
                </FloatLabel>
                <Message v-if="form.importUrl?.invalid" severity="error" size="small" variant="simple">
                    {{ form.importUrl.error.message }}
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
import {importRecipe} from "@/services/recipeService.ts";

interface Props {
    visible: boolean
}

interface Emits {
    'update:visible': [value: boolean]
    'update:imported': [data: any]
}

defineProps<Props>()
const emit = defineEmits<Emits>()

const importSchema = z.object({
    importUrl: z.url()
        .min(1, 'URL is required')
        .max(255, 'URL must be less than 255 characters')
})

const onSubmit = async (formData: { importUrl: string }) => {
    const importedRecipeData = await importRecipe(formData.importUrl.trim())
    if (importedRecipeData) {
        emit('update:imported', importedRecipeData)
        return true
    }
    return false
}
</script>
