import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

class RealtimeService {
  private echo: Echo | null = null
  private pollingIntervals: Map<string, number> = new Map()
  private listeners: Map<string, Set<Function>> = new Map()
  private isWebSocketEnabled = false

  constructor() {
    const pusherKey = import.meta.env.VITE_PUSHER_APP_KEY
    if (pusherKey) {
      this.initializeWebSocket(pusherKey)
    } else {
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
  }

  listenToProducts(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('products')
        .listen('.product.created', (data: any) => callback('product.created', data))
        .listen('.product.updated', (data: any) => callback('product.updated', data))
        .listen('.product.deleted', (data: any) => callback('product.deleted', data))
        .listen('.stock.changed', (data: any) => callback('stock.changed', data))
    } else {
      this.startPolling('products', callback, 3000)
    }
  }

  listenToOrders(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('orders')
        .listen('.order.created', (data: any) => callback('order.created', data))
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
    } else {
      this.startPolling('orders', callback, 5000)
    }
  }

  listenToCart(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('cart')
        .listen('.cart.updated', (data: any) => callback('cart.updated', data))
    } else {
    }
  }

  listenToHomepage(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('homepage')
        .listen('.homepage.updated', (data: any) => callback('homepage.updated', data))
    } else {
      this.startPolling('homepage', callback, 10000)
    }
  }

  listenToAdmin(callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.channel('admin')
        .listen('.stock.changed', (data: any) => callback('stock.changed', data))
        .listen('.order.created', (data: any) => callback('order.created', data))
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
        .listen('.user.login', (data: any) => callback('user.login', data))
        .listen('.user.registered', (data: any) => callback('user.registered', data))
        .listen('.payment.received', (data: any) => callback('payment.received', data))
        .listen('.promotion.created', (data: any) => callback('promotion.created', data))
        .listen('.product.created', (data: any) => callback('product.created', data))
        .listen('.product.updated', (data: any) => callback('product.updated', data))
    } else {
      this.startPolling('admin', callback, 3000)
    }
  }

  listenToUser(userId: number, callback: (event: string, data: any) => void) {
    if (this.echo && this.isWebSocketEnabled) {
      this.echo.private(`user.${userId}`)
        .listen('.order.status.updated', (data: any) => callback('order.status.updated', data))
    } else {
    }
  }

  private startPolling(channel: string, callback: Function, interval: number) {
    this.stopPolling(channel)

    if (!this.listeners.has(channel)) {
      this.listeners.set(channel, new Set())
    }
    this.listeners.get(channel)!.add(callback)
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
    this.pollingIntervals.forEach((intervalId) => clearInterval(intervalId))
    this.pollingIntervals.clear()
    this.listeners.clear()
  }
}

export const realtimeService = new RealtimeService()

declare global {
  interface Window {
    Pusher: typeof Pusher
    Echo: Echo
  }
}
