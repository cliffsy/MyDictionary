import api from './axios'

const url = '/favorites'

async function list() {
  try {
    const { data } = await api.get(`${url}`)
    return data
  } catch (e) {
    throw e
  }
}

async function show(word) {
  try {
    const { data } = await api.get(`${url}/${word}`)
    return data
  } catch (e) {
    throw e
  }
}

async function save(form) {
  try {
    var result = null
    if (form?.id) {
      result = await api.put(`${url}/${form.id}`, form)
    } else {
      result = await api.post(`${url}`, form)
    }
    return result
  } catch (e) {
    throw e
  }
}

async function remove(form) {
  try {
    if (form?.id) {
      await api.delete(`${url}/${form.id}`)
    }
  } catch (e) {
    throw e
  }
}

export default { list, show, save, remove }
