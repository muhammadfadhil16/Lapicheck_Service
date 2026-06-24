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
  ],
})

export default router
