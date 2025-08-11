import {defineStore} from 'pinia'
import {computed, ref} from 'vue'
import api from '@/services/api'
import {Notify} from 'quasar'

interface User {
    id: number
    name: string
    email: string
    created_at: string
}

interface LoginResponse {
    user: User
    token: string
}

interface RegisterData {
    name: string
    email: string,
    password: string,
    password_confirmation: string
}

enum AuthState {
    IDLE = 'idle',
    LOADING = 'loading',
    AUTHENTICATED = 'authenticated',
    ERROR = 'error'
}

export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null)
    const token = ref<string | null>(localStorage.getItem('auth_token'))
    const authState = ref<AuthState>(AuthState.IDLE)
    const errorMessage = ref<string>('')

    const isAuthenticated = computed((): boolean => {
        return !!(token.value && user.value)
    })

    const isLoading = computed((): boolean => {
        return authState.value === AuthState.LOADING
    })

    const register = async (userData: RegisterData): Promise<boolean> => {
        errorMessage.value = ''
        authState.value = AuthState.LOADING

        try {
            const response = await api.post<LoginResponse>('/register', userData)

            const authData = response.data
            token.value = authData.token
            user.value = authData.user

            localStorage.setItem('auth_token', token.value)
            authState.value = AuthState.AUTHENTICATED

            Notify.create({
                type: 'positive',
                message: `Welcome to Mise En Place, ${user.value.name}!`
            })

            return true

        } catch (error: any) {
            authState.value = AuthState.ERROR
            errorMessage.value = error.response?.data?.message || 'Registration failed'

            Notify.create({
                type: 'negative',
                message: errorMessage.value
            })

            return false
        }
    }

    const login = async (email: string, password: string): Promise<boolean> => {
        errorMessage.value = ''
        authState.value = AuthState.LOADING

        try {
            const response = await api.post<LoginResponse>('/login', {email, password})

            const authData = response.data
            token.value = authData.token
            user.value = authData.user

            // Save token to localStorage so it persists across browser sessions
            localStorage.setItem('auth_token', token.value)

            // Update our state to reflect successful authentication
            authState.value = AuthState.AUTHENTICATED

            // Show success message to user
            Notify.create({
                type: 'positive',
                message: `Welcome back, ${user.value.name}!`
            })

            return true // Indicate success to the calling component

        } catch (error: any) {
            // Handle login failure
            authState.value = AuthState.ERROR

            // Extract error message from API response, with a sensible fallback
            errorMessage.value = error.response?.data?.message || 'Login failed. Please check your credentials.'

            // Show error to user
            Notify.create({
                type: 'negative',
                message: errorMessage.value
            })

            return false // Indicate failure to the calling component
        }
    }

    const logout = async (): Promise<void> => {
        authState.value = AuthState.LOADING

        try {
            // Tell the server to invalidate the token
            if (token.value) {
                await api.post('/logout')
            }
        } catch (error) {
            // Even if the API call fails, we still want to clear local state
            console.error('Logout error:', error)
        } finally {
            // Clear all authentication state regardless of API response
            user.value = null
            token.value = null
            localStorage.removeItem('auth_token')
            authState.value = AuthState.IDLE
            errorMessage.value = ''

            Notify.create({
                type: 'info',
                message: 'You have been logged out'
            })
        }
    }

    // Function to initialize authentication state when the app starts
    const initializeAuth = async (): Promise<void> => {
        // If we have a stored token, try to fetch current user data
        if (token.value) {
            authState.value = AuthState.LOADING

            try {
                const response = await api.get<User>('/user')
                user.value = response.data
                authState.value = AuthState.AUTHENTICATED
            } catch (error) {
                // Token is probably expired or invalid
                console.error('Failed to fetch user:', error)

                // Clear invalid authentication state
                user.value = null
                token.value = null
                localStorage.removeItem('auth_token')
                authState.value = AuthState.IDLE
            }
        }
    }

    // Return all the state and functions that components can use
    return {
        // State that components can read
        user,
        token,
        authState,
        errorMessage,

        // Computed values that components can use for logic
        isAuthenticated,
        isLoading,

        // Functions that components can call
        login,
        register,
        logout,
        initializeAuth
    }
})
