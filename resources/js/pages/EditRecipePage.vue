<template>
    <div class="space-y-4 p-2 sm:p-4">
        <div v-if="loading" class="text-center">
            <p>Loading recipe...</p>
        </div>

        <template v-else-if="recipe">
            <RecipeForm
                :initial-data="recipe"
                mode="edit"
                @abort="router.back()"
                @submit="handleUpdate"
            />
        </template>

        <div v-else class="text-center">
            <p class="text-red-600">Error loading recipe. Please try again later.</p>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import RecipeForm from '@/components/RecipeForm.vue'
import {fetchRecipeById, updateRecipe} from '@/services/recipeService'
import type {Recipe} from '@/types/recipe'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const recipe = ref<Recipe | null>(null)

const loadRecipe = async () => {
    const id = parseInt(route.params.id as string)
    recipe.value = await fetchRecipeById(id)
    loading.value = false
}

const handleUpdate = async (formData: FormData) => {
    if (!recipe.value) return

    const success = await updateRecipe(recipe.value.id, formData)
    if (success) {
        router.push(`/cookbook/${recipe.value.id}`)
    }
}

onMounted(loadRecipe)
</script>
