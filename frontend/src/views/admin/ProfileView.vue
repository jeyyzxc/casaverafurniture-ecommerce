<template>
  <div class="profile-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">My Profile</h1>
        <p class="page-subtitle">Manage your account information and preferences</p>
      </div>
    </div>

    <div class="profile-content">
      <div class="profile-card">
        <div class="profile-header">
          <div class="profile-avatar-section">
            <div class="profile-avatar-large">
              <img v-if="profileData?.avatar" :src="profileData.avatar" alt="Avatar" class="avatar-img">
              <svg v-else viewBox="0 0 24 24" fill="currentColor">
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
          <div v-if="isLoadingProfile" class="loading-state">
            <div class="spinner"></div>
            <p>Loading profile...</p>
          </div>

          <form v-else @submit.prevent="saveProfile" class="profile-form">
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
                  <div class="password-input-wrapper">
                    <input
                      v-model="profileForm.currentPassword"
                      :type="showCurrentPassword ? 'text' : 'password'"
                      placeholder="Enter current password (required to change password)"
                      class="form-input"
                    >
                    <button type="button" class="toggle-password" @click="showCurrentPassword = !showCurrentPassword">
                      <svg v-if="showCurrentPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                      </svg>
                      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                </div>

                <div class="form-group">
                  <label>New Password</label>
                  <div class="password-input-wrapper">
                    <input
                      v-model="profileForm.newPassword"
                      :type="showNewPassword ? 'text' : 'password'"
                      placeholder="Enter new password (leave blank to keep current)"
                      class="form-input"
                      :class="{ 'has-error': passwordError }"
                    >
                    <button type="button" class="toggle-password" @click="showNewPassword = !showNewPassword">
                      <svg v-if="showNewPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                      </svg>
                      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                  <span v-if="passwordError" class="error-message">{{ passwordError }}</span>
                </div>

                <div class="form-group">
                  <label>Confirm New Password</label>
                  <div class="password-input-wrapper">
                    <input
                      v-model="profileForm.confirmPassword"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Confirm new password"
                      class="form-input"
                      :class="{ 'has-error': passwordError }"
                    >
                    <button type="button" class="toggle-password" @click="showConfirmPassword = !showConfirmPassword">
                      <svg v-if="showConfirmPassword" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                        <line x1="1" y1="1" x2="23" y2="23"></line>
                      </svg>
                      <svg v-else viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                        <circle cx="12" cy="12" r="3"></circle>
                      </svg>
                    </button>
                  </div>
                </div>
              </div>
            </div>

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
                    <option value="de">German</option>
                    <option value="zh">Chinese</option>
                    <option value="ja">Japanese</option>
                    <option value="ko">Korean</option>
                    <option value="tl">Tagalog</option>
                    <option value="it">Italian</option>
                    <option value="pt">Portuguese</option>
                    <option value="ru">Russian</option>
                    <option value="ar">Arabic</option>
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
                    <option value="Asia/Tokyo">Japan Standard Time (JST)</option>
                    <option value="Europe/London">Greenwich Mean Time (GMT)</option>
                  </select>
                </div>
              </div>
            </div>

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
              <div class="stat-value small-text">{{ accountStats.lastLogin }}</div>
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
import { upload as uploadApi, activityLogs, admins as adminsApi } from '@/services/adminApi'

const router = useRouter()
const adminStore = useAdminAuthStore()
const { success, error: showError } = useNotification()

const avatarInput = ref<HTMLInputElement | null>(null)
const isSaving = ref(false)
const isLoadingProfile = ref(true)
const passwordError = ref('')

const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

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

const profileData = ref<any>(null)

const currentAdmin = computed(() => adminStore.admin)

const roleDisplay = computed(() => {
  const role = profileData.value?.role?.slug || profileData.value?.role?.name || 'Staff'
  if (role === 'super-admin' || role === 'super_admin') return 'Super Admin'
  if (role === 'admin') return 'Admin'
  return role
})

const triggerAvatarUpload = () => {
  avatarInput.value?.click()
}

const handleAvatarChange = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  if (file) {
    try {
      const response = await uploadApi.image(file, 'avatars')
      if (response.data.success) {
        const updateRes = await adminStore.updateProfile({
          avatar: response.data.data.url
        } as any)

        if (updateRes.success) {
          profileData.value.avatar = response.data.data.url
          success('Avatar Updated', 'Your profile picture has been updated successfully.')
        } else {
          showError('Update Failed', updateRes.message)
        }
      }
    } catch (err: any) {
      console.error('Failed to upload avatar:', err)
      showError('Upload Failed', err.response?.data?.message || 'Failed to upload avatar.')
    }
  }
}

