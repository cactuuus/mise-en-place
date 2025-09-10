<template>
    <Card class="recipe-card transition-all duration-200 hover:shadow-lg">
        <template #header>
            <!-- Deferred Image Loading -->
            <DeferredContent>
                <SmartImage
                    v-if="recipe.image_urls?.medium"
                    :alt="recipe.title"
                    :src="recipe.image_urls.medium"
                    image-class="image-preview"
                />
                <PlaceholderRecipeImage v-else class="image-preview"/>
            </DeferredContent>
        </template>

        <template #title>
            {{ recipe.title }}
        </template>

        <template #subtitle>
            by {{ recipe.user.name }}
        </template>

        <template #content>
            <!-- Recipe Info -->
            <div class="space-y-3">

                <!-- Rating -->
                <div v-if="recipe.average_rating || recipe.total_ratings" class="flex items-center gap-2">
                    <Rating
                        :cancel="false"
                        :modelValue="recipe.average_rating || 0"
                        class="text-sm"
                        readonly
                    />
                    <span class="text-sm">
                        ({{ recipe.total_ratings }})
                    </span>
                </div>

                <!-- Recipe Details -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1">
                            <i class="pi pi-clock text-xs"></i>
                            <span>{{ recipe.total_time || '-' }}m</span>
                        </div>
                        <div v-if="recipe.recipe_yield" class="flex items-center gap-1">
                            <i class="pi pi-bolt text-xs"></i>
                            <span>{{ recipe.recipe_yield }}</span>
                        </div>
                    </div>
                    <DifficultyBadge
                        v-if="recipe.difficulty_level"
                        :difficulty="recipe.difficulty_level"
                        class="!text-xs"
                    />
                </div>
            </div>
        </template>

        <template #footer>
            <!-- Tags -->
            <div v-if="recipe.tags && recipe.tags.length > 0" class="flex flex-wrap gap-1 items-baseline">
                <Tag
                    v-for="tag in recipe.tags.slice(0, MAX_TAGS)"
                    :key="tag.id"
                    :value="getTagLabel(tag.name)"
                    class="!text-xs"
                    severity="secondary"
                />
                <span v-if="recipe.tags.length > MAX_TAGS" class="text-xs">
                        +{{ recipe.tags.length - MAX_TAGS }} more
                    </span>
            </div>
        </template>
    </Card>
</template>

<script lang="ts" setup>
import Card from 'primevue/card'
import Rating from 'primevue/rating'
import Tag from 'primevue/tag'
import DeferredContent from 'primevue/deferredcontent'
import {getTagLabel, RecipePreview} from '@/types/recipe'
import DifficultyBadge from "@/components/DifficultyBadge.vue"
import SmartImage from "@/components/SmartImage.vue";
import PlaceholderRecipeImage from "@/components/PlaceholderRecipeImage.vue";

const MAX_TAGS = 5
defineProps<{
    recipe: RecipePreview
}>()
</script>
