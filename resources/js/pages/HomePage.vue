<template>
    <q-page padding>
        <div class="text-center q-mt-xl">
            <h1 class="text-h4 text-primary q-mb-md">Welcome to Mise En Place</h1>

            <div v-if="!authStore.isAuthenticated" class="q-gutter-md">
                <p class="text-body1 text-grey-7">
                    Discover and share amazing recipes with the community!
                </p>

                <div class="q-gutter-sm">
                    <q-btn
                        color="primary"
                        label="Get Started"
                        size="lg"
                        @click="goToRegister"
                    />
                    <q-btn
                        color="secondary"
                        label="Sign In"
                        outline
                        size="lg"
                        @click="goToLogin"
                    />
                </div>
            </div>

            <div v-else class="q-gutter-md">
                <q-card class="q-pa-md bg-blue-1">
                    <q-card-section>
                        <div class="text-h6">Hello, {{ authStore.user?.name }}! 👋</div>
                        <div class="text-body2 text-grey-7">Ready to cook something delicious?</div>
                    </q-card-section>
                    <q-card-actions align="right">
                        <q-btn
                            color="negative"
                            flat
                            label="Logout"
                            @click="authStore.logout"
                        />
                    </q-card-actions>
                </q-card>
            </div>
        </div>
    </q-page>
</template>

<script lang="ts" setup>
import {onMounted} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'

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
