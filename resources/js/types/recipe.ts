import {UserPreview} from "@/types/user.ts";

export const DIFFICULTY_LEVELS = {
    EASY: {
        value: 1,
        label: 'Easy',
        color: 'green',
        severity: 'success',
    },
    MEDIUM: {
        value: 2,
        label: 'Medium',
        color: 'yellow',
        severity: 'warn',
    },
    HARD: {
        value: 3,
        label: 'Hard',
        color: 'red',
        severity: 'danger',
    }
} as const

export type DifficultyLevel = typeof DIFFICULTY_LEVELS[keyof typeof DIFFICULTY_LEVELS]

// Base recipe for previews/cards
export interface RecipePreview {
    id: number
    title: string
    user: UserPreview
    tags?: Array<{
        id: number
        name: string | { [key: string]: string }
    }>
    prep_time?: number
    cook_time?: number
    total_time?: number
    serves?: number
    difficulty_level?: DifficultyLevel
    created_at: string
    average_rating?: number
    total_ratings?: number
    image_urls: {
        small: string | null
        medium: string | null
        large: string | null
    }
}

// Complete recipe
export interface Recipe extends RecipePreview {
    ingredients: string[]
    instructions: string[]
    notes?: string
    source_url?: string
    is_public: boolean
    forked_from_recipe_id?: number
    parent_recipe?: {
        user: UserPreview
    }
}

// Helper function to convert API number to difficulty object
export const getDifficultyFromValue = (value?: number): DifficultyLevel | undefined => {
    return Object.values(DIFFICULTY_LEVELS).find(d => d.value === value)
}

// Helper function to combine recipe times
export function getTimeBreakdown(recipe: Recipe | RecipePreview): string {
    const hints = []

    if (recipe.prep_time) {
        hints.push(`${recipe.prep_time} prep`)
    }

    if (recipe.cook_time) {
        hints.push(`${recipe.cook_time} cook`)
    }

    return `${recipe.total_time} mins` + (hints.length > 0 ? ` (${hints.join(' + ')})` : '')
}

// Helper function to extract the label from a tag name which may be localized
export const getTagLabel = (tagName: string | { [key: string]: string }): string => {
    if (typeof tagName === 'string') {
        return tagName
    }

    // Extract from JSON object - prefer English, fallback to first available
    return tagName.en || tagName.eng || Object.values(tagName)[0]
}
