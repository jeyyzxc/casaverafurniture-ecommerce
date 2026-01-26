<template>
  <div class="admin-cms-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Content Management</h1>
        <p class="page-subtitle">Manage homepage sections, banners, and page content.</p>
      </div>
      <div class="header-actions">
        <button class="btn-primary" @click="showAddSectionModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
          </svg>
          Add Section
        </button>
        <button class="btn-secondary" @click="showAddBannerModal = true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
            <line x1="3" y1="9" x2="21" y2="9"/>
            <line x1="9" y1="21" x2="9" y2="9"/>
          </svg>
          Add Banner
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="loading-container">
      <div class="spinner"></div>
      <p>Loading content...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="error-container">
      <div class="error-icon">⚠️</div>
      <h3>Failed to Load Content</h3>
      <p>{{ error }}</p>
      <button class="btn-primary" @click="loadContent">Try Again</button>
    </div>

    <!-- Content -->
    <div v-else class="cms-content">
      <!-- Homepage Sections -->
      <div class="content-section">
        <h2 class="section-title">Homepage Sections</h2>
        <div class="cms-sections">
          <div v-for="section in sections" :key="section.id" class="section-card">
            <div class="section-header">
              <div>
                <h3>{{ section.name }}</h3>
                <span class="section-type">{{ section.type }}</span>
              </div>
              <div class="section-actions">
                <button class="btn-icon" @click="editSection(section)" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="btn-icon btn-danger" @click="deleteSection(section.id)" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="section-body">
              <p v-if="section.title" class="section-title-text">{{ section.title }}</p>
              <p v-if="section.subtitle" class="section-subtitle-text">{{ section.subtitle }}</p>
              <div class="section-meta">
                <span :class="['visibility-badge', section.is_visible ? 'visible' : 'hidden']">
                  {{ section.is_visible ? 'Visible' : 'Hidden' }}
                </span>
                <span class="order-badge">Order: {{ section.display_order }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Banners -->
      <div class="content-section">
        <h2 class="section-title">Banners</h2>
        <div class="cms-sections">
          <div v-for="banner in banners" :key="banner.id" class="section-card">
            <div class="section-header">
              <div>
                <h3>{{ banner.name }}</h3>
                <span class="section-type">{{ banner.position }}</span>
              </div>
              <div class="section-actions">
                <button class="btn-icon" @click="editBanner(banner)" title="Edit">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="btn-icon btn-danger" @click="deleteBanner(banner.id)" title="Delete">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </div>
            <div class="section-body">
              <div v-if="banner.desktop_image" class="banner-preview">
                <img :src="banner.desktop_image" :alt="banner.alt_text || banner.name" />
              </div>
              <p v-if="banner.title" class="section-title-text">{{ banner.title }}</p>
              <div class="section-meta">
                <span :class="['visibility-badge', banner.is_visible ? 'visible' : 'hidden']">
                  {{ banner.is_visible ? 'Visible' : 'Hidden' }}
                </span>
                <span class="order-badge">Order: {{ banner.display_order }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Add/Edit Section Modal -->
    <Teleport to="body">
      <div v-if="showAddSectionModal || editingSection" class="modal-overlay" @click="closeSectionModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h2>{{ editingSection ? 'Edit Section' : 'Add New Section' }}</h2>
            <button class="btn-close" @click="closeSectionModal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Name *</label>
              <input v-model="sectionForm.name" type="text" placeholder="Section name" required />
            </div>
            <div class="form-group">
              <label>Type *</label>
              <select v-model="sectionForm.type" required>
                <option value="hero">Hero</option>
                <option value="featured">Featured Products</option>
                <option value="categories">Categories</option>
                <option value="collections">Collections</option>
                <option value="new_arrivals">New Arrivals</option>
                <option value="bestsellers">Bestsellers</option>
                <option value="sale">Sale</option>
                <option value="testimonials">Testimonials</option>
                <option value="newsletter">Newsletter</option>
                <option value="banner">Banner</option>
                <option value="custom">Custom</option>
              </select>
            </div>
            <div class="form-group">
              <label>Title</label>
              <input v-model="sectionForm.title" type="text" placeholder="Section title" />
            </div>
            <div class="form-group">
              <label>Subtitle</label>
              <input v-model="sectionForm.subtitle" type="text" placeholder="Section subtitle" />
            </div>
            <div class="form-group">
              <label>Content</label>
              <textarea v-model="sectionForm.content" rows="5" placeholder="Section content (HTML allowed)"></textarea>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Display Order</label>
                <input v-model.number="sectionForm.display_order" type="number" min="0" />
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="sectionForm.is_visible" />
                  Visible
                </label>
              </div>
            </div>
            <div class="form-group">
              <label>Background Color</label>
              <input v-model="sectionForm.background_color" type="color" />
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeSectionModal">Cancel</button>
            <button class="btn-primary" @click="saveSection" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : (editingSection ? 'Update' : 'Create') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Add/Edit Banner Modal -->
    <Teleport to="body">
      <div v-if="showAddBannerModal || editingBanner" class="modal-overlay" @click="closeBannerModal">
        <div class="modal-container" @click.stop>
          <div class="modal-header">
            <h2>{{ editingBanner ? 'Edit Banner' : 'Add New Banner' }}</h2>
            <button class="btn-close" @click="closeBannerModal">×</button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label>Name *</label>
              <input v-model="bannerForm.name" type="text" placeholder="Banner name" required />
            </div>
            <div class="form-group">
              <label>Position *</label>
              <select v-model="bannerForm.position" required>
                <option value="home_hero">Home Hero</option>
                <option value="home_middle">Home Middle</option>
                <option value="home_bottom">Home Bottom</option>
                <option value="category_top">Category Top</option>
                <option value="product_top">Product Top</option>
                <option value="cart_top">Cart Top</option>
                <option value="checkout_top">Checkout Top</option>
                <option value="sidebar">Sidebar</option>
                <option value="popup">Popup</option>
              </select>
            </div>
            <div class="form-group">
              <label>Title</label>
              <input v-model="bannerForm.title" type="text" placeholder="Banner title" />
            </div>
            <div class="form-group">
              <label>Subtitle</label>
              <input v-model="bannerForm.subtitle" type="text" placeholder="Banner subtitle" />
            </div>
            <div class="form-group">
              <label>Description</label>
              <textarea v-model="bannerForm.description" rows="3" placeholder="Banner description"></textarea>
            </div>
            <div class="form-group">
              <label>Desktop Image URL *</label>
              <input v-model="bannerForm.desktop_image" type="text" placeholder="https://..." required />
            </div>
            <div class="form-group">
              <label>Mobile Image URL</label>
              <input v-model="bannerForm.mobile_image" type="text" placeholder="https://..." />
            </div>
            <div class="form-group">
              <label>Alt Text</label>
              <input v-model="bannerForm.alt_text" type="text" placeholder="Image alt text" />
            </div>
            <div class="form-group">
              <label>Link URL</label>
              <input v-model="bannerForm.link_url" type="text" placeholder="https://..." />
            </div>
            <div class="form-group">
              <label>Link/Button Text</label>
              <input v-model="bannerForm.link_text" type="text" placeholder="Click here" />
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Display Order</label>
                <input v-model.number="bannerForm.display_order" type="number" min="0" />
              </div>
              <div class="form-group">
                <label>
                  <input type="checkbox" v-model="bannerForm.is_visible" />
                  Visible
                </label>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button class="btn-secondary" @click="closeBannerModal">Cancel</button>
            <button class="btn-primary" @click="saveBanner" :disabled="isSaving">
              {{ isSaving ? 'Saving...' : (editingBanner ? 'Update' : 'Create') }}
            </button>
          </div>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { cms as cmsApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

const isLoading = ref(false)
const error = ref<string | null>(null)
const isSaving = ref(false)

const sections = ref<any[]>([])
const banners = ref<any[]>([])

const showAddSectionModal = ref(false)
const showAddBannerModal = ref(false)
const editingSection = ref<any>(null)
const editingBanner = ref<any>(null)

const sectionForm = ref({
  name: '',
  type: 'custom',
  title: '',
  subtitle: '',
  content: '',
  display_order: 0,
  is_visible: true,
  background_color: '#ffffff',
})

const bannerForm = ref({
  name: '',
  position: 'home_hero',
  title: '',
  subtitle: '',
  description: '',
  desktop_image: '',
  mobile_image: '',
  alt_text: '',
  link_url: '',
  link_text: '',
  display_order: 0,
  is_visible: true,
})

const loadContent = async () => {
  isLoading.value = true
  error.value = null

  try {
    const [sectionsResponse, bannersResponse] = await Promise.all([
      cmsApi.getSections(),
      cmsApi.getBanners(),
    ])

    if (sectionsResponse.data.success) {
      sections.value = sectionsResponse.data.data
    } else {
      throw new Error(sectionsResponse.data.message || 'Failed to load sections')
    }

    if (bannersResponse.data.success) {
      banners.value = bannersResponse.data.data
    } else {
      throw new Error(bannersResponse.data.message || 'Failed to load banners')
    }
  } catch (err: any) {
    console.error('Failed to load content:', err)
    error.value = err.response?.data?.message || err.message || 'Failed to load content. Please try again.'
    showError('Failed to Load', error.value)
  } finally {
    isLoading.value = false
  }
}

const editSection = (section: any) => {
  editingSection.value = section
  sectionForm.value = {
    name: section.name || '',
    type: section.type || 'custom',
    title: section.title || '',
    subtitle: section.subtitle || '',
    content: section.content || '',
    display_order: section.display_order || 0,
    is_visible: section.is_visible !== undefined ? section.is_visible : true,
    background_color: section.background_color || '#ffffff',
  }
  showAddSectionModal.value = true
}

const editBanner = (banner: any) => {
  editingBanner.value = banner
  bannerForm.value = {
    name: banner.name || '',
    position: banner.position || 'home_hero',
    title: banner.title || '',
    subtitle: banner.subtitle || '',
    description: banner.description || '',
    desktop_image: banner.desktop_image || '',
    mobile_image: banner.mobile_image || '',
    alt_text: banner.alt_text || '',
    link_url: banner.link_url || '',
    link_text: banner.link_text || banner.button_text || '',
    display_order: banner.display_order || 0,
    is_visible: banner.is_visible !== undefined ? banner.is_visible : true,
  }
  showAddBannerModal.value = true
}

const closeSectionModal = () => {
  showAddSectionModal.value = false
  editingSection.value = null
  sectionForm.value = {
    name: '',
    type: 'custom',
    title: '',
    subtitle: '',
    content: '',
    display_order: 0,
    is_visible: true,
    background_color: '#ffffff',
  }
}

const closeBannerModal = () => {
  showAddBannerModal.value = false
  editingBanner.value = null
  bannerForm.value = {
    name: '',
    position: 'home_hero',
    title: '',
    subtitle: '',
    description: '',
    desktop_image: '',
    mobile_image: '',
    alt_text: '',
    link_url: '',
    link_text: '',
    display_order: 0,
    is_visible: true,
  }
}

const saveSection = async () => {
  if (!sectionForm.value.name || !sectionForm.value.type) {
    showError('Validation Failed', 'Please fill in all required fields.')
    return
  }

  isSaving.value = true

  try {
    let response
    if (editingSection.value) {
      response = await cmsApi.updateSection(editingSection.value.id, sectionForm.value)
    } else {
      response = await cmsApi.createSection(sectionForm.value)
    }

    if (response.data.success) {
      success(
        editingSection.value ? 'Section Updated' : 'Section Created',
        `Homepage section "${sectionForm.value.name}" has been ${editingSection.value ? 'updated' : 'created'} successfully.`
      )
      closeSectionModal()
      await loadContent()
    } else {
      throw new Error(response.data.message || 'Failed to save section')
    }
  } catch (err: any) {
    console.error('Failed to save section:', err)
    showError('Failed to Save', err.response?.data?.message || err.message || 'Failed to save section. Please try again.')
  } finally {
    isSaving.value = false
  }
}

const saveBanner = async () => {
  if (!bannerForm.value.name || !bannerForm.value.position || !bannerForm.value.desktop_image) {
    showError('Validation Failed', 'Please fill in all required fields.')
    return
  }

  isSaving.value = true

  try {
    let response
    if (editingBanner.value) {
      response = await cmsApi.updateBanner(editingBanner.value.id, bannerForm.value)
    } else {
      response = await cmsApi.createBanner(bannerForm.value)
    }

    if (response.data.success) {
      success(
        editingBanner.value ? 'Banner Updated' : 'Banner Created',
        `Banner "${bannerForm.value.name}" has been ${editingBanner.value ? 'updated' : 'created'} successfully.`
      )
      closeBannerModal()
      await loadContent()
    } else {
      throw new Error(response.data.message || 'Failed to save banner')
    }
  } catch (err: any) {
    console.error('Failed to save banner:', err)
    showError('Failed to Save', err.response?.data?.message || err.message || 'Failed to save banner. Please try again.')
  } finally {
    isSaving.value = false
  }
}

const deleteSection = async (id: number) => {
  if (!confirm('Are you sure you want to delete this section? This action cannot be undone.')) {
    return
  }

  try {
    const response = await cmsApi.deleteSection(id)
    if (response.data.success) {
      success('Section Deleted', 'The section has been deleted successfully.')
      await loadContent()
    } else {
      throw new Error(response.data.message || 'Failed to delete section')
    }
  } catch (err: any) {
    console.error('Failed to delete section:', err)
    showError('Failed to Delete', err.response?.data?.message || err.message || 'Failed to delete section. Please try again.')
  }
}

const deleteBanner = async (id: number) => {
  if (!confirm('Are you sure you want to delete this banner? This action cannot be undone.')) {
    return
  }

  try {
    const response = await cmsApi.deleteBanner(id)
    if (response.data.success) {
      success('Banner Deleted', 'The banner has been deleted successfully.')
      await loadContent()
    } else {
      throw new Error(response.data.message || 'Failed to delete banner')
    }
  } catch (err: any) {
    console.error('Failed to delete banner:', err)
    showError('Failed to Delete', err.response?.data?.message || err.message || 'Failed to delete banner. Please try again.')
  }
}

onMounted(() => {
  loadContent()
})
</script>

<style scoped>
.admin-cms-page {
  --gold: #c9a050;
  --dark: #1a1d29;
  --light: #f5f7fa;
  --white: #ffffff;
  --gray: #6b7280;
  padding-top: 3.5rem;
  padding-left: 2rem;
  padding-right: 2rem;
  padding-bottom: 2rem;
  min-height: 100vh;
  background: var(--light);
}

.page-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
  flex-wrap: wrap;
  gap: 1rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.page-subtitle {
  color: #000000;
  font-size: 0.95rem;
  margin: 0;
}

.header-actions {
  display: flex;
  gap: 0.75rem;
}

.btn-primary,
.btn-secondary {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  font-size: 0.9rem;
}

.btn-primary {
  background: var(--gold);
  color: white;
}

.btn-primary:hover {
  background: #b8860b;
  transform: translateY(-1px);
}

.btn-secondary {
  background: #f3f4f6;
  color: var(--dark);
}

.btn-secondary:hover {
  background: #e5e7eb;
}

.loading-container,
.error-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 4rem 2rem;
  text-align: center;
}

.spinner {
  width: 48px;
  height: 48px;
  border: 4px solid #e5e7eb;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.error-container .error-icon {
  font-size: 3rem;
  margin-bottom: 1rem;
}

.error-container h3 {
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.error-container p {
  color: var(--gray);
  margin-bottom: 1.5rem;
}

.cms-content {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.content-section {
  background: var(--white);
  border-radius: 16px;
  padding: 2rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.section-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 1.5rem;
}

.cms-sections {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
  gap: 1.5rem;
}

.section-card {
  background: #f9fafb;
  border-radius: 12px;
  padding: 1.5rem;
  border: 2px solid #e5e7eb;
  transition: all 0.3s ease;
}

.section-card:hover {
  border-color: var(--gold);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.section-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
  padding-bottom: 1rem;
  border-bottom: 2px solid #e5e7eb;
}

.section-header h3 {
  margin: 0 0 0.25rem;
  font-size: 1.1rem;
  font-weight: 700;
  color: var(--dark);
}

.section-type {
  display: inline-block;
  padding: 0.25rem 0.75rem;
  background: var(--gold);
  color: white;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.section-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-icon {
  padding: 0.5rem;
  background: transparent;
  border: 2px solid #e5e7eb;
  border-radius: 6px;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  color: var(--gray);
}

.btn-icon:hover {
  border-color: var(--gold);
  color: var(--gold);
  background: #fefbf5;
}

.btn-icon svg {
  width: 18px;
  height: 18px;
}

.btn-icon.btn-danger:hover {
  border-color: #ef4444;
  color: #ef4444;
  background: #fef2f2;
}

.section-body {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.section-title-text {
  font-weight: 600;
  color: var(--dark);
  margin: 0;
}

.section-subtitle-text {
  color: var(--gray);
  margin: 0;
}

.section-meta {
  display: flex;
  gap: 0.75rem;
  flex-wrap: wrap;
  margin-top: 0.5rem;
}

.visibility-badge,
.order-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 6px;
  font-size: 0.75rem;
  font-weight: 600;
}

.visibility-badge.visible {
  background: #d1fae5;
  color: #065f46;
}

.visibility-badge.hidden {
  background: #fee2e2;
  color: #991b1b;
}

.order-badge {
  background: #e0e7ff;
  color: #3730a3;
}

.banner-preview {
  width: 100%;
  height: 150px;
  border-radius: 8px;
  overflow: hidden;
  margin-bottom: 0.75rem;
}

.banner-preview img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 2rem;
}

.modal-container {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 700px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  color: #000000;
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.modal-header h2 {
  margin: 0;
  font-size: 1.5rem;
  color: #000000;
}

.btn-close {
  background: none;
  border: none;
  font-size: 2rem;
  color: #6b7280;
  cursor: pointer;
  line-height: 1;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 6px;
  transition: all 0.2s;
}

.btn-close:hover {
  background: #f3f4f6;
  color: var(--dark);
}

.modal-body {
  padding: 1.5rem;
  color: #000000;
}

.form-group {
  margin-bottom: 1.5rem;
}

.form-group label {
  display: block;
  font-weight: 600;
  color: #000000;
  margin-bottom: 0.5rem;
  font-size: 0.9rem;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 0.75rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #000000;
  transition: all 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.form-group input[type="checkbox"] {
  width: auto;
  margin-right: 0.5rem;
}

.form-group select option {
  color: #000000;
  background: white;
}

.form-group input::placeholder,
.form-group textarea::placeholder {
  color: #6b7280;
}

.form-group input[type="color"] {
  height: 50px;
  padding: 0.25rem;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.5rem;
  border-top: 2px solid #e5e7eb;
}

@media (max-width: 768px) {
  .admin-cms-page {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  .page-header {
    flex-direction: column;
  }

  .cms-sections {
    grid-template-columns: 1fr;
  }

  .form-row {
    grid-template-columns: 1fr;
  }
}
</style>
