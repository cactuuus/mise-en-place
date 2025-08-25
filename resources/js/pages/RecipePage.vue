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

    <div v-else-if="recipe" class="max-w-4xl mx-auto space-y-6  text-sm">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-lg">
            <SmartImage
                v-if="recipe.image_urls?.large"
                :alt="recipe.title"
                :max-retries="5"
                :retry-delay="2000"
                :src="recipe.image_urls.large"
                image-class="w-full h-48 md:h-64 object-cover rounded-lg"
            />
            <PlaceholderRecipeImage v-else class="w-full h-48 md:h-64 object-cover rounded-lg"/>

            <div
                class="absolute bottom-0 left-0 flex justify-between p-1 w-full backdrop-blur-sm dark:bg-black/20"
                style="mask: linear-gradient(to top, black 95%, transparent 100%);">
                <DifficultyBadge
                    v-if="recipe.difficulty_level"
                    :difficulty="recipe.difficulty_level"
                />
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <Button icon="pi pi-heart" size="small"/>
                    <Button icon="pi pi-share-alt" size="small"/>
                </div>
            </div>
        </div>

        <!-- Header Info -->
        <div class="space-y-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ recipe.title }}</h1>
                <span class="text-sm">by {{ recipe.user.name }}</span>
            </div>

            <!-- Rating -->
            <div v-if="recipe.average_rating || recipe.total_ratings" class="flex items-center gap-3">
                <Rating
                    :cancel="false"
                    :model-value="recipe.average_rating"
                    readonly
                />
                <span class="text-xs">
                    ({{ recipe.total_ratings }} reviews)
                </span>
            </div>

            <!-- Recipe Meta -->
            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs">
                <div v-if="recipe.serves" class="flex items-center gap-1">
                    <i class="pi pi-users"></i>
                    <span>Serves {{ recipe.serves }}</span>
                </div>
                <div v-if="recipe.total_time" class="flex items-center gap-1">
                    <i class="pi pi-clock"></i>
                    <span>{{ getTimeBreakdown(recipe) }}</span>
                </div>
            </div>

            <!-- Tags -->
            <div v-if="recipe.tags && recipe.tags.length > 0" class="flex flex-wrap gap-2">
                <Tag
                    v-for="tag in recipe.tags"
                    :key="tag.id"
                    :value="getTagLabel(tag.name)"
                    class="!text-xs"
                    severity="secondary"
                />
            </div>
        </div>

        <!-- Main Content -->

        <!-- Ingredients -->
        <Card>
            <template #title>
                Ingredients
            </template>
            <template #content>
                <ul v-if="recipe.ingredients && recipe.ingredients.length > 0" class="space-y-2">
                    <li
                        v-for="(ingredient, index) in recipe.ingredients"
                        :key="index"
                        class="flex items-center gap-2"
                    >
                        <Checkbox
                            v-model="checkedIngredients[index]"
                            :input-id="index.toString()"
                            binary
                            size="small"
                        />
                        <label
                            :class="{ 'line-through text-surface-500': checkedIngredients[index] }"
                            :for="index"
                            class="cursor-pointer flex-1"
                        >
                            {{ ingredient.ingredient }}
                        </label>
                    </li>
                </ul>
                <p v-else class="text-surface-500 italic">No ingredients listed</p>
            </template>
        </Card>

        <!-- Instructions -->
        <Card>
            <template #title>
                Instructions
            </template>
            <template #content>
                <ol v-if="recipe.instructions && recipe.instructions.length > 0" class="divide-y space-y-2">
                    <li
                        v-for="(instruction, index) in recipe.instructions"
                        :key="index"
                    >
                        <h4 class="font-semibold my-1">
                            Step {{ index + 1 }}
                        </h4>
                        <p>{{ instruction.instruction }}</p>
                    </li>
                </ol>
                <p v-else class="text-surface-500 italic">No instructions provided</p>
            </template>
        </Card>

        <!-- Notes Section -->
        <Card v-if="recipe.notes">
            <template #title>
                Notes
            </template>
            <template #content>
                <p>{{ recipe.notes }}</p>
            </template>
        </Card>

        <!-- Source -->
        <div v-if="recipe.source_url || recipe.parent_recipe"
             class="text-center p-4 bg-surface-50 dark:bg-surface-800 rounded-lg">
            <p class="text-surface-600 mb-2">
                {{ sourceText }}
            </p>
            <Button
                icon="pi pi-external-link"
                label="View original recipe"
                link
                @click="viewOriginal"
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
import {computed, onMounted, ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import Card from 'primevue/card'
import Button from 'primevue/button'
import Rating from 'primevue/rating'
import Tag from 'primevue/tag'
import Checkbox from 'primevue/checkbox'
import Skeleton from 'primevue/skeleton'
import {getTagLabel, getTimeBreakdown, Recipe} from '@/types/recipe'
import {fetchRecipeById} from '@/services/recipeService'
import SmartImage from '@/components/SmartImage.vue'
import DifficultyBadge from '@/components/DifficultyBadge.vue'
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue";

const route = useRoute()
const router = useRouter()

const recipe = ref<Recipe | null>(null)
const loading = ref(true)
const checkedIngredients = ref<boolean[]>([])

watch(() => recipe.value?.ingredients, (ingredients) => {
    if (ingredients) {
        checkedIngredients.value = new Array(ingredients.length).fill(false)
    }
    console.log(recipe.value)
}, {immediate: true})

const loadRecipe = async () => {
    const recipeId = parseInt(route.params.id as string)
    if (isNaN(recipeId)) {
        router.push('/recipes')
        return
    }

    recipe.value = await fetchRecipeById(recipeId)
    loading.value = false
}

const sourceText = computed(() => {
    if (recipe.value?.parent_recipe) {
        return `Forked from ${recipe.value?.parent_recipe.user.name}'s recipe`
    } else if (recipe.value?.source_url) {
        return "Imported from external source"
    }
})

const viewOriginal = () => {
    if (recipe.value?.forked_from_recipe_id) {
        // Navigate to internal recipe page
        const url = router.resolve(`/recipes/${recipe.value.forked_from_recipe_id}`).href
        window.open(url, '_blank', 'noopener,noreferrer')
    } else if (recipe.value?.source_url) {
        // Open external URL
        window.open(recipe.value?.source_url, '_blank', 'noopener,noreferrer')
    }
}

onMounted(() => {
    loadRecipe()
})
</script>
