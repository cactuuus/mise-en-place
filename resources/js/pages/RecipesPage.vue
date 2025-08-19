<template>
    <div class="max-w-6xl mx-auto">
        <!-- Loading State -->
        <div v-if="loading && recipes.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <Card v-for="i in 6" :key="i" class="animate-pulse">
                <template #content>
                    <div class="space-y-4">
                        <div class="bg-surface-200 h-48 rounded"></div>
                        <div class="bg-surface-200 h-4 rounded w-3/4"></div>
                        <div class="bg-surface-200 h-3 rounded w-1/2"></div>
                    </div>
                </template>
            </Card>
        </div>

        <!-- Recipes Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <RecipeCard
                v-for="recipe in recipes"
                :key="recipe.id"
                :recipe="recipe"
                @click="viewRecipe(recipe)"
            />
        </div>

        <!-- Empty State -->
        <div v-if="!loading && recipes.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">🍽️</div>
            <h3 class="text-xl font-semibold mb-2">No recipes found</h3>
            <p class="text-surface-600">Be the first to share a delicious recipe!</p>
        </div>

        <!-- Load More Button (temporary - will become infinite scroll) -->
        <div v-if="hasMore && !loading" class="text-center mt-8">
            <Button
                :loading="loadingMore"
                label="Load More"
                @click="loadMore"
            />
        </div>

        <!-- Loading More Indicator -->
        <div v-if="loadingMore" class="text-center mt-8">
            <ProgressSpinner style="width: 50px; height: 50px"/>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import ProgressSpinner from 'primevue/progressspinner'
import RecipeCard from '@/components/RecipeCard.vue'
import {Recipe} from '@/types/recipe.ts'
import api from '@/services/api'

interface ApiResponse {
    data: Recipe[]
    current_page: number
    last_page: number
    per_page: number
    total: number
}

// Router
const router = useRouter()

// Reactive data
const recipes = ref<Recipe[]>([])
const loading = ref(true)
const loadingMore = ref(false)
const currentPage = ref(1)
const hasMore = ref(true)

// Functions
const fetchRecipes = async (page: number = 1): Promise<void> => {
    try {
        const response = await api.get<ApiResponse>(`/recipes?page=${page}`)
        const data = response.data

        if (page === 1) {
            recipes.value = data.data
        } else {
            recipes.value.push(...data.data)
        }

        hasMore.value = data.current_page < data.last_page
        currentPage.value = data.current_page

    } catch (error) {
        console.error('Failed to fetch recipes:', error)
        // TODO: Show error toast
    }
}

const loadMore = async (): Promise<void> => {
    if (loadingMore.value || !hasMore.value) return

    loadingMore.value = true
    await fetchRecipes(currentPage.value + 1)
    loadingMore.value = false
}

const viewRecipe = (recipe: Recipe): void => {
    // TODO: Navigate to recipe detail page
    console.log('View recipe:', recipe.title)
}

// Lifecycle
onMounted(async () => {
    await fetchRecipes()
    loading.value = false
})
</script>
