<template>
    <div class="min-h-screen bg-surface-200 dark:bg-surface-800">
        <!-- Header -->
        <AppHeader @showLoginModal="showLoginModal = true"/>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <RouterView/>
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
import Toast from 'primevue/toast'
import Dialog from 'primevue/dialog'
import LoginModal from '@/components/LoginModal.vue'
import {initializeToast} from "@/services/toastService.ts"
import AppHeader from "@/components/AppHeader.vue";

initializeToast()
const showLoginModal = ref(false)

</script>
