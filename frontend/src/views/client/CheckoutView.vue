<template>
  <div class="checkout-page">
    <HeroSection
      title="Checkout"
      subtitle="Complete your order and secure your curated pieces."
      size="large"
    />

    <div class="checkout-container">
      <div v-if="!orderPlaced" class="checkout-layout">
        <!-- Checkout Form -->
        <div class="checkout-form-wrapper rise-up">
          <form @submit.prevent="handleCheckout" class="checkout-form">
            <!-- Shipping Address -->
            <section class="form-section rise-up">
              <div class="section-header">
                <h3 class="section-title">Shipping Address</h3>
                <button type="button" class="btn-use-default" @click="useDefaultShippingAddress" v-if="defaultShippingAddress">
                  Use Default Address
                </button>
              </div>
              
              <!-- Address Selection -->
              <div class="form-group full-width" v-if="savedAddresses.length > 0">
                <label>Select Saved Address (Optional)</label>
                <select v-model="selectedShippingAddressId" @change="onShippingAddressSelect" class="address-select">
                  <option :value="null">Enter new address</option>
                  <option v-for="address in savedAddresses" :key="address.id" :value="address.id">
                    {{ address.label }} - {{ address.address_line_1 }}, {{ address.city }}
                  </option>
                </select>
              </div>

              <div class="form-grid" v-show="!selectedShippingAddressId">
                <div class="form-group full-width">
                  <label>Recipient Name *</label>
                  <input v-model="form.shipping_name" type="text" required />
                </div>
                <div class="form-group full-width">
                  <label>Phone Number *</label>
                  <input v-model="form.shipping_phone" type="tel" required />
                </div>
                <div class="form-group full-width">
                  <label>Address Line 1 *</label>
                  <input 
                    v-model="form.shipping_address_line_1" 
                    type="text" 
                    required 
                    @input="formatShippingAddressLine1"
                    @blur="formatShippingAddressLine1"
                    placeholder="Street address, building name, house number"
                  />
                </div>
                <div class="form-group full-width">
                  <label>Address Line 2</label>
                  <input 
                    v-model="form.shipping_address_line_2" 
                    type="text" 
                    @input="formatShippingAddressLine2"
                    @blur="formatShippingAddressLine2"
                    placeholder="Barangay, subdivision, village"
                  />
                </div>
                <div class="form-group">
                  <label>Province *</label>
                  <div class="select-wrapper">
                    <select 
                      v-model="form.shipping_province" 
                      @change="onProvinceChange"
                      required 
                      class="zone-select"
                    >
                      <option value="">Select Province</option>
                      <option v-for="province in provinceNames" :key="province" :value="province">
                        {{ province }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>City *</label>
                  <div class="select-wrapper">
                    <select 
                      v-model="form.shipping_city" 
                      :disabled="!form.shipping_province"
                      required 
                      class="zone-select"
                    >
                      <option value="">{{ form.shipping_province ? 'Select City' : 'Select Province First' }}</option>
                      <option v-for="city in availableCities" :key="city" :value="city">
                        {{ city }}
                      </option>
                    </select>
                  </div>
                </div>
                <div class="form-group">
                  <label>Postal Code *</label>
                  <input 
                    v-model="form.shipping_postal_code" 
                    type="text" 
                    required 
                    maxlength="4"
                    pattern="[0-9]{4}"
                    placeholder="Auto-filled based on location"
                  />
                  <small v-if="form.shipping_postal_code" class="postal-code-hint">
                    Postal code for {{ form.shipping_city }}, {{ form.shipping_province }}
                  </small>
                </div>
                <div class="form-group">
                  <label>Shipping Zone *</label>
                  <div class="select-wrapper">
                    <select 
                      v-model="form.shipping_zone_id" 
                      required 
                      @change="updateShipping" 
                      class="zone-select"
                      :disabled="isLoadingZones"
                    >
                      <option value="">{{ isLoadingZones ? 'Loading zones...' : 'Select Shipping Zone' }}</option>
                      <option v-for="zone in shippingZones" :key="zone.id" :value="zone.id">
                        {{ getZoneDisplayText(zone) }}
                      </option>
                    </select>
                    <div v-if="isLoadingZones" class="select-loading">
                      <div class="spinner-small"></div>
                    </div>
                  </div>
                  <small v-if="form.shipping_zone_id" class="zone-info">
                    <template v-for="zone in shippingZones" :key="zone.id">
                      <span v-if="zone.id === form.shipping_zone_id && zone.description">
                        <i class="info-icon">ℹ️</i> {{ zone.description }}
                        <template v-if="zone.free_shipping_threshold">
                          • Free shipping on orders over ₱{{ formatPrice(zone.free_shipping_threshold) }}
                        </template>
                      </span>
                    </template>
                  </small>
                  <small v-if="!isLoadingZones && shippingZones.length === 0" class="zone-error">
                    ⚠️ No shipping zones available. Please contact support.
                  </small>
                </div>
              </div>
            </section>

            <!-- Payment Method -->
            <section class="form-section">
              <h3 class="section-title">Payment Method</h3>
              <div v-if="isLoadingPaymentMethods" class="loading-payment-methods">
                <div class="spinner-small"></div>
                <span>Loading payment methods...</span>
              </div>
              <div v-else-if="paymentMethods.filter(m => m.is_active).length === 0" class="no-payment-methods">
                <p>⚠️ No payment methods available. Please contact support.</p>
              </div>
              <div v-else class="payment-methods">
                <div
                  v-for="method in paymentMethods.filter(m => m.is_active)"
                  :key="method.id"
                  class="payment-method-card"
                  :class="{ 
                    active: form.payment_method_id === method.id,
                    disabled: !isPaymentMethodAvailable(method)
                  }"
                  @click="isPaymentMethodAvailable(method) ? selectPaymentMethod(method) : null"
                  role="button"
                  :tabindex="isPaymentMethodAvailable(method) ? 0 : -1"
                  @keydown.enter="isPaymentMethodAvailable(method) ? selectPaymentMethod(method) : null"
                  @keydown.space.prevent="isPaymentMethodAvailable(method) ? selectPaymentMethod(method) : null"
                >
                  <div class="method-header">
                    <div class="method-radio-wrapper">
                      <input
                        type="radio"
                        :value="method.id"
                        v-model="form.payment_method_id"
                        :id="`method-${method.id}`"
                        :disabled="!isPaymentMethodAvailable(method)"
                        @click.stop
                      />
                      <span class="custom-radio" :class="{ checked: form.payment_method_id === method.id }"></span>
                    </div>
                    <div class="method-content">
                      <div class="method-title-row">
                        <span v-if="getPaymentIcon(method.code)" class="method-icon">
                          <img :src="getPaymentIcon(method.code)" :alt="method.name" />
                        </span>
                        <label :for="`method-${method.id}`" class="method-name">
                          {{ method.name }}
                        </label>
                        <span v-if="getPaymentFeeDisplay(method)" class="fee-badge">
                          {{ getPaymentFeeDisplay(method) }}
                        </span>
                        <span v-if="method.min_amount || method.max_amount" class="amount-limit-badge">
                          <template v-if="method.min_amount && method.max_amount">
                            ₱{{ formatPrice(method.min_amount) }} - ₱{{ formatPrice(method.max_amount) }}
                          </template>
                          <template v-else-if="method.min_amount">
                            Min: ₱{{ formatPrice(method.min_amount) }}
                          </template>
                          <template v-else-if="method.max_amount">
                            Max: ₱{{ formatPrice(method.max_amount) }}
                          </template>
                        </span>
                      </div>
                      <p v-if="method.description" class="method-description">{{ method.description }}</p>
                      <p v-if="!isPaymentMethodAvailable(method) && method.is_active" class="method-unavailable">
                        <template v-if="method.min_amount && (subtotal - discount + shippingAmount) < method.min_amount">
                          ⚠️ Minimum order amount: ₱{{ formatPrice(method.min_amount) }}
                        </template>
                        <template v-else-if="method.max_amount && (subtotal - discount + shippingAmount) > method.max_amount">
                          ⚠️ Maximum order amount: ₱{{ formatPrice(method.max_amount) }}
                        </template>
                        <template v-else>
                          ⚠️ This payment method is currently unavailable
                        </template>
                      </p>
                    </div>
                    <div v-if="form.payment_method_id === method.id" class="method-check-icon">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </div>
                  </div>
                  <div v-if="method.account_details && form.payment_method_id === method.id" class="account-details">
                    <div class="account-details-header">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                        <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                      </svg>
                      <strong>Account Details</strong>
                    </div>
                    <div class="account-info">
                      <template v-if="method.account_details.account_number">
                        <div class="account-item">
                          <span class="account-label">Account Number:</span>
                          <span class="account-value">{{ method.account_details.account_number }}</span>
                        </div>
                      </template>
                      <template v-if="method.account_details.account_name">
                        <div class="account-item">
                          <span class="account-label">Account Name:</span>
                          <span class="account-value">{{ method.account_details.account_name }}</span>
                        </div>
                      </template>
                      <template v-if="method.account_details.bank_name">
                        <div class="account-item">
                          <span class="account-label">Bank:</span>
                          <span class="account-value">{{ method.account_details.bank_name }}</span>
                        </div>
                      </template>
                      <template v-if="method.account_details.branch">
                        <div class="account-item">
                          <span class="account-label">Branch:</span>
                          <span class="account-value">{{ method.account_details.branch }}</span>
                        </div>
                      </template>
                      <template v-if="method.account_details.paypal_email">
                        <div class="account-item">
                          <span class="account-label">PayPal Email:</span>
                          <span class="account-value">{{ method.account_details.paypal_email }}</span>
                        </div>
                      </template>
                    </div>
                  </div>
                </div>
              </div>
            </section>

            <!-- Order Notes -->
            <section class="form-section rise-up-delay-2">
              <h3 class="section-title">Order Notes (Optional)</h3>
              <textarea
                v-model="form.notes"
                placeholder="Special delivery instructions or notes..."
                rows="3"
              ></textarea>
            </section>

            <div v-if="checkoutError" class="error-message">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 0.5rem; flex-shrink: 0;">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
              </svg>
              <span>{{ checkoutError }}</span>
            </div>

            <button type="submit" class="btn-submit" :disabled="isSubmitting">
              <span v-if="isSubmitting">Placing Order...</span>
              <span v-else>Place Order</span>
            </button>
          </form>
        </div>

        <!-- Order Summary -->
        <div class="order-summary-sidebar rise-up-delay-2">
          <div class="summary-card">
            <h4>Order Summary</h4>
            <div v-if="cartItems.length === 0" class="empty-cart-warning">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
              <p>Your cart is empty</p>
              <router-link to="/cart" class="btn-back-to-cart">Go to Cart</router-link>
            </div>
            <div v-else class="summary-items">
              <div v-for="item in cartItems" :key="item.id" class="summary-item">
                <img :src="item.product_image || '/images/products/placeholder.png'" :alt="item.product_name" />
                <div class="item-info">
                  <p class="item-name">{{ item.product_name }}</p>
                  <p class="item-qty">Qty: {{ item.quantity }}</p>
                </div>
                <p class="item-price">₱{{ formatPrice(item.subtotal) }}</p>
              </div>
            </div>
            <div class="summary-totals">
              <div class="summary-row">
                <span>Subtotal</span>
                <span>₱{{ formatPrice(subtotal) }}</span>
              </div>
              <div v-if="discount > 0" class="summary-row">
                <span>Discount</span>
                <span class="discount">-₱{{ formatPrice(discount) }}</span>
              </div>
              <div class="summary-row">
                <span>
                  Shipping
                  <span v-if="form.shipping_zone_id" class="shipping-zone-badge">
                    {{ getSelectedZoneName() }}
                  </span>
                  <span v-else class="shipping-warning">⚠️ Select zone</span>
                </span>
                <span :class="{ 'free-shipping': shippingAmount === 0 && form.shipping_zone_id }">
                  <span v-if="shippingAmount === 0 && form.shipping_zone_id">FREE</span>
                  <span v-else>₱{{ formatPrice(shippingAmount) }}</span>
                </span>
              </div>
              <div v-if="paymentFee > 0 && selectedPaymentMethod" class="summary-row">
                <span>
                  Payment Fee
                  <span class="payment-method-badge">{{ selectedPaymentMethod.name }}</span>
                </span>
                <span>₱{{ formatPrice(paymentFee) }}</span>
              </div>
              <div class="summary-row total">
                <span>Total</span>
                <span class="gold">₱{{ formatPrice(total) }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Success with Transaction Summary -->
      <div v-else class="order-success rise-up">
        <div class="success-icon">
          <svg viewBox="0 0 24 24" fill="currentColor">
            <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z"/>
          </svg>
        </div>
        <h2>Order Placed Successfully!</h2>
        <p class="order-number">Order Number: <strong>{{ orderNumber }}</strong></p>
        <p class="success-message">We've received your order and will process it shortly.</p>

        <!-- Transaction Summary -->
        <div v-if="orderDetails" class="transaction-summary">
          <h3 class="summary-title">Transaction Summary</h3>
          
          <!-- User Information -->
          <div class="summary-section">
            <h4 class="section-heading">Customer Information</h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ orderDetails.customer_name || authStore.user?.full_name }}</span>
              </div>
              <div class="info-item">
                <span class="info-label">Email:</span>
                <span class="info-value">{{ orderDetails.customer_email || authStore.user?.email }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.customer_phone">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ orderDetails.customer_phone }}</span>
              </div>
            </div>
          </div>

          <!-- Shipping Address -->
          <div class="summary-section rise-up-delay-2">
            <h4 class="section-heading">Shipping Address</h4>
            <div class="address-display">
              <p><strong>{{ orderDetails.shipping_name }}</strong></p>
              <p>{{ orderDetails.shipping_address_line_1 }}</p>
              <p v-if="orderDetails.shipping_address_line_2">{{ orderDetails.shipping_address_line_2 }}</p>
              <p>{{ orderDetails.shipping_city }}, {{ orderDetails.shipping_province }} {{ orderDetails.shipping_postal_code }}</p>
              <p>{{ orderDetails.shipping_country || 'Philippines' }}</p>
              <p v-if="orderDetails.shipping_phone">Phone: {{ orderDetails.shipping_phone }}</p>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="summary-section rise-up-delay-3" v-if="orderDetails.latest_payment">
            <h4 class="section-heading">Payment Information</h4>
            <div class="info-grid">
              <div class="info-item">
                <span class="info-label">Payment Method:</span>
                <span class="info-value">{{ orderDetails.latest_payment.payment_method_name }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.transaction_id">
                <span class="info-label">Transaction ID:</span>
                <span class="info-value">{{ orderDetails.latest_payment.transaction_id }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.payment_details">
                <template v-if="orderDetails.latest_payment.payment_details.sender_name">
                  <div class="info-item">
                    <span class="info-label">Sender Name:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.sender_name }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.sender_account">
                  <div class="info-item">
                    <span class="info-label">Sender Account:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.sender_account }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.reference_number">
                  <div class="info-item">
                    <span class="info-label">Reference Number:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.reference_number }}</span>
                  </div>
                </template>
                <template v-if="orderDetails.latest_payment.payment_details.card">
                  <div class="info-item">
                    <span class="info-label">Card (Last 4):</span>
                    <span class="info-value">****{{ orderDetails.latest_payment.payment_details.card.last_four }}</span>
                  </div>
                  <div class="info-item" v-if="orderDetails.latest_payment.payment_details.card.holder_name">
                    <span class="info-label">Card Holder:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.card.holder_name }}</span>
                  </div>
                  <div class="info-item" v-if="orderDetails.latest_payment.payment_details.card.expiry">
                    <span class="info-label">Expiry:</span>
                    <span class="info-value">{{ orderDetails.latest_payment.payment_details.card.expiry }}</span>
                  </div>
                </template>
              </div>
              <div class="info-item">
                <span class="info-label">Amount Paid:</span>
                <span class="info-value gold">₱{{ formatPrice(orderDetails.latest_payment.amount) }}</span>
              </div>
              <div class="info-item" v-if="orderDetails.latest_payment.fee_amount > 0">
                <span class="info-label">Payment Fee:</span>
                <span class="info-value">₱{{ formatPrice(orderDetails.latest_payment.fee_amount) }}</span>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="summary-section rise-up-delay-4" v-if="orderDetails.items && orderDetails.items.length > 0">
            <h4 class="section-heading">Order Items</h4>
            <div class="order-items-summary">
              <div v-for="item in orderDetails.items" :key="item.id" class="summary-item-row">
                <div class="item-info">
                  <span class="item-name">{{ item.product_name }}</span>
                  <span class="item-qty">Qty: {{ item.quantity }}</span>
                </div>
                <span class="item-price">₱{{ formatPrice(item.total) }}</span>
              </div>
            </div>
          </div>

          <!-- Order Totals -->
          <div class="summary-section rise-up-delay-5">
            <h4 class="section-heading">Order Totals</h4>
            <div class="totals-grid">
              <div class="total-row">
                <span>Subtotal:</span>
                <span>₱{{ formatPrice(orderDetails.subtotal || 0) }}</span>
              </div>
              <div class="total-row" v-if="orderDetails.discount_amount > 0">
                <span>Discount:</span>
                <span class="discount">-₱{{ formatPrice(orderDetails.discount_amount) }}</span>
              </div>
              <div class="total-row">
                <span>Shipping:</span>
                <span>₱{{ formatPrice(orderDetails.shipping_amount || 0) }}</span>
              </div>
              <div class="total-row" v-if="orderDetails.latest_payment?.fee_amount > 0">
                <span>Payment Fee:</span>
                <span>₱{{ formatPrice(orderDetails.latest_payment.fee_amount) }}</span>
              </div>
              <div class="total-row final-total">
                <span>Total:</span>
                <span class="gold">₱{{ formatPrice(orderDetails.total || 0) }}</span>
              </div>
            </div>
          </div>
        </div>

        <div class="success-actions">
          <router-link to="/orders" class="btn-primary">View All Orders</router-link>
          <router-link :to="`/orders/${orderNumber}`" class="btn-secondary">View Order Details</router-link>
          <router-link to="/products" class="btn-secondary">Continue Shopping</router-link>
        </div>
      </div>
    </div>

    <!-- Payment Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showPaymentConfirmation" class="modal-overlay" @click.self="showPaymentConfirmation = false">
        <div class="modal-box payment-confirmation-modal">
          <button class="modal-close" @click="showPaymentConfirmation = false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M18 6L6 18M6 6l12 12"/>
            </svg>
          </button>

          <h3 class="modal-title">Confirm Payment Details</h3>
          <p class="modal-subtitle">Please review and confirm your payment information</p>

          <!-- Order Summary in Modal -->
          <div class="confirmation-summary">
            <div class="summary-row">
              <span>Subtotal:</span>
              <span>₱{{ formatPrice(subtotal) }}</span>
            </div>
            <div v-if="discount > 0" class="summary-row">
              <span>Discount:</span>
              <span class="discount">-₱{{ formatPrice(discount) }}</span>
            </div>
            <div class="summary-row">
              <span>Shipping:</span>
              <span>₱{{ formatPrice(shippingAmount) }}</span>
            </div>
            <div v-if="paymentFee > 0" class="summary-row">
              <span>Payment Fee:</span>
              <span>₱{{ formatPrice(paymentFee) }}</span>
            </div>
            <div class="summary-row total">
              <span>Total:</span>
              <span class="gold">₱{{ formatPrice(total) }}</span>
            </div>
          </div>

          <form @submit.prevent="submitOrderWithConfirmation" class="payment-confirmation-form">
            <!-- Payment Method Specific Fields -->
            <div v-if="selectedPaymentMethod">
              <!-- GCash, PayPal, Maya -->
              <template v-if="['gcash', 'paypal', 'maya'].includes(selectedPaymentMethod.code?.toLowerCase() || '')">
                <div class="form-group">
                  <label>Sender Name *</label>
                  <input 
                    v-model="paymentConfirmationData.sender_name" 
                    type="text" 
                    required 
                    :placeholder="authStore.user?.full_name || 'Enter sender name'"
                  />
                </div>
                <div class="form-group">
                  <label>Sender Account (Last 4 digits or email)</label>
                  <input 
                    v-model="paymentConfirmationData.sender_account" 
                    type="text" 
                    placeholder="Optional"
                  />
                </div>
                <div class="form-group">
                  <label>Reference Number *</label>
                  <input 
                    v-model="paymentConfirmationData.reference_number" 
                    type="text" 
                    required 
                    placeholder="Enter transaction reference number"
                  />
                </div>
                <div class="form-group">
                  <label>Proof of Payment Image (Optional)</label>
                  <div class="image-upload-wrapper">
                    <input 
                      type="file" 
                      ref="proofImageInput"
                      accept="image/*"
                      @change="handleProofImageSelect"
                      style="display: none;"
                    />
                    <div v-if="proofImagePreview" class="image-preview-container">
                      <img :src="proofImagePreview" alt="Proof of payment" class="image-preview" />
                      <button type="button" class="remove-image-btn" @click="removeProofImage">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                          <path d="M18 6L6 18M6 6l12 12"/>
                        </svg>
                      </button>
                    </div>
                    <button 
                      type="button" 
                      class="upload-image-btn" 
                      @click="triggerProofImageUpload"
                      v-if="!proofImagePreview"
                    >
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                        <polyline points="17 8 12 3 7 8"/>
                        <line x1="12" y1="3" x2="12" y2="15"/>
                      </svg>
                      Upload Proof Image
                    </button>
                    <button 
                      type="button" 
                      class="change-image-btn" 
                      @click="triggerProofImageUpload"
                      v-else
                    >
                      Change Image
                    </button>
                    <p class="image-upload-hint">Upload a screenshot or photo of your payment receipt (Max 5MB)</p>
                  </div>
                </div>
                <div class="form-group">
                  <label>Payment Date *</label>
                  <input 
                    v-model="paymentConfirmationData.payment_date" 
                    type="date" 
                    required 
                    :max="new Date().toISOString().split('T')[0]"
                  />
                </div>
              </template>

              <!-- Bank Transfer -->
              <template v-else-if="selectedPaymentMethod.type === 'bank_transfer' || (selectedPaymentMethod.code?.toLowerCase() || '').includes('bank')">
                <div class="form-group">
                  <label>Card Number (Last 4 digits) *</label>
                  <input 
                    v-model="paymentConfirmationData.card_number" 
                    type="text" 
                    required 
                    maxlength="4"
                    placeholder="1234"
                    pattern="[0-9]{4}"
                  />
                </div>
                <div class="form-group">
                  <label>Card Holder Name *</label>
                  <input 
                    v-model="paymentConfirmationData.card_holder_name" 
                    type="text" 
                    required 
                    :placeholder="authStore.user?.full_name || 'Enter card holder name'"
                  />
                </div>
                <div class="form-row">
                  <div class="form-group">
                    <label>Expiry Date (MM/YY) *</label>
                    <input 
                      v-model="paymentConfirmationData.card_expiry" 
                      type="text" 
                      required 
                      placeholder="12/25"
                      maxlength="5"
                      pattern="[0-9]{2}/[0-9]{2}"
                    />
                  </div>
                  <div class="form-group">
                    <label>CVV *</label>
                    <input 
                      v-model="paymentConfirmationData.card_cvv" 
                      type="password" 
                      required 
                      placeholder="123"
                      maxlength="4"
                      pattern="[0-9]{3,4}"
                    />
                  </div>
                </div>
              </template>

              <!-- COD or other methods -->
              <template v-else>
                <div class="info-message">
                  <p>No additional payment details required for {{ selectedPaymentMethod.name }}.</p>
                </div>
              </template>
            </div>

            <div class="modal-actions">
              <button type="button" class="btn-secondary" @click="showPaymentConfirmation = false" :disabled="isSubmitting">
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="isSubmitting">
                <span v-if="isSubmitting">Processing...</span>
                <span v-else>Confirm & Place Order</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import HeroSection from '@/components/HeroSection.vue'
