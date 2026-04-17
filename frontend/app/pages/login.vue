<template>
  <div class="w-full">
    <!-- Logo -->
    <div class="text-center mb-10">
      <h1 class="text-4xl font-bold text-green-700">Vanda <span class="text-orange-500">B2B</span></h1>
      <p class="text-gray-500 mt-2 text-sm">Commerce de gros simplifié</p>
    </div>

    <!-- Formulaire -->
    <div class="card w-full">
      <h2 class="text-xl font-bold text-gray-800 mb-6">Connexion</h2>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Numéro de téléphone</label>
          <input
            v-model="form.phone"
            type="tel"
            placeholder="06 XXX XX XX"
            class="input-field"
            required
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input
            v-model="form.password"
            type="password"
            placeholder="••••••"
            class="input-field"
            required
          />
        </div>

        <p v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">{{ error }}</p>

        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Connexion...' : 'Se connecter' }}
        </button>
      </form>
    </div>

    <p class="text-center mt-6 text-gray-600">
      Pas encore de compte ?
      <NuxtLink to="/register" class="text-green-700 font-semibold"> S'inscrire</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({ phone: '', password: '' })
const loading = ref(false)
const error = ref('')

async function handleLogin() {
  loading.value = true
  error.value = ''
  try {
    await authStore.login(form.phone, form.password)
    router.push('/dashboard')
  } catch (e: any) {
    error.value = e?.data?.message || 'Identifiants incorrects. Veuillez réessayer.'
  } finally {
    loading.value = false
  }
}
</script>
