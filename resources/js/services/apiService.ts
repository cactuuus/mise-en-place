import {showError, showSuccess} from '@/services/toastService'

interface ApiCallOptions<T> {
    call: () => Promise<T>
    successMessage?: string
    errorMessage?: string
    onSuccess?: (response: T) => void
    onError?: (error: any) => void
}

const executeApiCall = async <T>(options: ApiCallOptions<T>): Promise<boolean> => {
    try {
        const response = await options.call()

        if (options.onSuccess) {
            options.onSuccess(response)
        }

        if (options.successMessage) {
            showSuccess(options.successMessage)
        }

        return true

    } catch (error: any) {
        if (options.onError) {
            options.onError(error)
        }

        if (options.errorMessage) {
            showError(options.errorMessage || 'An error occurred')
        }

        console.error('API call failed:', error)
        return false
    }
}

export default executeApiCall
