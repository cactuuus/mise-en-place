<template>
    <div class="space-y-4 p-4">
        <div class="flex items-center">
            <Button
                icon="pi pi-download"
                label="Import from URL"
                outlined
                @click="showImportModal = true"
            />
        </div>

        <RecipeForm
            :key="recipeFormKey"
            :initial-data="importedData"
            mode="create"
            @abort="router.back()"
            @submit="handleCreate"
        />
    </div>

    <!-- Import Modal -->
    <ImportRecipeFromUrlModal
        v-model:visible="showImportModal"
        @update:imported="updateFormData"
    />
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import Button from 'primevue/button'
import RecipeForm from '@/components/RecipeForm.vue'
import {createRecipe} from '@/services/recipeService'
import ImportRecipeFromUrlModal from "@/components/ImportRecipeFromUrlModal.vue";

const recipeFormKey = ref(0)
const router = useRouter()
const showImportModal = ref(false)
const importedData = ref()

const handleCreate = async (formData: FormData) => {
    const recipe = await createRecipe(formData)
    if (recipe) {
        await router.push(`/cookbook/${recipe.id}`)
    }
}

const updateFormData = async (formData: FormData) => {
    importedData.value = formData
    // Increment the key to force re-mount
    recipeFormKey.value += 1
    showImportModal.value = false
}
</script>
