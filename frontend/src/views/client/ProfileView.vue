<template>
  <Teleport to="body">
    <div v-if="showAddressModal" class="modal-overlay" @click.self="showAddressModal = false">
      <div class="modal-box address-modal">
        <button class="modal-close" @click="showAddressModal = false">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 6L6 18M6 6l12 12"/>
          </svg>
        </button>

        <h3 class="modal-title">{{ editingAddress ? 'Edit Address' : 'Add New Address' }}</h3>

        <form @submit.prevent="saveAddress" class="address-form">
          <div class="form-group">
            <label>Label *</label>
            <select v-model="selectedLabelType" @change="onLabelTypeChange" required>
              <option value="">Select Label</option>
              <option value="Home">Home</option>
              <option value="Office">Office</option>
              <option value="Others">Others</option>
            </select>
            <input
              v-if="selectedLabelType === 'Others'"
              v-model="addressForm.label"
              type="text"
              required
              placeholder="Enter custom label"
              class="custom-label-input"
            />
          </div>

          <div class="form-group">
            <label>Recipient Name *</label>
            <input v-model="addressForm.recipient_name" type="text" required />
          </div>

          <div class="form-group">
            <label>Phone Number *</label>
            <input v-model="addressForm.phone" type="tel" required />
          </div>

          <div class="form-group">
            <label>Address Line 1 *</label>
            <input
              v-model="addressForm.address_line_1"
              type="text"
              required
              @input="formatAddressLine1"
              @blur="formatAddressLine1"
              placeholder="Street address, building name, house number"
            />
          </div>

          <div class="form-group">
            <label>Address Line 2</label>
            <input
              v-model="addressForm.address_line_2"
              type="text"
              @input="formatAddressLine2"
              @blur="formatAddressLine2"
              placeholder="Barangay, subdivision, village"
            />
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Province *</label>
              <select v-model="addressForm.province" @change="onProvinceChange" required>
                <option value="">Select Province</option>
                <option v-for="province in provinceNames" :key="province" :value="province">
                  {{ province }}
                </option>
              </select>
            </div>

            <div class="form-group">
              <label>City *</label>
              <select v-model="addressForm.city" :disabled="!addressForm.province" required>
                <option value="">{{ addressForm.province ? 'Select City' : 'Select Province First' }}</option>
                <option v-for="city in getCitiesByProvince(addressForm.province)" :key="city" :value="city">
                  {{ city }}
                </option>
              </select>
            </div>
          </div>

          <div class="form-group">
            <label>Postal Code *</label>
            <input
              v-model="addressForm.postal_code"
              type="text"
              required
              maxlength="4"
              pattern="[0-9]{4}"
              placeholder="Auto-filled based on location"
            />
            <small v-if="addressForm.postal_code" class="postal-code-hint">
              Postal code for {{ addressForm.city }}, {{ addressForm.province }}
            </small>
          </div>

          <div class="form-group">
            <label>Country</label>
            <input v-model="addressForm.country" type="text" />
          </div>

          <div class="checkbox-group">
            <label class="checkbox-label">
              <input type="checkbox" v-model="addressForm.is_default_shipping" />
              Set as default shipping address
            </label>
            <label class="checkbox-label">
              <input type="checkbox" v-model="addressForm.is_default_billing" />
              Set as default billing address
            </label>
          </div>

          <div class="modal-actions">
            <button type="button" class="btn-secondary" @click="showAddressModal = false">Cancel</button>
            <button type="submit" class="btn-primary">Save Address</button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>

  <div class="profile-page">
    <HeroSection
      title="My Profile"
      subtitle="Manage your account information and preferences"
      size="medium"
    />

    <div class="profile-container">
      <div v-if="!authStore.isAuthenticated" class="not-authenticated">
        <p>Please log in to view your profile.</p>
        <router-link to="/?login=true" class="btn-primary">Login</router-link>
      </div>

      <div v-else class="profile-content">
        <!-- Profile Card -->
        <div class="profile-card rise-up">
          <div class="profile-header">
            <div class="profile-avatar-section">
              <div class="profile-avatar-large">
                <span>{{ userInitials }}</span>
              </div>
              <button class="btn-change-avatar" @click="triggerAvatarUpload" v-if="false">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M23 19a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h4l2-3h6l2 3h4a2 2 0 0 1 2 2z"/>
                  <circle cx="12" cy="13" r="4"/>
                </svg>
                Change Avatar
              </button>
              <input
                ref="avatarInput"
                type="file"
                accept="image/*"
                @change="handleAvatarChange"
                class="avatar-input"
                style="display: none;"
              >
            </div>
            <h2 class="profile-name">{{ userFullName }}</h2>
            <p class="profile-email">{{ authStore.user?.email || '' }}</p>
          </div>

          <div class="profile-body">
            <form @submit.prevent="saveProfile" class="profile-form">
              <!-- Personal Information -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                  </svg>
                  Personal Information
                </h3>

                <div class="form-grid">
                  <div class="form-group">
                    <label>First Name <span class="required">*</span></label>
                    <input
                      v-model="profileForm.first_name"
                      type="text"
                      required
                      placeholder="Enter your first name"
                      class="form-input"
                      @input="handleFirstNameInput"
                      @blur="validateFirstName"
                    >
                    <span v-if="errors.first_name" class="error-text">{{ errors.first_name }}</span>
                  </div>

                  <div class="form-group">
                    <label>Last Name <span class="required">*</span></label>
                    <input
                      v-model="profileForm.last_name"
                      type="text"
                      required
                      placeholder="Enter your last name"
                      class="form-input"
                      @input="handleLastNameInput"
                      @blur="validateLastName"
                    >
                    <span v-if="errors.last_name" class="error-text">{{ errors.last_name }}</span>
                  </div>

                  <div class="form-group">
                    <label>Email Address <span class="required">*</span></label>
                    <input
                      v-model="profileForm.email"
                      type="email"
                      required
                      placeholder="Enter your email"
                      class="form-input"
                      @input="handleEmailInput"
                      @blur="validateEmail"
                    >
                    <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
                  </div>

                  <div class="form-group">
                    <label>Phone Number</label>
                    <input
                      v-model="profileForm.phone"
                      type="tel"
                      placeholder="Enter your phone number"
                      class="form-input"
                    >
                  </div>
                </div>
              </div>

              <!-- Address Information -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                    <circle cx="12" cy="10" r="3"/>
                  </svg>
                  Address Information
                </h3>

                <div class="form-grid">
                  <div class="form-group full-width">
                    <label>Address Line 1</label>
                    <input
                      v-model="profileForm.address_line_1"
                      type="text"
                      placeholder="Street address, building name, house number"
                      class="form-input"
                      @input="formatProfileAddressLine1"
                      @blur="formatProfileAddressLine1"
                    >
                  </div>

                  <div class="form-group full-width">
                    <label>Address Line 2</label>
                    <input
                      v-model="profileForm.address_line_2"
                      type="text"
                      placeholder="Barangay, subdivision, village"
                      class="form-input"
                      @input="formatProfileAddressLine2"
                      @blur="formatProfileAddressLine2"
                    >
                  </div>

                  <div class="form-group">
                    <label>Province</label>
                    <div class="select-wrapper">
                      <select
                        v-model="profileForm.province"
                        @change="onProvinceChange"
                        class="form-select"
                      >
                        <option value="">Select Province</option>
                        <option v-for="province in provinceNames" :key="province" :value="province">
                          {{ province }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>City</label>
                    <div class="select-wrapper">
                      <select
                        v-model="profileForm.city"
                        :disabled="!profileForm.province"
                        class="form-select"
                      >
                        <option value="">{{ profileForm.province ? 'Select City' : 'Select Province First' }}</option>
                        <option v-for="city in availableCities" :key="city" :value="city">
                          {{ city }}
                        </option>
                      </select>
                    </div>
                  </div>

                  <div class="form-group">
                    <label>Postal Code</label>
                    <input
                      v-model="profileForm.postal_code"
                      type="text"
                      maxlength="4"
                      pattern="[0-9]{4}"
                      placeholder="Auto-filled based on location"
                      class="form-input"
                    />
                    <small v-if="profileForm.postal_code && profileForm.city && profileForm.province" class="postal-code-hint">
                      Postal code for {{ profileForm.city }}, {{ profileForm.province }}
                    </small>
                  </div>
                </div>
              </div>

              <!-- Saved Addresses Management -->
              <div class="form-section">
                <div class="section-header">
                  <h3 class="section-title">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                      <circle cx="12" cy="10" r="3"/>
                    </svg>
                    Saved Addresses
                  </h3>
                  <button type="button" class="btn-add-address" @click="openAddAddressModal">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="5" x2="12" y2="19"/>
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Add New Address
                  </button>
                </div>

                <div v-if="savedAddresses.length === 0" class="no-addresses">
                  <p>No saved addresses yet. Add your first address to get started.</p>
                </div>

                <div v-else class="addresses-list">
                  <div v-for="(address, index) in savedAddresses" :key="address.id" class="address-card" :class="`rise-up-delay-${Math.min(index + 1, 5)}`">
                    <div class="address-header">
                      <div class="address-label-badge">
                        <span class="address-label">{{ address.label }}</span>
                        <span v-if="address.is_default_shipping" class="badge badge-shipping">Default Shipping</span>
                        <span v-if="address.is_default_billing" class="badge badge-billing">Default Billing</span>
                      </div>
                      <div class="address-actions">
                        <button type="button" class="btn-icon" @click="editAddress(address)" title="Edit">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                          </svg>
                        </button>
                        <button type="button" class="btn-icon btn-danger" @click="openDeleteAddressModal(address)" title="Delete">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="3 6 5 6 21 6"/>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                          </svg>
                        </button>
                      </div>
                    </div>
                    <div class="address-body">
                      <p class="address-recipient">{{ address.recipient_name }}</p>
                      <p class="address-phone">{{ address.phone }}</p>
                      <p class="address-lines">
                        {{ address.address_line_1 }}<br v-if="address.address_line_2">
                        <span v-if="address.address_line_2">{{ address.address_line_2 }}<br></span>
                        {{ address.city }}, {{ address.province }} {{ address.postal_code }}<br>
                        {{ address.country || 'Philippines' }}
                      </p>
                    </div>
                    <div class="address-footer">
                      <button
                        type="button"
                        class="btn-set-default"
                        :class="{ active: address.is_default_shipping }"
                        @click="setDefaultShipping(address.id)"
                      >
                        Set as Default Shipping
                      </button>
                      <button
                        type="button"
                        class="btn-set-default"
                        :class="{ active: address.is_default_billing }"
                        @click="setDefaultBilling(address.id)"
                      >
                        Set as Default Billing
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Change Password -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                  </svg>
                  Change Password
                </h3>

                <div class="form-grid">
                  <div class="form-group">
                    <label>Current Password</label>
                    <input
                      v-model="profileForm.current_password"
                      type="password"
                      placeholder="Enter current password"
                      class="form-input"
                    >
                  </div>

                  <div class="form-group">
                    <label>New Password</label>
                    <input
                      v-model="profileForm.new_password"
                      type="password"
                      placeholder="Enter new password"
                      class="form-input"
                      @input="handlePasswordInput"
                      @blur="validatePassword"
                    >
                    <span v-if="errors.password" class="error-text">{{ errors.password }}</span>
                  </div>

                  <div class="form-group">
                    <label>Confirm New Password</label>
                    <input
                      v-model="profileForm.confirm_password"
                      type="password"
                      placeholder="Confirm new password"
                      class="form-input"
                      @blur="validateConfirmPassword"
                    >
                    <span v-if="errors.confirm_password" class="error-text">{{ errors.confirm_password }}</span>
                  </div>
                </div>
              </div>

              <!-- Form Actions -->
              <div class="form-actions">
                <button type="button" class="btn-secondary" @click="cancelEdit">Cancel</button>
                <button type="submit" class="btn-primary" :disabled="isSaving">
                  <span v-if="isSaving">Saving...</span>
                  <span v-else>Save Changes</span>
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- Account Statistics Card -->
        <div class="stats-card">
          <h3 class="stats-title">Account Information</h3>
          <div class="stats-grid">
            <div class="stat-item">
              <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                  <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
                </svg>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ accountStats.totalOrders }}</div>
                <div class="stat-label">Total Orders</div>
              </div>
            </div>

            <div class="stat-item">
              <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </div>
              <div class="stat-info">
                <div class="stat-value">{{ accountStats.memberSince }}</div>
                <div class="stat-label">Member Since</div>
              </div>
            </div>

            <div class="stat-item">
              <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
              </div>
              <div class="stat-info">
                <div class="stat-value">₱{{ formatPrice(accountStats.totalSpent) }}</div>
                <div class="stat-label">Total Spent</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Address Confirmation Modal -->
    <Teleport to="body">
      <div v-if="showDeleteAddressModal" class="modal-overlay" @click.self="showDeleteAddressModal = false">
        <div class="modal-container">
          <div class="modal-header">
            <h3 class="modal-title">Delete Address</h3>
            <button class="modal-close" @click="showDeleteAddressModal = false">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <div class="warning-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                <line x1="12" y1="9" x2="12" y2="13"/>
                <line x1="12" y1="17" x2="12.01" y2="17"/>
              </svg>
            </div>
            <p class="modal-message">
              Are you sure you want to delete the address <strong>"{{ selectedAddressLabel }}"</strong>?
            </p>
            <p class="modal-submessage">
              This action cannot be undone. The address will be permanently removed from your saved addresses.
            </p>
          </div>

          <div class="modal-footer">
            <button class="btn-modal-secondary" @click="showDeleteAddressModal = false">
              Keep Address
            </button>
            <button class="btn-modal-danger" @click="deleteAddress" :disabled="isDeletingAddress">
              <span v-if="isDeletingAddress">Deleting...</span>
              <span v-else>Yes, Delete Address</span>
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { Teleport } from 'vue'
import { useRouter } from 'vue-router'
import HeroSection from '@/components/HeroSection.vue'
import { useAuthStore } from '@/stores/auth'
import { useNotification } from '@/composables/useNotification'
import { auth, addresses as addressesApi } from '@/services/clientApi'
import { getProvinceNames, getCitiesByProvince, getPostalCode } from '@/data/philippineLocations'

