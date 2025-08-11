<template>
    <div class="min-h-screen bg-surface-50">
        <!-- Header -->
        <div class="bg-primary-600 shadow-lg">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-4">
                    <h1 class="text-2xl font-bold">Mise En Place</h1>
                    <!-- Add navigation items here later -->
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="container mx-auto px-4 py-8">
            <router-view/>
        </div>

        <!-- Toast Container -->
        <Toast/>
    </div>
</template>

<script lang="ts" setup>
import Toast from 'primevue/toast';
</script>
