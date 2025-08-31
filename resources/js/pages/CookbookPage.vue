<template>
    <div>
        <!-- DataView -->
        <DataView
            :loading="loading"
            :value="recipes"
            class="!rounded-lg !overflow-hidden"
            data-key="id"
        >
            <!-- Header -->
            <template #header>
                <div class="flex justify-between items-center">
                    <h2 class="text-xl font-bold">My Cookbook</h2>
                    <Button
                        :disabled="loading"
                        icon="pi pi-plus"
                        label="Add Recipe"
                        severity="success"
                        size="small"
                        @click="addRecipe"
                    />
                </div>
            </template>

            <template #empty>
                <div
                    v-for="i in 10"
                    v-if="loading"
                    :key="i"
                    class="flex items-center gap-3 p-3 border-b secondary-border"
                >
                    <Skeleton
                        border-radius="0.5rem"
                        size="5rem"
                    ></Skeleton>
                    <div class="space-y-1 grow">
                        <Skeleton height="1.2rem" width="15rem"></Skeleton>
                        <div class="flex gap-2 items-center">
                            <Skeleton height="1.5rem" width="2rem"></Skeleton>
                            <Skeleton height="1.5rem" width="6rem"></Skeleton>
                        </div>
                        <div class="flex gap-1 items-center mt-1">
                            <Skeleton v-for="i in 3" :key="i" width="4rem"></Skeleton>
                        </div>
                    </div>
                    <Skeleton size="2rem"></Skeleton>
                </div>
                <div v-else class="p-3 text-center secondary-text">
                    You have no recipes in your cookbook. <br/> Click "Add Recipe" to create one!
                </div>
            </template>

            <!-- List Item Template -->
            <template #list="slotProps">
                <div v-for="(recipe, index) in slotProps.items" :key="index">
                    <div class="flex items-center gap-3 p-3 border-b secondary-border">
                        <!-- Image Preview -->
                        <DeferredContent>
                            <div class="h-20 w-20 relative overflow-hidden aspect-square rounded-lg">
                                <SmartImage
                                    v-if="recipe.image_urls?.small"
                                    :alt="recipe.title"
                                    :src="recipe.image_urls?.small"
                                    image-class="h-20 w-20 object-cover"
                                />
                                <PlaceholderRecipeImage v-else class="h-20 w-20 object-cover"/>
                            </div>
                        </DeferredContent>

                        <!-- Recipe Info -->
                        <div class="grow">
                            <div class="flex gap-1 items-center font-semibold">
                                <i v-if="!recipe.is_public" class="pi pi-lock" title="Private recipe"/>
                                <h3 class="text-lg ">{{ recipe.title }}</h3>
                            </div>

                            <div class="flex items-stretch gap-2">
                                <DifficultyBadge
                                    v-if="recipe.difficulty_level"
                                    :difficulty="recipe.difficulty_level"
                                />
                                <Tag
                                    v-if="recipe.total_ratings"
                                    :value="`${Math.floor(recipe.average_rating * 10) / 10} (${recipe.total_ratings} ratings)`"
                                    icon="pi pi-star-fill"
                                    severity="secondary"
                                />
                                <Tag v-else
                                     icon="pi pi-star"
                                     severity="secondary"
                                     value="not yet rated"
                                />
                            </div>

                            <!-- Recipe Meta -->
                            <div class="flex items-center justify-start gap-4 secondary-text text-sm mt-1">
                                <div class="flex items-center gap-1">
                                    <i class="pi pi-clock"></i>
                                    <span>{{ recipe.total_time || '-' }}m</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i class="pi pi-users"></i>
                                    <span>{{ recipe.serves || '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div>
                            <Button
                                icon="pi pi-ellipsis-v"
                                severity="secondary"
                                text
                            />
                        </div>
                    </div>
                </div>
            </template>
        </DataView>
    </div>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import DataView from 'primevue/dataview'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import DeferredContent from "primevue/deferredcontent"
import Skeleton from "primevue/skeleton"
import {RecipePreview} from '@/types/recipe'
import DifficultyBadge from "@/components/DifficultyBadge.vue"
import SmartImage from "@/components/SmartImage.vue"
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue"
import {fetchMyRecipes} from "@/services/recipeService.ts";

const router = useRouter()

// Reactive data
const recipes = ref<RecipePreview[]>([])
const loading = ref(true)

// Methods
const loadRecipes = async () => {
    const result = await fetchMyRecipes()
    await new Promise(resolve => setTimeout(resolve, 1000)); // 2 second delay
    if (result) {
        recipes.value = result.recipes
    }
}

const addRecipe = () => {
    router.push('/recipes/create')
}

const editRecipe = (recipe: RecipePreview) => {
    router.push(`/recipes/${recipe.id}/edit`)
}

const viewRecipe = (recipe: RecipePreview) => {
    router.push(`/discover/${recipe.id}`)
}

// Lifecycle
onMounted(async () => {
    await loadRecipes()
    loading.value = false
})
</script>
