<template>
  <section class="hero-section" :class="sizeClass">
    <div class="hero-slider">
      <div 
        v-for="(slide, index) in slides" 
        :key="index"
        class="hero-slide"
        :class="{ active: currentSlide === index }"
        :style="{ backgroundImage: `url(${slide})` }"
      ></div>
    </div>
    <div class="hero-overlay"></div>
    <div class="hero-content">
      <h1 class="hero-title">{{ title }}</h1>
      <p class="hero-subtitle">{{ subtitle }}</p>
      <span v-if="tagline" class="hero-tagline">{{ tagline }}</span>
      <slot></slot>
    </div>
    
    <!-- Scroll Indicator (only for full-height hero) -->
    <div v-if="size === 'full'" class="scroll-indicator">
      <div class="scroll-mouse">
        <div class="scroll-wheel"></div>
      </div>
      <span>Scroll to explore</span>
    </div>
  </section>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue'

// Import default hero images
import heroImg1 from '@/assets/images/f2.png'
import heroImg2 from '@/assets/images/f3.png'
import heroImg3 from '@/assets/images/f4.png'

// Props
interface Props {
  title: string
  subtitle: string
  tagline?: string
  size?: 'full' | 'large' | 'medium' | 'small'
  images?: string[]
}

const props = withDefaults(defineProps<Props>(), {
  size: 'medium',
  images: () => []
})

// Use custom images or default
const slides = computed(() => {
  return props.images.length > 0 ? props.images : [heroImg1, heroImg2, heroImg3]
})

// Size class
const sizeClass = computed(() => `hero--${props.size}`)

// Slider state
const currentSlide = ref(0)
let sliderInterval: ReturnType<typeof setInterval> | null = null

const startSlider = () => {
  sliderInterval = setInterval(() => {
    currentSlide.value = (currentSlide.value + 1) % slides.value.length
  }, 6000)
}

const stopSlider = () => {
  if (sliderInterval) {
    clearInterval(sliderInterval)
    sliderInterval = null
  }
}

onMounted(() => {
  startSlider()
})

onUnmounted(() => {
  stopSlider()
})
</script>

<style scoped>
/* ═══════════════════════════════════════════════════
   CSS VARIABLES
   ═══════════════════════════════════════════════════ */
.hero-section {
  --gold: #c9a050;
  --gold-light: #d4af37;
  --dark: #1a1a1a;
  --white: #ffffff;
  
  position: relative;
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  overflow: hidden;
  background-color: var(--dark);
}

/* ═══════════════════════════════════════════════════
   SIZE VARIANTS
   ═══════════════════════════════════════════════════ */
.hero--full {
  height: 100vh;
  height: 100dvh;
  min-height: 600px;
}

.hero--large {
  height: 55vh;
  min-height: 400px;
  max-height: 520px;
}

.hero--medium {
  height: 45vh;
  min-height: 340px;
  max-height: 440px;
}

.hero--small {
  height: 38vh;
  min-height: 280px;
  max-height: 360px;
}

/* ═══════════════════════════════════════════════════
   SLIDER
   ═══════════════════════════════════════════════════ */
.hero-slider {
  position: absolute;
  inset: 0;
  z-index: 0;
}

.hero-slide {
  position: absolute;
  inset: 0;
  background-size: cover;
  background-position: center;
  filter: blur(2px);
  transform: scale(1.05);
  opacity: 0;
  transition: opacity 1.5s ease-in-out;
}

.hero-slide.active {
  opacity: 1;
  animation: heroZoom 8s ease-in-out infinite alternate;
}

@keyframes heroZoom {
  from { transform: scale(1.05); }
  to { transform: scale(1.15); }
}

/* ═══════════════════════════════════════════════════
   OVERLAY
   ═══════════════════════════════════════════════════ */
.hero-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to bottom,
    rgba(0, 0, 0, 0.3) 0%,
    rgba(0, 0, 0, 0.5) 50%,
    rgba(0, 0, 0, 0.7) 100%
  );
  z-index: 1;
}

/* ═══════════════════════════════════════════════════
   CONTENT
   ═══════════════════════════════════════════════════ */
.hero-content {
  position: relative;
  z-index: 2;
  text-align: center;
  max-width: 800px;
  padding: 2rem;
}

.hero-title {
  font-family: 'Playfair Display', serif;
  font-size: clamp(2.5rem, 8vw, 4.5rem);
  font-weight: 600;
  line-height: 1.1;
  margin-bottom: 1rem;
  background: linear-gradient(
    90deg,
    var(--gold) 0%,
    var(--gold-light) 25%,
    var(--white) 50%,
    var(--gold-light) 75%,
    var(--gold) 100%
  );
  background-size: 200% 100%;
  -webkit-background-clip: text;
  background-clip: text;
  -webkit-text-fill-color: transparent;
  animation: gradientBackForth 4s ease-in-out infinite;
  filter: drop-shadow(0 0 20px rgba(255, 215, 0, 0.25));
}

@keyframes gradientBackForth {
  0%, 100% { background-position: 0% center; }
  50% { background-position: 100% center; }
}

.hero-subtitle {
  font-size: clamp(1rem, 2.5vw, 1.2rem);
  color: rgba(255, 255, 255, 0.9);
  line-height: 1.7;
  margin: 0;
  text-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
  max-width: 550px;
  margin-left: auto;
  margin-right: auto;
}

.hero-tagline {
  display: block;
  margin-top: 0.75rem;
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 3px;
  color: rgba(255, 255, 255, 0.6);
}

/* ═══════════════════════════════════════════════════
   SCROLL INDICATOR
   ═══════════════════════════════════════════════════ */
.scroll-indicator {
  position: absolute;
  bottom: 2rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 2;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.75rem;
  color: rgba(255, 255, 255, 0.6);
  font-size: 0.7rem;
  letter-spacing: 2px;
  text-transform: uppercase;
  animation: fadeInUp 1s ease 1s both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateX(-50%) translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
}

.scroll-mouse {
  width: 24px;
  height: 40px;
  border: 2px solid rgba(255, 255, 255, 0.4);
  border-radius: 12px;
  position: relative;
}

.scroll-wheel {
  position: absolute;
  top: 8px;
  left: 50%;
  transform: translateX(-50%);
  width: 4px;
  height: 8px;
  background: rgba(255, 255, 255, 0.6);
  border-radius: 2px;
  animation: scrollWheel 2s ease-in-out infinite;
}

@keyframes scrollWheel {
  0%, 100% {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
  }
  50% {
    opacity: 0.3;
    transform: translateX(-50%) translateY(12px);
  }
}

/* ═══════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════ */
@media (max-width: 768px) {
  .hero--full {
    min-height: 500px;
  }
  
  .hero--large {
    height: 45vh;
    min-height: 320px;
  }
  
  .hero--medium {
    height: 38vh;
    min-height: 280px;
  }
  
  .hero--small {
    height: 32vh;
    min-height: 240px;
  }
  
  .scroll-indicator {
    display: none;
  }
}
</style>