import { useCartStore } from '@/stores/cart'
import { useAuthStore } from '@/stores/auth'
import { checkout, orders, addresses as addressesApi } from '@/services/clientApi'
import { getProvinceNames, getCitiesByProvince, getPostalCode } from '@/data/philippineLocations'

// TypeScript Interfaces
interface PaymentMethodAccountDetails {
  account_number?: string
  account_name?: string
  bank_name?: string
  branch?: string
  paypal_email?: string
}

interface PaymentMethod {
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

interface ShippingZone {
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

const router = useRouter()
const cartStore = useCartStore()
const authStore = useAuthStore()

// Form state
const form = ref({
  shipping_name: '',
  shipping_phone: '',
  shipping_address_line_1: '',
  shipping_address_line_2: '',
  shipping_city: '',
  shipping_province: '',
  shipping_postal_code: '',
  shipping_zone_id: null as number | null,
  payment_method_id: null as number | null,
  billing_same_as_shipping: true,
  notes: '',
})

const shippingZones = ref<ShippingZone[]>([])
const paymentMethods = ref<PaymentMethod[]>([])
const isSubmitting = ref(false)
const isLoadingZones = ref(false)
const isLoadingPaymentMethods = ref(false)
const checkoutError = ref('')
const orderPlaced = ref(false)
const orderNumber = ref('')
const orderDetails = ref<any>(null)
const shippingAmount = ref(0)
const paymentFee = ref(0)

// Address management
const savedAddresses = ref<any[]>([])
const selectedShippingAddressId = ref<number | null>(null)
const selectedBillingAddressId = ref<number | null>(null)
const defaultShippingAddress = computed(() => savedAddresses.value.find(a => a.is_default_shipping))
const defaultBillingAddress = computed(() => savedAddresses.value.find(a => a.is_default_billing))

// Payment confirmation
const showPaymentConfirmation = ref(false)
const paymentConfirmationData = ref({
  sender_name: '',
  sender_account: '',
  reference_number: '',
  payment_date: '',
  card_number: '',
  card_holder_name: '',
  card_expiry: '',
  card_cvv: '',
  proof_image: '' // Base64 encoded image
})
const pendingOrderData = ref<any>(null)
const proofImageInput = ref<HTMLInputElement | null>(null)
const proofImagePreview = ref<string>('')

// Philippine locations
const provinceNames = computed(() => getProvinceNames())
const availableCities = computed(() => {
  if (!form.value.shipping_province) return []
  return getCitiesByProvince(form.value.shipping_province)
})

// Computed
const cartItems = computed(() => cartStore.items)
const subtotal = computed(() => {
  const value = cartStore.subtotal
  // Ensure proper number conversion - handle string values
  if (typeof value === 'string') {
    return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0
  }
  return Number(value) || 0
})
const discount = computed(() => {
  const value = cartStore.discount
  // Ensure proper number conversion - handle string values
  if (typeof value === 'string') {
    return parseFloat(value.replace(/[^0-9.-]/g, '')) || 0
  }
  return Number(value) || 0
})

// Get selected payment method
const selectedPaymentMethod = computed(() => {
  if (!form.value.payment_method_id) return null
  return paymentMethods.value.find(m => m.id === form.value.payment_method_id) || null
})

// Calculate payment fee based on selected method
const calculatePaymentFee = (): number => {
  if (!selectedPaymentMethod.value) return 0
  
  const method = selectedPaymentMethod.value
  // Ensure proper number conversion
  const sub = typeof subtotal.value === 'number' ? subtotal.value : parseFloat(String(subtotal.value)) || 0
  const disc = typeof discount.value === 'number' ? discount.value : parseFloat(String(discount.value)) || 0
  const shipping = typeof shippingAmount.value === 'number' ? shippingAmount.value : parseFloat(String(shippingAmount.value)) || 0
  const amountBeforeFee = sub - disc + shipping
  
  // Check min/max amount constraints
  const minAmount = typeof method.min_amount === 'number' ? method.min_amount : parseFloat(String(method.min_amount)) || 0
  const maxAmount = typeof method.max_amount === 'number' ? method.max_amount : parseFloat(String(method.max_amount)) || 0
  
  if (method.min_amount && amountBeforeFee < minAmount) return 0
  if (method.max_amount && amountBeforeFee > maxAmount) return 0
  
  // Calculate fee: fixed + percentage
  const feePercentage = typeof method.fee_percentage === 'number' ? method.fee_percentage : parseFloat(String(method.fee_percentage)) || 0
  const feeFixed = typeof method.fee_fixed === 'number' ? method.fee_fixed : parseFloat(String(method.fee_fixed)) || 0
  
  const percentageFee = amountBeforeFee * (feePercentage / 100)
  return parseFloat((feeFixed + percentageFee).toFixed(2))
}

// Watch for payment method or amount changes to recalculate fee
watch(
  [() => form.value.payment_method_id, subtotal, discount, shippingAmount],
  () => {
    paymentFee.value = calculatePaymentFee()
  },
  { immediate: true }
)

const total = computed(() => {
  // Ensure all values are numbers, not strings
  const sub = typeof subtotal.value === 'number' ? subtotal.value : parseFloat(String(subtotal.value)) || 0
  const disc = typeof discount.value === 'number' ? discount.value : parseFloat(String(discount.value)) || 0
  const shipping = typeof shippingAmount.value === 'number' ? shippingAmount.value : parseFloat(String(shippingAmount.value)) || 0
  const fee = typeof paymentFee.value === 'number' ? paymentFee.value : parseFloat(String(paymentFee.value)) || 0
  
  // Calculate total with proper numeric operations
  const baseTotal = sub - disc + shipping
  const finalTotal = baseTotal + fee
  
  // Return as number with 2 decimal places
  return parseFloat(finalTotal.toFixed(2))
})

// Methods
const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2 })
}

