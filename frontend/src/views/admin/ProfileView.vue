<template>
  <div class="profile-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and preferences</p>
      </div>
    </div>

    <!-- Profile Content -->
    <div class="profile-content">
      <!-- Profile Card -->
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar-section">
            <div class="profile-avatar-large">
              <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
              </svg>
            </div>
            <button class="btn-change-avatar" @click="triggerAvatarUpload">
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
        </div>

        <div class="profile-body">
          <form @submit.prevent="saveProfile" class="profile-form">
            <!-- Personal Information Section -->
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
                  >
                </div>
                
                <div class="form-group">
                  <label>Last Name <span class="required">*</span></label>
                  <input 
                    v-model="profileForm.last_name" 
                    type="text" 
                    required
                    placeholder="Enter your last name"
                    class="form-input"
                  >
                </div>
                
                <div class="form-group">
                  <label>Email Address <span class="required">*</span></label>
                  <input 
                    v-model="profileForm.email" 
                    type="email" 
                    required
                    placeholder="Enter your email"
                    class="form-input"
                  >
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
                
                <div class="form-group">
                  <label>Role</label>
                  <input 
                    :value="roleDisplay"
                    type="text" 
                    disabled
                    class="form-input disabled-input"
                  >
                </div>
              </div>
            </div>

            <!-- Security Section -->
            <div class="form-section">
              <h3 class="section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
                Security Settings
              </h3>
              
              <div class="form-grid">
                <div class="form-group">
                  <label>Current Password</label>
                  <input 
                    v-model="profileForm.currentPassword" 
                    type="password" 
                    placeholder="Enter current password (required to change password)"
                    class="form-input"
                  >
                </div>
                
                <div class="form-group">
                  <label>New Password</label>
                  <input 
                    v-model="profileForm.newPassword" 
                    type="password" 
                    placeholder="Enter new password (leave blank to keep current)"
                    class="form-input"
                    :class="{ 'has-error': passwordError }"
                  >
                  <span v-if="passwordError" class="error-message">{{ passwordError }}</span>
                </div>
                
                <div class="form-group">
                  <label>Confirm New Password</label>
                  <input 
                    v-model="profileForm.confirmPassword" 
                    type="password" 
                    placeholder="Confirm new password"
                    class="form-input"
                    :class="{ 'has-error': passwordError }"
                  >
                </div>
              </div>
            </div>

            <!-- Preferences Section -->
            <div class="form-section">
              <h3 class="section-title">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="3"/>
                  <path d="M12 1v6m0 6v6M5.64 5.64l4.24 4.24m4.24 4.24l4.24 4.24M1 12h6m6 0h6M5.64 18.36l4.24-4.24m4.24-4.24l4.24-4.24"/>
                </svg>
                Preferences
              </h3>
              
              <div class="form-grid">
                <div class="form-group">
                  <label>Language</label>
                  <select v-model="profileForm.language" class="form-input">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                  </select>
                </div>
                
                <div class="form-group">
                  <label>Timezone</label>
                  <select v-model="profileForm.timezone" class="form-input">
                    <option value="UTC">UTC</option>
                    <option value="America/New_York">Eastern Time (ET)</option>
                    <option value="America/Chicago">Central Time (CT)</option>
                    <option value="America/Denver">Mountain Time (MT)</option>
                    <option value="America/Los_Angeles">Pacific Time (PT)</option>
                    <option value="Asia/Manila">Philippine Time (PHT)</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="form-actions">
              <button type="button" class="btn-secondary" @click="cancelEdit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
              </button>
              <button type="submit" class="btn-primary" :disabled="isSaving">
                <svg v-if="!isSaving" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/>
                  <polyline points="17 21 17 13 7 13 7 21"/>
                  <polyline points="7 3 7 8 15 8"/>
                </svg>
                <span v-else class="spinner"></span>
                {{ isSaving ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Account Statistics Card -->
      <div class="stats-card">
        <h3 class="stats-title">Account Statistics</h3>
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/>
                <rect x="8" y="2" width="8" height="4" rx="1" ry="1"/>
              </svg>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ accountStats.totalActions }}</div>
              <div class="stat-label">Total Actions</div>
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
              <div class="stat-value">{{ accountStats.lastLogin }}</div>
              <div class="stat-label">Last Login</div>
            </div>
          </div>
          
          <div class="stat-item">
            <div class="stat-icon">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
              </svg>
            </div>
            <div class="stat-info">
              <div class="stat-value">{{ accountStats.memberSince }}</div>
              <div class="stat-label">Member Since</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAdminAuthStore } from '@/stores/adminAuth'
