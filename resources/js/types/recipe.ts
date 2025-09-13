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

export interface RecipeTags {
    recipe_cuisine: string[]
    recipe_category: string[]
    recipe_diet: string[]
    recipe_keyword: string[]
}

export interface RecipeStep {
    type: 'step'
    position: number
    text: string
    name?: string
}

export interface RecipeSection {
    type: 'section'
    position: number
    name: string
    steps: RecipeStep[]
}

export type RecipeInstruction = RecipeStep | RecipeSection

// Base recipe for previews/cards
export interface RecipePreview {
    id: number
    title: string
    user: UserPreview
    tags: RecipeTags
    prep_time?: number
    cook_time?: number
    total_time?: number
    recipe_yield?: string
    difficulty_level?: DifficultyLevel
    created_at: string
    is_public: boolean
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
    instructions: RecipeInstruction[]
    notes?: string
    source_url?: string
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
