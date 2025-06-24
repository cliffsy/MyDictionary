import api from './axios'

const url = '/dictionary'

async function search(query) {
  const { data } = await api.get(`${url}/search`, {
    params: {
      q: query,
    },
  })
  return data
}

export default { search }
