import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Real-time service using enhanced polling with WebSocket support
// For development: uses polling
// For production: can use Pusher WebSockets

class RealtimeService {
  private echo: Echo | null = null
  private pollingIntervals: Map<string, number> = new Map()
  private listeners: Map<string, Set<Function>> = new Map()
  private isWebSocketEnabled = false

  constructor() {
    // Check if WebSocket is enabled (Pusher configured)
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY
    if (pusherKey) {
      this.initializeWebSocket(pusherKey)
    } else {
      // Use polling fallback
      this.initializePolling()
    }
  }

  private initializeWebSocket(pusherKey: string) {
    try {
      window.Pusher = Pusher

      this.echo = new Echo({
        broadcaster: 'pusher',
        key: pusherKey,
        cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'mt1',
        forceTLS: true,
        encrypted: true,
        authEndpoint: `${import.meta.env.VITE_API_URL}/broadcasting/auth`,
        auth: {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('user_token') || localStorage.getItem('admin_token') || ''}`,
          },
        },
      })

      this.isWebSocketEnabled = true
      console.log('WebSocket (Pusher) initialized')
    } catch (error) {
      console.warn('WebSocket initialization failed, falling back to polling:', error)
      this.initializePolling()
    }
  }

  private initializePolling() {
    console.log('Using polling for real-time updates')
    // Polling will be set up per channel
  }

  // Listen to products channel
  listenToProducts(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('products')
        .listen('.product.created', (data: any) => callback('product.created', data))
        .listen('.product.updated', (data: any) => callback('product.updated', data))
        .listen('.product.deleted', (data: any) => callback('product.deleted', data))
        .listen('.stock.changed', (data: any) => callback('stock.changed', data))
    } else {
      // Polling fallback
      this.startPolling('products', callback, 3000) // Poll every 3 seconds
    }
  }

  // Listen to orders channel
  listenToOrders(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('orders')
        .listen('.order.created', (data: any) => callback('order.created', data))
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
    } else {
      // Polling fallback
      this.startPolling('orders', callback, 5000) // Poll every 5 seconds
    }
  }

  // Listen to cart channel
  listenToCart(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('cart')
        .listen('.cart.updated', (data: any) => callback('cart.updated', data))
    } else {
      // Polling fallback - cart updates are immediate via API responses
      // No polling needed as cart updates happen synchronously
    }
  }

  // Listen to homepage channel
  listenToHomepage(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('homepage')
        .listen('.homepage.updated', (data: any) => callback('homepage.updated', data))
    } else {
      // Polling fallback
      this.startPolling('homepage', callback, 10000) // Poll every 10 seconds
    }
  }

  // Listen to admin channel
  listenToAdmin(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('admin')
        .listen('.stock.changed', (data: any) => callback('stock.changed', data))
        .listen('.order.created', (data: any) => callback('order.created', data))
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
    } else {
      // Polling fallback
      this.startPolling('admin', callback, 3000) // Poll every 3 seconds
    }
  }

  // Private user channel
  listenToUser(userId: number, callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.private(`user.${userId}`)
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
    } else {
      // Polling fallback - handled by component-level polling
    }
  }

  private startPolling(channel: string, callback: Function, interval: number) {
    // Stop existing polling for this channel
    this.stopPolling(channel)

    // Store callback
    if (!this.listeners.has(channel)) {
      this.listeners.set(channel, new Set())
    }
    this.listeners.get(channel)!.add(callback)

    // Note: Actual polling is handled by individual components
    // This service provides the structure for WebSocket connections
    // Components use their own polling as fallback
  }

  private stopPolling(channel: string) {
    const intervalId = this.pollingIntervals.get(channel)
    if (intervalId) {
      clearInterval(intervalId)
      this.pollingIntervals.delete(channel)
    }
  }

  disconnect() {
    if (this.echo) {
      this.echo.disconnect()
    }
    // Clear all polling intervals
    this.pollingIntervals.forEach((intervalId) => clearInterval(intervalId))
    this.pollingIntervals.clear()
    this.listeners.clear()
  }
}

// Export singleton instance
export const realtimeService = new RealtimeService()

// Declare Pusher on window for Laravel Echo
declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo
  }
}
