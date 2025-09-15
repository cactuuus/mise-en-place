import {defineStore} from 'pinia'
import {computed, ref, watch} from 'vue'
import api from '@/services/api'
import {useRouter} from 'vue-router'
import executeApiCall from '@/services/apiService'
import {showSuccess} from "@/services/toastService.ts";
import {AuthenticatedUser} from "@/types/user.ts";

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
    const authState = ref<AuthState>(AuthState.IDLE)
    const errorMessage = ref<string>('')
    const router = useRouter()
    const isInitialized = ref<boolean>(false)
    const isAuthenticated = computed((): boolean => !!user.value)
    const isLoading = computed((): boolean => authState.value === AuthState.LOADING)

    // Promise that resolves when the auth store is initialized
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

    const register = async (userData: RegisterData): Promise<boolean> => {
        errorMessage.value = ''
        authState.value = AuthState.LOADING

        return await executeApiCall({
            call: () => api.post<AuthenticatedUser>('/register', userData),
            successMessage: `Welcome to Mise En Place, ${userData.name}!`,
            errorMessage: 'Registration failed.',
            onSuccess: (response) => {
                setUser(response.data)
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
            call: () => api.post<AuthenticatedUser>('/login', {email, password}),
            successMessage: 'Login successful!',
            errorMessage: 'Login failed.',
            onSuccess: (response) => {
                setUser(response.data)
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

        await executeApiCall({
            call: () => api.post('/logout'),
        })
        // Clear all authentication state regardless of API response
        await clearAuth(true)
        showSuccess('You have been logged out')
    }

    // Function to initialize authentication state when the app starts
    const initializeAuth = async (): Promise<void> => {
        authState.value = AuthState.LOADING

        // First, get the CSRF cookie
        const fetchedCRSF = await executeApiCall({
            call: () => api.get('/sanctum/csrf-cookie'),
            onError: (error) => {
                console.error('Failed to initialize CSRF cookie:', error)
            }
        })

        if (fetchedCRSF) {
            await executeApiCall({
                call: () => api.get<AuthenticatedUser>('/user'),
                onSuccess: (response) => {
                    setUser(response.data)
                    authState.value = AuthState.AUTHENTICATED
                    console.debug('User session restored')
                },
                onError: () => {
                    // Token is probably expired or invalid
                    console.warn('No active user session found - likely expired')
                    clearAuth()
                }
            })
        }

        isInitialized.value = true
    }

    const clearAuth = async (redirect: boolean = false): Promise<void> => {
        user.value = null
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
