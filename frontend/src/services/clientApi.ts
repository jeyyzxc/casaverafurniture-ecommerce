import api, { getOrCreateSessionId } from './api'

// ==================
// AUTH
// ==================

export const auth = {
  register: (data: {
    first_name: string
    last_name: string
    email: string
    password: string
    password_confirmation: string
    phone?: string
    newsletter_subscribed?: boolean
  }) => api.post('/auth/register', data),

  login: (email: string, password: string, remember = false) =>
    api.post('/auth/login', { email, password, remember }),

  logout: () => api.post('/auth/logout'),

  refresh: () => api.post('/auth/refresh'),

  me: () => api.get('/auth/me'),

  updateProfile: (data: Record<string, unknown>) => api.put('/auth/profile', data),

  changePassword: (currentPassword: string, password: string, passwordConfirmation: string) =>
    api.put('/auth/password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    }),

  getAccountStats: () => api.get('/auth/account-stats'),
}

// ==================
// HOME & SITE
// ==================

export const home = {
  getData: () => api.get('/home'),

  getSettings: () => api.get('/settings'),

  getCategories: () => api.get('/categories'),
}

// ==================
// PRODUCTS
// ==================

export const products = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    category?: string
    min_price?: number
    max_price?: number
    in_stock?: boolean
    tags?: string
    material?: string
    color?: string
    featured?: boolean
    is_new?: boolean
    bestseller?: boolean
    sort_by?: string
    sort_order?: string
  }) => api.get('/products', { params }),

  get: (slug: string) => api.get(`/products/${slug}`),

  getByCategory: (
    slug: string,
    params?: { page?: number; per_page?: number; sort_by?: string },
  ) => api.get(`/categories/${slug}`, { params }),
}

// ==================
// CART
// ==================

export const cart = {
  get: () => {
    getOrCreateSessionId() // Ensure session ID exists
    return api.get('/cart')
  },

  addItem: (productId: number, quantity = 1) => {
    getOrCreateSessionId()
    return api.post('/cart/items', { product_id: productId, quantity })
  },

  updateItem: (itemId: number, quantity: number) => api.put(`/cart/items/${itemId}`, { quantity }),

  removeItem: (itemId: number) => api.delete(`/cart/items/${itemId}`),

  clear: () => api.delete('/cart'),

  applyCoupon: (code: string) => api.post('/cart/coupon', { code }),

  removeCoupon: () => api.delete('/cart/coupon'),
}

// ==================
// ADDRESSES
// ==================

export const addresses = {
  list: () => api.get('/addresses'),

  create: (data: {
    label: string
    recipient_name: string
    phone: string
    address_line_1: string
    address_line_2?: string
    city: string
    province: string
    postal_code: string
    country?: string
    is_default_shipping?: boolean
    is_default_billing?: boolean
  }) => api.post('/addresses', data),

  update: (addressId: number, data: {
    label?: string
    recipient_name?: string
    phone?: string
    address_line_1?: string
    address_line_2?: string
    city?: string
    province?: string
    postal_code?: string
    country?: string
    is_default_shipping?: boolean
    is_default_billing?: boolean
  }) => api.put(`/addresses/${addressId}`, data),

  delete: (addressId: number) => api.delete(`/addresses/${addressId}`),

  setDefaultShipping: (addressId: number) => api.post(`/addresses/${addressId}/default-shipping`),

  setDefaultBilling: (addressId: number) => api.post(`/addresses/${addressId}/default-billing`),
}

// ==================
// CHECKOUT
// ==================

export const checkout = {
  getShippingZones: () => api.get('/checkout/shipping-zones'),

  getPaymentMethods: () => api.get('/checkout/payment-methods'),
}

// ==================
// ORDERS
// ==================

export const orders = {
  list: (params?: { page?: number; per_page?: number; status?: string }) =>
    api.get('/orders', { params }),

  get: (orderNumber: string) => api.get(`/orders/${orderNumber}`),

  create: (data: {
    // Address selection (either use saved address ID or provide full address)
    shipping_address_id?: number
    billing_address_id?: number
    billing_same_as_shipping?: boolean
    
    // Full address details (required if address_id not provided)
    shipping_address_line_1?: string
    shipping_address_line_2?: string
    shipping_city?: string
    shipping_province?: string
    shipping_postal_code?: string
    shipping_name?: string
    shipping_phone?: string
    
    billing_address_line_1?: string
    billing_address_line_2?: string
    billing_city?: string
    billing_province?: string
    billing_postal_code?: string
    
    shipping_zone_id: number
    payment_method_id: number
    
    // Payment confirmation details
    payment_confirmation?: {
      sender_name?: string
      sender_account?: string
      reference_number?: string
      payment_date?: string
      proof_image?: string
      card_number?: string
      card_holder_name?: string
      card_expiry?: string
      card_cvv?: string
    }
    
    notes?: string
  }) => api.post('/orders', data),

  submitPayment: (
    orderNumber: string,
    data: {
      sender_name: string
      sender_account?: string
      reference_number: string
      payment_date: string
      proof_image?: string
      notes?: string
    },
  ) => api.post(`/orders/${orderNumber}/payment`, data),

  cancel: (orderNumber: string, reason?: string) => api.post(`/orders/${orderNumber}/cancel`, { reason }),
}

// ==================
// WISHLIST
// ==================

export const wishlist = {
  get: () => api.get('/wishlist'),

  add: (productId: number) => api.post('/wishlist', { product_id: productId }),

  remove: (productId: number) => api.delete(`/wishlist/${productId}`),

  check: (productId: number) => api.get(`/wishlist/check/${productId}`),

  moveToCart: (productId: number) => api.post(`/wishlist/${productId}/move-to-cart`),
}

// ==================
// PROMOTIONS
// ==================

export const promotions = {
  list: () => api.get('/promotions'),
}
