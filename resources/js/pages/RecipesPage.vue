<template>
    <!-- Loading State -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6 place-items-center items-stretch">
        <Card
            v-for="i in 10"
            v-if="loading && recipes.length === 0"
            :key="i"
            class="recipe-card">
            <template #header>
                <Skeleton border-radius="0" class="image-preview" size="100%"></Skeleton>
            </template>
            <template #content>
                <div class="space-y-2">
                    <Skeleton height="1.5rem" width="90%"></Skeleton>
                    <Skeleton width="40%"></Skeleton>
                    <Skeleton height="2rem"></Skeleton>
                </div>
            </template>
            <template #footer>
                <div class="flex flex-row gap-4">
                    <Skeleton v-for="i in 3" :key="i" height="1.2rem" width="25%"></Skeleton>
                </div>
            </template>
        </Card>

        <!-- Recipes Grid -->
        <RecipeCard
            v-for="recipe in recipes"
            v-else
            :key="recipe.id"
            :recipe="recipe"
            @click="viewRecipe(recipe)"
        />
    </div>

    <!-- Empty State -->
    <div v-if="!loading && recipes.length === 0" class="text-center py-12">
        <h3 class="text-xl font-semibold mb-2">No recipes found</h3>
        <p class="text-surface-600">Be the first to share a delicious recipe!</p>
    </div>

    <!-- Load More Button (temporary - will become infinite scroll) -->
    <div v-if="pagination?.hasMore && !loading" class="text-center mt-8">
        <Button
            :loading="loadingMore"
            label="Load More"
            @click="loadMore"
        />
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Skeleton from 'primevue/skeleton'
import RecipeCard from '@/components/RecipeCard.vue'
import {Recipe} from '@/types/recipe'
import {fetchRecipes} from '@/services/recipeService'

const router = useRouter()
const recipes = ref<Recipe[]>([])
const loading = ref(true)
const loadingMore = ref(false)
const pagination = ref<{
    currentPage: number
    lastPage: number
    perPage: number
    total: number
    hasMore: boolean
} | null>(null)

// Functions
const loadRecipes = async (page: number = 1): Promise<void> => {
    const result = await fetchRecipes(page)
    if (!result) return

    if (page === 1) {
        recipes.value = result.recipes
    } else {
        recipes.value.push(...result.recipes)
    }

    pagination.value = result.pagination
}

const loadMore = async (): Promise<void> => {
    if (loadingMore.value || !pagination.value?.hasMore) return

    loadingMore.value = true
    await loadRecipes(pagination.value.currentPage + 1)
    loadingMore.value = false
}

const viewRecipe = (recipe: Recipe): void => {
    router.push(`/recipes/${recipe.id}`)
}

// Lifecycle
onMounted(async () => {
    await loadRecipes()
    loading.value = false
})
</script>
