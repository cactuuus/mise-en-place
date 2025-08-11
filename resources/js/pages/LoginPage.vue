<template>
    <q-page class="flex flex-center">
        <q-card
            class="q-pa-lg w-full max-w-xl"
        >
            <!-- Tab Navigation -->
            <q-tabs
                v-model="activeTab"
                active-color="primary"
                align="justify"
                class="text-grey-8"
                indicator-color="primary"
                narrow-indicator
            >
                <q-tab label="Sign In" name="login"/>
                <q-tab label="Create Account" name="register"/>
            </q-tabs>

            <q-separator/>

            <!-- Tab Panels -->
            <q-tab-panels v-model="activeTab" animated>

                <!-- Login Panel -->
                <q-tab-panel name="login">
                    <q-card-section>
                        <q-form class="q-gutter-y-md" @submit="handleLogin">
                            <h4 class="text-h6 sm:text-h4 q-mb-xl">Welcome Back!</h4>

                            <q-input
                                v-model="loginForm.email"
                                :disable="authStore.isLoading"
                                :error="!!authStore.errorMessage"
                                label="Email"
                                outlined
                                required
                                standout
                                type="email"
                            />

                            <q-input
                                v-model="loginForm.password"
                                :disable="authStore.isLoading"
                                :error="!!authStore.errorMessage"
                                label="Password"
                                outlined
                                required
                                type="password"
                            />

                            <div v-if="authStore.errorMessage" class="text-negative text-center text-caption m-0">
                                {{ authStore.errorMessage }}
                            </div>

                            <div class="flex flex-row gap-4 justify-evenly q-mt-lg">
                                <q-btn
                                    class="grow"
                                    color="grey-2"
                                    rounded
                                    size="lg"
                                    text-color="grey-8"
                                    unelevated
                                    @click="router.push('/')"
                                >
                                    Back
                                </q-btn>

                                <q-btn
                                    :disable="authStore.isLoading"
                                    :loading="authStore.isLoading"
                                    class="grow"
                                    color="primary"
                                    outlined
                                    rounded
                                    size="lg"
                                    type="submit"
                                    unelevated
                                >
                                    Sign In
                                </q-btn>
                            </div>
                        </q-form>
                    </q-card-section>

                    <q-card-section class="text-center q-pb-none">
                        <q-btn
                            color="grey-8"
                            flat
                            label="Don't have an account? Register now!"
                            no-caps
                            size="md"
                            type="a"
                            @click="activeTab = 'register'"
                        />
                    </q-card-section>
                </q-tab-panel>

                <!-- Registration Panel -->
                <q-tab-panel name="register">
                    <q-card-section>
                        <q-form class="q-gutter-y-md" @submit="handleRegister">
                            <h4 class="text-h4 text-center q-mb-xl">Join millions* of other chefs</h4>


                            <q-input
                                v-model="registerForm.name"
                                :disable="authStore.isLoading"
                                :error="!!formErrors.name"
                                :error-message="formErrors.name"
                                label="Full Name"
                                outlined
                                required
                                type="text"
                            />

                            <q-input
                                v-model="registerForm.email"
                                :disable="authStore.isLoading"
                                :error="!!formErrors.email"
                                :error-message="formErrors.email"
                                label="Email"
                                outlined
                                required
                                type="email"
                            />

                            <q-input
                                v-model="registerForm.password"
                                :disable="authStore.isLoading"
                                :error="!!formErrors.password"
                                :error-message="formErrors.password"
                                hint="At least 8 characters"
                                label="Password"
                                outlined
                                required
                                type="password"
                            >
                                <template v-slot:prepend>
                                    <q-icon name="key"></q-icon>
                                </template>
                            </q-input>

                            <q-input
                                v-model="registerForm.password_confirmation"
                                :disable="authStore.isLoading"
                                :error="!!formErrors.password_confirmation"
                                :error-message="formErrors.password_confirmation"
                                label="Confirm Password"
                                outlined
                                required
                                type="password"
                            />

                            <div v-if="authStore.errorMessage" class="text-negative text-center text-caption">
                                {{ authStore.errorMessage }}
                            </div>

                            <div class="flex flex-row gap-4 justify-evenly q-mt-lg">
                                <q-btn
                                    class="grow"
                                    color="grey-2"
                                    label="Back"
                                    rounded
                                    size="lg"
                                    text-color="grey-8"
                                    unelevated
                                    @click="router.push('/')"
                                />

                                <q-btn
                                    :disable="authStore.isLoading"
                                    :loading="authStore.isLoading"
                                    class="grow"
                                    color="primary"
                                    label="Register"
                                    outlined
                                    rounded
                                    size="lg"
                                    type="submit"
                                    unelevated
                                />
                            </div>
                        </q-form>
                    </q-card-section>

                    <q-card-section class="text-center q-pb-none">
                        <q-btn
                            color="grey-8"
                            flat
                            label="Already have an account? Sign in!"
                            no-caps
                            size="md"
                            type="a"
                            @click="activeTab = 'login'"
                        />
                    </q-card-section>
                </q-tab-panel>
            </q-tab-panels>
        </q-card>
    </q-page>
</template>

<script lang="ts" setup>
import {ref, watch} from 'vue'
import {useRoute, useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'

const activeTab = ref<'login' | 'register'>('login')

const loginForm = ref({
    email: '',
    password: ''
})

const registerForm = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const formErrors = ref<Record<string, string>>({})

const authStore = useAuthStore()
const router = useRouter()
const route = useRoute()

// Set initial tab based on route
if (route.name === 'Register') {
    activeTab.value = 'register'
}

// Clear errors when switching tabs
watch(activeTab, () => {
    authStore.errorMessage = ''
    formErrors.value = {}
})

const handleLogin = async (): Promise<void> => {
    if (!loginForm.value.email || !loginForm.value.password) {
        return
    }
    try {
        const success = await authStore.login(loginForm.value.email, loginForm.value.password)
        if (success) {
            router.push('/')
        }
    } catch (error) {
        console.error('Login error:', error)
    }
}

const validateRegistration = (): boolean => {
    formErrors.value = {}

    if (!registerForm.value.name.trim()) {
        formErrors.value.name = 'Name is required'
    }

    if (!registerForm.value.email.trim()) {
        formErrors.value.email = 'Email is required'
    }

    if (!registerForm.value.password) {
        formErrors.value.password = 'Password is required'
    } else if (registerForm.value.password.length < 8) {
        formErrors.value.password = 'Password must be at least 8 characters'
    }

    if (registerForm.value.password !== registerForm.value.password_confirmation) {
        formErrors.value.password_confirmation = 'Passwords do not match'
    }

    return Object.keys(formErrors.value).length === 0
}

const handleRegister = async (): Promise<void> => {
    if (!validateRegistration()) {
        return
    }

    try {
        const success = await authStore.register({
            name: registerForm.value.name.trim(),
            email: registerForm.value.email.trim(),
            password: registerForm.value.password,
            password_confirmation: registerForm.value.password_confirmation
        })

        if (success) {
            router.push('/')
        }
    } catch (error) {
        console.error('Registration error:', error)
    }
}
</script>

<style scoped>
/* Make the whole field yellow when autofilled */
:deep(.q-field__control:has(.q-field__native:-webkit-autofill)) {
    background-color: #fffcc8 !important; /* Chrome's autofill yellow */
}
</style>