const router = useRouter()
const authStore = useAuthStore()
const { success, error: showError } = useNotification()


const avatarInput = ref<HTMLInputElement | null>(null)
const isSaving = ref(false)
const autoSaveTimeout = ref<number | null>(null)
const lastSavedData = ref<string>('')
const showDeleteAddressModal = ref(false)
const selectedAddressId = ref<number | null>(null)
const selectedAddressLabel = ref<string>('')
const isDeletingAddress = ref(false)
const errors = ref({
  first_name: '',
  last_name: '',
  email: '',
  password: '',
  confirm_password: ''
})

const profileForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  province: '',
  city: '',
  postal_code: '',
  current_password: '',
  new_password: '',
  confirm_password: ''
})

const accountStats = ref({
  totalOrders: 0,
  memberSince: '',
  totalSpent: 0
})


const savedAddresses = ref<any[]>([])
const showAddressModal = ref(false)
const editingAddress = ref<any | null>(null)
const selectedLabelType = ref<string>('')
const addressForm = ref({
  label: '',
  recipient_name: '',
  phone: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  province: '',
  postal_code: '',
  country: 'Philippines',
  is_default_shipping: false,
  is_default_billing: false
})


const onLabelTypeChange = () => {
  if (selectedLabelType.value === 'Home' || selectedLabelType.value === 'Office') {
    addressForm.value.label = selectedLabelType.value
  } else if (selectedLabelType.value === 'Others') {
    addressForm.value.label = ''
  } else {
    addressForm.value.label = ''
  }
}


