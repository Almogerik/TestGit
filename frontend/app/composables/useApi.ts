// Composable pour toutes les requêtes API vers le backend Laravel
export const useApi = () => {
  const config = useRuntimeConfig()
  const baseURL = config.public.apiBase

  const getHeaders = () => {
    const token = useCookie('auth_token').value
    return token
      ? { Authorization: `Bearer ${token}`, Accept: 'application/json' }
      : { Accept: 'application/json' }
  }

  const get = <T>(path: string, params?: Record<string, any>) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'GET',
      headers: getHeaders(),
      params,
    })

  const post = <T>(path: string, body?: any) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'POST',
      headers: { ...getHeaders(), 'Content-Type': 'application/json' },
      body,
    })

  const put = <T>(path: string, body?: any) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'PUT',
      headers: { ...getHeaders(), 'Content-Type': 'application/json' },
      body,
    })

  const patch = <T>(path: string, body?: any) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'PATCH',
      headers: { ...getHeaders(), 'Content-Type': 'application/json' },
      body,
    })

  const del = <T>(path: string) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'DELETE',
      headers: getHeaders(),
    })

  const postForm = <T>(path: string, formData: FormData) =>
    $fetch<T>(`${baseURL}${path}`, {
      method: 'POST',
      headers: getHeaders(),
      body: formData,
    })

  return { get, post, put, patch, del, postForm }
}
