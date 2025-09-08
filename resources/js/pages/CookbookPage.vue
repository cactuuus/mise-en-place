<template>
    <!-- DataView -->
    <DataView
        :loading="loading"
        :value="recipes"
        data-key="id"
    >
        <!-- Header -->
        <template #header>
            <div class="flex justify-between items-center gap-4">
                <Button
                    disabled
                    icon="pi pi-filter"
                    label="Filter"
                    outlined
                    severity="secondary"
                    size="small"
                />

                <!-- todo: update to IconField once issue of icon appearing outside input is resolved (keep an eye out on releases) -->
                <InputText
                    v-model="searchValue"
                    class="max-w-xs grow"
                    disabled
                    icon="pi pi-search"
                    placeholder="Search recipes..."
                    size="small"
                />

                <Button
                    icon="pi pi-plus"
                    label="New"
                    severity="success"
                    size="small"
                    @click="router.push('/cookbook/create')"
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
                <div
                    class="flex items-center gap-3 p-3 border-b secondary-border cursor-pointer hover:bg-primary-500/5 transition"
                    @click="router.push(`/cookbook/${recipe.id}`)"
                >
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
                    <div class="grow truncate">
                        <div class="flex gap-1 items-center font-semibold">
                            <i v-if="!recipe.is_public" class="pi pi-lock" title="Private recipe"/>
                            <h3 class="text-lg ">{{ recipe.title }}</h3>
                        </div>

                        <div class="flex items-stretch gap-2">
                            <DifficultyBadge
                                v-if="recipe.difficulty_level"
                                :difficulty="recipe.difficulty_level"
                                class="!text-xs"
                            />
                            <Tag
                                v-if="recipe.total_ratings"
                                :value="`${Math.floor(recipe.average_rating * 10) / 10} (${recipe.total_ratings} ratings)`"
                                class="!text-xs"
                                icon="pi pi-star-fill"
                                severity="secondary"
                            />
                            <Tag v-else
                                 class="!text-xs"
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
                            icon="pi pi-bars"
                            severity="secondary"
                            text
                            @click.stop="(event) => recipeActionsMenuRef.toggle(event, recipe)"/>
                    </div>
                </div>
            </div>
        </template>
    </DataView>

    <RecipeActionsMenu ref="recipeActionsMenuRef"/>
</template>

<script lang="ts" setup>
import {onMounted, ref} from 'vue'
import {useRouter} from 'vue-router'
import DataView from 'primevue/dataview'
import Button from 'primevue/button'
import Tag from 'primevue/tag'
import DeferredContent from "primevue/deferredcontent"
import Skeleton from "primevue/skeleton"
import InputText from 'primevue/inputtext'
import {RecipePreview} from '@/types/recipe'
import DifficultyBadge from "@/components/DifficultyBadge.vue"
import SmartImage from "@/components/SmartImage.vue"
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue"
import {fetchMyRecipes} from "@/services/recipeService.ts";
import RecipeActionsMenu from "@/components/RecipeActionsMenu.vue";

const router = useRouter()

// Reactive data
const recipes = ref<RecipePreview[]>([])
const loading = ref(true)
const searchValue = ref('')
const recipeActionsMenuRef = ref()

// Methods
const loadRecipes = async () => {
    const result = await fetchMyRecipes()
    if (result) {
        recipes.value = result.recipes
    }
}

// Lifecycle
onMounted(async () => {
    await loadRecipes()
    loading.value = false
})
</script>