const provinceNames = ref<string[]>(getProvinceNames())
const availableCities = computed(() => {
  if (!profileForm.value.province) return []
  return getCitiesByProvince(profileForm.value.province)
})



const userFullName = computed(() => {
  if (authStore.user) {
    return `${authStore.user.first_name || ''} ${authStore.user.last_name || ''}`.trim() || 'User'
  }
  return 'User'
})

const userInitials = computed(() => {
  const name = userFullName.value
  const parts = name.split(' ')
  if (parts.length >= 2) {
    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase()
  }
  return name.substring(0, 2).toUpperCase()
})


const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const triggerAvatarUpload = () => {
  avatarInput.value?.click()
}

const handleAvatarChange = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {

    console.log('Avatar upload:', file)
  }
}


const handleFirstNameInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value

  value = value.replace(/^ +/, '').replace(/  +/g, ' ')
  profileForm.value.first_name = value
}

const handleLastNameInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  value = value.replace(/^ +/, '').replace(/  +/g, ' ')
  profileForm.value.last_name = value
}

const handleEmailInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  value = value.replace(/^ +/, '').replace(/  +/g, ' ')
  profileForm.value.email = value
}

const handlePasswordInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value

  value = value.replace(/^ +/, '')
  profileForm.value.new_password = value
}

