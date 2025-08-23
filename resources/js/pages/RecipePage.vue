<template>
    <div v-if="loading" class="max-w-4xl mx-auto">
        <Skeleton class="mb-6" height="300px"/>
        <Skeleton class="mb-4" height="2rem" width="60%"/>
        <Skeleton class="mb-8" height="1rem" width="40%"/>
        <div class="grid md:grid-cols-2 gap-8">
            <div class="space-y-4">
                <Skeleton height="1.5rem" width="30%"/>
                <Skeleton height="200px"/>
            </div>
            <div class="space-y-4">
                <Skeleton height="1.5rem" width="30%"/>
                <Skeleton height="300px"/>
            </div>
        </div>
    </div>

    <div v-else-if="recipe" class="max-w-4xl mx-auto space-y-8">
        <!-- Hero Section -->
        <div class="relative">
            <SmartImage
                v-if="recipe.image_urls?.large"
                :alt="recipe.title"
                :max-retries="5"
                :retry-delay="2000"
                :src="recipe.image_urls.large"
                image-class="w-full h-48 md:h-64 object-cover rounded-lg"
            />
            <PlaceholderRecipeImage v-else class="w-full h-48 md:h-64 object-cover rounded-lg"/>
        </div>

        <!-- Header Info -->
        <div class="space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h1 class="text-3xl md:text-4xl font-bold text-surface-900 dark:text-surface-0">
                        {{ recipe.title }}
                    </h1>
                    <p class="text-surface-600 mt-2">by {{ recipe.user.name }}</p>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <Button icon="pi pi-heart" outlined/>
                    <Button icon="pi pi-share-alt" outlined/>
                </div>
            </div>

            <!-- Rating -->
            <div v-if="recipe.average_rating || recipe.total_ratings" class="flex items-center gap-3">
                <Rating
                    :cancel="false"
                    :model-value="recipe.average_rating || 0"
                    readonly
                />
                <span class="text-surface-600">
                    {{ recipe.average_rating?.toFixed(1) || '0.0' }} ({{ recipe.total_ratings }} reviews)
                </span>
            </div>

            <!-- Recipe Meta -->
            <div class="flex flex-wrap gap-6 text-sm text-surface-600">
                <div v-if="recipe.prep_time" class="flex items-center gap-1">
                    <i class="pi pi-clock"></i>
                    <span>Prep: {{ recipe.prep_time }}m</span>
                </div>
                <div v-if="recipe.cook_time" class="flex items-center gap-1">
                    <i class="pi pi-fire"></i>
                    <span>Cook: {{ recipe.cook_time }}m</span>
                </div>
                <div v-if="recipe.total_time" class="flex items-center gap-1">
                    <i class="pi pi-hourglass"></i>
                    <span>Total: {{ recipe.total_time }}m</span>
                </div>
                <div v-if="recipe.serves" class="flex items-center gap-1">
                    <i class="pi pi-users"></i>
                    <span>Serves {{ recipe.serves }}</span>
                </div>
                <DifficultyBadge
                    v-if="recipe.difficulty_level"
                    :difficulty="recipe.difficulty_level"
                />
            </div>

            <!-- Tags -->
            <div v-if="recipe.tags && recipe.tags.length > 0" class="flex flex-wrap gap-2">
                <Chip
                    v-for="tag in recipe.tags"
                    :key="getTagKey(tag)"
                    :label="getTagLabel(tag.name)"
                    class="tag"
                />
            </div>
        </div>

        <!-- Main Content -->
        <div class="grid md:grid-cols-2 gap-8">
            <!-- Ingredients -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-list"></i>
                        Ingredients
                    </div>
                </template>
                <template #content>
                    <ul v-if="recipe.ingredients && recipe.ingredients.length > 0" class="space-y-2">
                        <li
                            v-for="(ingredient, index) in recipe.ingredients"
                            :key="index"
                            class="flex items-start gap-2 p-2 rounded hover:bg-surface-50 dark:hover:bg-surface-800"
                        >
                            <Checkbox
                                :input-id="`ingredient-${index}`"
                                class="mt-1"
                                @change="toggleIngredient(index)"
                            />
                            <label
                                :class="{ 'line-through text-surface-500': checkedIngredients.has(index) }"
                                :for="`ingredient-${index}`"
                                class="cursor-pointer flex-1"
                            >
                                {{ ingredient }}
                            </label>
                        </li>
                    </ul>
                    <p v-else class="text-surface-500 italic">No ingredients listed</p>
                </template>
            </Card>

            <!-- Instructions -->
            <Card>
                <template #title>
                    <div class="flex items-center gap-2">
                        <i class="pi pi-book"></i>
                        Instructions
                    </div>
                </template>
                <template #content>
                    <ol v-if="recipe.instructions && recipe.instructions.length > 0" class="space-y-4">
                        <li
                            v-for="(instruction, index) in recipe.instructions"
                            :key="index"
                            class="flex gap-3 p-3 rounded hover:bg-surface-50 dark:hover:bg-surface-800"
                        >
                            <span
                                class="flex-shrink-0 w-6 h-6 bg-primary text-primary-contrast rounded-full text-sm font-semibold flex items-center justify-center">
                                {{ index + 1 }}
                            </span>
                            <div class="flex-1">
                                <p>{{ instruction }}</p>
                            </div>
                        </li>
                    </ol>
                    <p v-else class="text-surface-500 italic">No instructions provided</p>
                </template>
            </Card>
        </div>

        <!-- Notes Section -->
        <Card v-if="recipe.notes" class="mt-8">
            <template #title>
                <div class="flex items-center gap-2">
                    <i class="pi pi-info-circle"></i>
                    Notes
                </div>
            </template>
            <template #content>
                <p class="whitespace-pre-line text-surface-700 dark:text-surface-300">{{ recipe.notes }}</p>
            </template>
        </Card>

        <!-- Source -->
        <div v-if="recipe.source_url" class="text-center p-4 bg-surface-50 dark:bg-surface-800 rounded-lg">
            <p class="text-surface-600 mb-2">Recipe source:</p>
            <Button
                :label="recipe.source_url"
                link
                @click="openExternalLink(recipe.source_url)"
            />
        </div>
    </div>

    <!-- Error State -->
    <div v-else class="max-w-4xl mx-auto text-center py-12">
        <i class="pi pi-exclamation-triangle text-6xl text-surface-400 mb-4"></i>
        <h2 class="text-2xl font-semibold mb-2">Recipe not found</h2>
        <p class="text-surface-600 mb-4">The recipe you're looking for doesn't exist or has been removed.</p>
        <Button
            label="Back to Recipes"
            @click="$router.push('/recipes')"
        />
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Rating from 'primevue/rating'
import Chip from 'primevue/chip'
import Checkbox from 'primevue/checkbox'
import Skeleton from 'primevue/skeleton'
import {Recipe} from '@/types/recipe'
import {fetchRecipeById} from '@/services/recipeService'
import SmartImage from '@/components/SmartImage.vue'
import DifficultyBadge from '@/components/DifficultyBadge.vue'
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue";

const route = useRoute()
const router = useRouter()

const recipe = ref<Recipe | null>(null)
const loading = ref(true)
const checkedIngredients = ref<Set<number>>(new Set())

const loadRecipe = async () => {
    const recipeId = parseInt(route.params.id as string)
    if (isNaN(recipeId)) {
        router.push('/recipes')
        return
    }

    recipe.value = await fetchRecipeById(recipeId)
    loading.value = false
}

const toggleIngredient = (index: number) => {
    if (checkedIngredients.value.has(index)) {
        checkedIngredients.value.delete(index)
    } else {
        checkedIngredients.value.add(index)
    }
}

const getTagLabel = (tagName: string | { [key: string]: string }): string => {
    if (typeof tagName === 'string') {
        return tagName
    }
    return tagName.en || tagName.eng || Object.values(tagName)[0] || 'tag'
}

const getTagKey = (tag: { name: string | { [key: string]: string } }): string => {
    return getTagLabel(tag.name)
}

const openExternalLink = (url: string) => {
    window.open(url, '_blank')
}

onMounted(() => {
    loadRecipe()
})
</script>
