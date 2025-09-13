import {defineStore} from 'pinia'
import {computed, ref, watch} from 'vue'
import api from '@/services/api'
import {useRouter} from 'vue-router'
import executeApiCall from '@/services/apiService'
import {showSuccess} from "@/services/toastService.ts";
import {AuthenticatedUser} from "@/types/user.ts";

interface LoginResponse {
    user: AuthenticatedUser
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
    const user = ref<AuthenticatedUser | null>(null)
    const token = ref<string | null>(localStorage.getItem('auth_token'))
    const authState = ref<AuthState>(AuthState.IDLE)
    const errorMessage = ref<string>('')
    const router = useRouter()
    const isInitialized = ref<boolean>(false)

    const waitForInitialization = async (): Promise<void> => {
        if (isInitialized.value) return

        return new Promise((resolve) => {
            const unwatch = watch(isInitialized, (initialized) => {
                if (initialized) {
                    unwatch()
                    resolve()
                }
            }, {immediate: true})
        })
    }

    const isAuthenticated = computed((): boolean => {
        return !!(token.value && user.value)
    })

    const isLoading = computed((): boolean => {
        return authState.value === AuthState.LOADING
    })

    const register = async (userData: RegisterData): Promise<boolean> => {
        errorMessage.value = ''
        authState.value = AuthState.LOADING

        return await executeApiCall({
            call: () => api.post<LoginResponse>('/register', userData),
            successMessage: `Welcome to Mise En Place, ${userData.name}!`,
            errorMessage: 'Registration failed.',
            onSuccess: (response) => {
                const authData = response.data
                token.value = authData.token
                user.value = authData.user
                localStorage.setItem('auth_token', token.value)
                authState.value = AuthState.AUTHENTICATED
            },
            onError: (error) => {
                authState.value = AuthState.ERROR
                errorMessage.value = error.response?.data?.message || 'Registration failed'
            }
        })
    }

    const login = async (email: string, password: string): Promise<boolean> => {
        errorMessage.value = ''
        authState.value = AuthState.LOADING

        return await executeApiCall({
            call: () => api.post<LoginResponse>('/login', {email, password}),
            successMessage: 'Login successful!',
            errorMessage: 'Login failed.',
            onSuccess: (response) => {
                const authData = response.data
                token.value = authData.token
                user.value = authData.user
                localStorage.setItem('auth_token', token.value)
                authState.value = AuthState.AUTHENTICATED
            },
            onError: (error) => {
                authState.value = AuthState.ERROR
                errorMessage.value = error.response?.data?.message || 'Login failed. Please check your credentials.'
            }
        })
    }

    const logout = async (): Promise<void> => {
        authState.value = AuthState.LOADING

        // Call logout endpoint if we have a token (don't show error toast if it fails)
        if (token.value) {
            await executeApiCall({
                call: () => api.post('/logout'),
                onError: (error) => {
                    console.error('Logout API error:', error)
                }
            })
        }
        // Clear all authentication state regardless of API response
        await clearAuth(true)
        showSuccess('You have been logged out')
    }

    // Function to initialize authentication state when the app starts
    const initializeAuth = async (): Promise<void> => {
        // If we have a stored token, try to fetch current user data
        if (token.value) {
            authState.value = AuthState.LOADING

            const success = await executeApiCall({
                call: () => api.get<AuthenticatedUser>('/user'),
                onSuccess: (response) => {
                    user.value = response.data
                    authState.value = AuthState.AUTHENTICATED
                },
                onError: (error) => {
                    // Token is probably expired or invalid
                    console.error('Failed to fetch user - token likely expired:', error)
                    clearAuth()
                }
            })
        }
        isInitialized.value = true
    }

    const clearAuth = async (redirect: boolean = false): Promise<void> => {
        user.value = null
        token.value = null
        localStorage.removeItem('auth_token')
        authState.value = AuthState.IDLE
        errorMessage.value = ''

        if (redirect) {
            await router.push('/')
        }
    }

    const setUser = (userData: AuthenticatedUser): void => {
        user.value = userData
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
        initializeAuth,
        clearAuth,
        setUser,
        waitForInitialization
    }
})
