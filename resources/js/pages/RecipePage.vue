<template>
    <div v-if="loading" class="space-y-4 p-4">
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

    <div v-else-if="recipe" class="page-container p-4">
        <!-- Hero Section -->
        <div class="relative overflow-hidden rounded-lg">
            <SmartImage
                v-if="recipe.image_urls?.large"
                :alt="recipe.title"
                :max-retries="5"
                :retry-delay="2000"
                :src="recipe.image_urls.large"
                image-class="w-full h-48 md:h-64 object-cover"
            />
            <PlaceholderRecipeImage v-else class="w-full h-48 md:h-64 object-cover"/>

            <!-- Action Button -->
            <Button
                class="!absolute !top-2 !right-2"
                icon="pi pi-bars"
                raised
                severity="secondary"
                @click.stop="recipeActionsMenuRef.toggle($event, recipe)"
            />
        </div>

        <!-- Header Info -->
        <div class="space-y-2 my-3">
            <div>
                <h2 class="text-2xl font-semibold">{{ recipe.title }}</h2>
                <div class="text-lg flex items-center gap-2">
                    <SmartImage
                        :alt="recipe.user.name"
                        :max-retries="5"
                        :retry-delay="2000"
                        :src="recipe.user.avatar_urls?.small || '/images/avatar-placeholder.svg'"
                        image-class="user-avatar"
                    />
                    <span>by {{ recipe.user.name }}</span>
                </div>
            </div>

            <!-- Rating -->
            <div class="flex items-stretch gap-3">
                <DifficultyBadge
                    v-if="recipe.difficulty_level"
                    :difficulty="recipe.difficulty_level"
                />
                <div class="flex items-center gap-2 px-2 secondary-bg rounded-md">
                    <Rating
                        :cancel="false"
                        :model-value="recipe.average_rating"
                        :stars="recipe.average_rating ? 5 : 1"
                        readonly
                    />
                    <span class="secondary-text font-semibold text-sm">
                        {{ recipe.total_ratings ? `(${recipe.total_ratings} ratings)` : 'not yet rated' }}
                    </span>
                </div>
            </div>

            <!-- Recipe Meta -->
            <div class="flex flex-wrap gap-x-4 gap-y-1 secondary-text">
                <div v-if="recipe.total_time" class="flex items-center gap-1">
                    <i class="pi pi-clock"></i>
                    <span>{{ getTimeBreakdown(recipe) }}</span>
                </div>
                <div v-if="recipe.recipe_yield" class="flex items-center gap-1">
                    <i class="pi pi-bolt"></i>
                    <span>{{ recipe.recipe_yield }}</span>
                </div>
            </div>

            <!-- Tags -->
            <div v-if="displayTags.length > 0" class="flex flex-wrap gap-1 items-baseline">
                <Tag
                    v-for="(tag, index) in displayTags"
                    :key="index"
                    :icon="tag.icon"
                    :severity="tag.severity"
                    :value="tag.text"
                    class="!text-xs"
                />
            </div>
        </div>

        <Divider/>

        <!-- Ingredients -->
        <section>
            <h3 class="text-xl font-semibold my-3">
                Ingredients
            </h3>
            <ul v-if="recipe.ingredients && recipe.ingredients.length > 0" class="space-y-2">
                <li
                    v-for="(ingredient, index) in recipe.ingredients"
                    :key="index"
                    class="flex items-center gap-3"
                >
                    <Checkbox
                        v-model="checkedIngredients[index]"
                        :input-id="index.toString()"
                        binary
                    />
                    <label
                        :class="{ 'line-through secondary-text': checkedIngredients[index] }"
                        :for="index.toString()"
                        class="cursor-pointer flex-1"
                    >
                        {{ ingredient }}
                    </label>
                </li>
            </ul>
            <p v-else class="secondary-text italic">No ingredients listed</p>
        </section>

        <!-- Instructions -->
        <Divider/>

        <h3 class="text-xl font-semibold my-3">
            Instructions
        </h3>
        <div v-if="recipe.instructions.length > 0" class="space-y-4">
            <div
                v-for="(instruction, index) in recipe.instructions"
                :key="`instruction-${index}`"
            >
                <!-- Section Header -->
                <div v-if="instruction.type === 'section'"
                     class="p-1 mb-3 secondary-bg rounded-sm">
                    <h4 class="font-semibold flex items-center gap-2 primary-theme-text">
                        <i class="pi pi-bookmark-fill !text-xs"></i>
                        {{ instruction.position }} # {{ instruction.name }}
                    </h4>
                </div>

                <!-- Section Steps -->
                <div v-if="instruction.type === 'section'" class="space-y-4 pl-1">
                    <div
                        v-for="step in instruction.steps"
                        :key="`step-${step.position}`"
                        class="flex items-baseline gap-2"
                    >
                        <div
                            class="shrink-0 primary-theme-text font-medium text-sm">
                            {{ instruction.position }}.{{ step.position }}
                        </div>
                        <p>
                        <span v-if="step.name" class="font-medium primary-theme-text">
                            {{ step.name }} ~
                        </span>
                            {{ step.text }}
                        </p>
                    </div>
                </div>

                <!-- Standalone Step -->
                <div v-else class="flex items-baseline pl-1 gap-2">
                    <div class="shrink-0 primary-theme-text font-medium text-sm">
                        {{ instruction.position }}
                    </div>
                    <p>
                        <span v-if="instruction.name" class="font-medium primary-theme-text">
                            {{ instruction.name }} ~
                        </span>
                        {{ instruction.text }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Notes Section -->
        <template v-if="recipe.notes">
            <Divider/>

            <section>
                <h3 class="text-xl font-semibold my-3">
                    Notes
                </h3>
                <p>{{ recipe.notes }}</p>
            </section>
        </template>

        <!-- Original recipe -->
        <template v-if="recipe.source_url || recipe.parent_recipe">
            <Divider/>

            <section
                class="text-center p-2 secondary-bg rounded-lg">
                <p class="secondary-text text-sm">
                    {{ sourceText }}
                </p>
                <Button
                    icon="pi pi-external-link"
                    label="View original recipe"
                    link
                    size="small"
                    @click="viewOriginal"
                />
            </section>
        </template>
    </div>

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
            <p class="secondary-text">The recipe you're looking for doesn't exist or has been removed.</p>
        </div>
        <Button
            icon="pi pi-arrow-left"
            label="Go Back"
            @click="$router.back()"
        />
    </div>

    <RecipeActionsMenu ref="recipeActionsMenuRef"/>
</template>

<script lang="ts" setup>
import {computed, onMounted, ref, watch} from 'vue'
import {useRouter} from 'vue-router'
import Button from 'primevue/button'
import Rating from 'primevue/rating'
import Tag from 'primevue/tag'
import Checkbox from 'primevue/checkbox'
import Skeleton from 'primevue/skeleton'
import Divider from 'primevue/divider'
import {getTimeBreakdown, Recipe} from '@/types/recipe'
import {fetchRecipeById} from '@/services/recipeService'
import SmartImage from '@/components/SmartImage.vue'
import DifficultyBadge from '@/components/DifficultyBadge.vue'
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue";
import RecipeActionsMenu from "@/components/RecipeActionsMenu.vue";
import {FlattenedTag, flattenRecipeTags} from "@/utils/tagUtils.ts";

const router = useRouter()

const props = defineProps<{
    id: number
}>()

const recipe = ref<Recipe | null>(null)
const loading = ref(true)
const checkedIngredients = ref<boolean[]>([])
const recipeActionsMenuRef = ref()
const displayTags = ref<FlattenedTag[]>([]);

watch(() => recipe.value?.ingredients, (ingredients) => {
    if (ingredients) {
        checkedIngredients.value = new Array(ingredients.length).fill(false)
    }
}, {immediate: true})

const loadRecipe = async () => {
    recipe.value = await fetchRecipeById(props.id)
    if (recipe.value) {
        displayTags.value = flattenRecipeTags(recipe.value.tags)
    }
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

const flattenedInstructions = computed(() => {
    if (!recipe.value?.instructions) return []

    const flattened: any[] = []
    let globalStepCounter = 1

    recipe.value.instructions.forEach(instruction => {
        if (instruction.type === 'section') {
            // Add section with globally numbered steps
            const sectionWithGlobalSteps = {
                ...instruction,
                steps: instruction.steps.map(step => ({
                    ...step,
                    globalPosition: globalStepCounter++
                }))
            }
            flattened.push(sectionWithGlobalSteps)
        } else {
            // Add standalone step with global position
            flattened.push({
                ...instruction,
                globalPosition: globalStepCounter++
            })
        }
    })

    return flattened
})

const getAccordionPanelClass = (item: any, index: number) => {
    const baseClass = index !== flattenedInstructions.value.length - 1 ? '!border-dashed' : '!border-none'
    return item.type === 'section' ? `${baseClass} section-panel` : baseClass
}

onMounted(() => {
    loadRecipe()
})
</script>
