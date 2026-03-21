/**
 * Shared TypeScript types and interfaces for the client-side application
 */




export interface Product {
  id: number
  name: string
  slug: string
  category?: string
  category_name?: string
  price: number
  sale_price?: number | null
  image?: string
  primary_image?: string
  is_new?: boolean
  is_featured?: boolean
  is_bestseller?: boolean
  description?: string
  stock?: number
  stock_quantity?: number
  stock_status?: string
  low_stock_threshold?: number
  track_inventory?: boolean
  sku?: string
}

export interface ProductCategory {
  id: number
  name: string
  slug: string
  description?: string
  image?: string
}




export interface CartItem {
  id: number
  product_id: number
  product_name: string
  product_sku: string
  product_image?: string
  quantity: number
  max_quantity: number
  price: number
  subtotal: number
}

export interface CartSummary {
  subtotal: number
  discount: number
  shipping: number
  payment_fee: number
  total: number
}




export interface OrderItem {
  id: number
  product_id: number
  product_name: string
  product_sku: string
  product_image?: string
  quantity: number
  price: number
  total: number
}

export interface Order {
  id: number
  order_number: string
  status: 'pending' | 'processing' | 'shipped' | 'delivered' | 'cancelled'
  customer_name: string
  customer_email: string
  customer_phone?: string
  shipping_name: string
  shipping_address_line_1: string
  shipping_address_line_2?: string
  shipping_city: string
  shipping_province: string
  shipping_postal_code: string
  shipping_country: string
  shipping_phone?: string
  shipping_amount: number
  subtotal: number
  discount_amount: number
  total: number
  items: OrderItem[]
  notes?: string
  created_at: string
  updated_at: string
  latest_payment?: Payment
}

export interface Payment {
  id: number
  payment_method_id: number
  payment_method_name: string
  amount: number
  fee_amount: number
  status: 'pending' | 'processing' | 'completed' | 'failed' | 'refunded'
  transaction_id?: string
  payment_details?: Record<string, unknown>
  created_at: string
}




export interface UserAddress {
  id: number
  label: 'Home' | 'Office' | 'Others'
  custom_label?: string
  name: string
  phone: string
  address_line_1: string
  address_line_2?: string
  city: string
  province: string
  postal_code: string
  country: string
  is_default: boolean
  created_at: string
  updated_at: string
}

export interface AddressFormData {
  label: 'Home' | 'Office' | 'Others'
  custom_label?: string
  name: string
  phone: string
  address_line_1: string
  address_line_2?: string
  city: string
  province: string
  postal_code: string
  country: string
  is_default: boolean
}




export interface PaymentMethod {
  id: number
  name: string
  code: string
  type: string
  description?: string
  icon?: string
  payment_instructions?: string
  account_details?: PaymentMethodAccountDetails
  fee_fixed: number
  fee_percentage: number
  requires_verification: boolean
  requires_proof_of_payment: boolean
  min_amount?: number
  max_amount?: number
  is_active: boolean
  display_order: number
}

export interface PaymentMethodAccountDetails {
  account_number?: string
  account_name?: string
  bank_name?: string
  branch?: string
  paypal_email?: string
}

export interface ShippingZone {
  id: number
  name: string
  type: string
  description?: string
  regions?: string[] | null
  base_rate: number
  free_shipping_threshold?: number
  min_delivery_days?: number
  max_delivery_days?: number
}

export interface CheckoutFormData {
  shipping_name: string
  shipping_phone: string
  shipping_address_line_1: string
  shipping_address_line_2?: string
  shipping_city: string
  shipping_province: string
  shipping_postal_code: string
  shipping_zone_id: number | null
  payment_method_id: number | null
  billing_same_as_shipping: boolean
  notes?: string
}




export interface User {
  id: number
  first_name: string
  last_name: string
  email: string
  phone?: string
  avatar?: string
  email_verified_at?: string
  created_at: string
}

export interface AccountStats {
  totalOrders: number
  memberSince: string
  totalSpent: number
}




export interface ProductFilters {
  search?: string
  category?: string
  min_price?: number
  max_price?: number
  in_stock?: boolean
  featured?: boolean
  is_new?: boolean
  bestseller?: boolean
  sort_by?: string
  sort_order?: string
}

export interface FilterState {
  searchQuery: string
  selectedCategory: string
  maxPrice: number
  pricePreset: string
  sortBy: string
}




export interface ApiResponse<T> {
  success: boolean
  data: T
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}
