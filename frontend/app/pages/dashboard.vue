<template>
  <div class="p-4 space-y-5">
    <!-- Bonjour -->
    <div class="pt-2">
      <p class="text-gray-500 text-sm">Bonjour 👋</p>
      <h2 class="text-2xl font-bold text-gray-800">{{ authStore.user?.name }}</h2>
      <p class="text-green-700 font-medium text-sm">{{ authStore.user?.store_name }}</p>
    </div>

    <!-- Stats rapides (grossiste) -->
    <div v-if="authStore.isWholesaler" class="grid grid-cols-2 gap-3">
      <div class="card text-center">
        <p class="text-3xl font-bold text-green-700">{{ stats.todayOrders }}</p>
        <p class="text-xs text-gray-500 mt-1">Commandes aujourd'hui</p>
      </div>
      <div class="card text-center">
        <p class="text-3xl font-bold text-orange-500">{{ stats.pendingOrders }}</p>
        <p class="text-xs text-gray-500 mt-1">En attente</p>
      </div>
    </div>

    <!-- Actions rapides (détaillant) -->
    <div v-if="authStore.isRetailer" class="space-y-3">
      <NuxtLink to="/catalog" class="btn-primary block text-center">
        🛒 Passer une commande
      </NuxtLink>
      <NuxtLink to="/orders" class="btn-outline block text-center">
        📦 Mes commandes
      </NuxtLink>
    </div>

    <!-- Actions rapides (grossiste) -->
    <div v-if="authStore.isWholesaler" class="space-y-3">
      <NuxtLink to="/orders" class="btn-primary block text-center">
        📋 Voir les commandes
      </NuxtLink>
      <NuxtLink to="/products" class="btn-outline block text-center">
        📦 Gérer mon catalogue
      </NuxtLink>
    </div>

    <!-- Dernières commandes -->
    <div>
      <h3 class="font-bold text-gray-700 mb-3">Activité récente</h3>
      <div v-if="loadingOrders" class="text-center py-6 text-gray-400">Chargement...</div>
      <div v-else-if="recentOrders.length === 0" class="card text-center text-gray-400 py-6">
        Aucune commande pour l'instant
      </div>
      <div v-else class="space-y-2">
        <NuxtLink
          v-for="order in recentOrders"
          :key="order.id"
          :to="`/orders/${order.id}`"
          class="card flex items-center justify-between"
        >
          <div>
            <p class="font-semibold text-sm text-gray-800">{{ order.reference }}</p>
            <p class="text-xs text-gray-500">
              {{ authStore.isWholesaler ? order.retailer?.store_name : order.wholesaler?.store_name }}
            </p>
          </div>
          <div class="text-right">
            <span :class="`badge-${order.status}`">{{ statusLabel(order.status) }}</span>
            <p class="text-xs text-gray-500 mt-1">{{ formatPrice(order.total_amount) }}</p>
          </div>
        </NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

definePageMeta({ layout: 'default', middleware: 'auth' })

const authStore = useAuthStore()
const { get } = useApi()

const loadingOrders = ref(true)
const recentOrders = ref<any[]>([])
const stats = ref({ todayOrders: 0, pendingOrders: 0 })

onMounted(async () => {
  try {
    const data = await get<any>('/orders', { per_page: 5 })
    recentOrders.value = data.data
    stats.value.pendingOrders = data.data.filter((o: any) => o.status === 'pending').length
    stats.value.todayOrders = data.data.length
  } catch (_) {}
  loadingOrders.value = false
})

const statusLabel = (s: string) => ({
  pending: 'En attente', confirmed: 'Confirmée',
  preparing: 'En préparation', delivering: 'En livraison',
  delivered: 'Livrée', cancelled: 'Annulée',
}[s] || s)

const formatPrice = (n: string | number) =>
  new Intl.NumberFormat('fr-CG', { style: 'currency', currency: 'XAF', maximumFractionDigits: 0 }).format(Number(n))
</script>