const getZoneDisplayText = (zone: { name: string; base_rate: number; free_shipping_threshold?: number; min_delivery_days?: number; max_delivery_days?: number }) => {
  let text = `${zone.name} - ₱${formatPrice(zone.base_rate)}`
  if (zone.free_shipping_threshold) {
    text += ` (Free over ₱${formatPrice(zone.free_shipping_threshold)})`
  }
  if (zone.min_delivery_days && zone.max_delivery_days) {
    text += ` - ${zone.min_delivery_days}-${zone.max_delivery_days} days`
  }
  return text
}

const getSelectedZoneName = () => {
  const zone = shippingZones.value.find((z) => z.id === form.value.shipping_zone_id)
  return zone ? zone.name : ''
}

const selectPaymentMethod = (method: PaymentMethod) => {
  if (!method.is_active) {
    checkoutError.value = 'This payment method is currently unavailable.'
    return
  }
  
  // Check amount constraints
  const amountBeforeFee = subtotal.value - discount.value + shippingAmount.value
  if (method.min_amount && amountBeforeFee < method.min_amount) {
    checkoutError.value = `Minimum amount for ${method.name} is ₱${formatPrice(method.min_amount)}.`
    return
  }
  if (method.max_amount && amountBeforeFee > method.max_amount) {
    checkoutError.value = `Maximum amount for ${method.name} is ₱${formatPrice(method.max_amount)}.`
    return
  }
  
  form.value.payment_method_id = method.id
  checkoutError.value = '' // Clear any previous errors
}

