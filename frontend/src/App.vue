<script setup lang="ts">
import { onMounted, onUnmounted, watch } from 'vue'
import { useRoute } from 'vue-router'
import { RouterView } from 'vue-router'
import NotificationContainer from './components/NotificationContainer.vue'
import { useScrollAnimation } from './composables/useScrollAnimation'

const route = useRoute()

const { initScrollAnimation, destroyScrollAnimation, reinitScrollAnimation } = useScrollAnimation()

onMounted(() => {
  requestAnimationFrame(() => {
    initScrollAnimation()
  })
})

watch(() => route.path, () => {
  requestAnimationFrame(() => {
    reinitScrollAnimation()
  })
})

onUnmounted(() => {
  destroyScrollAnimation()
})
</script>

<template>
  <RouterView />
  <NotificationContainer />
</template>
