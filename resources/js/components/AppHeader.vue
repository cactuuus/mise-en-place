<template>
  <header>
    <div id="header-content">
      <!-- Logo -->
      <RouterLink id="banner" to="/">
        <h1>Mise En Place</h1>
      </RouterLink>

      <!-- User Section -->
      <div id="user-section">
        <div v-if="!authStore.isAuthenticated">
          <Button label="Login" text @click="$emit('showLoginModal')"/>
        </div>

        <div v-else class="flex items-center gap-2">
          <!-- Authenticated user - show avatar -->
          <SmartImage
              :src="authStore.user?.avatar_urls?.small || '/images/avatar-placeholder.svg'"
              image-class="user-avatar"
              @click="toggleUserMenu"
          />
          <!-- User dropdown menu -->
          <Popover id="user-menu" ref="userMenuRef">
            <Button
                icon="pi pi-cog"
                label="Settings"
                severity="secondary"
                text
                @click="navigateToSettings"
            />
            <Button
                :loading="authStore.isLoading"
                icon="pi pi-sign-out"
                label="Logout"
                severity="danger"
                text
                @click="authStore.logout()"
            />
          </Popover>
        </div>
      </div>

      <!-- Navigation -->
      <nav id="main-nav">
        <template v-for="(item, index) in navItems" :key="item.key">
          <RouterLink
              v-if="!item.disabled"
              :to="item.to"
              active-class="active"
              class="nav-link enabled"
          >
            <i :class="item.icon"></i>
            <span class="label">{{ item.label }}</span>
          </RouterLink>

          <!-- Disabled link -->
          <button
              v-else
              class="nav-link disabled"
              @click="item.alternateAction()"
          >
            <i :class="item.icon"></i>
            <span class="label">{{ item.label }}</span>
          </button>
          <div
              v-if="index < navItems.length - 1"
              class="separator"
          >
          </div>
        </template>
      </nav>


    </div>
  </header>
</template>

<script lang="ts" setup>
import {computed, ref} from 'vue'
import {useRouter} from 'vue-router'
import {useAuthStore} from '@/stores/auth'
import Button from 'primevue/button'
import Popover from 'primevue/popover'
import SmartImage from '@/components/SmartImage.vue'

// Emits
const emit = defineEmits<{
  showLoginModal: []
}>()

// Composables
const router = useRouter()
const authStore = useAuthStore()
const userMenuRef = ref()

// Navigation items
const navItems = computed(() => [
  {
    key: 'discover',
    label: 'Discover',
    icon: 'pi pi-compass',
    to: '/discover',
    disabled: false,
    alternateAction: () => {
    },
  },
  {
    key: 'cookbook',
    label: 'My Cookbook',
    icon: 'pi pi-bookmark',
    to: '/cookbook',
    disabled: !authStore.isAuthenticated,
    alternateAction: () => {
      emit('showLoginModal')
    },
  },
])

// Functions
const toggleUserMenu = (event: Event) => {
  userMenuRef.value?.toggle(event)
}

const navigateToSettings = () => {
  userMenuRef.value?.hide()
  router.push('/settings')
}
</script>
