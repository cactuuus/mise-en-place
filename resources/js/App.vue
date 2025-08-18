<template>
    <div class="min-h-screen bg-surface-50">
        <!-- Header with Menubar -->
        <Menubar :model="menuItems" class="bg-primary-600">
            <!-- Logo/Brand on the left -->
            <template #start>
                <div class="text-xl font-bold">
                    Mise En Place
                </div>
            </template>

            <!-- User section on the right -->
            <template #end>
                <div v-if="!authStore.isAuthenticated">
                    <!-- Guest user - show login button -->
                    <Button
                        class="text-white"
                        label="Login"
                        text
                        @click="showLoginModal = true"
                    />
                </div>
                <div v-else class="flex items-center gap-2">
                    <!-- Authenticated user - show avatar and name -->
                    <Avatar
                        :label="userInitials"
                        class="bg-surface-500 text-white cursor-pointer"
                        @click="toggleUserMenu"
                    />
                    <span class="text-white font-medium hidden sm:block">
                        {{ authStore.user?.name }}
                    </span>

                    <!-- User dropdown menu -->
                    <Popover ref="userMenuRef" class="w-48">
                        <div class="flex flex-col gap-1">
                            <Button
                                class="justify-start"
                                icon="pi pi-cog"
                                label="Settings"
                                text
                            />
                            <Button
                                class="justify-start"
                                icon="pi pi-sign-out"
                                label="Logout"
                                text
                                @click="handleLogout"
                            />
                        </div>
                    </Popover>
                </div>
            </template>
        </Menubar>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <router-view/>
        </div>

        <!-- Toast Container -->
        <Toast/>

        <!-- Login Modal (placeholder for now) -->
        <Dialog
            v-model:visible="showLoginModal"
            class="w-full max-w-md"
            header="Login"
            modal
        >
            <p>Login modal content will go here</p>
        </Dialog>
    </div>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Toast from 'primevue/toast'
import Menubar from 'primevue/menubar'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Popover from 'primevue/popover'
import Dialog from 'primevue/dialog'

// Get our stores and router
const authStore = useAuthStore()
const router = useRouter()

// Refs for UI state
const showLoginModal = ref(false)
const userMenuRef = ref()

// Menu items for the navigation
const menuItems = ref([
    {
        label: 'Recipes',
        icon: 'pi pi-book',
        command: () => router.push('/recipes')
    }
])

// Computed property to get user initials for avatar
const userInitials = computed(() => {
    if (!authStore.user?.name) return 'U'

    const names = authStore.user.name.split(' ')
    if (names.length >= 2) {
        return names[0][0] + names[1][0]
    }
    return names[0][0]
})

// Functions
const toggleUserMenu = (event: Event) => {
    userMenuRef.value?.toggle(event)
}

const handleLogout = () => {
    userMenuRef.value?.hide()
    authStore.logout()
}
</script>
