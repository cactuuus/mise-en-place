export const DIFFICULTY_LEVELS = {
    EASY: {
        value: 1,
        label: 'Easy',
        color: 'green'
    },
    MEDIUM: {
        value: 2,
        label: 'Medium',
        color: 'yellow'
    },
    HARD: {
        value: 3,
        label: 'Hard',
        color: 'red'
    }
} as const

export type DifficultyLevel = typeof DIFFICULTY_LEVELS[keyof typeof DIFFICULTY_LEVELS]

export interface Recipe {
    id: number
    title: string
    user: {
        id: number
        name: string
    }
    tags?: Array<{ name: string | { [key: string]: string } }>
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

// Helper function to convert API number to difficulty object
export const getDifficultyFromValue = (value?: number): DifficultyLevel | undefined => {
    return Object.values(DIFFICULTY_LEVELS).find(d => d.value === value)
}
