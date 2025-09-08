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
            :initial-data="importedData"
            mode="create"
            @abort="router.back()"
            @submit="handleCreate"
        />
    </div>
    <!-- Import Modal (placeholder for now) -->
    <Dialog v-model:visible="showImportModal" header="Import Recipe">
        <p>Import functionality coming soon...</p>
    </Dialog>
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import Button from 'primevue/button'
import Dialog from 'primevue/dialog'
import RecipeForm from '@/components/RecipeForm.vue'
import {createRecipe} from '@/services/recipeService'

const router = useRouter()
const showImportModal = ref(false)
const importedData = ref()

const handleCreate = async (formData: FormData) => {
    const recipe = await createRecipe(formData)
    if (recipe) {
        router.push(`/cookbook/${recipe.id}`)
    }
}
</script>
