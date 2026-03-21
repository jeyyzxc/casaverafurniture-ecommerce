import api from './api'


export const adminAuth = {
  login: (email: string, password: string, remember = false) =>
    api.post('/admin/auth/login', { email, password, remember }),

  logout: () => api.post('/admin/auth/logout'),

  refresh: () => api.post('/admin/auth/refresh'),

  me: () => api.get('/admin/auth/me'),

  updateProfile: (data: { first_name?: string; last_name?: string; phone?: string }) =>
    api.put('/admin/auth/profile', data),

  changePassword: (currentPassword: string, password: string, passwordConfirmation: string) =>
    api.put('/admin/auth/password', {
      current_password: currentPassword,
      password,
      password_confirmation: passwordConfirmation,
    }),
}


export const dashboard = {
  getStats: (startDate?: string, endDate?: string) =>
    api.get('/admin/dashboard', { params: { start_date: startDate, end_date: endDate } }),

  getQuickStats: () => api.get('/admin/dashboard/quick-stats'),
}


export const notifications = {
  list: (params?: { page?: number; per_page?: number; read?: boolean }) =>
    api.get('/admin/notifications', { params }),

  markAsRead: (id: string) => api.post(`/admin/notifications/${id}/mark-as-read`),

  markAllAsRead: () => api.post('/admin/notifications/mark-all-as-read'),

  delete: (id: string) => api.delete(`/admin/notifications/${id}`),

  getUnreadCount: () => api.get('/admin/notifications/unread-count'),
}


export const products = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    category_id?: number
    status?: string
    stock_status?: string
    is_featured?: boolean
    low_stock?: boolean
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/products', { params }),

  get: (id: number) => api.get(`/admin/products/${id}`),

  create: (data: Record<string, unknown>) => api.post('/admin/products', data),

  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/products/${id}`, data),

  delete: (id: number) => api.delete(`/admin/products/${id}`),

  bulkUpdate: (ids: number[], action: string) => api.post('/admin/products/bulk', { ids, action }),

  updateStock: (id: number, quantity: number, type: 'set' | 'add' | 'subtract', reason?: string) =>
    api.put(`/admin/products/${id}/stock`, { quantity, type, reason }),

  getStockHistory: (id: number, params?: { page?: number; per_page?: number }) =>
    api.get(`/admin/products/${id}/stock-history`, { params }),
}


export const categories = {
  list: (params?: { parent_id?: number | null; is_visible?: boolean; hierarchical?: boolean; search?: string }) =>
    api.get('/admin/categories', { params }),

  get: (id: number) => api.get(`/admin/categories/${id}`),

  create: (data: Record<string, unknown>) => api.post('/admin/categories', data),

  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/categories/${id}`, data),

  delete: (id: number) => api.delete(`/admin/categories/${id}`),

  reorder: (categories: { id: number; display_order: number }[]) =>
    api.post('/admin/categories/reorder', { categories }),
}


export const orders = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
    payment_status?: string
    start_date?: string
    end_date?: string
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/orders', { params }),

  get: (id: number) => api.get(`/admin/orders/${id}`),

  updateStatus: (id: number, status: string, comment?: string, notifyCustomer = false, trackingNumber?: string) =>
    api.put(`/admin/orders/${id}/status`, {
      status,
      comment,
      notify_customer: notifyCustomer,
      tracking_number: trackingNumber,
    }),

  updateShipping: (
    id: number,
    data: { courier_id?: number; tracking_number?: string; tracking_url?: string; estimated_delivery_date?: string },
  ) => api.put(`/admin/orders/${id}/shipping`, data),

  addNote: (id: number, note: string, isPrivate = true) =>
    api.post(`/admin/orders/${id}/notes`, { note, is_private: isPrivate }),

  cancel: (id: number, reason: string) => api.post(`/admin/orders/${id}/cancel`, { reason }),

  statistics: () => api.get('/admin/orders/statistics'),
}


export const users = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/users', { params }),

  get: (id: number) => api.get(`/admin/users/${id}`),

  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/users/${id}`, data),

  delete: (id: number) => api.delete(`/admin/users/${id}`),

  ban: (id: number, reason: string) => api.post(`/admin/users/${id}/ban`, { reason }),

  unban: (id: number) => api.post(`/admin/users/${id}/unban`),

  getOrders: (id: number, params?: { page?: number; per_page?: number }) =>
    api.get(`/admin/users/${id}/orders`, { params }),
}


export const payments = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
    payment_method_id?: number
    start_date?: string
    end_date?: string
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/payments', { params }),

  get: (id: number) => api.get(`/admin/payments/${id}`),

  verify: (id: number, notes?: string) => api.post(`/admin/payments/${id}/verify`, { notes }),

  reject: (id: number, reason: string) => api.post(`/admin/payments/${id}/reject`, { reason }),

  statistics: () => api.get('/admin/payments/statistics'),
}


export const settings = {
  getAll: (group?: string) => api.get('/admin/settings', { params: { group } }),

  update: (settings: { key: string; value: unknown; group?: string }[]) =>
    api.put('/admin/settings', { settings }),

  getPaymentMethods: () => api.get('/admin/settings/payment-methods'),

  updatePaymentMethod: (id: number, data: Record<string, unknown>) =>
    api.put(`/admin/settings/payment-methods/${id}`, data),

  getShippingZones: () => api.get('/admin/settings/shipping-zones'),

  updateShippingZone: (id: number, data: Record<string, unknown>) =>
    api.put(`/admin/settings/shipping-zones/${id}`, data),

  getCouriers: () => api.get('/admin/settings/couriers'),

  updateCourier: (id: number, data: Record<string, unknown>) => api.put(`/admin/settings/couriers/${id}`, data),
}


export const shipping = {
  list: (params?: { active_only?: boolean }) => api.get('/admin/shipping', { params }),

  get: (id: number) => api.get(`/admin/shipping/${id}`),

  create: (data: Record<string, unknown>) => api.post('/admin/shipping', data),

  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/shipping/${id}`, data),

  delete: (id: number) => api.delete(`/admin/shipping/${id}`),
}


