<template>
  <div class="w-full">
    <div class="text-center mb-8">
      <h1 class="text-4xl font-bold text-green-700">Vanda <span class="text-orange-500">B2B</span></h1>
      <p class="text-gray-500 mt-2 text-sm">Créer votre compte</p>
    </div>

    <div class="card w-full">
      <!-- Choix du rôle -->
      <div class="flex rounded-xl overflow-hidden border border-gray-200 mb-6">
        <button
          @click="form.role = 'retailer'"
          class="flex-1 py-3 text-sm font-semibold transition-colors"
          :class="form.role === 'retailer' ? 'bg-green-700 text-white' : 'text-gray-500 bg-white'"
        >
          🛍️ Détaillant
        </button>
        <button
          @click="form.role = 'wholesaler'"
          class="flex-1 py-3 text-sm font-semibold transition-colors"
          :class="form.role === 'wholesaler' ? 'bg-green-700 text-white' : 'text-gray-500 bg-white'"
        >
          🏭 Grossiste
        </button>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet</label>
          <input v-model="form.name" type="text" placeholder="Votre nom" class="input-field" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
          <input v-model="form.phone" type="tel" placeholder="06 XXX XX XX" class="input-field" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            {{ form.role === 'wholesaler' ? 'Nom du dépôt' : 'Nom de la boutique' }}
          </label>
          <input v-model="form.store_name" type="text" placeholder="Ex: Dépôt Central, Chez Mama Cécile" class="input-field" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Quartier / Adresse</label>
          <input v-model="form.address" type="text" placeholder="Ex: Poto-Poto, rue principale" class="input-field" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Mot de passe</label>
          <input v-model="form.password" type="password" placeholder="Min. 6 caractères" class="input-field" required />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Confirmer le mot de passe</label>
          <input v-model="form.password_confirmation" type="password" placeholder="Répéter le mot de passe" class="input-field" required />
        </div>

        <p v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg">{{ error }}</p>

        <button type="submit" class="btn-primary" :disabled="loading">
          {{ loading ? 'Inscription...' : 'Créer mon compte' }}
        </button>
      </form>
    </div>

    <p class="text-center mt-6 text-gray-600">
      Déjà un compte ?
      <NuxtLink to="/login" class="text-green-700 font-semibold"> Se connecter</NuxtLink>
    </p>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'auth' })

const authStore = useAuthStore()
const router = useRouter()

const form = reactive({
  name: '', phone: '', store_name: '', address: '',
  password: '', password_confirmation: '',
  role: 'retailer' as 'retailer' | 'wholesaler',
  city: 'Brazzaville',
})
const loading = ref(false)
const error = ref('')

async function handleRegister() {
  loading.value = true
  error.value = ''
  try {
    await authStore.register(form)
    router.push('/dashboard')
  } catch (e: any) {
    const errors = e?.data?.errors
    if (errors) {
      error.value = Object.values(errors).flat().join(' ')
    } else {
      error.value = e?.data?.message || 'Une erreur est survenue.'
    }
  } finally {
    loading.value = false
  }
}
</script>