// Province to Region mapping (Philippines)
const provinceToRegionMap: Record<string, string> = {
  // Metro Manila / NCR
  'Metro Manila': 'NCR',
  'NCR': 'NCR',
  
  // Calabarzon (Region IV-A)
  'Cavite': 'Calabarzon',
  'Laguna': 'Calabarzon',
  'Batangas': 'Calabarzon',
  'Rizal': 'Calabarzon',
  'Quezon': 'Calabarzon',
  
  // Central Luzon (Region III)
  'Bulacan': 'Central Luzon',
  'Pampanga': 'Central Luzon',
  'Nueva Ecija': 'Central Luzon',
  'Tarlac': 'Central Luzon',
  'Zambales': 'Central Luzon',
  'Bataan': 'Central Luzon',
  'Aurora': 'Central Luzon',
  
  // Ilocos Region (Region I)
  'Ilocos Norte': 'Ilocos Region',
  'Ilocos Sur': 'Ilocos Region',
  'La Union': 'Ilocos Region',
  'Pangasinan': 'Ilocos Region',
  
  // Cagayan Valley (Region II)
  'Cagayan': 'Cagayan Valley',
  'Isabela': 'Cagayan Valley',
  'Nueva Vizcaya': 'Cagayan Valley',
  'Quirino': 'Cagayan Valley',
  'Batanes': 'Cagayan Valley',
  
  // Bicol Region (Region V)
  'Albay': 'Bicol Region',
  'Camarines Norte': 'Bicol Region',
  'Camarines Sur': 'Bicol Region',
  'Catanduanes': 'Bicol Region',
  'Masbate': 'Bicol Region',
  'Sorsogon': 'Bicol Region',
  
  // Western Visayas (Region VI)
  'Aklan': 'Western Visayas',
  'Antique': 'Western Visayas',
  'Capiz': 'Western Visayas',
  'Guimaras': 'Western Visayas',
  'Iloilo': 'Western Visayas',
  'Negros Occidental': 'Western Visayas',
  
  // Central Visayas (Region VII)
  'Bohol': 'Central Visayas',
  'Cebu': 'Central Visayas',
  'Negros Oriental': 'Central Visayas',
  'Siquijor': 'Central Visayas',
  
  // Eastern Visayas (Region VIII)
  'Biliran': 'Eastern Visayas',
  'Eastern Samar': 'Eastern Visayas',
  'Leyte': 'Eastern Visayas',
  'Northern Samar': 'Eastern Visayas',
  'Samar': 'Eastern Visayas',
  'Southern Leyte': 'Eastern Visayas',
  
  // Zamboanga Peninsula (Region IX)
  'Zamboanga del Norte': 'Zamboanga Peninsula',
  'Zamboanga del Sur': 'Zamboanga Peninsula',
  'Zamboanga Sibugay': 'Zamboanga Peninsula',
  
  // Northern Mindanao (Region X)
  'Bukidnon': 'Northern Mindanao',
  'Camiguin': 'Northern Mindanao',
  'Lanao del Norte': 'Northern Mindanao',
  'Misamis Occidental': 'Northern Mindanao',
  'Misamis Oriental': 'Northern Mindanao',
  
  // Davao Region (Region XI)
  'Davao del Norte': 'Davao Region',
  'Davao del Sur': 'Davao Region',
  'Davao Occidental': 'Davao Region',
  'Davao Oriental': 'Davao Region',
  
  // SOCCSKSARGEN (Region XII)
  'Cotabato': 'SOCCSKSARGEN',
  'Sarangani': 'SOCCSKSARGEN',
  'South Cotabato': 'SOCCSKSARGEN',
  'Sultan Kudarat': 'SOCCSKSARGEN',
  
  // CARAGA (Region XIII)
  'Agusan del Norte': 'CARAGA',
  'Agusan del Sur': 'CARAGA',
  'Dinagat Islands': 'CARAGA',
  'Surigao del Norte': 'CARAGA',
  'Surigao del Sur': 'CARAGA',
  
  // Cordillera Administrative Region (CAR)
  'Abra': 'CAR',
  'Apayao': 'CAR',
  'Benguet': 'CAR',
  'Ifugao': 'CAR',
  'Kalinga': 'CAR',
  'Mountain Province': 'CAR',
  
  // MIMAROPA (Region IV-B)
  'Marinduque': 'MIMAROPA',
  'Occidental Mindoro': 'MIMAROPA',
  'Oriental Mindoro': 'MIMAROPA',
  'Palawan': 'MIMAROPA',
  'Romblon': 'MIMAROPA',
  
  // BARMM (Bangsamoro Autonomous Region)
  'Basilan': 'BARMM',
  'Lanao del Sur': 'BARMM',
  'Maguindanao': 'BARMM',
  'Sulu': 'BARMM',
  'Tawi-Tawi': 'BARMM',
}

// Function to get region from province
const getRegionFromProvince = (province: string): string | null => {
  if (!province) return null
  
  // Direct lookup
  const region = provinceToRegionMap[province]
  if (region) return region
  
  // Case-insensitive lookup
  const provinceLower = province.toLowerCase()
  for (const [key, value] of Object.entries(provinceToRegionMap)) {
    if (key.toLowerCase() === provinceLower) {
      return value
    }
  }
  
  return null
}

// Function to find matching shipping zone based on location
const findMatchingShippingZone = (province: string, city?: string): ShippingZone | null => {
  if (!province || shippingZones.value.length === 0) return null
  
  // Get the region for the province
  const region = getRegionFromProvince(province)
  
  if (!region) {
    // Fallback: try to find zone that matches province name directly
    for (const zone of shippingZones.value) {
      const regions = zone.regions || []
      if (Array.isArray(regions)) {
        const provinceMatch = regions.some((r: string) => 
          r.toLowerCase().includes(province.toLowerCase()) || 
          province.toLowerCase().includes(r.toLowerCase())
        )
        if (provinceMatch) {
          return zone
        }
      }
    }
    // If no match, return first available zone as fallback
    return shippingZones.value.length > 0 ? shippingZones.value[0] : null
  }
  
  // Find zone that matches the region
  for (const zone of shippingZones.value) {
    const zoneName = zone.name || ''
    const regions = zone.regions || []
    
    // Check if zone name matches the region
    if (zoneName.toLowerCase().includes(region.toLowerCase()) || 
        region.toLowerCase().includes(zoneName.toLowerCase())) {
      return zone
    }
    
    // Check if any region in the zone matches
    if (Array.isArray(regions)) {
      const regionMatch = regions.some((r: string) => 
        r.toLowerCase().includes(region.toLowerCase()) || 
        region.toLowerCase().includes(r.toLowerCase())
      )
      if (regionMatch) {
        return zone
      }
    }
  }
  
  // If no exact match, return first available zone as fallback
  return shippingZones.value.length > 0 ? shippingZones.value[0] : null
}

const onProvinceChange = () => {
  // Reset city when province changes
  form.value.shipping_city = ''
  form.value.shipping_postal_code = ''
  
  // Auto-select shipping zone based on province
  if (form.value.shipping_province) {
    const matchingZone = findMatchingShippingZone(form.value.shipping_province)
    if (matchingZone) {
      form.value.shipping_zone_id = matchingZone.id
      updateShipping()
    }
  }
}

// Format shipping address line 1
const formatShippingAddressLine1 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value
    // Capitalize first letter of each word, remove extra spaces
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    form.value.shipping_address_line_1 = value
  } else {
    let value = form.value.shipping_address_line_1 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    form.value.shipping_address_line_1 = value
  }
}

// Format shipping address line 2
const formatShippingAddressLine2 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value
    // Capitalize first letter of each word, remove extra spaces
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    form.value.shipping_address_line_2 = value
  } else {
    let value = form.value.shipping_address_line_2 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    form.value.shipping_address_line_2 = value
  }
}

// Watch for city changes to update shipping zone and postal code
watch(
  () => form.value.shipping_city,
  (newCity) => {
    if (newCity && form.value.shipping_province) {
      const matchingZone = findMatchingShippingZone(form.value.shipping_province, newCity)
      if (matchingZone && matchingZone.id !== form.value.shipping_zone_id) {
        form.value.shipping_zone_id = matchingZone.id
        updateShipping()
      }
      
      // Auto-fill postal code
      const postalCode = getPostalCode(form.value.shipping_province, newCity)
      if (postalCode) {
        form.value.shipping_postal_code = postalCode
      }
    } else if (!newCity) {
      form.value.shipping_postal_code = ''
    }
  }
)

// Watch for province changes to update postal code
watch(
  () => form.value.shipping_province,
  (newProvince) => {
    if (newProvince && form.value.shipping_city) {
      const postalCode = getPostalCode(newProvince, form.value.shipping_city)
      if (postalCode) {
        form.value.shipping_postal_code = postalCode
      }
    } else if (!newProvince) {
      form.value.shipping_postal_code = ''
    }
  }
)

