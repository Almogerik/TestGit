import { defineStore } from 'pinia'

interface User {
  id: number
  name: string
  phone: string
  email?: string
  role: 'wholesaler' | 'retailer' | 'driver' | 'admin'
  store_name?: string
  address?: string
  city?: string
  is_active: boolean
}

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null)
  const token = useCookie<string | null>('auth_token', {
    maxAge: 60 * 60 * 24 * 30, // 30 jours
    sameSite: 'lax',
  })

  const isAuthenticated = computed(() => !!token.value && !!user.value)
  const isWholesaler = computed(() => user.value?.role === 'wholesaler')
  const isRetailer = computed(() => user.value?.role === 'retailer')

  const { post, get } = useApi()

  async function login(phone: string, password: string) {
    const data = await post<{ user: User; token: string }>('/auth/login', { phone, password })
    token.value = data.token
    user.value = data.user
    return data.user
  }

  async function register(payload: {
    name: string
    phone: string
    password: string
    password_confirmation: string
    role: 'wholesaler' | 'retailer'
    store_name?: string
    address?: string
    city?: string
  }) {
    const data = await post<{ user: User; token: string }>('/auth/register', payload)
    token.value = data.token
    user.value = data.user
    return data.user
  }

  async function logout() {
    try { await post('/auth/logout') } catch (_) {}
    token.value = null
    user.value = null
    navigateTo('/login')
  }

  async function fetchMe() {
    if (!token.value) return
    try {
      user.value = await get<User>('/auth/me')
    } catch (_) {
      token.value = null
      user.value = null
    }
  }

  return { user, token, isAuthenticated, isWholesaler, isRetailer, login, register, logout, fetchMe }
})
