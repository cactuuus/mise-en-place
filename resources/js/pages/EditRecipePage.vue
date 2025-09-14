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

        <!-- Error State -->
        <div v-else class="max-w-4xl mx-auto text-center py-20 space-y-6">
            <SmartImage
                :max-retries="3"
                :retry-delay="0"
                alt="Recipe Not Found"
                image-class="mx-auto w-48 h-48 aspect-square object-contain !bg-transparent"
                src="/images/not-found.svg"
            />
            <div>
                <h2 class="text-2xl font-semibold mb-2">Recipe not found!</h2>
                <p class="secondary-text">
                    The recipe you're looking for can't be loaded, the image might have been deleted,
                    on an error might have occurred.
                </p>
            </div>
            <Button
                icon="pi pi-arrow-left"
                label="Go Back"
                @click="$router.back()"
            />
        </div>
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import RecipeForm from '@/components/RecipeForm.vue'
import {fetchRecipeById, updateRecipe} from '@/services/recipeService'
import type {Recipe} from '@/types/recipe'
import SmartImage from "@/components/SmartImage.vue";
import Button from "primevue/button";

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
