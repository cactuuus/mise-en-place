import {createApp} from 'vue'
import {createPinia} from "pinia";
import PrimeVue from 'primevue/config';
import Aura from '@primevue/themes/aura';
import {definePreset} from "@primevue/themes";
import {colors} from './colors';
import ToastService from 'primevue/toastservice';
import router from './router'
import App from './App.vue'
import {useAuthStore} from './stores/auth'
import 'primeicons/primeicons.css'

const app = createApp(App)
const pinia = createPinia()
const primevueConfig = {
    theme: {
        preset: definePreset(Aura, {
            semantic: {
                primary: colors.primary,
                colorScheme: {
                    light: {
                        surface: colors.surface
                    },
                    dark: {
                        surface: colors.surface
                    }
                }
            }
        }),
        options: {
            prefix: 'p',
            darkModeSelector: 'system',
            cssLayer: false
        }
    }
}

// Initialize authentication before mounting
const initializeApp = async () => {
    app.use(pinia)
    const authStore = useAuthStore()
    await authStore.initializeAuth()

    app.use(PrimeVue, primevueConfig);
    app.use(router)
    app.use(ToastService);

    app.mount('#app')
}

initializeApp().catch(console.error)