const validateFirstName = () => {
  if (!profileForm.value.first_name.trim()) {
    errors.value.first_name = 'First name is required'
  } else if (/^ /.test(profileForm.value.first_name)) {
    errors.value.first_name = 'First name cannot start with a space'
  } else if (/  /.test(profileForm.value.first_name)) {
    errors.value.first_name = 'First name cannot contain consecutive spaces'
  } else {
    errors.value.first_name = ''
  }
}

const validateLastName = () => {
  if (!profileForm.value.last_name.trim()) {
    errors.value.last_name = 'Last name is required'
  } else if (/^ /.test(profileForm.value.last_name)) {
    errors.value.last_name = 'Last name cannot start with a space'
  } else if (/  /.test(profileForm.value.last_name)) {
    errors.value.last_name = 'Last name cannot contain consecutive spaces'
  } else {
    errors.value.last_name = ''
  }
}

const validateEmail = () => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!profileForm.value.email.trim()) {
    errors.value.email = 'Email is required'
  } else if (/^ /.test(profileForm.value.email)) {
    errors.value.email = 'Email cannot start with a space'
  } else if (/  /.test(profileForm.value.email)) {
    errors.value.email = 'Email cannot contain consecutive spaces'
  } else if (!emailRegex.test(profileForm.value.email)) {
    errors.value.email = 'Please enter a valid email address'
  } else {
    errors.value.email = ''
  }
}

const validatePassword = () => {
  if (profileForm.value.new_password && profileForm.value.new_password.length < 8) {
    errors.value.password = 'Password must be at least 8 characters long'
  } else if (/^ /.test(profileForm.value.new_password)) {
    errors.value.password = 'Password cannot start with a space'
  } else {
    errors.value.password = ''
  }
}

const validateConfirmPassword = () => {
  if (profileForm.value.new_password && profileForm.value.confirm_password !== profileForm.value.new_password) {
    errors.value.confirm_password = 'Passwords do not match'
  } else {
    errors.value.confirm_password = ''
  }
}

const onProvinceChange = () => {
  profileForm.value.city = ''
  profileForm.value.postal_code = ''
  addressForm.value.city = ''
  addressForm.value.postal_code = ''
}


const formatProfileAddressLine1 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    profileForm.value.address_line_1 = value
  } else {
    let value = profileForm.value.address_line_1 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    profileForm.value.address_line_1 = value
  }
}


const formatProfileAddressLine2 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    profileForm.value.address_line_2 = value
  } else {
    let value = profileForm.value.address_line_2 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    profileForm.value.address_line_2 = value
  }
}


const formatAddressLine1 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value

    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    addressForm.value.address_line_1 = value
  } else {
    let value = addressForm.value.address_line_1 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    addressForm.value.address_line_1 = value
  }
}


const formatAddressLine2 = (event?: Event) => {
  if (event) {
    const target = event.target as HTMLInputElement
    let value = target.value

    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    addressForm.value.address_line_2 = value
  } else {
    let value = addressForm.value.address_line_2 || ''
    value = value
      .trim()
      .replace(/\s+/g, ' ')
      .split(' ')
      .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase())
      .join(' ')
    addressForm.value.address_line_2 = value
  }
}


watch(
  () => [addressForm.value.province, addressForm.value.city],
  ([province, city]) => {
    if (province && city) {
      const postalCode = getPostalCode(province, city)
      if (postalCode) {
        addressForm.value.postal_code = postalCode
      }
    } else if (!province || !city) {
      addressForm.value.postal_code = ''
    }
  }
)


watch(
  () => [profileForm.value.province, profileForm.value.city],
  ([province, city]) => {
    if (province && city) {
      const postalCode = getPostalCode(province, city)
      if (postalCode && !profileForm.value.postal_code) {
        profileForm.value.postal_code = postalCode
      }
    }
  }
)

