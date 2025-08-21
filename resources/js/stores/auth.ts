import {defineStore} from 'pinia'
import {computed, ref} from 'vue'
import api from '@/services/api'
import {useToast} from 'primevue/usetoast'
import {useRouter} from 'vue-router'

interface User {
    id: number
    name: string
    email: string
    created_at: string
    avatar_urls: {
        small: string | null
        large: string | null
    }
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
    const toast = useToast()
    const router = useRouter()

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

            toast.add({
                severity: 'success',
                summary: 'Registration Successful!',
                detail: `Welcome to Mise En Place, ${user.value.name}!`,
                life: 3000
            })

            return true

        } catch (error: any) {
            authState.value = AuthState.ERROR
            errorMessage.value = error.response?.data?.message || 'Registration failed'

            toast.add({
                severity: 'error',
                summary: 'Registration Failed',
                detail: errorMessage.value,
                life: 5000
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
            toast.add({
                severity: 'success',
                summary: 'Login Successful!',
                detail: `Welcome back, ${user.value.name}!`,
                life: 3000
            })

            return true // Indicate success to the calling component

        } catch (error: any) {
            // Handle login failure
            authState.value = AuthState.ERROR

            // Extract error message from API response, with a sensible fallback
            errorMessage.value = error.response?.data?.message || 'Login failed. Please check your credentials.'

            // Show error to user
            toast.add({
                severity: 'error',
                summary: 'Login Failed',
                detail: errorMessage.value,
                life: 5000
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

            await router.push('/')
            toast.add({
                severity: 'info',
                summary: 'Logged Out',
                detail: 'You have been logged out',
                life: 3000
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

    const updateName = async (name: string): Promise<boolean> => {
        try {
            const response = await api.put<{ user: User; message: string }>('/user/name', {name})
            user.value = response.data.user

            toast.add({
                severity: 'success',
                summary: 'Name Updated',
                detail: response.data.message,
                life: 3000
            })

            return true
        } catch (error: any) {
            toast.add({
                severity: 'error',
                summary: 'Update Failed',
                detail: error.response?.data?.message || 'Failed to update name',
                life: 5000
            })
            return false
        }
    }

    const updateAvatar = async (avatarFile: File): Promise<boolean> => {
        try {
            const formData = new FormData()
            formData.append('avatar', avatarFile)

            const response = await api.post<{ user: User; message: string }>('/user/avatar', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            })
            user.value = response.data.user

            toast.add({
                severity: 'success',
                summary: 'Avatar Updated',
                detail: response.data.message,
                life: 3000
            })

            return true
        } catch (error: any) {
            toast.add({
                severity: 'error',
                summary: 'Upload Failed',
                detail: error.response?.data?.message || 'Failed to upload avatar',
                life: 5000
            })
            return false
        }
    }

    const deleteAvatar = async (): Promise<boolean> => {
        try {
            const response = await api.delete<{ user: User; message: string }>('/user/avatar')

            user.value = response.data.user

            toast.add({
                severity: 'success',
                summary: 'Avatar Removed',
                detail: response.data.message,
                life: 3000
            })

            return true
        } catch (error: any) {
            toast.add({
                severity: 'error',
                summary: 'Delete Failed',
                detail: error.response?.data?.message || 'Failed to delete avatar',
                life: 5000
            })
            return false
        }
    }

    const updatePassword = async (passwordData: {
        current_password: string;
        new_password: string;
        new_password_confirmation: string
    }): Promise<boolean> => {
        try {
            const response = await api.put<{ message: string }>('/user/password', passwordData)

            toast.add({
                severity: 'success',
                summary: 'Password Updated',
                detail: response.data.message,
                life: 3000
            })

            return true
        } catch (error: any) {
            toast.add({
                severity: 'error',
                summary: 'Password Update Failed',
                detail: error.response?.data?.message || 'Failed to update password',
                life: 5000
            })
            return false
        }
    }

    const deleteAccount = async (): Promise<boolean> => {
        try {
            const response = await api.delete<{ message: string }>('/user/account')

            // Clear all authentication state
            user.value = null
            token.value = null
            localStorage.removeItem('auth_token')
            authState.value = AuthState.IDLE
            errorMessage.value = ''

            await router.push('/')
            toast.add({
                severity: 'success',
                summary: 'Account Deleted',
                detail: response.data.message,
                life: 5000
            })

            return true
        } catch (error: any) {
            toast.add({
                severity: 'error',
                summary: 'Delete Failed',
                detail: error.response?.data?.message || 'Failed to delete account',
                life: 5000
            })
            return false
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
        initializeAuth,
        updateName,
        updateAvatar,
        deleteAvatar,
        updatePassword,
        deleteAccount
    }
})
