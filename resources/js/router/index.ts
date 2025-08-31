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
        redirect: '/discover'
    },
    {
        path: '/discover',
        children: [
            {
                path: '',
                name: 'discover',
                component: () => import('@/pages/DiscoverPage.vue'),
                meta: {title: 'Discover'}
            },
            {
                path: ':id',
                name: 'recipe-detail',
                component: () => import('@/pages/RecipePage.vue'),
                props: true,
                meta: {title: 'Recipe Detail'}
            }
        ]
    },
    {
        path: '/cookbook',
        name: 'cookbook',
        component: () => import('@/pages/CookbookPage.vue'),
        meta: {title: 'Cookbook', requiresAuth: true}
    },
    {
        path: '/settings',
        name: 'settings',
        component: () => import('@/pages/UserSettingsPage.vue'),
        meta: {title: 'Settings', requiresAuth: true}
    },

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