const saveProfile = async (showNotification = true) => {

  validateFirstName()
  validateLastName()
  validateEmail()

  if (profileForm.value.new_password) {
    validatePassword()
    validateConfirmPassword()

    if (errors.value.password || errors.value.confirm_password) {
      return
    }
  }

  if (errors.value.first_name || errors.value.last_name || errors.value.email) {
    return
  }

  isSaving.value = true

  try {

    const updateData: any = {}

    if (profileForm.value.first_name) {
      updateData.first_name = profileForm.value.first_name.trim()
    }
    if (profileForm.value.last_name) {
      updateData.last_name = profileForm.value.last_name.trim()
    }
    if (profileForm.value.email) {
      updateData.email = profileForm.value.email.trim()
    }
    if (profileForm.value.phone !== undefined) {
      updateData.phone = profileForm.value.phone ? profileForm.value.phone.trim() : null
    }
    if (profileForm.value.address_line_1 !== undefined) {
      updateData.address_line_1 = profileForm.value.address_line_1 ? profileForm.value.address_line_1.trim() : null
    }
    if (profileForm.value.address_line_2 !== undefined) {
      updateData.address_line_2 = profileForm.value.address_line_2 ? profileForm.value.address_line_2.trim() : null
    }
    if (profileForm.value.province !== undefined) {
      updateData.province = profileForm.value.province ? profileForm.value.province.trim() : null
    }
    if (profileForm.value.city !== undefined) {
      updateData.city = profileForm.value.city ? profileForm.value.city.trim() : null
    }
    if (profileForm.value.postal_code !== undefined) {
      updateData.postal_code = profileForm.value.postal_code ? profileForm.value.postal_code.trim() : null
    }


    if (profileForm.value.new_password && profileForm.value.current_password) {
      try {
        await auth.changePassword(
          profileForm.value.current_password,
          profileForm.value.new_password,
          profileForm.value.confirm_password
        )

        profileForm.value.current_password = ''
        profileForm.value.new_password = ''
        profileForm.value.confirm_password = ''
        if (showNotification) {
          success('Password Changed', 'Your password has been changed successfully.')
        }
      } catch (passwordError: any) {
        const passwordApiError = passwordError as { response?: { data?: { message?: string } } }
        showError('Password Change Failed', passwordApiError.response?.data?.message || 'Failed to change password. Please try again.')
        return
      }
    }


    if (Object.keys(updateData).length > 0) {
      const response = await auth.updateProfile(updateData)

      if (!response.data.success) {
        throw new Error(response.data.message || 'Failed to update profile')
      }


      if (response.data.data?.user) {
        authStore.user = response.data.data.user

        localStorage.setItem('user', JSON.stringify(response.data.data.user))
      }


      await authStore.fetchUser()


      const freshUser = authStore.user
      if (freshUser) {
        profileForm.value.first_name = freshUser.first_name || ''
        profileForm.value.last_name = freshUser.last_name || ''
        profileForm.value.email = freshUser.email || ''
        profileForm.value.phone = freshUser.phone || ''
        profileForm.value.address_line_1 = freshUser.address_line_1 || ''
        profileForm.value.address_line_2 = freshUser.address_line_2 || ''
        profileForm.value.province = freshUser.province || ''
        profileForm.value.city = freshUser.city || ''
        profileForm.value.postal_code = freshUser.postal_code || ''
      }


      lastSavedData.value = JSON.stringify({
        first_name: profileForm.value.first_name,
        last_name: profileForm.value.last_name,
        email: profileForm.value.email,
        phone: profileForm.value.phone,
        address_line_1: profileForm.value.address_line_1,
        address_line_2: profileForm.value.address_line_2,
        province: profileForm.value.province,
        city: profileForm.value.city,
        postal_code: profileForm.value.postal_code,
        current_password: '',
        new_password: '',
        confirm_password: ''
      })


      await loadAccountStats()

      if (showNotification) {
        success('Profile Updated', 'Your profile information has been updated successfully.')
      }
    } else if (!profileForm.value.new_password) {

      if (showNotification) {
        showError('No Changes', 'No changes detected to save.')
      }
    }
  } catch (error: unknown) {
    const apiError = error as {
      response?: {
        data?: {
          message?: string
          errors?: {
            email?: string[]
            first_name?: string[]
            last_name?: string[]
            [key: string]: string[] | undefined
          }
        }
      }
      message?: string
    }


    if (apiError.response?.data?.errors) {
      const validationErrors = apiError.response.data.errors


      if (validationErrors.email) {
        errors.value.email = validationErrors.email[0]
      }
      if (validationErrors.first_name) {
        errors.value.first_name = validationErrors.first_name[0]
      }
      if (validationErrors.last_name) {
        errors.value.last_name = validationErrors.last_name[0]
      }


      if (validationErrors.email || validationErrors.first_name || validationErrors.last_name) {
        showError('Validation Failed', 'Please check the form for errors and try again.')
      } else {
        showError('Update Failed', apiError.response?.data?.message || apiError.message || 'Failed to update profile. Please try again.')
      }
    } else {

      showError('Update Failed', apiError.response?.data?.message || apiError.message || 'Failed to update profile. Please try again.')
    }
  } finally {
    isSaving.value = false
  }
}


watch(() => ({
  first_name: profileForm.value.first_name,
  last_name: profileForm.value.last_name,
  email: profileForm.value.email,
  phone: profileForm.value.phone,
  address_line_1: profileForm.value.address_line_1,
  address_line_2: profileForm.value.address_line_2,
  province: profileForm.value.province,
  city: profileForm.value.city,
  postal_code: profileForm.value.postal_code
}), (newVal) => {

  if (profileForm.value.current_password || profileForm.value.new_password || profileForm.value.confirm_password) {
    return
  }


  if (!lastSavedData.value) {
    return
  }


  if (autoSaveTimeout.value) {
    clearTimeout(autoSaveTimeout.value)
  }


  const currentData = JSON.stringify(newVal)
  const savedProfileData = JSON.parse(lastSavedData.value)
  const savedData = JSON.stringify({
    first_name: savedProfileData.first_name,
    last_name: savedProfileData.last_name,
    email: savedProfileData.email,
    phone: savedProfileData.phone,
    address_line_1: savedProfileData.address_line_1,
    address_line_2: savedProfileData.address_line_2,
    province: savedProfileData.province,
    city: savedProfileData.city,
    postal_code: savedProfileData.postal_code
  })

  if (currentData === savedData) {
    return
  }


  autoSaveTimeout.value = window.setTimeout(() => {
    saveProfile(false)
  }, 2000)
}, { deep: true })