import { useNotification } from '@/composables/useNotification'

const router = useRouter()
const adminStore = useAdminAuthStore()
const { success, error: showError } = useNotification()

// ═══════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════
const avatarInput = ref<HTMLInputElement | null>(null)
const isSaving = ref(false)
const passwordError = ref('')

const profileForm = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  currentPassword: '',
  newPassword: '',
  confirmPassword: '',
  language: 'en',
  timezone: 'Asia/Manila'
})

const accountStats = ref({
  totalActions: '0',
  lastLogin: 'N/A',
  memberSince: 'N/A'
})

// ═══════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════
const currentAdmin = computed(() => adminStore.admin)

const roleDisplay = computed(() => {
  const role = currentAdmin.value?.role?.slug || currentAdmin.value?.role
  if (role === 'super-admin' || role === 'super_admin') return 'Super Admin'
  if (role === 'admin') return 'Admin'
  return 'Staff'
})

// ═══════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════
const triggerAvatarUpload = () => {
  avatarInput.value?.click()
}

const handleAvatarChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    try {
      // TODO: Implement avatar upload via FileUploadController
      // For now, just log it
      console.log('Avatar file selected:', file.name)
      // This would typically upload to server and update avatar URL
    } catch (err) {
      console.error('Failed to upload avatar:', err)
    }
  }
}

const validatePassword = () => {
  passwordError.value = ''
  
  if (!profileForm.value.newPassword && !profileForm.value.confirmPassword) {
    return true // No password change requested
  }
  
  if (profileForm.value.newPassword && !profileForm.value.currentPassword) {
    passwordError.value = 'Current password is required to change password'
    return false
  }
  
  if (profileForm.value.newPassword.length < 6) {
    passwordError.value = 'Password must be at least 6 characters long'
    return false
  }
  
  if (profileForm.value.newPassword !== profileForm.value.confirmPassword) {
    passwordError.value = 'Passwords do not match'
    return false
  }
  
  return true
}

const saveProfile = async () => {
  if (!validatePassword()) {
    return
  }
  
  isSaving.value = true
  
  try {
    // TODO: Implement actual API call to update admin profile
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
    
    // Update local admin data
    if (adminStore.currentAdmin) {
      adminStore.currentAdmin.name = profileForm.value.name
      adminStore.currentAdmin.email = profileForm.value.email
      ;(adminStore.currentAdmin as any).phone = profileForm.value.phone
      
      // Save to localStorage
      localStorage.setItem('admin_user', JSON.stringify(adminStore.currentAdmin))
    }
    
    // Log activity
    adminStore.logActivity('profile', 'Admin profile updated')
    
    // Show success message
    alert('Profile updated successfully!')
    
  } catch (error) {
    console.error('Error saving profile:', error)
    alert('Failed to update profile. Please try again.')
  } finally {
    isSaving.value = false
  }
}

const cancelEdit = () => {
  router.push('/admin/dashboard')
}

// ═══════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════
onMounted(async () => {
  // Fetch latest admin data
  if (!currentAdmin.value) {
    await adminStore.fetchAdmin()
  }
  
  // Initialize form with current admin data
  if (currentAdmin.value) {
    const admin = currentAdmin.value
    profileForm.value = {
      first_name: admin.first_name || '',
      last_name: admin.last_name || '',
      email: admin.email || '',
      phone: admin.phone || '',
      currentPassword: '',
      newPassword: '',
      confirmPassword: '',
      language: 'en',
      timezone: 'Asia/Manila'
    }
    
    // Update account stats (if available from admin data)
    if (admin.created_at) {
      const createdDate = new Date(admin.created_at)
      accountStats.value.memberSince = createdDate.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
    }
  }
})
</script>

<style scoped>
.profile-page {
  --gold: #c9a050;
  --gold-light: #e6c866;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  padding-top: 3.5rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.page-subtitle {
  color: var(--gray);
  margin: 0;
  font-size: 0.95rem;
}

