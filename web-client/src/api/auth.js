import api from './axios'
import axios from 'axios'
import Cookies from 'js-cookie'

const url = '/auth'

async function login(form) {
  try {
    await axios.get('/sanctum/csrf-cookie')
    const { data } = await api.post(`${url}/login`, form)

    var user = data?.data?.user
    var token = data?.data?.access_token
    Cookies.set('user', JSON.stringify(user), {
      expires: 1,
    })
    Cookies.set('access-token', token, {
      expires: 1,
    })

    window.location.href = '/'
  } catch (e) {
    throw e
  }
}

async function me() {
  try {
    const res = await api.get(`${url}/me`)
    return res
  } catch (e) {
    throw e
  }
}

async function register(form) {
  try {
    const res = await api.post(`${url}/register`, form)
    return res
  } catch (e) {
    throw e
  }
}

async function logout() {
  Cookies.remove('user')
  Cookies.remove('access-token')
  await api.post(`${url}/logout`)
  window.location.href = '/'
}

export default { login, me, register, logout }
