<template>
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<script setup lang="ts">
import { useAuthStore } from '~/stores/auth'

const authStore = useAuthStore()

// Recharger l'utilisateur au montage si un token existe
onMounted(() => {
  authStore.fetchMe()
})
</script>
