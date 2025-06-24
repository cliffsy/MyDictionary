import { createRouter, createWebHistory } from 'vue-router'
import Login from '@/views/auth/login.vue'
import Register from '@/views/auth/register.vue'
import Home from '@/views/home.vue'

import Cookies from 'js-cookie'
import api from '@/api/axios'

import auth from '@/api/auth'

async function requireAuth(to, from, next) {
  try {
    const { data } = await auth.me()
    if (data?.statusCode == 200 && data?.data) {
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
    if (Cookies.get('user')) {
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
      path: '/',
      name: 'home',
      component: Home,
      beforeEnter: requireAuth,
    },
    {
      path: '/register',
      name: 'register',
      component: Register,
      beforeEnter: redirectIfAuthenticated,
    },
  ],
})

export default router