const cancelEdit = () => {
  router.push('/')
}


const loadAddresses = async () => {
  try {
    const response = await addressesApi.list()

    if (response.data.success) {
      savedAddresses.value = response.data.data || []
    }
  } catch (error: any) {
    console.error('Failed to load addresses:', error)
  }
}

const openAddAddressModal = () => {
  editingAddress.value = null
  selectedLabelType.value = ''
  addressForm.value = {
    label: '',
    recipient_name: authStore.user?.full_name || '',
    phone: authStore.user?.phone || '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    province: '',
    postal_code: '',
    country: 'Philippines',
    is_default_shipping: false,
    is_default_billing: false
  }
  showAddressModal.value = true
}

const editAddress = (address: any) => {
  editingAddress.value = address
  const label = address.label || ''


  if (label === 'Home' || label === 'Office') {
    selectedLabelType.value = label
    addressForm.value.label = label
  } else if (label) {

    selectedLabelType.value = 'Others'
    addressForm.value.label = label
  } else {
    selectedLabelType.value = ''
    addressForm.value.label = ''
  }

  addressForm.value = {
    ...addressForm.value,
    recipient_name: address.recipient_name || '',
    phone: address.phone || '',
    address_line_1: address.address_line_1 || '',
    address_line_2: address.address_line_2 || '',
    city: address.city || '',
    province: address.province || '',
    postal_code: address.postal_code || '',
    country: address.country || 'Philippines',
    is_default_shipping: address.is_default_shipping || false,
    is_default_billing: address.is_default_billing || false
  }
  showAddressModal.value = true
}

const saveAddress = async () => {

  if (!selectedLabelType.value) {
    showError('Validation Failed', 'Please select a label type.')
    return
  }


  if (selectedLabelType.value === 'Others' && !addressForm.value.label?.trim()) {
    showError('Validation Failed', 'Please enter a custom label.')
    return
  }


  if (selectedLabelType.value !== 'Others') {
    addressForm.value.label = selectedLabelType.value
  }

  try {
    if (editingAddress.value) {
      await addressesApi.update(editingAddress.value.id, addressForm.value)
      success('Address Updated', 'Your address has been updated successfully.')
    } else {
      await addressesApi.create(addressForm.value)
      success('Address Added', 'Your new address has been added successfully.')
    }
    showAddressModal.value = false
    await loadAddresses()
  } catch (error: any) {
    showError('Save Failed', error.response?.data?.message || 'Failed to save address. Please try again.')
  }
}

const openDeleteAddressModal = (address: any) => {
  selectedAddressId.value = address.id
  selectedAddressLabel.value = address.label || 'this address'
  showDeleteAddressModal.value = true
}

const deleteAddress = async () => {
  if (!selectedAddressId.value) return

  isDeletingAddress.value = true

  try {
    await addressesApi.delete(selectedAddressId.value)
    showDeleteAddressModal.value = false
    success('Address Deleted', `The address "${selectedAddressLabel.value}" has been deleted successfully.`)
    await loadAddresses()
    selectedAddressId.value = null
    selectedAddressLabel.value = ''
  } catch (error: any) {
    showError('Delete Failed', error.response?.data?.message || 'Failed to delete address. Please try again.')
  } finally {
    isDeletingAddress.value = false
  }
}

const setDefaultShipping = async (addressId: number) => {
  try {
    await addressesApi.setDefaultShipping(addressId)
    success('Default Shipping Updated', 'Your default shipping address has been updated.')
    await loadAddresses()
  } catch (error: any) {
    showError('Update Failed', error.response?.data?.message || 'Failed to set default shipping address.')
  }
}

const setDefaultBilling = async (addressId: number) => {
  try {
    await addressesApi.setDefaultBilling(addressId)
    success('Default Billing Updated', 'Your default billing address has been updated.')
    await loadAddresses()
  } catch (error: any) {
    showError('Update Failed', error.response?.data?.message || 'Failed to set default billing address.')
  }
}


const loadUserData = async () => {
  if (!authStore.isAuthenticated) return

  try {

    await authStore.fetchUser()
    const user = authStore.user

    if (user) {
      profileForm.value = {
        first_name: user.first_name || '',
        last_name: user.last_name || '',
        email: user.email || '',
        phone: user.phone || '',
        address_line_1: user.address_line_1 || '',
        address_line_2: user.address_line_2 || '',
        province: user.province || '',
        city: user.city || '',
        postal_code: user.postal_code || '',
        current_password: '',
        new_password: '',
        confirm_password: ''
      }


      lastSavedData.value = JSON.stringify({
        first_name: profileForm.value.first_name,
        last_name: profileForm.value.last_name,
        email: profileForm.value.email,
        phone: profileForm.value.phone,
        address_line_1: profileForm.value.address_line_1,
        address_line_2: profileForm.value.address_line_2,
        province: profileForm.value.province,
        city: profileForm.value.city,
        postal_code: profileForm.value.postal_code,
        current_password: '',
        new_password: '',
        confirm_password: ''
      })
    }
  } catch (error) {
    console.error('Failed to load user data:', error)
  }
}