const getPaymentIcon = (code: string): string | null => {
  // Map payment method codes to image file paths
  const iconMap: Record<string, string> = {
    gcash: '/images/payment-methods/gcash.png',
    maya: '/images/payment-methods/maya.png',
    bank_bdo: '/images/payment-methods/bdo.png',
    bank_bpi: '/images/payment-methods/bpi.png',
    bank_metrobank: '/images/payment-methods/metrobank.png',
    metrobank: '/images/payment-methods/metrobank.png',
    paypal: '/images/payment-methods/paypal.png',
    // Keep SVG fallbacks for card and COD
    card: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHJ4PSI0IiBmaWxsPSIjMUYyOTM3Ii8+PHRleHQgeD0iMTYiIHk9IjIwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Q0FSRDwvdGV4dD48L3N2Zz4=',
    cod: 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHZpZXdCb3g9IjAgMCAzMiAzMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMzIiIGhlaWdodD0iMzIiIHJ4PSI0IiBmaWxsPSIjMDU5NjY5Ii8+PHRleHQgeD0iMTYiIHk9IjIwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTAiIGZvbnQtd2VpZ2h0PSJib2xkIiBmaWxsPSJ3aGl0ZSIgdGV4dC1hbmNob3I9Im1pZGRsZSI+Q09EPC90ZXh0Pjwvc3ZnPg==',
  }
  return iconMap[code.toLowerCase()] || null
}

const getPaymentFeeDisplay = (method: PaymentMethod): string => {
  if (method.fee_percentage > 0 && method.fee_fixed > 0) {
    return `+₱${formatPrice(method.fee_fixed)} + ${method.fee_percentage}%`
  } else if (method.fee_percentage > 0) {
    return `+${method.fee_percentage}%`
  } else if (method.fee_fixed > 0) {
    return `+₱${formatPrice(method.fee_fixed)}`
  }
  return ''
}

const isPaymentMethodAvailable = (method: PaymentMethod): boolean => {
  if (!method.is_active) return false
  
  const amountBeforeFee = subtotal.value - discount.value + shippingAmount.value
  
  if (method.min_amount && amountBeforeFee < method.min_amount) return false
  if (method.max_amount && amountBeforeFee > method.max_amount) return false
  
  return true
}

// Proof image upload handlers
const triggerProofImageUpload = () => {
  proofImageInput.value?.click()
}

const handleProofImageSelect = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (!file) return
  
  // Validate file type
  if (!file.type.startsWith('image/')) {
    checkoutError.value = 'Please select a valid image file.'
    return
  }
  
  // Validate file size (max 5MB)
  const maxSize = 5 * 1024 * 1024 // 5MB in bytes
  if (file.size > maxSize) {
    checkoutError.value = 'Image size must be less than 5MB. Please compress or select a smaller image.'
    return
  }
  
  // Read file as base64
  const reader = new FileReader()
  
  reader.onload = (e) => {
    const result = e.target?.result as string
    paymentConfirmationData.value.proof_image = result
    proofImagePreview.value = result
    checkoutError.value = '' // Clear any previous errors
  }
  
  reader.onerror = () => {
    checkoutError.value = 'Error reading image file. Please try again.'
  }
  
  reader.readAsDataURL(file)
}

const removeProofImage = () => {
  paymentConfirmationData.value.proof_image = ''
  proofImagePreview.value = ''
  if (proofImageInput.value) {
    proofImageInput.value.value = ''
  }
}

// Address management methods
const loadAddresses = async () => {
  try {
    const response = await addressesApi.list()
    
    if (response.data.success) {
      savedAddresses.value = response.data.data || []
      
      // Auto-select default shipping address if available
      if (defaultShippingAddress.value && !selectedShippingAddressId.value) {
        selectedShippingAddressId.value = defaultShippingAddress.value.id
        onShippingAddressSelect()
      }
    }
  } catch (error: any) {
    console.error('Failed to load addresses:', error)
  }
}

const useDefaultShippingAddress = () => {
  if (defaultShippingAddress.value) {
    selectedShippingAddressId.value = defaultShippingAddress.value.id
    onShippingAddressSelect()
  }
}

const onShippingAddressSelect = () => {
  if (selectedShippingAddressId.value) {
    const address = savedAddresses.value.find(a => a.id === selectedShippingAddressId.value)
    if (address) {
      form.value.shipping_name = address.recipient_name
      form.value.shipping_phone = address.phone
      form.value.shipping_address_line_1 = address.address_line_1
      form.value.shipping_address_line_2 = address.address_line_2 || ''
      form.value.shipping_city = address.city
      form.value.shipping_province = address.province
      
      // Auto-fill postal code if not provided in address
      if (address.postal_code) {
        form.value.shipping_postal_code = address.postal_code
      } else if (address.province && address.city) {
        const postalCode = getPostalCode(address.province, address.city)
        if (postalCode) {
          form.value.shipping_postal_code = postalCode
        }
      }
      
      // Auto-select shipping zone based on address location
      if (address.province) {
        const matchingZone = findMatchingShippingZone(address.province, address.city)
        if (matchingZone) {
          form.value.shipping_zone_id = matchingZone.id
          updateShipping()
        }
      }
    }
  }
}

const loadCheckoutData = async () => {
  isLoadingZones.value = true
  isLoadingPaymentMethods.value = true
  checkoutError.value = ''
  
  try {
    const [zonesRes, methodsRes] = await Promise.all([
      checkout.getShippingZones(),
      checkout.getPaymentMethods(),
    ])

    if (zonesRes.data.success) {
      const zones = zonesRes.data.data || []
      // Remove duplicates based on zone name (keep first occurrence)
      const uniqueZones = zones.filter((zone: ShippingZone, index: number, self: ShippingZone[]) => 
        index === self.findIndex((z: ShippingZone) => z.name === zone.name)
      )
      shippingZones.value = uniqueZones
    } else {
      console.error('Failed to load zones:', zonesRes.data)
      checkoutError.value = 'Failed to load shipping zones. Please refresh the page.'
    }
    
    if (methodsRes.data.success) {
      paymentMethods.value = methodsRes.data.data || []
      console.log('Loaded payment methods:', paymentMethods.value.length, paymentMethods.value)
      
      // Log active payment methods
      const activeMethods = paymentMethods.value.filter(m => m.is_active)
      console.log('Active payment methods:', activeMethods.length, activeMethods)
      
      // Auto-select first available payment method if none selected
      if (paymentMethods.value.length > 0 && !form.value.payment_method_id) {
        const firstAvailable = paymentMethods.value.find(m => isPaymentMethodAvailable(m))
        if (firstAvailable) {
          form.value.payment_method_id = firstAvailable.id
          console.log('Auto-selected payment method:', firstAvailable.name)
        } else {
          // If no available method, still try to select first active one
          const firstActive = paymentMethods.value.find(m => m.is_active)
          if (firstActive) {
            form.value.payment_method_id = firstActive.id
            console.log('Auto-selected first active payment method:', firstActive.name)
          }
        }
      }
    } else {
      console.error('Failed to load payment methods:', methodsRes.data)
      checkoutError.value = 'Failed to load payment methods. Please refresh the page.'
    }
  } catch (error: unknown) {
    console.error('Failed to load checkout data:', error)
    const apiError = error as { response?: { data?: { message?: string } } }
    checkoutError.value = apiError.response?.data?.message || 'Failed to load checkout data. Please try again.'
    shippingZones.value = []
    paymentMethods.value = []
  } finally {
    isLoadingZones.value = false
    isLoadingPaymentMethods.value = false
  }
}

const updateShipping = () => {
  const zone = shippingZones.value.find((z) => z.id === form.value.shipping_zone_id)
  if (zone) {
    calculateShippingCost(zone)
  } else {
    shippingAmount.value = 0
  }
}

const calculateShippingCost = (zone: any) => {
  // Ensure proper number conversion
  const sub = typeof subtotal.value === 'number' ? subtotal.value : parseFloat(String(subtotal.value)) || 0
  const freeThreshold = typeof zone.free_shipping_threshold === 'number' 
    ? zone.free_shipping_threshold 
    : parseFloat(String(zone.free_shipping_threshold)) || Infinity
  const baseRate = typeof zone.base_rate === 'number' 
    ? zone.base_rate 
    : parseFloat(String(zone.base_rate)) || 0
  
  shippingAmount.value = sub >= freeThreshold ? 0 : baseRate
}

// Watch cart subtotal to automatically recalculate shipping when cart changes
watch(
  () => subtotal.value,
  () => {
    if (form.value.shipping_zone_id) {
      updateShipping()
    }
  },
  { immediate: false }
)

// Watch cart items to recalculate shipping when items change
watch(
  () => cartItems.value.length,
  () => {
    if (form.value.shipping_zone_id) {
      updateShipping()
    }
  }
)

