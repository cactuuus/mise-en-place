import api from './api'
import executeApiCall from './apiService'
import {getDifficultyFromValue, Recipe, RecipePreview, Tag} from '@/types/recipe'

interface RecipesApiResponse {
    data: any[] // Raw API data with numeric difficulty_level
    current_page: number
    last_page: number
    per_page: number
    total: number
}

interface RecipesResponse {
    recipes: RecipePreview[]
    pagination: {
        currentPage: number
        lastPage: number
        perPage: number
        total: number
        hasMore: boolean
    }
}

export const fetchRecipes = async (page: number): Promise<RecipesResponse | null> => {
    let result: RecipesResponse | null = null

    await executeApiCall({
        call: () => api.get<RecipesApiResponse>(`/recipes?page=${page}`),
        errorMessage: 'Failed to load recipes',
        onSuccess: (response) => {
            // Transform raw API data
            const transformedRecipes = response.data.data.map((recipe: any) => ({
                ...recipe,
                difficulty_level: getDifficultyFromValue(recipe.difficulty_level)
            }))

            result = {
                recipes: transformedRecipes,
                pagination: {
                    currentPage: response.data.current_page,
                    lastPage: response.data.last_page,
                    perPage: response.data.per_page,
                    total: response.data.total,
                    hasMore: response.data.current_page < response.data.last_page
                }
            }
        }
    })

    return result
}

export const fetchRecipeById = async (id: number): Promise<Recipe | null> => {
    let result: Recipe | null = null

    await executeApiCall({
        call: () => api.get<any>(`/recipes/${id}`),
        errorMessage: 'Failed to load recipe',
        onSuccess: (response) => {
            result = {
                ...response.data,
                difficulty_level: getDifficultyFromValue(response.data.difficulty_level),
            }
        }
    })

    return result
}

export const fetchMyRecipes = async (page: number = 1): Promise<RecipesResponse | null> => {
    let result: RecipesResponse | null = null

    await executeApiCall({
        call: () => api.get<RecipesApiResponse>(`/recipes/mine?page=${page}`),
        errorMessage: 'Failed to load your recipes',
        onSuccess: (response) => {
            // Transform raw API data (same logic as fetchRecipes)
            const transformedRecipes = response.data.data.map((recipe: any) => ({
                ...recipe,
                difficulty_level: getDifficultyFromValue(recipe.difficulty_level)
            }))

            result = {
                recipes: transformedRecipes,
                pagination: {
                    currentPage: response.data.current_page,
                    lastPage: response.data.last_page,
                    perPage: response.data.per_page,
                    total: response.data.total,
                    hasMore: response.data.current_page < response.data.last_page
                }
            }
        }
    })

    return result
}

export const createRecipe = async (formData: FormData): Promise<Recipe | null> => {
    let result = null

    await executeApiCall({
        call: () => api.post('/recipes', formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        }),
        successMessage: 'Recipe created successfully!',
        errorMessage: 'Failed to create recipe',
        onSuccess: (response) => {
            result = {
                ...response.data,
                difficulty_level: getDifficultyFromValue(response.data.difficulty_level),
            }
        }
    })

    return result
}

export const updateRecipe = async (id: number, formData: FormData): Promise<boolean> => {
    return await executeApiCall({
        call: () => api.post(`/recipes/${id}`, formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        }),
        successMessage: 'Recipe updated successfully!',
        errorMessage: 'Failed to update recipe'
    })
}

export const deleteRecipe = async (id: number): Promise<boolean> => {
    return await executeApiCall({
        call: () => api.delete(`/recipes/${id}`),
        successMessage: 'Recipe deleted successfully!',
        errorMessage: 'Failed to delete recipe'
    })
}

export const fetchRecipeTags = async (tagsType: string = ''): Promise<Tag[]> => {
    let tags: Tag[] = []

    await executeApiCall({
        call: () => api.get(`/tags${tagsType ? `?type=${tagsType}` : ''}`),
        errorMessage: 'Failed to load tags',
        onSuccess: (response) => {
            tags = response.data
        }
    })

    return tags
}