const loadAccountStats = async () => {
  if (!authStore.isAuthenticated) return

  try {
    const response = await auth.getAccountStats()

    if (response.data.success) {
      const stats = response.data.data
      accountStats.value = {
        totalOrders: stats.total_orders || 0,
        memberSince: stats.member_since || '',
        totalSpent: parseFloat(stats.total_spent || 0)
      }
    }
  } catch (error) {
    console.error('Failed to load account stats:', error)

    const user = authStore.user
    if (user) {
      accountStats.value = {
        totalOrders: user.order_count || 0,
        memberSince: user.created_at ? new Date(user.created_at).toLocaleDateString('en-US', { month: 'short', year: 'numeric' }) : '',
        totalSpent: parseFloat(user.total_spent || 0)
      }
    }
  }
}


watch(showAddressModal, (isOpen) => {
  if (!isOpen) {
    selectedLabelType.value = ''
  }
})

onMounted(async () => {
  if (!authStore.isAuthenticated) {
    sessionStorage.setItem('redirectAfterLogin', '/profile')
    router.push({ name: 'home', query: { login: 'true' } })
    return
  }

  await authStore.fetchUser()
  await loadUserData()
  await loadAccountStats()
  await loadAddresses()
})
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background: #f5f7fa;
}

.profile-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 3rem 2rem;
}

.not-authenticated {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}

.profile-content {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
}

.profile-card {
  background: white;
  border-radius: 20px;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.profile-header {
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  padding: 3rem 2.5rem;
  text-align: center;
}

.profile-avatar-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 1.5rem;
}

.profile-avatar-large {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 2.5rem;
  font-weight: 700;
  color: #c9a050;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.profile-name {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: white;
  margin: 1rem 0 0.5rem;
}

.profile-email {
  color: rgba(255, 255, 255, 0.9);
  font-size: 1rem;
  margin: 0;
}

.btn-change-avatar {
  padding: 0.5rem 1rem;
  background: white;
  border: none;
  border-radius: 8px;
  color: #c9a050;
  font-weight: 600;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.3s ease;
}

.btn-change-avatar:hover {
  background: #f5f5f5;
  transform: translateY(-2px);
}

.profile-body {
  padding: 2.5rem;
}

.form-section {
  margin-bottom: 2.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 1.5rem;
}

.section-title svg {
  width: 24px;
  height: 24px;
  color: #c9a050;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
}

.form-group.full-width {
  grid-column: 1 / -1;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-group label {
  font-weight: 600;
  margin-bottom: 0.5rem;
  color: #374151;
  font-size: 0.9rem;
}

.required {
  color: #dc2626;
}

.form-input,
.form-select {
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
  background: white;
}

.form-input:focus,
.form-select:focus {
  outline: none;
  border-color: #c9a050;
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.form-input:disabled,
.form-input[readonly],
.form-select:disabled {
  background: #f3f4f6;
  cursor: not-allowed;
  opacity: 0.7;
}

.field-note {
  color: #6b7280;
  font-size: 0.8rem;
  margin-top: 0.5rem;
  font-style: italic;
  display: block;
}

.select-wrapper {
  position: relative;
}

.form-select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23666' d='M2 4l4 4 4-4'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 1rem center;
  padding-right: 3rem;
}

.error-text {
  display: block;
  color: #dc2626;
  font-size: 0.75rem;
  margin-top: 0.5rem;
  padding-left: 0.5rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  margin-top: 2rem;
  padding-top: 2rem;
  border-top: 2px solid #e5e7eb;
}

.btn-primary,
.btn-secondary {
  padding: 0.875rem 2rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.btn-primary {
  background: #c9a050;
  color: white;
}

.btn-primary:hover:not(:disabled) {
  background: #b8860b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-secondary {
  background: #e5e7eb;
  color: #374151;
}

.btn-secondary:hover {
  background: #d1d5db;
}

.stats-card {
  background: white;
  border-radius: 20px;
  padding: 2rem;
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
  height: fit-content;
}

.stats-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 1.5rem;
}

.stats-grid {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.stat-item {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1rem;
  background: #f9fafb;
  border-radius: 12px;
}

.stat-icon {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  flex-shrink: 0;
}

.stat-icon svg {
  width: 24px;
  height: 24px;
}

.stat-info {
  flex: 1;
}

.stat-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: #1a1a1a;
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.875rem;
  color: #6b7280;
}

@media (max-width: 968px) {
  .profile-content {
    grid-template-columns: 1fr;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }
}
/* Address Management Styles */
.section-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1.5rem;
}