const handleCheckout = async () => {
  // Step 1: Verify authentication
  if (!authStore.isAuthenticated) {
    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    router.push({ name: 'home', query: { login: 'true' } })
    return
  }

  // Step 2: Verify authentication - tokens are in memory, not in authStore
  // Check if user is authenticated instead of checking for token property
  if (!authStore.isAuthenticated || !authStore.user) {
    checkoutError.value = 'Please log in to continue with checkout.'
    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    router.push({ name: 'home', query: { login: 'true' } })
    return
  }

  try {
    const authResult = await authStore.fetchUser()
    // fetchUser always returns an object, so we can safely check it
    if (!authResult.success || !authStore.isAuthenticated) {
      checkoutError.value = authResult.expired 
        ? 'Your session has expired. Please log in again.'
        : 'Authentication failed. Please log in again.'
      sessionStorage.setItem('redirectAfterLogin', '/checkout')
      setTimeout(() => {
        router.push({ name: 'home', query: { login: 'true' } })
      }, 2000)
      return
    }
  } catch (error) {
    console.error('Failed to verify authentication:', error)
    checkoutError.value = 'Authentication failed. Please log in again.'
    sessionStorage.setItem('redirectAfterLogin', '/checkout')
    setTimeout(() => {
      router.push({ name: 'home', query: { login: 'true' } })
    }, 2000)
    return
  }

  // Step 3: Validate and refresh cart
  try {
    await cartStore.fetchCart()
  } catch (error) {
    console.error('Failed to fetch cart:', error)
    checkoutError.value = 'Failed to load cart. Please try again.'
    return
  }

  // Step 4: Validate cart has items
  if (!cartStore.items || cartStore.items.length === 0) {
    checkoutError.value = 'Your cart is empty. Please add items before checkout.'
    return
  }

  // Step 5: Validate form fields
  // Only validate manual address fields if no saved address is selected
  if (!selectedShippingAddressId.value) {
    if (!form.value.shipping_name?.trim()) {
      checkoutError.value = 'Please enter recipient name.'
      return
    }

    if (!form.value.shipping_phone?.trim()) {
      checkoutError.value = 'Please enter recipient phone number.'
      return
    }

    if (!form.value.shipping_address_line_1?.trim()) {
      checkoutError.value = 'Please enter shipping address.'
      return
    }

    if (!form.value.shipping_city?.trim()) {
      checkoutError.value = 'Please select shipping city.'
      return
    }

    if (!form.value.shipping_province?.trim()) {
      checkoutError.value = 'Please select shipping province.'
      return
    }

    if (!form.value.shipping_postal_code?.trim()) {
      checkoutError.value = 'Please enter postal code.'
      return
    }
  } else {
    // Validate that selected address exists
    const selectedAddress = savedAddresses.value.find(a => a.id === selectedShippingAddressId.value)
    if (!selectedAddress) {
      checkoutError.value = 'Selected address not found. Please select another address.'
      return
    }
  }

  if (!form.value.shipping_zone_id) {
    checkoutError.value = 'Please select a shipping zone.'
    return
  }

  if (!form.value.payment_method_id) {
    checkoutError.value = 'Please select a payment method.'
    return
  }
  
  // Step 6: Validate selected payment method is still available
  const selectedMethod = paymentMethods.value.find(m => m.id === form.value.payment_method_id)
  if (!selectedMethod || !isPaymentMethodAvailable(selectedMethod)) {
    checkoutError.value = 'The selected payment method is no longer available. Please select another method.'
    return
  }

  // Step 7: Ensure shipping cost is calculated
  if (form.value.shipping_zone_id) {
    updateShipping()
  }

  // Step 8: Prepare order data
  const orderData: any = {
    shipping_zone_id: form.value.shipping_zone_id,
    payment_method_id: form.value.payment_method_id,
    billing_same_as_shipping: form.value.billing_same_as_shipping,
    notes: form.value.notes?.trim() || null,
  }

  // Add address data (either ID or full address)
  if (selectedShippingAddressId.value) {
    orderData.shipping_address_id = selectedShippingAddressId.value
  } else {
    orderData.shipping_name = form.value.shipping_name.trim()
    orderData.shipping_phone = form.value.shipping_phone.trim()
    orderData.shipping_address_line_1 = form.value.shipping_address_line_1.trim()
    orderData.shipping_address_line_2 = form.value.shipping_address_line_2?.trim() || null
    orderData.shipping_city = form.value.shipping_city.trim()
    orderData.shipping_province = form.value.shipping_province.trim()
    orderData.shipping_postal_code = form.value.shipping_postal_code.trim()
  }

  if (selectedBillingAddressId.value && !form.value.billing_same_as_shipping) {
    orderData.billing_address_id = selectedBillingAddressId.value
  }

  // Store order data and show payment confirmation
  pendingOrderData.value = orderData
  
  // Show payment confirmation modal
  showPaymentConfirmation.value = true
}

const submitOrderWithConfirmation = async () => {
  if (!pendingOrderData.value) return

  isSubmitting.value = true
  checkoutError.value = ''

  try {
    // Prepare payment confirmation data
    const paymentConfirmation: any = {}
    
    if (selectedPaymentMethod.value) {
      const method = selectedPaymentMethod.value
      const methodCode = (method.code || '').toLowerCase()
      
      // For GCash, PayPal, Maya - require sender details
      if (['gcash', 'paypal', 'maya'].includes(methodCode)) {
        if (!paymentConfirmationData.value.sender_name?.trim()) {
          checkoutError.value = 'Please enter sender name.'
          isSubmitting.value = false
          return
        }
        if (!paymentConfirmationData.value.reference_number?.trim()) {
          checkoutError.value = 'Please enter reference number.'
          isSubmitting.value = false
          return
        }
        
        paymentConfirmation.sender_name = paymentConfirmationData.value.sender_name.trim()
        paymentConfirmation.sender_account = paymentConfirmationData.value.sender_account?.trim() || null
        paymentConfirmation.reference_number = paymentConfirmationData.value.reference_number.trim()
        paymentConfirmation.payment_date = paymentConfirmationData.value.payment_date || new Date().toISOString().split('T')[0]
        // Add proof image if uploaded
        if (paymentConfirmationData.value.proof_image) {
          paymentConfirmation.proof_image = paymentConfirmationData.value.proof_image
        }
      }
      
      // For bank transfers - require card details
      if (method.type === 'bank_transfer' || methodCode.includes('bank')) {
        if (!paymentConfirmationData.value.card_number?.trim()) {
          checkoutError.value = 'Please enter card number.'
          isSubmitting.value = false
          return
        }
        if (!paymentConfirmationData.value.card_holder_name?.trim()) {
          checkoutError.value = 'Please enter card holder name.'
          isSubmitting.value = false
          return
        }
        if (!paymentConfirmationData.value.card_expiry?.trim()) {
          checkoutError.value = 'Please enter card expiry date.'
          isSubmitting.value = false
          return
        }
        
        paymentConfirmation.card_number = paymentConfirmationData.value.card_number.trim()
        paymentConfirmation.card_holder_name = paymentConfirmationData.value.card_holder_name.trim()
        paymentConfirmation.card_expiry = paymentConfirmationData.value.card_expiry.trim()
        // CVV is not stored for security, but we validate it
        if (!paymentConfirmationData.value.card_cvv?.trim()) {
          checkoutError.value = 'Please enter card CVV.'
          isSubmitting.value = false
          return
        }
      }
    }
    
    // Add payment confirmation to order data
    if (Object.keys(paymentConfirmation).length > 0) {
      pendingOrderData.value.payment_confirmation = paymentConfirmation
    }
    
    const response = await orders.create(pendingOrderData.value)

    if (response.data.success) {
      orderNumber.value = response.data.data.order.order_number
      orderDetails.value = response.data.data.order
      
      // Fetch full order details with payment info
      try {
        const orderResponse = await orders.get(orderNumber.value)
        if (orderResponse.data.success) {
          orderDetails.value = orderResponse.data.data
        }
      } catch (error) {
        console.error('Failed to fetch order details:', error)
      }
      
      orderPlaced.value = true
      showPaymentConfirmation.value = false
      // Reset payment confirmation data
      paymentConfirmationData.value = {
        sender_name: '',
        sender_account: '',
        reference_number: '',
        payment_date: '',
        card_number: '',
        card_holder_name: '',
        card_expiry: '',
        card_cvv: '',
        proof_image: ''
      }
      proofImagePreview.value = ''
      if (proofImageInput.value) {
        proofImageInput.value.value = ''
      }
      await cartStore.fetchCart() // Refresh cart
    } else {
      checkoutError.value = response.data.message || 'Failed to place order'
    }
  } catch (error: unknown) {
    console.error('Order creation error:', error)
    
    const apiError = error as { 
      response?: { 
        status?: number
        data?: { 
          message?: string
          errors?: Record<string, string[]>
          error?: string
        } 
      }
      message?: string
    }
    
    // Handle 401 Unauthorized
    if (apiError.response?.status === 401) {
      checkoutError.value = 'Your session has expired. Please log in again.'
      sessionStorage.setItem('redirectAfterLogin', '/checkout')
      setTimeout(() => {
        router.push({ name: 'home', query: { login: 'true' } })
      }, 2000)
      return
    }
    
    // Handle 422 Validation errors
    if (apiError.response?.status === 422) {
      const errors = apiError.response.data?.errors
      if (errors) {
        const errorMessages = Object.values(errors).flat()
        checkoutError.value = errorMessages.join(', ') || 'Please check your form data and try again.'
      } else {
        checkoutError.value = apiError.response.data?.message || 'Please check your form data and try again.'
      }
      return
    }
    
    // Handle 500 Internal Server Error
    if (apiError.response?.status === 500) {
      checkoutError.value = apiError.response.data?.message || 'A server error occurred. Please try again later or contact support if the problem persists.'
      return
    }
    
    // Handle other errors
    checkoutError.value = apiError.response?.data?.message || apiError.message || 'Failed to place order. Please try again.'
  } finally {
    isSubmitting.value = false
  }
}

// Watch for modal close to reset payment confirmation data
watch(showPaymentConfirmation, (isOpen) => {
  if (!isOpen) {
    // Reset payment confirmation data when modal closes
    paymentConfirmationData.value = {
      sender_name: '',
      sender_account: '',
      reference_number: '',
      payment_date: '',
      card_number: '',
      card_holder_name: '',
      card_expiry: '',
      card_cvv: '',
      proof_image: ''
    }
    proofImagePreview.value = ''
    if (proofImageInput.value) {
      proofImageInput.value.value = ''
    }
  }
})

