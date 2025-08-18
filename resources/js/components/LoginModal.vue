<template>
    <Tabs v-model:value="activeTab">
        <TabList>
            <Tab class="grow" value="login">Login</Tab>
            <Tab class="grow" value="register">Register</Tab>
        </TabList>

        <TabPanels id="login-panel">
            <TabPanel value="login">
                <h2 class="panel-heading">Have we met before?</h2>

                <Form v-slot="$form" :resolver="loginResolver" validate-on-value-update
                      @submit="handleLogin">
                    <div class="form-content">
                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <InputText
                                    id="login-email"
                                    :disabled="authStore.isLoading"
                                    fluid
                                    name="email"
                                    type="email"
                                />
                                <label for="login-email">Email</label>
                            </FloatLabel>
                            <Message v-if="$form.email?.invalid" severity="error" size="small"
                                     variant="simple">
                                {{ $form.email.error.message }}
                            </Message>
                        </div>

                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <Password
                                    id="login-password"
                                    :disabled="authStore.isLoading"
                                    :feedback="false"
                                    fluid
                                    name="password"
                                    toggleMask
                                />
                                <label for="login-password">Password</label>
                            </FloatLabel>
                            <Message v-if="$form.password?.invalid" severity="error" size="small"
                                     variant="simple">
                                {{ $form.password.error.message }}
                            </Message>
                        </div>

                        <Message v-if="authStore.errorMessage" severity="error">
                            {{ authStore.errorMessage }}
                        </Message>
                    </div>
                    <div class="button-container">
                        <Button
                            label="Close"
                            outlined
                            severity="secondary"
                            @click="emit('close')"
                        />
                        <Button
                            :disabled="authStore.isLoading || !$form.valid"
                            :loading="authStore.isLoading"
                            label="Sign In"
                            type="submit"
                        />
                    </div>
                </Form>

                <Button class="footer-text"
                        label="Don't have an account? Register now!"
                        link
                        @click="activeTab = 'register'"
                />
            </TabPanel>

            <TabPanel value="register">
                <div class="panel-heading">
                    <h2>Join millions* of chefs</h2>
                    <p class="text-gray-600 text-xs">* numbers might be severely inflated</p>
                </div>

                <Form v-slot="$form" :resolver="registerResolver" validate-on-value-update
                      @submit="handleRegister">
                    <div class="form-content">
                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <InputText
                                    id="register-name"
                                    :disabled="authStore.isLoading"
                                    fluid
                                    name="name"
                                    required
                                />
                                <label for="register-name">Full Name</label>
                            </FloatLabel>
                            <Message v-if="$form.name?.invalid" severity="error" size="small"
                                     variant="simple">
                                {{ $form.name.error.message }}
                            </Message>
                        </div>

                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <InputText
                                    id="register-email"
                                    :disabled="authStore.isLoading"
                                    fluid
                                    name="email"
                                    required
                                    type="email"
                                />
                                <label for="register-email">Email</label>
                            </FloatLabel>
                            <Message v-if="$form.email?.invalid" severity="error" size="small"
                                     variant="simple">
                                {{ $form.email.error.message }}
                            </Message>
                        </div>

                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <Password
                                    id="register-password"
                                    :disabled="authStore.isLoading"
                                    fluid
                                    mediumLabel="Medium"
                                    name="password"
                                    promptLabel="Enter a password"
                                    required
                                    strongLabel="Strong"
                                    toggleMask
                                    weakLabel="Weak"
                                />
                                <label for="register-password">Password</label>
                            </FloatLabel>
                            <Message v-if="$form.password?.invalid" severity="error" size="small"
                                     variant="simple">{{ $form.password.error.message }}
                            </Message>
                        </div>

                        <div class="flex flex-col gap-1">
                            <FloatLabel variant="on">
                                <Password
                                    id="register-password-confirmation"
                                    :disabled="authStore.isLoading"
                                    :feedback="false"
                                    fluid
                                    name="password_confirmation"
                                    required
                                    toggleMask
                                />
                                <label for="register-password-confirmation">Confirm Password</label>
                            </FloatLabel>
                            <Message v-if="$form.password_confirmation?.invalid" severity="error"
                                     size="small" variant="simple">
                                {{ $form.password_confirmation.error.message }}
                            </Message>
                        </div>
                    </div>

                    <Message v-if="authStore.errorMessage" severity="error">
                        {{ authStore.errorMessage }}
                    </Message>

                    <div class="button-container">
                        <Button
                            label="Close"
                            outlined
                            severity="secondary"
                            @click="emit('close')"
                        />
                        <Button
                            :disabled="authStore.isLoading || !$form.valid"
                            :loading="authStore.isLoading"
                            label="Register"
                            type="submit"
                        />
                    </div>
                </Form>

                <Button class="footer-text"
                        label="Already have an account? Sign in!"
                        link
                        @click="activeTab = 'login'"
                />
            </TabPanel>
        </TabPanels>
    </Tabs>
</template>

<script lang="ts" setup>
import {defineEmits, ref, watch} from 'vue'
import {useAuthStore} from '@/stores/auth'
import Tabs from 'primevue/tabs'
import Tab from 'primevue/tab'
import TabList from 'primevue/tablist'
import TabPanels from 'primevue/tabpanels'
import TabPanel from 'primevue/tabpanel'
import InputText from 'primevue/inputtext'
import Password from 'primevue/password'
import Button from 'primevue/button'
import FloatLabel from 'primevue/floatlabel'
import Message from 'primevue/message'
import {Form} from '@primevue/forms'
import {z} from 'zod'
import {zodResolver} from '@primevue/forms/resolvers/zod'


const activeTab = ref<'login' | 'register'>('login')
const authStore = useAuthStore()

const emit = defineEmits<{
    close: []
}>()

// Clear errors when switching tabs
watch(activeTab, () => {
    authStore.errorMessage = ''
})

const loginSchema = z.object({
    email: z.email('Please enter a valid email'),
    password: z.string().min(1, 'Password is required')
})

const registerSchema = z.object({
    name: z.string().min(1, 'Name is required'),
    email: z.email('Please enter a valid email'),
    password: z.string().min(8, 'Password must be at least 8 characters'),
    password_confirmation: z.string()
}).refine(data => data.password === data.password_confirmation, {
    message: "Passwords don't match",
    path: ['password_confirmation']
})

const loginResolver = zodResolver(loginSchema)
const registerResolver = zodResolver(registerSchema)

const handleLogin = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return

    // Extract values from states
    const values = Object.keys(event.states).reduce((acc, key) => {
        acc[key] = event.states[key].value
        return acc
    }, {} as Record<string, any>)

    try {
        const success = await authStore.login(values.email, values.password)
        if (success) {
            emit('close')
        }
    } catch (error) {
        console.error('Login error:', error)
    }
}

const handleRegister = async (event: { valid: boolean; states: Record<string, any> }): Promise<void> => {
    if (!event.valid) return

    // Extract values from states
    const values = Object.keys(event.states).reduce((acc, key) => {
        acc[key] = event.states[key].value
        return acc
    }, {} as Record<string, any>)

    try {
        const success = await authStore.register({
            name: values.name.trim(),
            email: values.email.trim(),
            password: values.password,
            password_confirmation: values.password_confirmation
        })

        if (success) {
            emit('close')
        }
    } catch (error) {
        console.error('Registration error:', error)
    }
}
</script>
