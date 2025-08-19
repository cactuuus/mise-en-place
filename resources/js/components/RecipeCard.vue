<template>
    <Card class="recipe-card cursor-pointer transition-all duration-200 hover:shadow-lg">
        <template #content>
            <!-- Recipe Image -->
            <div class="recipe-image-container mb-4">
                <Image
                    :alt="recipe.title"
                    :src="recipeImage"
                    class="w-full object-cover rounded-lg aspect-square"
                    @error="handleImageError"
                />
            </div>

            <!-- Recipe Info -->
            <div class="space-y-3">
                <!-- Title -->
                <h3 class="font-semibold text-lg line-clamp-2 text-surface-900">
                    {{ recipe.title }}
                </h3>

                <!-- Author -->
                <div class="flex items-center gap-2 text-sm text-surface-600">
                    <Avatar
                        :label="authorInitials"
                        class="bg-primary-100 text-primary-600"
                        shape="circle"
                    />
                    <span>by {{ recipe.user.name }}</span>
                </div>

                <!-- Rating -->
                <div v-if="recipe.average_rating || recipe.total_ratings" class="flex items-center gap-2">
                    <Rating
                        :cancel="false"
                        :modelValue="recipe.average_rating || 0"
                        class="text-sm"
                        readonly
                    />
                    <span class="text-sm text-surface-600">
                        {{ '(' + recipe.total_ratings + ')' || 'Not yet rated!' }}
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

                <!-- Tags -->
                <div v-if="recipe.tags && recipe.tags.length > 0" class="flex flex-wrap gap-1 items-baseline">
                    <Chip
                        v-for="tag in recipe.tags.slice(0, 3)"
                        :key="getTagKey(tag)"
                        :label="getTagLabel(tag.name)"
                        class="text-xs"
                    />
                    <span v-if="recipe.tags.length > 3" class="text-xs text-surface-500">
                        +{{ recipe.tags.length - 3 }} more
                    </span>
                </div>
            </div>
        </template>
    </Card>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import Card from 'primevue/card'
import Avatar from 'primevue/avatar'
import Rating from 'primevue/rating'
import Chip from 'primevue/chip'
import Image from 'primevue/image'
import {Recipe} from '@/types/recipe'
import DifficultyBadge from "@/components/DifficultyBadge.vue"

const props = defineProps<{
    recipe: Recipe
}>()

// Reactive data
const imageError = ref(false)

// Computed properties
const authorInitials = computed(() => {
    const names = props.recipe.user.name.split(' ')
    if (names.length >= 2) {
        return names[0][0] + names[1][0]
    }
    return names[0][0]
})

const recipeImage = computed(() => {
    if (imageError.value || !props.recipe.image_urls.medium || props.recipe.image_urls.medium.trim() === '') {
        return '/images/recipe-placeholder.svg'
    }
    return props.recipe.image_urls.medium
})

// Functions
const handleImageError = (): void => {
    console.log(`Image error occurred for recipe: ${props.recipe.title} - id ${props.recipe.id}`)
    imageError.value = true
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

<style scoped>
.recipe-card:hover {
    transform: translateY(-2px);
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
