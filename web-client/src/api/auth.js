import api from './axios'
import axios from 'axios'

async function login(form) {
  await axios.get('/sanctum/csrf-cookie')
  await api.post('/auth/login', form)
}

export { login }
