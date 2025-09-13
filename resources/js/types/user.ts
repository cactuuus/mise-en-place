// Minimal user info for recipe cards/lists
export interface UserPreview {
    id: number
    name: string
    avatar_urls: {
        small: string | null
        large: string | null
    }
}

export interface User extends UserPreview {
    created_at: string
    total_recipes?: number
    total_followers?: number
    total_following?: number
    is_followed?: boolean
}

// Authenticated user with sensitive information
export interface AuthenticatedUser extends User {
    email: string
}


