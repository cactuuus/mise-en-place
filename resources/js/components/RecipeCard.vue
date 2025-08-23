<template>
    <Card class="recipe-card transition-all duration-200 hover:shadow-lg">
        <template #header>
            <!-- Deferred Image Loading -->
            <DeferredContent @load="onImageLoad">
                <SmartImage
                    :alt="recipe.title"
                    :src="props.recipe.image_urls.medium || '/images/recipe-placeholder.svg'"
                    image-class="image-preview"
                />
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
                <!-- Author -->

                <!-- Rating -->
                <div v-if="recipe.average_rating || recipe.total_ratings" class="flex items-center gap-2">
                    <Rating
                        :cancel="false"
                        :modelValue="recipe.average_rating || 0"
                        class="text-sm"
                        readonly
                    />
                    <span class="text-sm text-surface-600">
                        ({{ recipe.total_ratings }})
                    </span>
                </div>

                <!-- Recipe Details -->
                <div class="flex items-center justify-between text-sm text-surface-600">
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1">
                            <i class="pi pi-clock text-xs"></i>
                            <span>{{ recipe.total_time || '-' }}m</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <i class="pi pi-users text-xs"></i>
                            <span>{{ recipe.serves || '-' }}</span>
                        </div>
                    </div>
                    <DifficultyBadge
                        v-if="recipe.difficulty_level"
                        :difficulty="recipe.difficulty_level"
                    />
                </div>
            </div>
        </template>

        <template #footer>
            <!-- Tags -->
            <div v-if="recipe.tags && recipe.tags.length > 0" class="flex flex-wrap gap-1 items-baseline">
                <Tag
                    v-for="tag in recipe.tags.slice(0, MAX_TAGS)"
                    :key="getTagKey(tag)"
                    :value="getTagLabel(tag.name)"
                    class="!text-xs"
                    severity="secondary"
                />
                <span v-if="recipe.tags.length > MAX_TAGS" class="text-xs text-surface-500">
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
import {RecipePreview} from '@/types/recipe'
import DifficultyBadge from "@/components/DifficultyBadge.vue"
import SmartImage from "@/components/SmartImage.vue";

const MAX_TAGS = 3
const props = defineProps<{
    recipe: RecipePreview
}>()


// Functions
const onImageLoad = (): void => {
    console.log(`Deferred content loaded for recipe: ${props.recipe.title}`)
}

const getTagLabel = (tagName: string | { [key: string]: string }): string => {
    if (typeof tagName === 'string') {
        return tagName
    }
    // If it's an object with language keys, try to get English first, then any value
    return tagName.en || tagName.eng || Object.values(tagName)[0] || 'tag'
}

const getTagKey = (tag: { name: string | { [key: string]: string } }): string => {
    return getTagLabel(tag.name)
}
</script>
