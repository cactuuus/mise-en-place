export enum DifficultyLevel {
    EASY = 1,
    MEDIUM = 2,
    HARD = 3
}

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
}

export const getDifficultyLabel = (difficulty?: DifficultyLevel): string => {
    switch (difficulty) {
        case DifficultyLevel.EASY:
            return 'Easy'
        case DifficultyLevel.MEDIUM:
            return 'Medium'
        case DifficultyLevel.HARD:
            return 'Hard'
        default:
            console.error(`Unknown difficulty level: ${difficulty}`)
            return '???'
    }
}

export const getDifficultyColor = (difficulty?: DifficultyLevel): string => {
    switch (difficulty) {
        case DifficultyLevel.EASY:
            return `green`
        case DifficultyLevel.MEDIUM:
            return `yellow`
        case DifficultyLevel.HARD:
            return `red`
        default:
            return `gray`
    }
}
