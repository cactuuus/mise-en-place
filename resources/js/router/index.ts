import {createRouter, createWebHistory, type RouteRecordRaw} from 'vue-router'
import {useAuthStore} from '@/stores/auth'

// This is TypeScript's way of extending existing types
// Extend custom properties we want to add to routes
declare module 'vue-router' {
    interface RouteMeta {
        title?: string
        requiresAuth?: boolean
    }
}

// Type-safe route definitions
const routes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'Home',
        component: () => import('@/pages/HomePage.vue'),
        meta: {title: 'Mise en Place'}
    },
    {
        path: '/recipes',
        name: 'recipes',
        component: () => import('@/pages/RecipesPage.vue'),
        meta: {title: 'Recipes'}
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/pages/UserSettingsPage.vue'),
        meta: {title: 'Settings', requiresAuth: true}
    },
    {
        path: '/recipes/:id',
        name: 'recipe-detail',
        component: () => import('@/pages/RecipePage.vue'),
        props: true
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

// Global navigation guard
router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()

    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        // Redirect to home if not authenticated
        next('/')
    } else {
        next()
    }
})

export default router
