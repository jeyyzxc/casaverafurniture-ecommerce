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
    
    if (observer) {
      observer.disconnect()
    }

    
    lastScrollY = window.scrollY || window.pageYOffset

    
    observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            
            entry.target.classList.remove('rise-up-hidden')
            entry.target.classList.add('in-view')
            
            
          } else {
            
            const rect = entry.target.getBoundingClientRect()
            const isAboveViewport = rect.bottom < 0
            
            
            
            if (scrollDirection === 'up' && isAboveViewport) {
              entry.target.classList.remove('in-view')
              entry.target.classList.add('rise-up-hidden')
            }
          }
        })
      },
      {
        threshold: 0.1, 
        rootMargin: '0px 0px -50px 0px' 
      }
    )

    
    const processElements = () => {
      const elements = document.querySelectorAll(
        '.rise-up, .rise-up-delay-1, .rise-up-delay-2, .rise-up-delay-3, .rise-up-delay-4, .rise-up-delay-5'
      )
      
      elements.forEach((el) => {
        
        if (el.classList.contains('in-view') && isElementInViewport(el)) {
          return
        }

        
        if (isElementInViewport(el)) {
          
          el.classList.add('in-view')
          el.classList.remove('rise-up-hidden')
        } else {
          
          el.classList.add('rise-up-hidden')
          if (observer) {
            observer.observe(el)
          }
        }
      })
    }

    
    setTimeout(() => {
      processElements()
    }, 100)

    
    window.addEventListener('scroll', handleScroll, { passive: true })
  }

  const destroyScrollAnimation = () => {
    if (observer) {
      observer.disconnect()
      observer = null
    }
    window.removeEventListener('scroll', handleScroll)
  }

  
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
