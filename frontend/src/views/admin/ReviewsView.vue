<template>
  <div class="admin-reviews-page">
    <div class="page-header">
      <div>
        <h1 class="page-title">Reviews & Ratings</h1>
        <p class="page-subtitle">Manage product reviews and customer feedback.</p>
      </div>
      <div class="header-actions">
        <select v-model="filterStatus" class="filter-select" @change="loadReviews">
          <option value="">All Reviews</option>
          <option value="pending">Pending</option>
          <option value="approved">Approved</option>
          <option value="rejected">Rejected</option>
        </select>
      </div>
    </div>

    <div v-if="isLoading" class="loading-state">
      <div class="spinner"></div>
      <p>Loading reviews...</p>
    </div>

    <div v-else-if="reviews.length === 0" class="empty-state">
      <p>No reviews found.</p>
    </div>

    <div v-else class="reviews-list">
      <div v-for="review in reviews" :key="review.id" class="review-card">
        <div class="review-header">
          <div class="reviewer-info">
            <div class="reviewer-avatar">{{ review.user?.first_name?.charAt(0) || 'U' }}</div>
            <div>
              <div class="reviewer-name">{{ review.user?.full_name || 'Unknown User' }}</div>
              <div class="review-date">{{ formatDate(review.created_at) }}</div>
            </div>
          </div>
          <div class="review-rating">
            <div class="stars">
              <span v-for="i in 5" :key="i" :class="{ filled: i <= review.rating }">★</span>
            </div>
            <span class="rating-value">{{ review.rating }}/5</span>
          </div>
        </div>
        <div class="review-product">
          <img :src="review.product?.image_url || '/placeholder.png'" :alt="review.product?.name" class="product-thumb">
          <span>{{ review.product?.name || 'Unknown Product' }}</span>
        </div>
        <div class="review-content">
          <p>{{ review.comment }}</p>
        </div>
        <div class="review-actions">
          <span class="status-badge" :class="review.status.toLowerCase()">{{ review.status }}</span>
          <div class="action-buttons">
            <button v-if="review.status === 'pending'" class="btn-small success" @click="updateStatus(review.id, 'approved')">Approve</button>
            <button v-if="review.status === 'pending'" class="btn-small danger" @click="updateStatus(review.id, 'rejected')">Reject</button>
            <button class="btn-small" @click="deleteReview(review.id)">Delete</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="pagination.last_page > 1">
      <button
        @click="changePage(pagination.current_page - 1)"
        :disabled="pagination.current_page === 1"
        class="page-btn"
      >
        Previous
      </button>
      <span class="page-info">
        Page {{ pagination.current_page }} of {{ pagination.last_page }}
      </span>
      <button
        @click="changePage(pagination.current_page + 1)"
        :disabled="pagination.current_page === pagination.last_page"
        class="page-btn"
      >
        Next
      </button>
    </div>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showDeleteModal }" @click.self="closeDeleteModal">
        <div class="modal-container delete-modal">
          <div class="delete-modal-content">
            <div class="delete-icon-wrapper">
              <div class="delete-icon-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="3 6 5 6 21 6"/>
                  <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  <line x1="10" y1="11" x2="10" y2="17"/>
                  <line x1="14" y1="11" x2="14" y2="17"/>
                </svg>
              </div>
            </div>
            <h2 class="delete-title">Delete Review</h2>
            <p class="delete-message">
              Are you sure you want to delete this review from
              <strong class="delete-item-name">{{ deletingReview?.user?.full_name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. The review will be permanently removed.
            </p>
            <div class="delete-actions">
              <button type="button" class="delete-btn-cancel" @click="closeDeleteModal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <line x1="18" y1="6" x2="6" y2="18"/>
                  <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
                Cancel
              </button>
              <button type="button" class="delete-btn-confirm" @click="confirmDelete">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <polyline points="20 6 9 17 4 12"/>
                </svg>
                Delete Review
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- Success Notification -->
    <Teleport to="body">
      <div class="success-notification" :class="{ active: showDeleteSuccess }">
        <div class="success-content">
          <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div class="success-text">
            <div class="success-title">Review Deleted</div>
            <div class="success-message">The review has been successfully removed.</div>
          </div>
          <button class="success-close" @click="showDeleteSuccess = false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <line x1="18" y1="6" x2="6" y2="18"/>
              <line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
          </button>
        </div>
      </div>
    </Teleport>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { reviews as reviewsApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()

interface Review {
  id: number
  user_id: number
  product_id: number
  rating: number
  comment: string
  status: 'pending' | 'approved' | 'rejected'
  created_at: string
  user?: {
    first_name: string
    last_name: string
    full_name: string
  }
  product?: {
    name: string
    image_url: string
  }
}

const reviews = ref<Review[]>([])
const isLoading = ref(false)
const filterStatus = ref('')
const showDeleteModal = ref(false)
const deletingReview = ref<Review | null>(null)
const showDeleteSuccess = ref(false)

const pagination = ref({
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 0
})

const loadReviews = async () => {
  isLoading.value = true
  try {
    const params: any = {
      page: pagination.value.current_page,
      per_page: pagination.value.per_page,
      sort_by: 'created_at',
      sort_order: 'desc'
    }

    if (filterStatus.value) {
      params.status = filterStatus.value
    }

    const response = await reviewsApi.list(params)

    if (response.data.success) {
      reviews.value = response.data.data.data
      pagination.value = {
        current_page: response.data.data.current_page,
        last_page: response.data.data.last_page,
        per_page: response.data.data.per_page,
        total: response.data.data.total
      }
    }
  } catch (err) {
    console.error('Failed to load reviews:', err)
    showError('Error', 'Failed to load reviews.')
  } finally {
    isLoading.value = false
  }
}

const changePage = (page: number) => {
  pagination.value.current_page = page
  loadReviews()
}

const formatDate = (dateString: string) => {
  if (!dateString) return ''
  return new Intl.DateTimeFormat('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  }).format(new Date(dateString))
}

const updateStatus = async (id: number, status: 'approved' | 'rejected') => {
  try {
    const response = await reviewsApi.updateStatus(id, status)
    if (response.data.success) {
      const review = reviews.value.find(r => r.id === id)
      if (review) review.status = status
      success('Success', `Review ${status}.`)
    }
  } catch (err) {
    console.error('Failed to update status:', err)
    showError('Error', 'Failed to update review status.')
  }
}

const deleteReview = (id: number) => {
  const review = reviews.value.find(r => r.id === id)
  if (review) {
    deletingReview.value = review
    showDeleteModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const confirmDelete = async () => {
  if (!deletingReview.value) return

  try {
    const response = await reviewsApi.delete(deletingReview.value.id)
    if (response.data.success) {
      reviews.value = reviews.value.filter(r => r.id !== deletingReview.value!.id)
      closeDeleteModal()
      showDeleteSuccess.value = true
      setTimeout(() => {
        showDeleteSuccess.value = false
      }, 4000)

      // Reload if page becomes empty
      if (reviews.value.length === 0 && pagination.value.current_page > 1) {
        pagination.value.current_page--
        loadReviews()
      }
    }
  } catch (err) {
    console.error('Failed to delete review:', err)
    showError('Error', 'Failed to delete review.')
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletingReview.value = null
  document.body.style.overflow = ''
}

onMounted(() => {
  loadReviews()
})
</script>

<style scoped>
.admin-reviews-page {
  --gold: #c9a050;
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
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 2rem;
}

.page-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.5rem;
  transition: color 0.3s ease;
}

.page-subtitle {
  color: #374151;
  font-size: 0.95rem;
  margin: 0;
  transition: color 0.3s ease;
}

.filter-select {
  padding: 0.875rem 1.25rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  font-size: 0.9rem;
  color: var(--dark);
  transition: all 0.3s ease;
  cursor: pointer;
}

.filter-select:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.reviews-list {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.review-card {
  background: var(--white);
  border-radius: 16px;
  padding: 1.5rem;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.review-header {
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  margin-bottom: 1rem;
}

.reviewer-info {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.reviewer-avatar {
  width: 48px;
  height: 48px;
  border-radius: 50%;
  background: var(--gold);
  color: white;
  display: flex;
  align-items: center;
  justify-content: center;
  font-weight: 700;
  font-size: 1.25rem;
}

.reviewer-name {
  font-weight: 600;
  color: var(--dark);
}

.review-date {
  font-size: 0.85rem;
  color: #6b7280;
}

.review-rating {
  display: flex;
  align-items: center;
  gap: 0.5rem;
}

.stars {
  color: #d1d5db;
  font-size: 1.25rem;
}

.stars .filled {
  color: var(--gold);
}

.rating-value {
  font-weight: 600;
  color: var(--dark);
}

.review-product {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem;
  background: #f9fafb;
  border-radius: 8px;
  margin-bottom: 1rem;
}

.product-thumb {
  width: 40px;
  height: 40px;
  object-fit: cover;
  border-radius: 6px;
}

.review-content {
  margin-bottom: 1rem;
  color: var(--dark);
  line-height: 1.6;
}

.review-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
}

.status-badge.pending {
  background: #fef3c7;
  color: #92400e;
}

.status-badge.approved {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.rejected {
  background: #fee2e2;
  color: #991b1b;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.btn-small {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 6px;
  font-size: 0.85rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-small.success {
  background: #d1fae5;
  color: #065f46;
}

.btn-small.success:hover {
  background: #bbf7d0;
}

.btn-small.danger {
  background: #fee2e2;
  color: #991b1b;
}

.btn-small.danger:hover {
  background: #fecaca;
}

.btn-small:not(.success):not(.danger) {
  background: #f3f4f6;
  color: var(--dark);
}

.btn-small:not(.success):not(.danger):hover {
  background: #e5e7eb;
}

/* Loading & Empty States */
.loading-state {
  text-align: center;
  padding: 4rem;
  color: var(--gray);
}

.spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #e5e7eb;
  border-top-color: var(--gold);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

.empty-state {
  text-align: center;
  padding: 4rem;
  color: var(--gray);
  background: var(--white);
  border-radius: 16px;
}

/* Pagination */
.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #e5e7eb;
  background: var(--white);
  border-radius: 6px;
  cursor: pointer;
  color: var(--dark);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--gold);
  color: var(--gold);
}

.page-info {
  color: var(--gray);
  font-size: 0.9rem;
}

/* Delete Modal Styles */
.modal-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(8px);
  z-index: 9999;
  display: flex;
  align-items: center;
  justify-content: center;
  opacity: 0;
  visibility: hidden;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  padding: 2rem;
}

.modal-overlay.active {
  opacity: 1;
  visibility: visible;
}

.delete-modal {
  max-width: 480px;
  width: 100%;
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25), 0 0 0 1px rgba(220, 53, 69, 0.1);
  transform: scale(0.9) translateY(20px);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.modal-overlay.active .delete-modal {
  transform: scale(1) translateY(0);
}

.delete-modal-content {
  padding: 2.5rem 2rem;
  text-align: center;
}

.delete-icon-wrapper {
  margin-bottom: 1.5rem;
  display: flex;
  justify-content: center;
}

.delete-icon-circle {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border: 3px solid #fca5a5;
  display: flex;
  align-items: center;
  justify-content: center;
  animation: pulse-delete 2s ease-in-out infinite;
  position: relative;
}

.delete-icon-circle::before {
  content: '';
  position: absolute;
  inset: -4px;
  border-radius: 50%;
  background: linear-gradient(135deg, rgba(220, 53, 69, 0.2) 0%, rgba(239, 68, 68, 0.1) 100%);
  z-index: -1;
  animation: ripple 2s ease-out infinite;
}

@keyframes pulse-delete {
  0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4); }
  50% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(220, 53, 69, 0); }
}

@keyframes ripple {
  0% { transform: scale(1); opacity: 1; }
  100% { transform: scale(1.3); opacity: 0; }
}

.delete-icon-circle svg {
  width: 36px;
  height: 36px;
  color: #dc2626;
}

.delete-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: #1a1d29;
  margin: 0 0 1rem;
}

.delete-message {
  font-size: 1rem;
  color: #4b5563;
  margin: 0 0 0.5rem;
  line-height: 1.6;
}

.delete-item-name {
  color: #dc2626;
  font-weight: 700;
  font-size: 1.1rem;
}

.delete-warning {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0 0 2rem;
  padding: 1rem;
  background: #fef2f2;
  border-left: 3px solid #dc2626;
  border-radius: 8px;
  text-align: left;
}

.delete-actions {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.delete-btn-cancel {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: 2px solid #d1d5db;
  border-radius: 12px;
  background: #ffffff;
  color: #374151;
  font-weight: 600;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.delete-btn-cancel svg {
  width: 18px;
  height: 18px;
}

.delete-btn-cancel:hover {
  border-color: #9ca3af;
  background: #f9fafb;
  color: #1f2937;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.delete-btn-confirm {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: none;
  border-radius: 12px;
  background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
  color: #ffffff;
  font-weight: 700;
  font-size: 0.95rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
  position: relative;
  overflow: hidden;
}

.delete-btn-confirm::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.delete-btn-confirm:hover::before {
  opacity: 1;
}

.delete-btn-confirm svg {
  width: 18px;
  height: 18px;
  position: relative;
  z-index: 1;
}

.delete-btn-confirm:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
  background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.delete-btn-confirm:active {
  transform: translateY(0);
}

.success-notification {
  position: fixed;
  top: 2rem;
  right: 2rem;
  z-index: 10000;
  opacity: 0;
  visibility: hidden;
  transform: translateX(400px);
  transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.success-notification.active {
  opacity: 1;
  visibility: visible;
  transform: translateX(0);
}

.success-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 1.25rem 1.5rem;
  background: linear-gradient(135deg, #ffffff 0%, #f9fafb 100%);
  border-radius: 16px;
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(16, 185, 129, 0.1);
  border-left: 4px solid #10b981;
  min-width: 320px;
  max-width: 420px;
  animation: slideInRight 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideInRight {
  from { transform: translateX(400px); opacity: 0; }
  to { transform: translateX(0); opacity: 1; }
}

.success-icon {
  width: 48px;
  height: 48px;
  border-radius: 12px;
  background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  animation: successBounce 0.6s ease;
}

@keyframes successBounce {
  0%, 100% { transform: scale(1); }
  50% { transform: scale(1.15); }
}

.success-icon svg {
  width: 24px;
  height: 24px;
  color: #10b981;
}

.success-text {
  flex: 1;
  min-width: 0;
}

.success-title {
  font-weight: 700;
  font-size: 1rem;
  color: #1a1d29;
  margin: 0 0 0.25rem;
}

.success-message {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
}

.success-close {
  width: 32px;
  height: 32px;
  border-radius: 8px;
  border: none;
  background: transparent;
  color: #9ca3af;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  flex-shrink: 0;
}

.success-close svg {
  width: 18px;
  height: 18px;
}

.success-close:hover {
  background: #f3f4f6;
  color: #374151;
}

@media (max-width: 768px) {
  .delete-modal-content {
    padding: 2rem 1.5rem;
  }

  .delete-actions {
    flex-direction: column;
  }

  .delete-btn-cancel,
  .delete-btn-confirm {
    width: 100%;
    justify-content: center;
  }

  .success-notification {
    top: 1rem;
    right: 1rem;
    left: 1rem;
  }

  .success-content {
    min-width: auto;
    max-width: none;
  }
}
</style>
