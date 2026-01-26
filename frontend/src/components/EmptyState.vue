<template>
  <div class="empty-state">
    <div class="empty-state-content">
      <div class="empty-state-icon" v-if="icon">
        <component :is="icon" />
      </div>
      <div class="empty-state-icon" v-else>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 640" fill="currentColor">
          <path d="M320 576C461.4 576 576 461.4 576 320C576 178.6 461.4 64 320 64C178.6 64 64 178.6 64 320C64 461.4 178.6 576 320 576zM410.6 462.1C390.2 434.1 357.2 416 320 416C282.8 416 249.8 434.1 229.4 462.1C221.6 472.8 206.6 475.2 195.9 467.4C185.2 459.6 182.8 444.6 190.6 433.9C219.7 394 266.8 368 320 368C373.2 368 420.3 394 449.4 433.9C457.2 444.6 454.8 459.6 444.1 467.4C433.4 475.2 418.4 472.8 410.6 462.1zM208 272C208 254.3 222.3 240 240 240C257.7 240 272 254.3 272 272C272 289.7 257.7 304 240 304C222.3 304 208 289.7 208 272zM400 240C417.7 240 432 254.3 432 272C432 289.7 417.7 304 400 304C382.3 304 368 289.7 368 272C368 254.3 382.3 240 400 240z"/>
        </svg>
      </div>
      <h3 class="empty-state-title">{{ title }}</h3>
      <p v-if="description" class="empty-state-description">{{ description }}</p>
      <p v-if="message" class="empty-state-message">{{ message }}</p>
      <slot name="action">
        <button v-if="actionLabel && actionHandler" @click="actionHandler" class="empty-state-button">
          {{ actionLabel }}
        </button>
      </slot>
    </div>
  </div>
</template>

<script setup lang="ts">
interface Props {
  title: string
  description?: string
  message?: string
  icon?: any
  actionLabel?: string
  actionHandler?: () => void
}

withDefaults(defineProps<Props>(), {
  description: undefined,
  message: undefined,
  icon: undefined,
  actionLabel: undefined,
  actionHandler: undefined,
})
</script>

<style scoped>
.empty-state {
  padding: 4rem 2rem;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 400px;
}

.empty-state-content {
  text-align: center;
  max-width: 500px;
}

.empty-state-icon {
  width: 120px;
  height: 120px;
  margin: 0 auto 1.5rem;
  color: #ccc;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-state-icon svg {
  width: 100%;
  height: 100%;
}

.empty-state-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0 0 0.75rem;
}

.empty-state-description {
  font-size: 1rem;
  color: #666;
  margin: 0 0 0.5rem;
  line-height: 1.6;
}

.empty-state-message {
  font-size: 0.9rem;
  color: #999;
  margin: 0 0 1.5rem;
  line-height: 1.5;
}

.empty-state-button {
  padding: 0.75rem 2rem;
  background: #c9a050;
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
  transition: background 0.3s ease;
}

.empty-state-button:hover {
  background: #a6833e;
}
</style>
