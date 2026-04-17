<template>
  <div definePageMeta>
    <NuxtLink to="/login" />
  </div>
</template>

<script setup lang="ts">
definePageMeta({ layout: false })
import { useAuthStore } from '~/stores/auth'
const authStore = useAuthStore()
if (authStore.isAuthenticated) {
  navigateTo('/dashboard')
} else {
  navigateTo('/login')
}
</script>