// Load user data if authenticated
onMounted(async () => {
  try {
    // Step 1: Check authentication - tokens are in memory, not in authStore
    // The router guard already verified authentication, but we'll verify token is still valid
    if (!authStore.isAuthenticated || !authStore.user) {
      sessionStorage.setItem('redirectAfterLogin', '/checkout')
      router.push({ name: 'home', query: { login: 'true' } })
      return
    }

    // Step 2: Verify token is still valid (refresh if needed)
    const authResult = await authStore.fetchUser()
    
    // fetchUser always returns an object, so we can safely check it
    if (!authResult.success || !authStore.isAuthenticated) {
      checkoutError.value = authResult.expired 
        ? 'Your session has expired. Please log in again.'
        : 'Please log in to continue with checkout.'
      sessionStorage.setItem('redirectAfterLogin', '/checkout')
      setTimeout(() => {
        router.push({ name: 'home', query: { login: 'true' } })
      }, 2000)
      return
    }

    // Step 3: Load addresses and cart
    await loadAddresses()
    await cartStore.fetchCart()
    
    // Step 4: Validate cart has items
    if (!cartStore.items || cartStore.items.length === 0) {
      checkoutError.value = 'Your cart is empty. Please add items before checkout.'
      setTimeout(() => {
        router.push('/cart')
      }, 2000)
      return
    }
    
    // Step 5: Load checkout data (zones, payment methods)
    await loadCheckoutData()

    // Step 6: Pre-fill user data
    if (authStore.user) {
      if (!form.value.shipping_name) {
        form.value.shipping_name = authStore.user.full_name
      }
      if (!form.value.shipping_phone && authStore.user.phone) {
        form.value.shipping_phone = authStore.user.phone
      }
    }

    // Step 7: Auto-select first shipping zone if available
    if (shippingZones.value.length > 0 && cartItems.value.length > 0 && !form.value.shipping_zone_id) {
      form.value.shipping_zone_id = shippingZones.value[0].id
      updateShipping()
    }
  } catch (error) {
    console.error('Failed to initialize checkout:', error)
    const apiError = error as { response?: { status?: number; data?: { message?: string } } }
    
    if (apiError.response?.status === 401) {
      checkoutError.value = 'Your session has expired. Please log in again.'
      sessionStorage.setItem('redirectAfterLogin', '/checkout')
      setTimeout(() => {
        router.push({ name: 'home', query: { login: 'true' } })
      }, 2000)
    } else {
      checkoutError.value = apiError.response?.data?.message || 'Failed to load checkout. Please refresh the page.'
    }
  }
})
</script>

<style scoped>
.checkout-page {
  min-height: 100vh;
  background: #f5f7fa;
}

.checkout-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 450px;
  gap: 3rem;
  align-items: start;
}

.checkout-form-wrapper {
  background: white;
  border-radius: 20px;
  padding: 2.5rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.form-section {
  margin-bottom: 2.5rem;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  color: #1a1a1a;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
    gap: 1.25rem;
  }
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 0.625rem;
  color: #374151;
  font-size: 0.9rem;
  display: block;
  letter-spacing: 0.01em;
}

.select-wrapper {
  position: relative;
}

.form-group select.zone-select {
  cursor: pointer;
  background-color: white;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 14 14'%3E%3Cpath fill='%23666' d='M7 10L2 5h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 3rem;
  width: 100%;
}

.form-group select.zone-select:disabled {
  opacity: 0.6;
  cursor: wait;
}

.select-loading {
  position: absolute;
  right: 1rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
}

.spinner-small {
  width: 16px;
  height: 16px;
  border: 2px solid #e5e7eb;
  border-top-color: #c9a050;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.zone-info {
  display: flex;
  align-items: flex-start;
  gap: 0.5rem;
  margin-top: 0.75rem;
  padding: 0.75rem;
  background: #f0f9ff;
  border-left: 3px solid #c9a050;
  border-radius: 6px;
  color: #1e40af;
  font-size: 0.875rem;
  line-height: 1.5;
}

.info-icon {
  font-style: normal;
  flex-shrink: 0;
}

.zone-error {
  display: block;
  margin-top: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.5rem;
  background: #fee2e2;
  border-radius: 6px;
  border-left: 3px solid #dc2626;
}

.zone-error {
  display: block;
  margin-top: 0.5rem;
  color: #dc2626;
  font-size: 0.875rem;
  padding: 0.5rem;
  background: #fee2e2;
  border-radius: 6px;
  border-left: 3px solid #dc2626;
}

.postal-code-hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #6b7280;
  font-style: italic;
}

.form-group input,
.form-group select,
.form-group textarea {
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: #ffffff;
  color: #1a1a1a;
  font-family: inherit;
}

.form-group input:hover,
.form-group select:hover,
.form-group textarea:hover {
  border-color: #d1d5db;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #9ca3af;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #c9a050;
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.payment-methods {
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.payment-method-card {
  border: 2px solid #e5e7eb;
  border-radius: 16px;
  padding: 1.5rem;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  background: white;
  position: relative;
  overflow: hidden;
}

.payment-method-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, #c9a050, #b8860b);
  transform: scaleX(0);
  transition: transform 0.3s ease;
}

.payment-method-card:hover {
  border-color: #c9a050;
  background: linear-gradient(to bottom, #fffef9, #fefbf5);
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(201, 160, 80, 0.12);
}

.payment-method-card:hover::before {
  transform: scaleX(1);
}

.payment-method-card.active {
  border-color: #c9a050;
  background: linear-gradient(to bottom, #fffef9, #fefbf5);
  box-shadow: 0 8px 24px rgba(201, 160, 80, 0.2);
  transform: translateY(-2px);
}

.payment-method-card.active::before {
  transform: scaleX(1);
}

.payment-method-card.disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f9fafb;
}

.payment-method-card.disabled:hover {
  border-color: #e5e7eb;
  background: #f9fafb;
  transform: none;
  box-shadow: none;
}

.payment-method-card:focus-visible {
  outline: 3px solid rgba(201, 160, 80, 0.5);
  outline-offset: 2px;
}

.method-header {
  display: flex;
  align-items: flex-start;
  gap: 1rem;
  position: relative;
}

.method-radio-wrapper {
  position: relative;
  flex-shrink: 0;
}

.method-radio-wrapper input[type="radio"] {
  position: absolute;
  opacity: 0;
  cursor: pointer;
  width: 0;
  height: 0;
}

.custom-radio {
  display: inline-block;
  width: 20px;
  height: 20px;
  border: 2px solid #d1d5db;
  border-radius: 50%;
  background: white;
  position: relative;
  transition: all 0.2s ease;
  cursor: pointer;
}

.custom-radio.checked {
  border-color: #c9a050;
  background: #c9a050;
}

.custom-radio.checked::after {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  width: 8px;
  height: 8px;
  border-radius: 50%;
  background: white;
}

.method-radio-wrapper input[type="radio"]:disabled + .custom-radio {
  opacity: 0.5;
  cursor: not-allowed;
  background: #f3f4f6;
  border-color: #d1d5db;
}

.method-radio-wrapper input[type="radio"]:disabled:checked + .custom-radio {
  background: #d1d5db;
  border-color: #d1d5db;
}

.method-title-row {
  display: flex;
  align-items: center;
  flex-wrap: wrap;
  gap: 0.5rem;
}

.method-name {
  font-weight: 600;
  font-size: 1.1rem;
  color: #1a1a1a;
  cursor: pointer;
  flex: 1;
  min-width: 0;
}

.method-description {
  margin-top: 0.75rem;
  font-size: 0.875rem;
  color: #6b7280;
  line-height: 1.6;
}

.method-unavailable {
  margin-top: 0.75rem;
  font-size: 0.8rem;
  color: #dc2626;
  line-height: 1.5;
  font-weight: 500;
  padding: 0.5rem;
  background: #fee2e2;
  border-radius: 6px;
  border-left: 3px solid #dc2626;
}


.method-icon {
  display: inline-block;
  width: 24px;
  height: 24px;
  margin-right: 0.75rem;
  vertical-align: middle;
  flex-shrink: 0;
}

.method-icon img {
  width: 100%;
  height: 100%;
  object-fit: contain;
  border-radius: 4px;
  display: block;
}

.fee-badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  background: #fef3c7;
  color: #92400e;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 600;
}

.amount-limit-badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  background: #e0f2fe;
  color: #0369a1;
  border-radius: 4px;
  font-size: 0.7rem;
  font-weight: 500;
}

.payment-method-badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  background: #f0f9ff;
  color: #0369a1;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.account-details {
  margin-top: 1.25rem;
  padding-top: 1.25rem;
  border-top: 2px solid #f3f4f6;
  animation: slideDown 0.3s ease;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.account-details-header {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-bottom: 1rem;
  color: #374151;
  font-weight: 600;
  font-size: 0.9rem;
}

.account-details-header svg {
  width: 18px;
  height: 18px;
  color: #c9a050;
}

.account-info {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  background: #f9fafb;
  padding: 1rem;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.account-item {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.account-label {
  font-size: 0.75rem;
  color: #6b7280;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.account-value {
  font-size: 0.95rem;
  color: #1f2937;
  font-weight: 600;
  font-family: 'Courier New', monospace;
}

.loading-payment-methods,
.no-payment-methods {
  padding: 2rem;
  text-align: center;
  color: #6b7280;
}

.loading-payment-methods {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.75rem;
}

.error-message {
  background: #fee2e2;
  border: 1px solid #fecaca;
  color: #dc2626;
  padding: 1rem;
  border-radius: 10px;
  margin-bottom: 1.5rem;
  display: flex;
  align-items: center;
  font-weight: 500;
  animation: shake 0.3s ease-in-out;
}

@keyframes shake {
  0%, 100% { transform: translateX(0); }
  25% { transform: translateX(-5px); }
  75% { transform: translateX(5px); }
}

.btn-submit {
  width: 100%;
  padding: 1.25rem;
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 10px 30px rgba(201, 160, 80, 0.4);
}

.btn-submit:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.order-summary-sidebar {
  position: relative;
}

.summary-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
  border: 1px solid #f0f0f0;
  position: sticky;
  top: 2rem;
}

.summary-card h4 {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  margin-bottom: 1.5rem;
  color: #1a1a1a;
  font-weight: 700;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f0f0f0;
}

.summary-items {
  margin-bottom: 1.5rem;
  max-height: 400px;
  overflow-y: auto;
}

.empty-cart-warning {
  text-align: center;
  padding: 3rem 1.5rem;
  color: #6b7280;
}

.empty-cart-warning svg {
  width: 64px;
  height: 64px;
  margin: 0 auto 1rem;
  color: #d1d5db;
}

.empty-cart-warning p {
  font-size: 1rem;
  margin-bottom: 1.5rem;
  color: #6b7280;
}

.btn-back-to-cart {
  display: inline-block;
  padding: 0.75rem 1.5rem;
  background: #c9a050;
  color: white;
  text-decoration: none;
  border-radius: 8px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.btn-back-to-cart:hover {
  background: #b8860b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.summary-item {
  display: flex;
  gap: 1rem;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 1px solid #f0f0f0;
}

.summary-item img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 8px;
}

.item-info {
  flex: 1;
}

.item-name {
  font-weight: 600;
  font-size: 0.9rem;
  margin-bottom: 0.25rem;
  color: #000000;
}

.item-qty {
  font-size: 0.85rem;
  color: #666;
}

.item-price {
  font-weight: 600;
  color: #c9a050;
}

.summary-totals {
  border-top: 2px solid #e5e7eb;
  padding-top: 1rem;
}

.summary-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 0.75rem;
  font-size: 0.95rem;
  color: #000000;
  font-weight: 500;
}

