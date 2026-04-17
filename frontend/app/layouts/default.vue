<template>
  <div class="min-h-screen bg-gray-50 flex flex-col max-w-md mx-auto">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 px-4 py-3 flex items-center justify-between sticky top-0 z-10">
      <NuxtLink to="/" class="flex items-center gap-2">
        <span class="text-2xl font-bold text-green-700">Vanda</span>
        <span class="text-xs bg-green-700 text-white px-1.5 py-0.5 rounded font-medium">B2B</span>
      </NuxtLink>
      <div class="flex items-center gap-3">
        <NuxtLink v-if="cartStore.count > 0" to="/cart" class="relative">
          <span class="text-2xl">🛒</span>
          <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold">
            {{ cartStore.count }}
          </span>
        </NuxtLink>
        <button @click="authStore.logout" class="text-gray-400 text-sm">
          Sortir
        </button>
      </div>
    </header>

    <!-- Content -->
    <main class="flex-1 overflow-auto pb-20">
      <slot />
    </main>

    <!-- Bottom nav -->
    <nav class="fixed bottom-0 left-1/2 -translate-x-1/2 w-full max-w-md bg-white border-t border-gray-200 flex">
      <NuxtLink
        v-for="item in navItems"
        :key="item.to"
        :to="item.to"
        class="flex-1 flex flex-col items-center py-3 text-xs gap-1"
        :class="$route.path.startsWith(item.to) ? 'text-green-700 font-semibold' : 'text-gray-400'"
      >
        <span class="text-xl">{{ item.icon }}</span>
        {{ item.label }}
      </NuxtLink>
    </nav>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'
import { useCartStore } from '~/stores/cart'

const authStore = useAuthStore()
const cartStore = useCartStore()

const navItems = computed(() => {
  if (authStore.isWholesaler) {
    return [
      { to: '/dashboard', icon: '🏠', label: 'Accueil' },
      { to: '/products', icon: '📦', label: 'Produits' },
      { to: '/orders', icon: '📋', label: 'Commandes' },
      { to: '/profile', icon: '👤', label: 'Profil' },
    ]
  }
  return [
    { to: '/dashboard', icon: '🏠', label: 'Accueil' },
    { to: '/catalog', icon: '🛍️', label: 'Catalogue' },
    { to: '/orders', icon: '📋', label: 'Commandes' },
    { to: '/profile', icon: '👤', label: 'Profil' },
  ]
})
</script>
