import api from './api'
import executeApiCall from './apiService'
import {getDifficultyFromValue, Recipe, RecipePreview} from '@/types/recipe'

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