.summary-row span:first-child {
  color: #000000;
  font-weight: 500;
}

.summary-row span:last-child {
  color: #000000;
  font-weight: 600;
}

.summary-row.total span:first-child {
  color: #000000;
  font-size: 1.1rem;
  font-weight: 700;
}

.summary-row.total span:last-child {
  color: #000000;
  font-size: 1.3rem;
  font-weight: 700;
}

.summary-row.total {
  font-size: 1.2rem;
  font-weight: 700;
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 2px solid #e5e7eb;
}

.summary-row .discount {
  color: #dc2626;
}

.summary-row .gold {
  color: #c9a050;
}

.shipping-zone-badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.125rem 0.5rem;
  background: #e0f2fe;
  color: #0369a1;
  border-radius: 4px;
  font-size: 0.75rem;
  font-weight: 500;
}

.shipping-warning {
  display: inline-block;
  margin-left: 0.5rem;
  color: #dc2626;
  font-size: 0.75rem;
  font-weight: 500;
}

.free-shipping {
  color: #059669;
  font-weight: 600;
}

.order-success {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  max-width: 900px;
  margin: 0 auto;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.success-icon {
  width: 100px;
  height: 100px;
  margin: 0 auto 2rem;
  background: linear-gradient(135deg, #c9a050, #b8860b);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
}

.success-icon svg {
  width: 60px;
  height: 60px;
}

.order-success h2 {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  margin-bottom: 1rem;
  color: #000000;
}

.order-number {
  font-size: 1.2rem;
  margin-bottom: 1rem;
  color: #000000;
}

.order-number strong {
  color: #000000;
}

.success-message {
  color: #666;
  margin-bottom: 2rem;
}

.success-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.btn-primary,
.btn-secondary {
  padding: 1rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s;
}

.btn-primary {
  background: linear-gradient(135deg, #c9a050, #b8860b);
  color: white;
}

.btn-secondary {
  background: white;
  color: #c9a050;
  border: 2px solid #c9a050;
}

@media (max-width: 1024px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
  
  .order-summary-sidebar {
    position: static;
  }
}
/* Address Selection Styles */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.btn-use-default {
  padding: 0.625rem 1.25rem;
  background: #f3f4f6;
  color: #4b5563;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-use-default:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
}

.address-select {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  background: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.address-select:focus {
  outline: none;
  border-color: #c9a050;
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

/* Payment Confirmation Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
}

.payment-confirmation-modal {
  background: white;
  border-radius: 24px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  position: relative;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
  padding: 2.5rem;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  width: 36px;
  height: 36px;
  border-radius: 50%;
  background: #f5f5f5;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.modal-close:hover {
  background: #eee;
  transform: rotate(90deg);
}

.modal-close svg {
  width: 18px;
  height: 18px;
  color: #666;
}

.modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 0.5rem;
}

.modal-subtitle {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 1.5rem;
}

.confirmation-summary {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  margin-bottom: 2rem;
}

.confirmation-summary .summary-row {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  font-size: 0.95rem;
}

.confirmation-summary .summary-row.total {
  border-top: 2px solid #e5e7eb;
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  font-weight: 600;
  font-size: 1.1rem;
}

.confirmation-summary .summary-row.total .gold {
  color: #000000;
  font-size: 1.25rem;
}

.confirmation-summary .summary-row {
  color: #000000;
}

.confirmation-summary .summary-row span {
  color: #000000;
}

.confirmation-summary .summary-row.total {
  color: #000000;
}

.confirmation-summary .summary-row.total span {
  color: #000000;
}

.payment-confirmation-form .form-group {
  margin-bottom: 1.25rem;
}

.payment-confirmation-form label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.payment-confirmation-form input {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.payment-confirmation-form input:focus {
  outline: none;
  border-color: #c9a050;
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.payment-confirmation-form .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.info-message {
  background: #eff6ff;
  border: 1px solid #bfdbfe;
  border-radius: 10px;
  padding: 1rem;
  color: #1e40af;
  margin-bottom: 1.5rem;
}

.info-message p {
  margin: 0;
  font-size: 0.9rem;
}

.modal-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

.modal-actions .btn-secondary {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
  padding: 0.875rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-actions .btn-secondary:hover:not(:disabled) {
  border-color: #d1d5db;
  background: #f9fafb;
}

.modal-actions .btn-primary {
  background: #c9a050;
  color: white;
  border: none;
  padding: 0.875rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.modal-actions .btn-primary:hover:not(:disabled) {
  background: #b8860b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.modal-actions button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Transaction Summary Styles */
.transaction-summary {
  text-align: left;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e5e7eb;
}

.summary-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 1.5rem;
  text-align: center;
}

.summary-section {
  margin-bottom: 2rem;
  padding: 1.5rem;
  background: #f9fafb;
  border-radius: 12px;
}

.section-heading {
  font-size: 1.1rem;
  font-weight: 600;
  color: #374151;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.info-grid {
  display: grid;
  gap: 0.75rem;
}

.info-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 0;
}

.info-label {
  font-weight: 600;
  color: #6b7280;
  font-size: 0.9rem;
}

.info-value {
  color: #1a1a1a;
  font-weight: 500;
}

.info-value.gold {
  color: #c9a050;
  font-weight: 600;
  font-size: 1.1rem;
}

.address-display {
  color: #4b5563;
  line-height: 1.8;
}

.address-display p {
  margin: 0.25rem 0;
}

.address-display p:first-child {
  font-weight: 600;
  color: #1a1a1a;
  margin-bottom: 0.5rem;
}

.order-items-summary {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.summary-item-row {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.75rem;
  background: white;
  border-radius: 8px;
  border: 1px solid #e5e7eb;
}

.summary-item-row .item-info {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.summary-item-row .item-name {
  font-weight: 600;
  color: #000000;
}

.summary-item-row .item-qty {
  font-size: 0.85rem;
  color: #6b7280;
}

.summary-item-row .item-price {
  font-weight: 600;
  color: #c9a050;
}

.totals-grid {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.total-row {
  display: flex;
  justify-content: space-between;
  padding: 0.75rem 0;
  font-size: 0.95rem;
  color: #000000;
}

.total-row span {
  color: #000000;
}

.total-row.final-total {
  border-top: 2px solid #e5e7eb;
  margin-top: 0.5rem;
  padding-top: 1rem;
  font-size: 1.2rem;
  font-weight: 600;
  color: #000000;
}

.total-row.final-total span {
  color: #000000;
}

.total-row .gold {
  color: #000000;
  font-size: 1.3rem;
}

.total-row .discount {
  color: #059669;
}

.success-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 2rem;
  flex-wrap: wrap;
}

.success-actions .btn-primary,
.success-actions .btn-secondary {
  padding: 0.875rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.3s ease;
  display: inline-block;
}

.success-actions .btn-primary {
  background: #c9a050;
  color: white;
}

.success-actions .btn-primary:hover {
  background: #b8860b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.success-actions .btn-secondary {
  background: white;
  color: #6b7280;
  border: 2px solid #e5e7eb;
}

.success-actions .btn-secondary:hover {
  border-color: #c9a050;
  color: #c9a050;
  background: #fef9e7;
}

/* Image Upload Styles */
.image-upload-wrapper {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.image-preview-container {
  position: relative;
  width: 100%;
  max-width: 400px;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  overflow: hidden;
  background: #f9fafb;
}

.image-preview {
  width: 100%;
  height: auto;
  display: block;
  max-height: 300px;
  object-fit: contain;
}

.remove-image-btn {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  background: rgba(220, 38, 38, 0.9);
  border: none;
  color: white;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
}

.remove-image-btn:hover {
  background: rgba(220, 38, 38, 1);
  transform: scale(1.1);
}

.remove-image-btn svg {
  width: 18px;
  height: 18px;
}

.upload-image-btn,
.change-image-btn {
  padding: 0.75rem 1.5rem;
  background: white;
  border: 2px dashed #c9a050;
  border-radius: 10px;
  color: #c9a050;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
  width: fit-content;
}

.upload-image-btn:hover,
.change-image-btn:hover {
  background: #fef9e7;
  border-color: #b8860b;
  color: #b8860b;
  transform: translateY(-2px);
}

.upload-image-btn svg {
  width: 20px;
  height: 20px;
}

.image-upload-hint {
  font-size: 0.8rem;
  color: #6b7280;
  margin: 0;
  font-style: italic;
}

@media (max-width: 768px) {
  .payment-confirmation-form .form-row {
    grid-template-columns: 1fr;
  }
  
  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }

  .transaction-summary {
    padding: 1rem;
  }

  .summary-section {
    padding: 1rem;
  }

  .success-actions {
    flex-direction: column;
  }

  .success-actions .btn-primary,
  .success-actions .btn-secondary {
    width: 100%;
  }

  .image-preview-container {
    max-width: 100%;
  }

  .upload-image-btn,
  .change-image-btn {
    width: 100%;
  }
}
</style>
