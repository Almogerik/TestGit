<template>
  <div class="pb-24">
    <!-- Header -->
    <div class="bg-white border-b px-4 py-4 sticky top-0 z-10">
      <h1 class="text-lg font-bold text-gray-800">Mon profil</h1>
    </div>

    <div v-if="!auth.user" class="flex justify-center py-12">
      <div class="w-8 h-8 border-4 border-green-700 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <div v-else class="p-4 space-y-4">
      <!-- Avatar + nom -->
      <div class="card flex items-center gap-4">
        <div class="w-16 h-16 rounded-full bg-green-700 flex items-center justify-center text-white text-2xl font-bold flex-shrink-0">
          {{ auth.user.name.charAt(0).toUpperCase() }}
        </div>
        <div>
          <p class="font-bold text-gray-800 text-lg">{{ auth.user.name }}</p>
          <p class="text-sm text-gray-500">{{ auth.user.phone }}</p>
          <span :class="auth.isWholesaler ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'" class="text-xs px-2 py-0.5 rounded-full font-medium">
            {{ auth.isWholesaler ? 'Grossiste' : 'Détaillant' }}
          </span>
        </div>
      </div>

      <!-- Informations -->
      <div class="card space-y-3">
        <h2 class="font-semibold text-gray-700">Informations</h2>
        <div v-if="!editing" class="space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-500">Nom complet</span>
            <span class="font-medium text-gray-800">{{ auth.user.name }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-gray-500">Téléphone</span>
            <span class="font-medium text-gray-800">{{ auth.user.phone }}</span>
          </div>
          <div v-if="auth.user.email" class="flex justify-between">
            <span class="text-gray-500">Email</span>
            <span class="font-medium text-gray-800">{{ auth.user.email }}</span>
          </div>
          <div v-if="auth.user.store_name" class="flex justify-between">
            <span class="text-gray-500">Commerce</span>
            <span class="font-medium text-gray-800">{{ auth.user.store_name }}</span>
          </div>
          <div v-if="auth.user.city" class="flex justify-between">
            <span class="text-gray-500">Ville</span>
            <span class="font-medium text-gray-800">{{ auth.user.city }}</span>
          </div>
          <div v-if="auth.user.address" class="flex justify-between">
            <span class="text-gray-500">Adresse</span>
            <span class="font-medium text-gray-800 text-right max-w-48">{{ auth.user.address }}</span>
          </div>
          <button @click="editing = true" class="btn-outline w-full mt-2 text-sm">Modifier mes infos</button>
        </div>

        <!-- Formulaire de modification -->
        <form v-else @submit.prevent="saveProfile" class="space-y-3 text-sm">
          <div>
            <label class="block text-gray-600 mb-1">Nom complet</label>
            <input v-model="form.name" type="text" class="input-field" required />
          </div>
          <div>
            <label class="block text-gray-600 mb-1">Email (optionnel)</label>
            <input v-model="form.email" type="email" class="input-field" />
          </div>
          <div>
            <label class="block text-gray-600 mb-1">Nom du commerce</label>
            <input v-model="form.store_name" type="text" class="input-field" />
          </div>
          <div>
            <label class="block text-gray-600 mb-1">Ville</label>
            <select v-model="form.city" class="input-field">
              <option value="">-- Choisir --</option>
              <option>Brazzaville</option>
              <option>Pointe-Noire</option>
              <option>Dolisie</option>
              <option>Nkayi</option>
              <option>Impfondo</option>
              <option>Ouesso</option>
              <option>Autre</option>
            </select>
          </div>
          <div>
            <label class="block text-gray-600 mb-1">Adresse</label>
            <input v-model="form.address" type="text" class="input-field" placeholder="Quartier, avenue..." />
          </div>
          <div v-if="profileError" class="text-red-600 text-xs bg-red-50 px-3 py-2 rounded-lg">{{ profileError }}</div>
          <div class="flex gap-3">
            <button type="button" @click="editing = false" class="btn-outline flex-1">Annuler</button>
            <button type="submit" :disabled="saving" class="btn-primary flex-1">
              {{ saving ? 'Sauvegarde...' : 'Enregistrer' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Changer le mot de passe -->
      <div class="card space-y-3">
        <h2 class="font-semibold text-gray-700">Changer le mot de passe</h2>
        <div v-if="!changingPassword">
          <button @click="changingPassword = true" class="btn-outline w-full text-sm">Changer le mot de passe</button>
        </div>
        <form v-else @submit.prevent="savePassword" class="space-y-3 text-sm">
          <div>
            <label class="block text-gray-600 mb-1">Nouveau mot de passe</label>
            <input v-model="pwForm.password" type="password" class="input-field" minlength="8" required />
          </div>
          <div>
            <label class="block text-gray-600 mb-1">Confirmer le mot de passe</label>
            <input v-model="pwForm.password_confirmation" type="password" class="input-field" minlength="8" required />
          </div>
          <div v-if="pwError" class="text-red-600 text-xs bg-red-50 px-3 py-2 rounded-lg">{{ pwError }}</div>
          <div v-if="pwSuccess" class="text-green-700 text-xs bg-green-50 px-3 py-2 rounded-lg">{{ pwSuccess }}</div>
          <div class="flex gap-3">
            <button type="button" @click="changingPassword = false" class="btn-outline flex-1">Annuler</button>
            <button type="submit" :disabled="savingPw" class="btn-primary flex-1">
              {{ savingPw ? '...' : 'Confirmer' }}
            </button>
          </div>
        </form>
      </div>

      <!-- Déconnexion -->
      <div class="card">
        <button @click="auth.logout()" class="w-full flex items-center justify-center gap-2 text-red-600 font-semibold py-1">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
          </svg>
          Se déconnecter
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
definePageMeta({ middleware: 'auth', layout: 'default' })

const auth = useAuthStore()
const { put } = useApi()

const editing = ref(false)
const saving = ref(false)
const profileError = ref('')

const changingPassword = ref(false)
const savingPw = ref(false)
const pwError = ref('')
const pwSuccess = ref('')

const form = ref({
  name: '',
  email: '',
  store_name: '',
  city: '',
  address: '',
})

const pwForm = ref({
  password: '',
  password_confirmation: '',
})

watch(() => auth.user, (user) => {
  if (user) {
    form.value = {
      name: user.name,
      email: user.email ?? '',
      store_name: user.store_name ?? '',
      city: user.city ?? '',
      address: user.address ?? '',
    }
  }
}, { immediate: true })

async function saveProfile() {
  saving.value = true
  profileError.value = ''
  try {
    const payload: Record<string, any> = { name: form.value.name }
    if (form.value.email) payload.email = form.value.email
    if (form.value.store_name) payload.store_name = form.value.store_name
    if (form.value.city) payload.city = form.value.city
    if (form.value.address) payload.address = form.value.address
    await put('/auth/me', payload)
    await auth.fetchMe()
    editing.value = false
  } catch (e: any) {
    profileError.value = e?.data?.message ?? 'Erreur lors de la mise à jour'
  } finally {
    saving.value = false
  }
}

async function savePassword() {
  if (pwForm.value.password !== pwForm.value.password_confirmation) {
    pwError.value = 'Les mots de passe ne correspondent pas'
    return
  }
  savingPw.value = true
  pwError.value = ''
  pwSuccess.value = ''
  try {
    await put('/auth/me', {
      password: pwForm.value.password,
      password_confirmation: pwForm.value.password_confirmation,
    })
    pwSuccess.value = 'Mot de passe mis à jour avec succès'
    pwForm.value = { password: '', password_confirmation: '' }
    changingPassword.value = false
  } catch (e: any) {
    pwError.value = e?.data?.message ?? 'Erreur lors du changement de mot de passe'
  } finally {
    savingPw.value = false
  }
}
</script>
