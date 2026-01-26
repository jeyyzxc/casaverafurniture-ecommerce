/**
 * Composable for bidirectional scroll-triggered rise-up animations
 * Observes elements with 'rise-up' classes and triggers animations when they enter viewport
 * Works for both scrolling down and scrolling up
 */
export function useScrollAnimation() {
  let observer: IntersectionObserver | null = null
  let lastScrollY = 0
  let scrollDirection: 'up' | 'down' = 'down'

  const isElementInViewport = (el: Element): boolean => {
    const rect = el.getBoundingClientRect()
    const windowHeight = window.innerHeight || document.documentElement.clientHeight
    const windowWidth = window.innerWidth || document.documentElement.clientWidth
    
    return (
      rect.top < windowHeight &&
      rect.bottom > 0 &&
      rect.left < windowWidth &&
      rect.right > 0
    )
  }

  const handleScroll = () => {
    const currentScrollY = window.scrollY || window.pageYOffset
    scrollDirection = currentScrollY > lastScrollY ? 'down' : 'up'
    lastScrollY = currentScrollY

    // Check all elements on scroll to handle bidirectional animation
    const elements = document.querySelectorAll(
      '.rise-up:not(.in-view), .rise-up-delay-1:not(.in-view), .rise-up-delay-2:not(.in-view), .rise-up-delay-3:not(.in-view), .rise-up-delay-4:not(.in-view), .rise-up-delay-5:not(.in-view)'
    )

    elements.forEach((el) => {
      if (isElementInViewport(el)) {
        el.classList.remove('rise-up-hidden')
        el.classList.add('in-view')
      }
    })
  }

  const initScrollAnimation = () => {
    // Disconnect existing observer if any
    if (observer) {
      observer.disconnect()
    }

    // Track scroll position
    lastScrollY = window.scrollY || window.pageYOffset

    // Create observer with options for bidirectional detection
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            // Remove hidden class and add in-view class to trigger animation
            entry.target.classList.remove('rise-up-hidden')
            entry.target.classList.add('in-view')
            // Optionally unobserve after animation to improve performance
            // observer?.unobserve(entry.target)
          } else {
            // Element left viewport - only re-hide if scrolling up and element is above viewport
            const rect = entry.target.getBoundingClientRect()
            const isAboveViewport = rect.bottom < 0
            
            // Only re-hide if scrolling up and element is above viewport
            // This allows re-animation when scrolling back up
            if (scrollDirection === 'up' && isAboveViewport) {
              entry.target.classList.remove('in-view')
              entry.target.classList.add('rise-up-hidden')
            }
          }
        })
      },
      {
        threshold: 0.1, // Trigger when 10% of element is visible
        rootMargin: '0px 0px -50px 0px' // Trigger slightly before element enters viewport
      }
    )

    // Process all elements with rise-up classes
    const processElements = () => {
      const elements = document.querySelectorAll(
        '.rise-up, .rise-up-delay-1, .rise-up-delay-2, .rise-up-delay-3, .rise-up-delay-4, .rise-up-delay-5'
      )
      
      elements.forEach((el) => {
        // Skip if already processed and in view
        if (el.classList.contains('in-view') && isElementInViewport(el)) {
          return
        }

        // Check if element is already in viewport
        if (isElementInViewport(el)) {
          // Element is already visible, mark as in-view (no animation needed)
          el.classList.add('in-view')
          el.classList.remove('rise-up-hidden')
        } else {
          // Element is outside viewport, hide it and observe for when it enters
          el.classList.add('rise-up-hidden')
          if (observer) {
            observer.observe(el)
          }
        }
      })
    }

    // Initial processing - wait a bit for DOM to be ready
    setTimeout(() => {
      processElements()
    }, 100)

    // Add scroll listener for bidirectional detection
    window.addEventListener('scroll', handleScroll, { passive: true })
  }

  const destroyScrollAnimation = () => {
    if (observer) {
      observer.disconnect()
      observer = null
    }
    window.removeEventListener('scroll', handleScroll)
  }

  // Re-initialize (useful for route changes)
  const reinitScrollAnimation = () => {
    destroyScrollAnimation()
    setTimeout(() => {
      initScrollAnimation()
    }, 200)
  }

  return {
    initScrollAnimation,
    destroyScrollAnimation,
    reinitScrollAnimation
  }
}
