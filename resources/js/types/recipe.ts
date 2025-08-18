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
    difficulty_level?: number
    created_at: string
    average_rating?: number
    total_ratings?: number
}
