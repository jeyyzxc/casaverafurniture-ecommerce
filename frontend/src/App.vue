<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { RouterView } from 'vue-router'
import NotificationContainer from './components/NotificationContainer.vue'
import { useScrollAnimation } from './composables/useScrollAnimation'

const route = useRoute()

// Initialize scroll-triggered animations globally
const { initScrollAnimation, destroyScrollAnimation, reinitScrollAnimation } = useScrollAnimation()

onMounted(() => {
  // Initialize scroll animations after a brief delay to ensure DOM is ready
  setTimeout(() => {
    initScrollAnimation()
  }, 300)
})

// Re-initialize on route changes to handle new content
watch(() => route.path, () => {
  setTimeout(() => {
    reinitScrollAnimation()
  }, 300)
})

onUnmounted(() => {
  destroyScrollAnimation()
})
</script>

<template>
  <RouterView />
  <NotificationContainer />
</template>
