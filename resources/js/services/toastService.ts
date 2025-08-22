import {useToast} from 'primevue/usetoast'
import type {ToastServiceMethods} from 'primevue/toastservice'

let toastInstance: ToastServiceMethods | null = null

export const initializeToast = () => {
    toastInstance = useToast()
}

export const showSuccess = (message: string, summary = 'Success') => {
    if (!toastInstance) {
        console.warn('Toast service not initialized')
        return
    }
    toastInstance.add({
        severity: 'success',
        summary,
        detail: message,
        life: 3000
    })
}

export const showError = (message: string, summary = 'Error') => {
    if (!toastInstance) {
        console.warn('Toast service not initialized')
        return
    }
    toastInstance.add({
        severity: 'error',
        summary,
        detail: message,
        life: 5000
    })
}