.btn-add-address {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: #c9a050;
  color: white;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-add-address:hover {
  background: #b8860b;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.btn-add-address svg {
  width: 18px;
  height: 18px;
}

.no-addresses {
  text-align: center;
  padding: 3rem 2rem;
  background: #f9fafb;
  border-radius: 12px;
  color: #6b7280;
}

.addresses-list {
  display: grid;
  gap: 1.5rem;
}

.address-card {
  background: white;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.address-card:hover {
  border-color: #c9a050;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.address-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.address-label-badge {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  flex-wrap: wrap;
}

.address-label {
  font-weight: 600;
  font-size: 1.1rem;
  color: #1a1a1a;
}

.badge {
  padding: 0.25rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.badge-shipping {
  background: #dbeafe;
  color: #1e40af;
}

.badge-billing {
  background: #fef3c7;
  color: #92400e;
}

.address-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  width: 36px;
  height: 36px;
  border-radius: 8px;
  border: 2px solid #e5e7eb;
  background: white;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-icon:hover {
  border-color: #c9a050;
  background: #fef9e7;
}

.btn-icon svg {
  width: 18px;
  height: 18px;
  color: #6b7280;
}

.btn-icon:hover svg {
  color: #c9a050;
}

.btn-danger:hover {
  border-color: #dc2626;
  background: #fee2e2;
}

.btn-danger:hover svg {
  color: #dc2626;
}

.address-body {
  margin-bottom: 1rem;
}

.address-recipient {
  font-weight: 600;
  font-size: 1rem;
  color: #1a1a1a;
  margin-bottom: 0.5rem;
}

.address-phone {
  color: #6b7280;
  font-size: 0.9rem;
  margin-bottom: 0.75rem;
}

.address-lines {
  color: #4b5563;
  font-size: 0.9rem;
  line-height: 1.6;
}

.address-footer {
  display: flex;
  gap: 0.75rem;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.btn-set-default {
  flex: 1;
  padding: 0.625rem 1rem;
  border: 2px solid #e5e7eb;
  background: white;
  border-radius: 8px;
  font-size: 0.85rem;
  font-weight: 600;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-set-default:hover {
  border-color: #c9a050;
  color: #c9a050;
  background: #fef9e7;
}

.btn-set-default.active {
  border-color: #c9a050;
  background: #c9a050;
  color: white;
}

/* Address Modal Styles */
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

.modal-box {
  background: white;
  border-radius: 24px;
  box-shadow: 0 25px 50px rgba(0, 0, 0, 0.25);
  position: relative;
  max-width: 600px;
  width: 100%;
  max-height: 90vh;
  overflow-y: auto;
}

.address-modal {
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
  margin-bottom: 1.5rem;
}

.address-form .form-group {
  margin-bottom: 1.25rem;
}

.address-form label {
  display: block;
  margin-bottom: 0.5rem;
  font-weight: 600;
  color: #374151;
  font-size: 0.9rem;
}

.address-form input,
.address-form select {
  width: 100%;
  padding: 0.875rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  transition: all 0.3s ease;
}

.address-form input:focus,
.address-form select:focus {
  outline: none;
  border-color: #c9a050;
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.address-form .custom-label-input {
  margin-top: 0.75rem;
}

.postal-code-hint {
  display: block;
  margin-top: 0.5rem;
  font-size: 0.8rem;
  color: #6b7280;
  font-style: italic;
}

.address-form .form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.checkbox-group {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin: 1.5rem 0;
}

.checkbox-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  font-size: 0.9rem;
  color: #4b5563;
}

.checkbox-label input[type="checkbox"] {
  width: 20px;
  height: 20px;
  cursor: pointer;
  accent-color: #c9a050;
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
}

.modal-actions .btn-secondary:hover {
  border-color: #d1d5db;
  background: #f9fafb;
}

@media (max-width: 768px) {
  .address-form .form-row {
    grid-template-columns: 1fr;
  }

  .address-footer {
    flex-direction: column;
  }

  .section-header {
    flex-direction: column;
    align-items: flex-start;
    gap: 1rem;
  }
}
/* Delete Address Confirmation Modal */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(4px);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  padding: 1rem;
  animation: fadeIn 0.2s ease-out;
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.modal-container {
  background: white;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  max-width: 500px;
  width: 100%;
  overflow: hidden;
  animation: slideUp 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes slideUp {
  from {
    transform: translateY(20px);
    opacity: 0;
  }
  to {
    transform: translateY(0);
    opacity: 1;
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  border-bottom: 1px solid #e5e7eb;
}

.modal-header .modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: #1a1a1a;
  margin: 0;
}

.modal-close {
  width: 32px;
  height: 32px;
  border: none;
  background: transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 8px;
  transition: all 0.2s;
  color: #666;
}

.modal-close:hover {
  background: #f3f4f6;
  color: #1a1a1a;
}

.modal-close svg {
  width: 20px;
  height: 20px;
}

.modal-body {
  padding: 2rem;
  text-align: center;
}

.warning-icon {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  color: #f59e0b;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #fef3c7;
  border-radius: 50%;
}

.warning-icon svg {
  width: 36px;
  height: 36px;
}

.modal-message {
  font-size: 1.1rem;
  color: #1a1a1a;
  margin-bottom: 0.75rem;
  line-height: 1.6;
}

.modal-message strong {
  color: #c9a050;
  font-weight: 600;
}

.modal-submessage {
  font-size: 0.9rem;
  color: #666;
  line-height: 1.5;
  margin: 0;
}

.modal-footer {
  display: flex;
  gap: 1rem;
  padding: 1.5rem 2rem;
  border-top: 1px solid #e5e7eb;
  background: #f9fafb;
}

.btn-modal-secondary,
.btn-modal-danger {
  flex: 1;
  padding: 0.875rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  font-size: 1rem;
  border: none;
  cursor: pointer;
  transition: all 0.3s;
}

.btn-modal-secondary {
  background: white;
  color: #374151;
  border: 2px solid #e5e7eb;
}

.btn-modal-secondary:hover {
  background: #f9fafb;
  border-color: #d1d5db;
}

.btn-modal-danger {
  background: linear-gradient(135deg, #ef4444, #dc2626);
  color: white;
}

.btn-modal-danger:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

.btn-modal-danger:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 768px) {
  .modal-container {
    max-width: 100%;
    margin: 1rem;
  }

  .modal-header,
  .modal-body,
  .modal-footer {
    padding: 1.25rem 1.5rem;
  }

  .modal-footer {
    flex-direction: column;
  }

  .btn-modal-secondary,
  .btn-modal-danger {
    width: 100%;
  }
}
</style>
