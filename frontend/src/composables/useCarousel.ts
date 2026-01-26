/**
 * Composable for infinite carousel functionality
 */
import { ref, computed, onUnmounted, nextTick, watch } from 'vue'
import type { Ref } from 'vue'

interface CarouselOptions {
  cardWidth: number
  gap: number
  autoPlayInterval?: number
  items: Ref<any[]>
}

export function useCarousel(options: CarouselOptions) {
  const { cardWidth, gap, autoPlayInterval = 4000, items } = options

  const slideStep = cardWidth + gap
  const centerOffset = -(cardWidth / 2)

  const currentIndex = ref(0)
  const position = ref(0)
  const isAnimating = ref(false)
  const isResetting = ref(false)
  const trackRef = ref<HTMLElement | null>(null)

  let interval: ReturnType<typeof setInterval> | null = null

  const totalReal = computed(() => items.value.length)
  const startIndex = computed(() => totalReal.value * 2) // Start in third copy
  const carouselItems = computed(() => {
    if (items.value.length === 0) return []
    // Create 6 copies for seamless infinite loop
    return [...items.value, ...items.value, ...items.value, ...items.value, ...items.value, ...items.value]
  })

  const moveToIndex = (index: number, animate = true) => {
    if (!trackRef.value || totalReal.value === 0 || items.value.length === 0) {
      return
    }

    if (!animate) {
      isResetting.value = true
    }

    position.value = centerOffset - (index * slideStep)
    currentIndex.value = index
    isAnimating.value = animate

    if (!animate) {
      void trackRef.value.offsetWidth
      requestAnimationFrame(() => {
        requestAnimationFrame(() => {
          if (trackRef.value) {
            isResetting.value = false
          }
        })
      })
    }
  }

  const onTransitionEnd = () => {
    if (totalReal.value === 0 || items.value.length === 0) return

    isAnimating.value = false
    const total = totalReal.value

    // Seamlessly reset when reaching boundaries
    if (currentIndex.value >= total * 4) {
      moveToIndex(currentIndex.value - total * 2, false)
    } else if (currentIndex.value < total * 2) {
      moveToIndex(currentIndex.value + total * 2, false)
    }
  }

  const start = () => {
    if (!trackRef.value || totalReal.value === 0 || items.value.length === 0) {
      if (trackRef.value && items.value.length === 0) {
        requestAnimationFrame(() => {
          if (trackRef.value && items.value.length > 0 && totalReal.value > 0) {
            start()
          }
        })
      }
      return
    }

    if (interval) {
      clearInterval(interval)
    }

    moveToIndex(startIndex.value, false)

    requestAnimationFrame(() => {
      interval = setInterval(() => {
        if (!isAnimating.value && !document.hidden && trackRef.value && totalReal.value > 0 && items.value.length > 0) {
          isAnimating.value = true
          currentIndex.value++
          moveToIndex(currentIndex.value, true)
        }
      }, autoPlayInterval)
    })
  }

  const stop = () => {
    if (interval) {
      clearInterval(interval)
      interval = null
    }
  }

  // Watch for items changes
  watch(items, (newItems) => {
    if (newItems.length > 0) {
      stop()
      nextTick().then(() => {
        start()
      })
    }
  }, { immediate: false })

  onUnmounted(() => {
    stop()
  })

  return {
    trackRef,
    currentIndex,
    position,
    isAnimating,
    isResetting,
    carouselItems,
    moveToIndex,
    onTransitionEnd,
    start,
    stop,
  }
}
