<template>
  <Teleport to="body">
    <div v-if="promotion" class="promotion-popup-overlay" @click.self="closePopup">
      <div class="promotion-popup" @click.stop>
        <button class="promotion-popup-close" @click="closePopup" aria-label="Close">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="18" y1="6" x2="6" y2="18"/>
            <line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </button>
        
        <div class="promotion-popup-content">
          <div class="promotion-popup-icon">🎉</div>
          <h2 class="promotion-popup-title">Special Offer!</h2>
          <h3 class="promotion-popup-name">{{ promotion.name }}</h3>
          
          <div class="promotion-popup-discount">
            <span class="discount-amount">
              {{ promotion.discountType === 'percentage' 
                ? `${promotion.value}%` 
                : `₱${formatPrice(promotion.value)}` }} OFF
            </span>
            <span v-if="promotion.code" class="discount-code">Use code: <strong>{{ promotion.code }}</strong></span>
          </div>
          
          <p v-if="promotion.description" class="promotion-popup-description">
            {{ promotion.description }}
          </p>
          
          <div v-if="promotion.minOrderAmount" class="promotion-popup-min-order">
            Minimum order: ₱{{ formatPrice(promotion.minOrderAmount) }}
          </div>
          
          <div class="promotion-popup-actions">
            <button class="promotion-popup-btn-primary" @click="goToProducts">
              Shop Now
            </button>
            <button class="promotion-popup-btn-secondary" @click="closePopup">
              Maybe Later
            </button>
          </div>
        </div>
      </div>
    </div>
  </Teleport>
</template>

<script setup lang="ts">
import { Teleport } from 'vue'
import { useRouter } from 'vue-router'

interface Promotion {
  id: number
  name: string
  code: string
  description?: string
  discountType: 'percentage' | 'fixed' | 'free_shipping' | 'buy_x_get_y'
  value: number
  minOrderAmount?: number
}

const props = defineProps<{
  promotion: Promotion | null
}>()

const emit = defineEmits<{
  close: []
}>()

const router = useRouter()

const formatPrice = (price: number): string => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const closePopup = () => {
  emit('close')
}

const goToProducts = () => {
  closePopup()
  router.push('/products')
}
</script>

<style scoped>
.promotion-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  animation: fadeIn 0.3s ease;
  padding: 1rem;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.promotion-popup {
  position: relative;
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  border-radius: 20px;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
  animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  overflow: hidden;
}

@keyframes slideUp {
  from {
    transform: translateY(50px) scale(0.9);
    opacity: 0;
  }
  to {
    transform: translateY(0) scale(1);
    opacity: 1;
  }
}

.promotion-popup-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(255, 255, 255, 0.2);
  border: none;
  border-radius: 50%;
  width: 36px;
  height: 36px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
  z-index: 10;
  color: white;
}

.promotion-popup-close:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: rotate(90deg);
}

.promotion-popup-close svg {
  width: 20px;
  height: 20px;
}

.promotion-popup-content {
  padding: 3rem 2rem 2rem;
  text-align: center;
  color: white;
}

.promotion-popup-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  animation: bounce 1s ease infinite;
}

@keyframes bounce {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.promotion-popup-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  margin: 0 0 0.5rem;
  color: white;
}

.promotion-popup-name {
  font-size: 1.5rem;
  font-weight: 600;
  margin: 0 0 1.5rem;
  color: white;
}

.promotion-popup-discount {
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 1.5rem;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.discount-amount {
  font-size: 2.5rem;
  font-weight: 700;
  color: white;
  text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
}

.discount-code {
  font-size: 1rem;
  color: rgba(255, 255, 255, 0.9);
}

.discount-code strong {
  font-size: 1.2rem;
  font-weight: 700;
  color: white;
  background: rgba(255, 255, 255, 0.2);
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  display: inline-block;
  margin-left: 0.5rem;
}

.promotion-popup-description {
  font-size: 1rem;
  line-height: 1.6;
  margin: 0 0 1rem;
  color: rgba(255, 255, 255, 0.95);
}

.promotion-popup-min-order {
  font-size: 0.9rem;
  color: rgba(255, 255, 255, 0.85);
  margin-bottom: 1.5rem;
  font-style: italic;
}

.promotion-popup-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  flex-wrap: wrap;
}

.promotion-popup-btn-primary,
.promotion-popup-btn-secondary {
  padding: 0.875rem 2rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 140px;
}

.promotion-popup-btn-primary {
  background: #000000;
  color: white;
}

.promotion-popup-btn-primary:hover {
  background: #333333;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.promotion-popup-btn-secondary {
  background: rgba(255, 255, 255, 0.2);
  color: white;
  backdrop-filter: blur(10px);
}

.promotion-popup-btn-secondary:hover {
  background: rgba(255, 255, 255, 0.3);
  transform: translateY(-2px);
}

@media (max-width: 640px) {
  .promotion-popup {
    max-width: 90%;
  }

  .promotion-popup-content {
    padding: 2rem 1.5rem 1.5rem;
  }

  .promotion-popup-title {
    font-size: 1.5rem;
  }

  .promotion-popup-name {
    font-size: 1.25rem;
  }

  .discount-amount {
    font-size: 2rem;
  }

  .promotion-popup-actions {
    flex-direction: column;
  }

  .promotion-popup-btn-primary,
  .promotion-popup-btn-secondary {
    width: 100%;
  }
}
</style>