export const upload = {
  image: (file: File, folder?: string) => {
    const formData = new FormData()
    formData.append('file', file)
    if (folder) {
      formData.append('folder', folder)
    }
    return api.post('/admin/upload/image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  deleteFile: (path: string) => api.delete('/admin/upload/file', { data: { path } }),
}


export const activityLogs = {
  list: (params?: {
    page?: number
    per_page?: number
    action?: string
    module?: string
    causer_id?: number
    date_from?: string
    date_to?: string
    search?: string
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/activity-logs', { params }),

  get: (id: number) => api.get(`/admin/activity-logs/${id}`),

  statistics: () => api.get('/admin/activity-logs/statistics'),
}


export const admins = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    role_id?: number
    status?: string
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/admins', { params }),

  get: (id: number) => api.get(`/admin/admins/${id}`),

  create: (data: {
    first_name: string
    last_name: string
    email: string
    password: string
    password_confirmation: string
    phone?: string
    role_id: number
    status?: 'active' | 'inactive'
  }) => api.post('/admin/admins', data),

  update: (id: number, data: {
    first_name?: string
    last_name?: string
    email?: string
    phone?: string
    avatar?: string
    role_id?: number
    status?: 'active' | 'inactive'
  }) => api.put(`/admin/admins/${id}`, data),

  delete: (id: number) => api.delete(`/admin/admins/${id}`),

  getRoles: () => api.get('/admin/admins/roles'),
}


export const cms = {
  
  getSections: () => api.get('/admin/cms/sections'),
  getSection: (id: number) => api.get(`/admin/cms/sections/${id}`),
  createSection: (data: Record<string, unknown>) => api.post('/admin/cms/sections', data),
  updateSection: (id: number, data: Record<string, unknown>) => api.put(`/admin/cms/sections/${id}`, data),
  deleteSection: (id: number) => api.delete(`/admin/cms/sections/${id}`),

  
  getBanners: (params?: { position?: string }) => api.get('/admin/cms/banners', { params }),
  getBanner: (id: number) => api.get(`/admin/cms/banners/${id}`),
  createBanner: (data: Record<string, unknown>) => api.post('/admin/cms/banners', data),
  updateBanner: (id: number, data: Record<string, unknown>) => api.put(`/admin/cms/banners/${id}`, data),
  deleteBanner: (id: number) => api.delete(`/admin/cms/banners/${id}`),
}


export const promotions = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    is_active?: boolean
    discount_type?: 'percentage' | 'fixed' | 'free_shipping' | 'buy_x_get_y'
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/promotions', { params }),

  get: (id: number) => api.get(`/admin/promotions/${id}`),

  create: (data: Record<string, unknown>) => api.post('/admin/promotions', data),

  update: (id: number, data: Record<string, unknown>) => api.put(`/admin/promotions/${id}`, data),

  delete: (id: number) => api.delete(`/admin/promotions/${id}`),

  toggle: (id: number) => api.post(`/admin/promotions/${id}/toggle`),
}


export const reviews = {
  list: (params?: {
    page?: number
    per_page?: number
    search?: string
    status?: string
    product_id?: number
    user_id?: number
    rating?: number
    sort_by?: string
    sort_order?: string
  }) => api.get('/admin/reviews', { params }),

  get: (id: number) => api.get(`/admin/reviews/${id}`),

  updateStatus: (id: number, status: 'approved' | 'rejected') =>
    api.put(`/admin/reviews/${id}/status`, { status }),

  delete: (id: number) => api.delete(`/admin/reviews/${id}`),
}


export const reports = {
  summary: (params?: { start_date?: string; end_date?: string }) =>
    api.get('/admin/reports/summary', { params }),

  sales: (params?: { start_date?: string; end_date?: string; group_by?: 'day' | 'week' | 'month' }) =>
    api.get('/admin/reports/sales', { params }),

  orders: (params?: { start_date?: string; end_date?: string }) =>
    api.get('/admin/reports/orders', { params }),

  products: (params?: { start_date?: string; end_date?: string; limit?: number }) =>
    api.get('/admin/reports/products', { params }),

  users: (params?: { start_date?: string; end_date?: string }) =>
    api.get('/admin/reports/users', { params }),
}
