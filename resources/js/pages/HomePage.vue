<template>
    <div class="flex flex-col items-center justify-center">
        <div class="text-center mb-8">
            <h1 class="text-4xl font-bold text-primary-600 mb-4">Welcome to Mise En Place</h1>

            <div v-if="!authStore.isAuthenticated" class="space-y-6">
                <p class="text-lg text-surface-600">
                    Discover and share amazing recipes with the community!
                </p>

                <div class="flex gap-4 justify-center">
                    <Button
                        label="Get Started"
                        size="large"
                        @click="goToRegister"
                    />
                    <Button
                        label="Sign In"
                        outlined
                        severity="secondary"
                        size="large"
                        @click="goToLogin"
                    />
                </div>
            </div>

            <div v-else class="max-w-md mx-auto">
                <Card class="bg-blue-50">
                    <template #content>
                        <div class="text-center space-y-4">
                            <div class="text-xl font-semibold">Hello, {{ authStore.user?.name }}! 👋</div>
                            <div class="text-surface-600">Ready to cook something delicious?</div>

                            <div class="flex justify-end pt-4">
                                <Button
                                    label="Logout"
                                    severity="danger"
                                    text
                                    @click="authStore.logout"
                                />
                            </div>
                        </div>
                    </template>
                </Card>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
import {onMounted} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Button from 'primevue/button'
import Card from 'primevue/card'

// Get access to our authentication store and router
const authStore = useAuthStore()
const router = useRouter()

// Initialize authentication when the component mounts
// This checks if the user has a valid stored token
onMounted(async () => {
    await authStore.initializeAuth()
})

// Navigation functions
const goToRegister = (): void => {
    router.push('/register')
}

const goToLogin = (): void => {
    router.push('/login')
}
</script>