.profile-content {
  display: grid;
  grid-template-columns: 1fr 400px;
  gap: 2rem;
}

/* Profile Card */
.profile-card {
  background: var(--white);
  border-radius: 24px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
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
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #ffffff;
  border: 4px solid rgba(255, 255, 255, 0.3);
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
  transition: all 0.3s ease;
}

.profile-avatar-large:hover {
  transform: scale(1.05);
  box-shadow: 0 12px 40px rgba(0, 0, 0, 0.3);
}

.profile-avatar-large svg {
  width: 60px;
  height: 60px;
}

.btn-change-avatar {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  background: rgba(255, 255, 255, 0.2);
  backdrop-filter: blur(10px);
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-radius: 12px;
  color: #ffffff;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-change-avatar:hover {
  background: rgba(255, 255, 255, 0.3);
  border-color: rgba(255, 255, 255, 0.5);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-change-avatar svg {
  width: 18px;
  height: 18px;
}

.profile-body {
  padding: 2.5rem;
}

.profile-form {
  display: flex;
  flex-direction: column;
  gap: 2.5rem;
}

.form-section {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
}

.section-title svg {
  width: 24px;
  height: 24px;
  color: var(--gold);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-size: 0.9rem;
  font-weight: 600;
  color: var(--dark);
}

.required {
  color: #ef4444;
}

.form-input {
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  font-size: 0.9rem;
  outline: none;
  transition: all 0.3s ease;
  background: #ffffff;
  color: var(--dark);
  font-family: inherit;
}

.form-input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
  background: #ffffff;
}

.form-input.disabled-input {
  background: #f9fafb;
  color: var(--gray);
  cursor: not-allowed;
}

.form-input.has-error {
  border-color: #ef4444;
}

.form-input.has-error:focus {
  box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.1);
}

.error-message {
  font-size: 0.85rem;
  color: #ef4444;
  margin-top: 0.25rem;
}

.form-actions {
  display: flex;
  gap: 1rem;
  justify-content: flex-end;
  padding-top: 2rem;
  border-top: 2px solid #f3f4f6;
  margin-top: 1rem;
}

.btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: #f3f4f6;
  border: 2px solid #e5e7eb;
  border-radius: 12px;
  color: var(--dark);
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-secondary:hover {
  background: #e5e7eb;
  border-color: #d1d5db;
  transform: translateY(-1px);
}

.btn-secondary svg {
  width: 18px;
  height: 18px;
}

.btn-primary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  background: linear-gradient(135deg, #c9a050 0%, #b8860b 100%);
  border: none;
  border-radius: 12px;
  color: #ffffff;
  font-size: 0.9rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.3);
}

.btn-primary:hover:not(:disabled) {
  background: linear-gradient(135deg, #b8860b 0%, #a0750a 100%);
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(201, 160, 80, 0.4);
}

.btn-primary:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

.spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.6s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Stats Card */
.stats-card {
  background: var(--white);
  border-radius: 24px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
  height: fit-content;
  position: sticky;
  top: 2rem;
}

.stats-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 1.5rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #f3f4f6;
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
  padding: 1.25rem;
  background: #f9fafb;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.stat-item:hover {
  background: #f3f4f6;
  transform: translateX(4px);
}

.stat-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, rgba(201, 160, 80, 0.1) 0%, rgba(201, 160, 80, 0.05) 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gold);
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
  color: var(--dark);
  margin-bottom: 0.25rem;
}

.stat-label {
  font-size: 0.85rem;
  color: var(--gray);
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

/* Responsive */
@media (max-width: 1024px) {
  .profile-content {
    grid-template-columns: 1fr;
  }
  
  .stats-card {
    position: static;
  }
}

@media (max-width: 768px) {
  .form-grid {
    grid-template-columns: 1fr;
  }
  
  .form-actions {
    flex-direction: column;
  }
  
  .btn-secondary,
  .btn-primary {
    width: 100%;
    justify-content: center;
  }
  
  .profile-header {
    padding: 2rem 1.5rem;
  }
  
  .profile-body {
    padding: 1.5rem;
  }
}
</style>
