<template>
    <div class="min-h-screen bg-zinc-200 dark:bg-zinc-800">
        <!-- Header with Menubar -->
        <Menubar
            :model="menuItems"
            class="!rounded-none !border-l-0 !border-r-0 !border-t-0"
        >
            <!-- Logo/Brand on the left -->
            <template #start>
                <div class="text-2xl font-bold">
                    Mise En Place
                </div>
            </template>

            <!-- User section on the right -->
            <template #end>
                <div v-if="!authStore.isAuthenticated">
                    <!-- Guest user - show login button -->
                    <Button
                        label="Login"
                        text
                        @click="showLoginModal = true"
                    />
                </div>
                <div v-else class="flex items-center gap-2">
                    <!-- Authenticated user - show avatar -->
                    <Button
                        class="!py-1"
                        text
                        @click="toggleUserMenu"
                    >
                        <Avatar
                            :image="authStore.user?.avatar_urls?.small || '/images/avatar-placeholder.svg'"
                            class="bg-surface-500 cursor-pointer"
                            shape="circle"
                        />
                    </Button>
                    <!-- User dropdown menu -->
                    <Popover ref="userMenuRef">
                        <div class="flex flex-col gap-1">
                            <Button
                                class="justify-start"
                                icon="pi pi-cog"
                                label="Settings"
                                text
                                @click="navigateToSettings"
                            />
                            <Button
                                class="justify-start"
                                icon="pi pi-sign-out"
                                label="Logout"
                                severity="danger"
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
        <Toast class="max-w-[85%]" position="bottom-center"/>

        <!-- Login Modal -->
        <Dialog
            v-model:visible="showLoginModal"
            :closable="false"
            :draggable="false"
            class="base-modal headless-modal"
            close-on-escape
            dismissable-mask
            modal
            responsive
        >
            <LoginModal @close="showLoginModal = false"/>
        </Dialog>
    </div>
</template>

<script lang="ts" setup>
import {ref} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Toast from 'primevue/toast'
import Menubar from 'primevue/menubar'
import Button from 'primevue/button'
import Avatar from 'primevue/avatar'
import Popover from 'primevue/popover'
import Dialog from 'primevue/dialog'
import LoginModal from '@/components/LoginModal.vue'

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

// Functions
const toggleUserMenu = (event: Event) => {
    userMenuRef.value?.toggle(event)
}

const navigateToSettings = () => {
    userMenuRef.value?.hide()
    router.push('/settings')
}

const handleLogout = () => {
    userMenuRef.value?.hide()
    authStore.logout()
}
</script>
