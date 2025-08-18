import {createRouter, createWebHistory, type RouteRecordRaw} from 'vue-router'

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
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
