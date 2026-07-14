import { createRouter, createWebHistory } from 'vue-router'
import FuzzyIndex from '../views/assessments/index.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: FuzzyIndex,
    },
    {
      path: '/history',
      name: 'history',
      component: () => import('../views/assessments/history.vue'),
    },
    {
      path: '/laptops',
      name: 'laptops',
      component: () => import('../views/laptops/index.vue'),
    },
    {
      path: '/laptops/brands',
      name: 'laptop-brands',
      component: () => import('../views/laptops/brands.vue'),
    },
    {
      path: '/settings/ai-keywords',
      name: 'ai-keywords',
      component: () => import('../views/settings/ai-keywords.vue'),
    },
  ],
})

export default router
