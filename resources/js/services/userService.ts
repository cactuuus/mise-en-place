import api from './api'
import executeApiCall from "@/services/apiService.ts";
import {useAuthStore} from '@/stores/auth'
import {AuthenticatedUser} from "@/types/user.ts";

interface PasswordData {
    current_password: string
    new_password: string
    new_password_confirmation: string
}

export const updateName = async (name: string): Promise<boolean> => {
    const authStore = useAuthStore()

    return await executeApiCall({
        call: () => api.put<AuthenticatedUser>('/user/name', {name}),
        successMessage: 'Name updated successfully',
        errorMessage: 'Failed to update name',
        onSuccess: (response) => authStore.setUser(response.data)
    })
}

export const updateAvatar = async (file: File): Promise<boolean> => {
    const authStore = useAuthStore()
    const formData = new FormData()
    formData.append('avatar', file)

    return await executeApiCall({
        call: () => api.post<AuthenticatedUser>('/user/avatar', formData, {
            headers: {'Content-Type': 'multipart/form-data'}
        }),
        successMessage: 'Avatar updated successfully',
        errorMessage: 'Failed to update avatar',
        onSuccess: (response) => authStore.setUser(response.data)
    })
}

export const deleteAvatar = async (): Promise<boolean> => {
    const authStore = useAuthStore()

    return await executeApiCall({
        call: () => api.delete<AuthenticatedUser>('/user/avatar'),
        successMessage: 'Avatar removed successfully',
        errorMessage: 'Failed to delete avatar',
        onSuccess: (response) => authStore.setUser(response.data)
    })
}

export const updatePassword = async (passwordData: PasswordData): Promise<boolean> => {
    return await executeApiCall({
        call: () => api.put('/user/password', passwordData),
        successMessage: 'Password updated successfully',
        errorMessage: 'Failed to update password',
    })
}

export const deleteAccount = async (): Promise<boolean> => {
    const authStore = useAuthStore()

    return await executeApiCall({
        call: () => api.delete('/user/account'),
        successMessage: 'Account deleted successfully',
        errorMessage: 'Failed to delete account',
        onSuccess: () => {
            authStore.clearAuth(true)
        }
    })
}
