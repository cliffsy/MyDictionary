import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/auth/login.vue'
import Register from '@/views/auth/register.vue'
import Home from '@/views/home.vue'
import Favorites from '@/views/favorites.vue'

import Cookies from 'js-cookie'
import api from '@/api/axios'

import auth from '@/api/auth'

async function requireAuth(to, from, next) {
  try {
    if (Cookies.get('user') && Cookies.get('access-token')) {
      next()
    } else {
      auth.logout()
    }
  } catch (e) {
    Cookies.remove('user')
    Cookies.remove('access-token')
    window.location.href = '/'
  }
}

async function redirectIfAuthenticated(to, from, next) {
  try {
    if (Cookies.get('user') && Cookies.get('access-token')) {
      next({ name: 'home' })
    } else {
      Cookies.remove('user')
      Cookies.remove('access-token')
      next()
    }
  } catch (e) {
    Cookies.remove('user')
    Cookies.remove('access-token')
    window.location.href = '/'
  }
}

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: Login,
      beforeEnter: redirectIfAuthenticated,
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      beforeEnter: redirectIfAuthenticated,
    },
    {
      path: '/',
      name: 'home',
      component: Home,
      beforeEnter: requireAuth,
    },
    {
      path: '/favorites',
      name: 'favorites',
      component: Favorites,
      beforeEnter: requireAuth,
    },
  ],
})

export default router