const validatePassword = () => {
  passwordError.value = ''

  if (!profileForm.value.newPassword && !profileForm.value.confirmPassword) {
    return true
  }

  if (profileForm.value.newPassword && !profileForm.value.currentPassword) {
    passwordError.value = 'Current password is required to change password'
    return false
  }

  if (profileForm.value.newPassword.length < 8) {
    passwordError.value = 'Password must be at least 8 characters long'
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
    const profileDataPayload = {
      first_name: profileForm.value.first_name,
      last_name: profileForm.value.last_name,
      email: profileForm.value.email,
      phone: profileForm.value.phone
    }

    const profileRes = await adminStore.updateProfile(profileDataPayload)

    if (!profileRes.success) {
      throw new Error(profileRes.message)
    }

    if (profileForm.value.newPassword) {
      const passwordRes = await adminStore.changePassword(
        profileForm.value.currentPassword,
        profileForm.value.newPassword,
        profileForm.value.confirmPassword
      )

      if (!passwordRes.success) {
        throw new Error(passwordRes.message)
      }

      profileForm.value.currentPassword = ''
      profileForm.value.newPassword = ''
      profileForm.value.confirmPassword = ''
    }

    success('Profile Updated', 'Your profile information has been saved successfully.')

    await fetchFullProfile()

  } catch (error: any) {
    console.error('Error saving profile:', error)
    showError('Update Failed', error.message || 'Failed to update profile. Please try again.')
  } finally {
    isSaving.value = false
  }
}

const cancelEdit = () => {
  router.push('/admin/dashboard')
}

const fetchFullProfile = async () => {
  isLoadingProfile.value = true
  try {
    if (!adminStore.admin?.id) {
      await adminStore.fetchAdmin()
    }

    const adminId = adminStore.admin?.id
    if (!adminId) {
      throw new Error('Admin ID not found')
    }

    const response = await adminsApi.get(adminId)
    if (response.data.success) {
      profileData.value = response.data.data

      profileForm.value.first_name = profileData.value.first_name || ''
      profileForm.value.last_name = profileData.value.last_name || ''
      profileForm.value.email = profileData.value.email || ''
      profileForm.value.phone = profileData.value.phone || ''

      if (profileData.value.created_at) {
        const date = new Date(profileData.value.created_at)
        accountStats.value.memberSince = date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric'
        })
      }

      if (profileData.value.last_login_at) {
        const date = new Date(profileData.value.last_login_at)
        accountStats.value.lastLogin = date.toLocaleDateString('en-US', {
          month: 'short',
          day: 'numeric',
          year: 'numeric',
          hour: '2-digit',
          minute: '2-digit'
        })
      }

      await loadAccountStats(adminId)
    }
  } catch (err) {
    console.error('Failed to fetch full profile:', err)
    showError('Error', 'Failed to load profile data.')
  } finally {
    isLoadingProfile.value = false
  }
}

const loadAccountStats = async (adminId: number) => {
  try {
    const response = await activityLogs.list({
      causer_id: adminId,
      per_page: 1
    })

    if (response.data.success) {
      const total = response.data.data.total || 0
      accountStats.value.totalActions = total.toLocaleString()
    }

    const loginResponse = await activityLogs.list({
      causer_id: adminId,
      action: 'login',
      per_page: 1,
      sort_by: 'created_at',
      sort_order: 'desc'
    })

    if (loginResponse.data.success && loginResponse.data.data.data.length > 0) {
      const lastLogin = loginResponse.data.data.data[0]
      const date = new Date(lastLogin.timestamp || lastLogin.created_at)
      accountStats.value.lastLogin = date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      })
    }

  } catch (err) {
    console.error('Failed to load account stats:', err)
  }
}

onMounted(() => {
  fetchFullProfile()
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
  overflow: hidden;
}

.avatar-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
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

.password-input-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.password-input-wrapper input {
  width: 100%;
  padding-right: 2.5rem;
}

.toggle-password {
  position: absolute;
  right: 0.75rem;
  background: none;
  border: none;
  cursor: pointer;
  color: #94a3b8;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem;
  transition: color 0.2s;
}

.toggle-password:hover {
  color: var(--dark);
}

.toggle-password svg {
  width: 20px;
  height: 20px;
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

.loading-state {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem;
  color: var(--gray);
  gap: 1rem;
}

.loading-state .spinner {
  width: 32px;
  height: 32px;
  border-color: #e5e7eb;
  border-top-color: var(--gold);
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

.stat-value.small-text {
  font-size: 1.1rem;
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
