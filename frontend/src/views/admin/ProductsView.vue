<template>
  <div class="admin-products-page">
    <!-- Page Header -->
    <div class="page-header">
      <div>
        <h1 class="page-title">Product Management</h1>
        <p class="page-subtitle">Manage your product catalog, inventory, and pricing.</p>
      </div>
      <div class="header-actions">
        <button class="btn-secondary" @click="exportProducts">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/>
            <line x1="12" y1="15" x2="12" y2="3"/>
          </svg>
          Export CSV
        </button>
        <button class="btn-primary" @click="openAddModal">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Add Product
        </button>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="filters-bar">
      <div class="search-box">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <circle cx="11" cy="11" r="8"/>
          <path d="m21 21-4.35-4.35"/>
        </svg>
        <input
          type="text"
          v-model="searchQuery"
          placeholder="Search products..."
          class="search-input"
        >
      </div>
      <select v-model="selectedCategory" class="filter-select">
        <option value="">All Categories</option>
        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
      </select>
      <select v-model="selectedStatus" class="filter-select">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="hidden">Hidden</option>
        <option value="out_of_stock">Out of Stock</option>
      </select>
      <button class="btn-clear" @click="clearFilters">Clear Filters</button>
    </div>

    <!-- Products Table -->
    <div class="table-card">
      <table class="data-table" v-if="paginatedProducts.length > 0">
        <thead>
          <tr>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Status</th>
            <th>Featured</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="product in paginatedProducts" :key="product.id">
            <td>
              <div class="product-cell">
                <img :src="product.image" :alt="product.name" class="product-thumb">
                <div>
                  <div class="product-name">{{ product.name }}</div>
                  <div class="product-id">ID: {{ product.id }}</div>
                </div>
              </div>
            </td>
            <td>{{ product.category || 'Uncategorized' }}</td>
            <td>
              <div class="price-cell">
                <span class="price-main">₱{{ formatPrice(product.price) }}</span>
                <span v-if="product.salePrice" class="price-sale">₱{{ formatPrice(product.salePrice) }}</span>
              </div>
            </td>
            <td>
              <span :class="{ 'low-stock': product.stock <= 5 }">{{ product.stock }}</span>
            </td>
            <td>
              <span class="status-badge" :class="product.status.toLowerCase()">
                {{ product.status }}
              </span>
            </td>
            <td>
              <label class="toggle-switch">
                <input type="checkbox" v-model="product.isFeatured" @change="updateFeatured(product.id, product.isFeatured)">
                <span class="toggle-slider"></span>
              </label>
            </td>
            <td>
              <div class="action-buttons">
                <button class="action-btn view" @click="viewProduct(product.id)" title="View Details">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                  </svg>
                </button>
                <button class="action-btn edit" @click="editProduct(product.id)" title="Edit Product">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                  </svg>
                </button>
                <button class="action-btn delete" @click="deleteProduct(product.id)" title="Delete Product">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                  </svg>
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
      <div v-else-if="!isLoading" class="empty-state">
      <div v-if="isLoading" class="loading-state">
        <div class="spinner"></div>
        <p>Loading products...</p>
      </div>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M9 12h6m-3-3v6m-9 1V7a2 2 0 0 1 2-2h6l2 2h6a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
        </svg>
        <h3>No products found</h3>
        <p v-if="searchQuery || selectedCategory || selectedStatus">
          Try adjusting your filters to see more results.
        </p>
        <p v-else>
          Get started by adding your first product.
        </p>
        <button class="btn-primary" @click="openAddModal" v-if="!searchQuery && !selectedCategory && !selectedStatus">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <path d="M12 8v8M8 12h8"/>
          </svg>
          Add Product
        </button>
      </div>
    </div>

    <!-- Pagination -->
    <div class="pagination" v-if="totalPages > 1">
      <button
        class="page-btn"
        :disabled="currentPage === 1"
        @click="goToPreviousPage"
        title="Previous Page"
      >
        Previous
      </button>
      <span class="page-info">Page {{ currentPage }} of {{ totalPages || 1 }}</span>
      <button
        class="page-btn"
        :disabled="currentPage >= totalPages"
        @click="goToNextPage"
        title="Next Page"
      >
        Next
      </button>
    </div>
    <div v-else-if="filteredProducts.length === 0" class="pagination">
      <span class="page-info">No products found</span>
    </div>

    <!-- ═══════════════════════════════════════════════════
         ADD PRODUCT MODAL
         ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showAddModal }" @click.self="closeAddModal">
        <div class="modal-container add-modal">
          <!-- Modal Header -->
          <div class="add-modal-header">
            <div class="add-header-left">
              <div class="add-icon-wrapper">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                  <line x1="3" y1="6" x2="21" y2="6"/>
                  <path d="M16 10a4 4 0 0 1-8 0"/>
                </svg>
              </div>
              <div>
                <h2 class="add-modal-title">Add New Product</h2>
                <p class="add-modal-desc">Create a new product listing for your catalog</p>
              </div>
            </div>
            <button class="add-close-btn" @click="closeAddModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <!-- Modal Body -->
          <div class="add-modal-body">
            <form @submit.prevent="saveNewProduct" class="add-form">
              <!-- Row 1: Product Info -->
              <div class="add-form-row">
                <div class="add-form-card">
                  <div class="card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                      <path d="M9 9h6v6H9z"/>
                    </svg>
                    <span>Product Information</span>
                  </div>
                  <div class="card-body">
                    <div class="input-group">
                      <label>Product Name <span class="req">*</span></label>
                      <input type="text" v-model="addForm.name" placeholder="Enter product name" required>
                    </div>
                    <div class="input-row">
                      <div class="input-group">
                        <label>Category <span class="req">*</span></label>
                        <select v-model="addForm.category" required>
                          <option value="">Select category</option>
                          <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                      </div>
                      <div class="input-group">
                        <label>SKU Code</label>
                        <input type="text" v-model="addForm.sku" placeholder="e.g., SOFA-001">
                      </div>
                    </div>
                    <div class="input-group">
                      <label>Description</label>
                      <textarea v-model="addForm.description" rows="3" placeholder="Enter product description..."></textarea>
                    </div>
                  </div>
                </div>

                <div class="add-form-card">
                  <div class="card-header pricing">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <line x1="12" y1="1" x2="12" y2="23"/>
                      <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                    <span>Pricing & Stock</span>
                  </div>
                  <div class="card-body">
                    <div class="input-row">
                      <div class="input-group">
                        <label>Regular Price <span class="req">*</span></label>
                        <div class="price-input">
                          <span class="currency">₱</span>
                          <input type="number" v-model.number="addForm.price" placeholder="0.00" min="0" step="0.01" required>
                        </div>
                      </div>
                      <div class="input-group">
                        <label>Sale Price</label>
                        <div class="price-input">
                          <span class="currency">₱</span>
                          <input type="number" v-model.number="addForm.salePrice" placeholder="0.00" min="0" step="0.01">
                        </div>
                      </div>
                    </div>
                    <div class="input-row">
                      <div class="input-group">
                        <label>Stock Quantity <span class="req">*</span></label>
                        <input type="number" v-model.number="addForm.stock" placeholder="0" min="0" required>
                      </div>
                      <div class="input-group">
                        <label>Low Stock Alert</label>
                        <input type="number" v-model.number="addForm.lowStockAlert" placeholder="5" min="0">
                      </div>
                    </div>
                    <div class="input-group">
                      <label>Status</label>
                      <select v-model="addForm.status">
                        <option value="Active">Active - Visible to customers</option>
                        <option value="Hidden">Hidden - Not visible</option>
                        <option value="Draft">Draft - Work in progress</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Row 2: Specifications & Settings -->
              <div class="add-form-row">
                <div class="add-form-card">
                  <div class="card-header specs">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                      <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <span>Specifications</span>
                  </div>
                  <div class="card-body">
                    <div class="input-row">
                      <div class="input-group">
                        <label>Dimensions</label>
                        <input type="text" v-model="addForm.dimensions" placeholder="L x W x H (cm)">
                      </div>
                      <div class="input-group">
                        <label>Weight</label>
                        <input type="text" v-model="addForm.weight" placeholder="e.g., 45 kg">
                      </div>
                    </div>
                    <div class="input-row">
                      <div class="input-group">
                        <label>Material</label>
                        <input type="text" v-model="addForm.material" placeholder="e.g., Velvet, Wood">
                      </div>
                      <div class="input-group">
                        <label>Color</label>
                        <input type="text" v-model="addForm.color" placeholder="e.g., Navy Blue">
                      </div>
                    </div>
                  </div>
                </div>

                <div class="add-form-card">
                  <div class="card-header media">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                      <rect x="3" y="3" width="18" height="18" rx="2"/>
                      <circle cx="8.5" cy="8.5" r="1.5"/>
                      <polyline points="21 15 16 10 5 21"/>
                    </svg>
                    <span>Media & Options</span>
                  </div>
                  <div class="card-body">
                    <div class="input-group">
                      <label>Product Images</label>
                      <div class="image-upload-section">
                        <div class="image-upload-area" @click="triggerImagePicker">
                          <input
                            ref="imageFileInput"
                            type="file"
                            multiple
                            accept="image/*"
                            @change="handleImageFileSelect"
                            style="display: none;"
                          >
                          <div v-if="addForm.images.length === 0" class="upload-placeholder">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <rect x="3" y="3" width="18" height="18" rx="2"/>
                              <circle cx="8.5" cy="8.5" r="1.5"/>
                              <polyline points="21 15 16 10 5 21"/>
                            </svg>
                            <p>Click to upload images</p>
                            <span>or drag and drop</span>
                          </div>
                          <div v-else class="image-preview-grid">
                            <div v-for="(img, index) in addForm.images" :key="index" class="image-preview-item" @click.stop="triggerImagePicker">
                              <img :src="img.preview || img.url" :alt="`Image ${index + 1}`">
                              <button type="button" class="remove-image-btn" @click.stop="removeImage(index)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                  <line x1="18" y1="6" x2="6" y2="18"/>
                                  <line x1="6" y1="6" x2="18" y2="18"/>
                                </svg>
                              </button>
                              <label class="primary-checkbox">
                                <input type="radio" :name="'primary-image'" :value="index" v-model="addForm.primaryImageIndex">
                                <span>Primary</span>
                              </label>
                            </div>
                            <div class="add-more-images" @click.stop="triggerImagePicker">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/>
                                <path d="M12 8v8M8 12h8"/>
                              </svg>
                              <span>Add More</span>
                            </div>
                          </div>
                        </div>
                        <p class="upload-hint" v-if="!isUploadingImages">Upload multiple images. First image will be set as primary by default.</p>
                        <p class="upload-hint" v-else style="color: var(--gold);">Uploading images...</p>
                      </div>
                    </div>
                    <div class="toggle-options">
                      <label class="toggle-option">
                        <input type="checkbox" v-model="addForm.isFeatured">
                        <div class="toggle-content">
                          <span class="toggle-title">Featured Product</span>
                          <span class="toggle-desc">Show on homepage</span>
                        </div>
                      </label>
                      <label class="toggle-option">
                        <input type="checkbox" v-model="addForm.isNew">
                        <div class="toggle-content">
                          <span class="toggle-title">New Arrival</span>
                          <span class="toggle-desc">Display "New" badge</span>
                        </div>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </form>
          </div>

          <!-- Modal Footer -->
          <div class="add-modal-footer">
            <button type="button" class="add-btn-cancel" @click="closeAddModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
              Cancel
            </button>
            <button type="button" class="add-btn-save" @click="saveNewProduct">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              Add Product
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════
         EDIT PRODUCT MODAL
         ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showEditModal }" @click.self="closeEditModal">
        <div class="modal-container modal-large">
          <div class="modal-header">
            <div class="modal-header-content">
              <div class="modal-icon edit">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                  <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                </svg>
              </div>
              <div>
                <h2 class="modal-title">Edit Product</h2>
                <p class="modal-subtitle">Update product information</p>
              </div>
            </div>
            <button class="modal-close" @click="closeEditModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="modal-body">
            <form @submit.prevent="saveEditProduct" class="product-form">
              <!-- Basic Information -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="3" width="18" height="18" rx="2"/>
                    <path d="M9 9h6v6H9z"/>
                  </svg>
                  Basic Information
                </h3>
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label class="form-label">Product Name <span class="required">*</span></label>
                    <input type="text" v-model="editForm.name" class="form-input" placeholder="Enter product name" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Category <span class="required">*</span></label>
                    <select v-model="editForm.category" class="form-select" required>
                      <option value="">Select category</option>
                      <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label class="form-label">SKU / Product Code</label>
                    <input type="text" v-model="editForm.sku" class="form-input" placeholder="e.g., SOFA-001">
                  </div>
                </div>
              </div>

              <!-- Pricing -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="12" y1="1" x2="12" y2="23"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                  </svg>
                  Pricing & Inventory
                </h3>
                <div class="form-grid">
                  <div class="form-group">
                    <label class="form-label">Regular Price (₱) <span class="required">*</span></label>
                    <input type="number" v-model.number="editForm.price" class="form-input" placeholder="0.00" min="0" step="0.01" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Sale Price (₱)</label>
                    <input type="number" v-model.number="editForm.salePrice" class="form-input" placeholder="0.00" min="0" step="0.01">
                  </div>
                  <div class="form-group">
                    <label class="form-label">Stock Quantity <span class="required">*</span></label>
                    <input type="number" v-model.number="editForm.stock" class="form-input" placeholder="0" min="0" required>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Status</label>
                    <select v-model="editForm.status" class="form-select">
                      <option value="Active">Active</option>
                      <option value="Hidden">Hidden</option>
                      <option value="Draft">Draft</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Product Details -->
              <div class="form-section">
                <h3 class="section-title">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                  </svg>
                  Product Details
                </h3>
                <div class="form-grid">
                  <div class="form-group full-width">
                    <label class="form-label">Description</label>
                    <textarea v-model="editForm.description" class="form-textarea" rows="4" placeholder="Enter product description..."></textarea>
                  </div>
                  <div class="form-group full-width">
                    <label class="form-label">Product Images</label>
                    <div class="image-upload-section">
                      <div class="image-upload-area" @click="triggerImagePicker">
                        <input
                          ref="editImageFileInput"
                          type="file"
                          multiple
                          accept="image/*"
                          @change="handleImageFileSelect"
                          style="display: none;"
                        >
                        <div v-if="editForm.images.length === 0" class="upload-placeholder">
                          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="3" width="18" height="18" rx="2"/>
                            <circle cx="8.5" cy="8.5" r="1.5"/>
                            <polyline points="21 15 16 10 5 21"/>
                          </svg>
                          <p>Click to upload images</p>
                        </div>
                        <div v-else class="image-preview-grid">
                          <div v-for="(img, index) in editForm.images" :key="index" class="image-preview-item" @click.stop="triggerImagePicker">
                            <img :src="img.preview || img.url" :alt="`Image ${index + 1}`">
                            <button type="button" class="remove-image-btn" @click.stop="removeImage(index)">
                              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="18" y1="6" x2="6" y2="18"/>
                                <line x1="6" y1="6" x2="18" y2="18"/>
                              </svg>
                            </button>
                            <label class="primary-checkbox">
                              <input type="radio" :name="'edit-primary-image'" :value="index" v-model="editForm.primaryImageIndex">
                              <span>Primary</span>
                            </label>
                          </div>
                          <div class="add-more-images" @click.stop="triggerImagePicker">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                              <circle cx="12" cy="12" r="10"/>
                              <path d="M12 8v8M8 12h8"/>
                            </svg>
                            <span>Add More</span>
                          </div>
                        </div>
                      </div>
                      <p class="upload-hint" v-if="!isUploadingImages">Upload multiple images. First image will be set as primary by default.</p>
                      <p class="upload-hint" v-else style="color: var(--gold);">Uploading images...</p>
                    </div>
                  </div>
                  <div class="form-group">
                    <label class="form-label">Material</label>
                    <input type="text" v-model="editForm.material" class="form-input" placeholder="e.g., Velvet, Wood">
                  </div>
                </div>
                  <div class="form-toggles">
                    <label class="toggle-label">
                      <input type="checkbox" v-model="editForm.isFeatured">
                      <span class="toggle-text">Featured Product</span>
                    </label>
                    <label class="toggle-label">
                      <input type="checkbox" v-model="editForm.isNew">
                      <span class="toggle-text">New Arrival</span>
                    </label>
                  </div>
              </div>
            </form>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeEditModal">Cancel</button>
            <button type="button" class="btn-save" @click="saveEditProduct">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
              Save Changes
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════
         VIEW PRODUCT MODAL
         ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div class="modal-overlay" :class="{ active: showViewModal }" @click.self="closeViewModal">
        <div class="modal-container modal-view">
          <div class="modal-header">
            <div class="modal-header-content">
              <div class="modal-icon view">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                  <circle cx="12" cy="12" r="3"/>
                </svg>
              </div>
              <div>
                <h2 class="modal-title">Product Details</h2>
                <p class="modal-subtitle">Complete information about this product</p>
              </div>
            </div>
            <button class="modal-close" @click="closeViewModal">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="18" y1="6" x2="6" y2="18"/>
                <line x1="6" y1="6" x2="18" y2="18"/>
              </svg>
            </button>
          </div>

          <div class="modal-body view-body" v-if="viewingProduct">
            <div class="view-layout">
              <div class="view-image-section">
                <div class="view-image-container">
                  <img :src="viewingProduct.image" :alt="viewingProduct.name" class="view-product-image">
                  <span v-if="viewingProduct.isFeatured" class="view-badge featured">Featured</span>
                  <span v-if="viewingProduct.stock <= 5" class="view-badge low-stock">Low Stock</span>
                </div>
              </div>

              <div class="view-details-section">
                <div class="view-header-info">
                  <span class="view-category">{{ viewingProduct.category }}</span>
                  <h3 class="view-product-name">{{ viewingProduct.name }}</h3>
                  <p class="view-product-id">Product ID: #{{ viewingProduct.id }}</p>
                </div>

                <div class="view-price-section">
                  <div class="view-price-main">₱{{ formatPrice(viewingProduct.price) }}</div>
                  <div v-if="viewingProduct.salePrice" class="view-price-sale">
                    <span class="sale-label">Sale Price:</span>
                    <span class="sale-amount">₱{{ formatPrice(viewingProduct.salePrice) }}</span>
                  </div>
                </div>

                <div class="view-info-grid">
                  <div class="view-info-card">
                    <div class="info-icon stock">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <path d="M16 10a4 4 0 0 1-8 0"/>
                      </svg>
                    </div>
                    <div class="info-content">
                      <span class="info-label">Stock</span>
                      <span class="info-value" :class="{ 'text-danger': viewingProduct.stock <= 5 }">{{ viewingProduct.stock }} units</span>
                    </div>
                  </div>

                  <div class="view-info-card">
                    <div class="info-icon status">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                        <polyline points="22 4 12 14.01 9 11.01"/>
                      </svg>
                    </div>
                    <div class="info-content">
                      <span class="info-label">Status</span>
                      <span class="info-value status-badge" :class="viewingProduct.status.toLowerCase()">{{ viewingProduct.status }}</span>
                    </div>
                  </div>

                  <div class="view-info-card">
                    <div class="info-icon featured">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
                      </svg>
                    </div>
                    <div class="info-content">
                      <span class="info-label">Featured</span>
                      <span class="info-value">{{ viewingProduct.isFeatured ? 'Yes' : 'No' }}</span>
                    </div>
                  </div>

                  <div class="view-info-card">
                    <div class="info-icon category">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 7h16M4 12h16M4 17h16"/>
                      </svg>
                    </div>
                    <div class="info-content">
                      <span class="info-label">Category</span>
                      <span class="info-value">{{ viewingProduct.category }}</span>
                    </div>
                  </div>
                </div>

                <div class="view-description" v-if="viewingProduct.description">
                  <h4>Description</h4>
                  <p>{{ viewingProduct.description }}</p>
                </div>
              </div>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" class="btn-cancel" @click="closeViewModal">Close</button>
            <button type="button" class="btn-edit" @click="openEditFromView">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
              </svg>
              Edit Product
            </button>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════
         DELETE CONFIRMATION MODAL
         ═══════════════════════════════════════════════════ -->
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

            <h2 class="delete-title">Delete Product</h2>
            <p class="delete-message">
              Are you sure you want to delete
              <strong class="delete-product-name">{{ deletingProduct?.name }}</strong>?
            </p>
            <p class="delete-warning">
              This action cannot be undone. All product data will be permanently removed.
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
                Delete Product
              </button>
            </div>
          </div>
        </div>
      </div>
    </Teleport>

    <!-- ═══════════════════════════════════════════════════
         DELETE SUCCESS NOTIFICATION
         ═══════════════════════════════════════════════════ -->
    <Teleport to="body">
      <div class="success-notification" :class="{ active: showDeleteSuccess }">
        <div class="success-content">
          <div class="success-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <polyline points="20 6 9 17 4 12"/>
            </svg>
          </div>
          <div class="success-text">
            <div class="success-title">Product Deleted</div>
            <div class="success-message">The product has been successfully removed.</div>
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
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { products as productsApi, categories as categoriesApi, upload as uploadApi } from '@/services/adminApi'
import { useNotification } from '@/composables/useNotification'

const { success, error: showError } = useNotification()
import { useRealtimeProducts } from '@/composables/useRealtimeProducts'

// Product Type Definition
interface Product {
  id: number
  name: string
  category: string
  categoryId?: number
  price: number
  salePrice: number | null
  stock: number
  status: string
  isFeatured: boolean
  image: string
  images?: Array<{ id: number; image_url: string; image_path: string; is_primary: boolean }>
  description?: string
  sku?: string
  isNew?: boolean
  attributes?: {
    dimensions?: string
    weight?: string
    material?: string
    color?: string
  }
}

interface Category {
  id: number
  name: string
  slug?: string
  description?: string
  parent_id?: number | null
  is_visible?: boolean
}

interface ApiProduct {
  id: number
  name: string
  category?: {
    id: number
    name: string
  }
  category_id?: number
  price: string | number
  sale_price?: string | number | null
  stock_quantity?: number
  status?: string
  is_featured?: boolean
  primary_image?: {
    image_path: string
  }
  images?: Array<{
    image_path: string
  }>
  description?: string
  sku?: string
  attributes?: {
    dimensions?: string
    weight?: string
    material?: string
    color?: string
  }
}

interface ApiError {
  response?: {
    data?: {
      message?: string
    }
  }
}

// ═══════════════════════════════════════════════════
// STATE
// ═══════════════════════════════════════════════════
const searchQuery = ref('')
const selectedCategory = ref('')
const selectedStatus = ref('')
const currentPage = ref(1)
const itemsPerPage = 20

// Modal States
const showAddModal = ref(false)
const showEditModal = ref(false)
const showViewModal = ref(false)
const showDeleteModal = ref(false)
const viewingProduct = ref<Product | null>(null)
const editingProductId = ref<number | null>(null)
const deletingProduct = ref<Product | null>(null)
const showDeleteSuccess = ref(false)

// File Input Ref
const imageFileInput = ref<HTMLInputElement | null>(null)
const editImageFileInput = ref<HTMLInputElement | null>(null)
const isUploadingImages = ref(false)

// Add Product Form
const addForm = ref({
  name: '',
  category: '',
  sku: '',
  price: null as number | null,
  salePrice: null as number | null,
  stock: null as number | null,
  lowStockAlert: 5,
  description: '',
  dimensions: '',
  weight: '',
  material: '',
  color: '',
  images: [] as Array<{ file?: File; preview?: string; url?: string; path?: string }>,
  primaryImageIndex: 0 as number,
  status: 'Active',
  isFeatured: false,
  isNew: true
})

// Edit Product Form
const editForm = ref({
  name: '',
  category: '',
  sku: '',
  price: null as number | null,
  salePrice: null as number | null,
  stock: null as number | null,
  description: '',
  dimensions: '',
  weight: '',
  material: '',
  color: '',
  image: '',
  images: [] as Array<{ id?: number; file?: File; preview?: string; url?: string; path?: string; is_primary?: boolean }>,
  primaryImageIndex: 0 as number,
  status: 'Active',
  isFeatured: false,
  isNew: false
})

const categories = ref<Category[]>([])
const products = ref<Product[]>([])
const isLoading = ref(false)
const totalProductsCount = ref(0)
const { startListening, stopListening } = useRealtimeProducts()

// Load categories from API
const loadCategories = async () => {
  try {
    const response = await categoriesApi.list({ is_visible: true })
    if (response.data.success) {
      categories.value = response.data.data || []
    }
  } catch (error) {
    console.error('Failed to load categories:', error)
  }
}

// Load products from API
const loadProducts = async () => {
  isLoading.value = true
  try {
    const params: Record<string, string | number | boolean | undefined> = {
      page: currentPage.value,
      per_page: itemsPerPage,
    }

    if (searchQuery.value) {
      params.search = searchQuery.value
    }

    if (selectedCategory.value) {
      params.category_id = parseInt(selectedCategory.value)
    }

    if (selectedStatus.value) {
      if (selectedStatus.value === 'out_of_stock') {
        params.stock_status = 'out_of_stock'
      } else {
        params.status = selectedStatus.value
      }
    }

    const response = await productsApi.list(params)

    if (response.data.success) {
      const data = response.data.data
      products.value = (data.data || []).map((p: ApiProduct) => ({
        id: p.id,
        name: p.name,
        category: p.category?.name || 'Uncategorized',
        categoryId: p.category_id,
        price: Number(p.price) || 0,
        salePrice: p.sale_price ? Number(p.sale_price) : null,
        stock: p.stock_quantity || 0,
        status: p.status === 'active' ? 'Active' : 'Hidden',
        isFeatured: p.is_featured || false,
        image: p.primary_image?.image_url || p.images?.[0]?.image_url || '/images/products/placeholder.png',
        images: (p.images || []).map(img => ({
          id: img.id,
          image_url: img.image_url,
          image_path: img.image_path,
          is_primary: img.is_primary
        })),
        description: p.description || '',
        sku: p.sku || '',
        isNew: p.is_new || false,
        attributes: p.attributes || {},
      }))
      totalProductsCount.value = data.total || 0
    }
  } catch (error) {
    console.error('Failed to load products:', error)
  } finally {
    isLoading.value = false
  }
}

// ═══════════════════════════════════════════════════
// COMPUTED
// ═══════════════════════════════════════════════════
// Products are already filtered by API, so we use them directly
const filteredProducts = computed(() => products.value)

const totalPages = computed(() => Math.ceil(totalProductsCount.value / itemsPerPage))

const paginatedProducts = computed(() => products.value)

// Watch for filter changes and reload products
watch([searchQuery, selectedCategory, selectedStatus], () => {
  currentPage.value = 1
  loadProducts()
})

watch(currentPage, () => {
  loadProducts()
})

// ═══════════════════════════════════════════════════
// METHODS
// ═══════════════════════════════════════════════════
const formatPrice = (price: number) => {
  return price.toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = ''
  selectedStatus.value = ''
  currentPage.value = 1
}

// Modal Functions
const openAddModal = () => {
  // Reset form
  addForm.value = {
    name: '',
    category: '',
    sku: '',
    price: null,
    salePrice: null,
    stock: null,
    lowStockAlert: 5,
    description: '',
    dimensions: '',
    weight: '',
    material: '',
    color: '',
    images: [],
    primaryImageIndex: 0,
    status: 'Active',
    isFeatured: false,
    isNew: true
  }
  // Reset file input
  if (imageFileInput.value) {
    imageFileInput.value.value = ''
  }
  showAddModal.value = true
  document.body.style.overflow = 'hidden'
}

const closeAddModal = () => {
  showAddModal.value = false
  document.body.style.overflow = ''
}

const saveNewProduct = async () => {
  if (!addForm.value.name || !addForm.value.category || !addForm.value.price || addForm.value.stock === null) {
    showError('Validation Error', 'Please fill in all required fields.')
    return
  }

  if (addForm.value.images.length === 0) {
    showError('Validation Error', 'Please upload at least one product image.')
    return
  }

  try {
    // Prepare images array for API
    const primaryIndex = addForm.value.primaryImageIndex ?? 0
    const imagesData = addForm.value.images.map((img, index) => ({
      image_path: img.path || img.url || '',
      is_primary: index === primaryIndex,
    }))

    const productData = {
      name: addForm.value.name,
      category_id: typeof addForm.value.category === 'string' ? parseInt(addForm.value.category) : addForm.value.category,
      sku: addForm.value.sku || undefined,
      price: addForm.value.price,
      sale_price: addForm.value.salePrice || null,
      stock_quantity: addForm.value.stock,
      low_stock_threshold: addForm.value.lowStockAlert || 5,
      description: addForm.value.description || '',
      status: addForm.value.status.toLowerCase(),
      is_featured: addForm.value.isFeatured,
      is_new: addForm.value.isNew,
      attributes: {
        dimensions: addForm.value.dimensions || '',
        weight: addForm.value.weight || '',
        material: addForm.value.material || '',
        color: addForm.value.color || '',
      },
      images: imagesData,
    }

    const response = await productsApi.create(productData)

    if (response.data.success) {
      success('Product Created', `Product "${addForm.value.name}" has been created successfully.`)

      // Clean up preview URLs
      addForm.value.images.forEach(img => {
        if (img.preview) {
          URL.revokeObjectURL(img.preview)
        }
      })

      closeAddModal()
      await loadProducts()
    } else {
      throw new Error(response.data.message || 'Failed to create product')
    }
  } catch (error: unknown) {
    console.error('Failed to create product:', error)
    const apiError = error as ApiError
    showError(
      'Failed to Create Product',
      apiError.response?.data?.message || 'Failed to create product. Please try again.'
    )
  }
}

const viewProduct = (id: number) => {
  const product = products.value.find(p => p.id === id)
  if (product) {
    viewingProduct.value = { ...product }
    showViewModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const closeViewModal = () => {
  showViewModal.value = false
  viewingProduct.value = null
  document.body.style.overflow = ''
}

const originalFormData = ref<Record<string, any>>({})

const editProduct = (id: number) => {
  const product = products.value.find(p => p.id === id)
  if (product) {
    editingProductId.value = id
    editForm.value = {
      name: product.name,
      category: product.categoryId?.toString() || '',
      sku: product.sku || '',
      price: product.price,
      salePrice: product.salePrice,
      stock: product.stock,
      description: product.description || '',
      material: product.attributes?.material || '',
      dimensions: product.attributes?.dimensions || '',
      weight: product.attributes?.weight || '',
      color: product.attributes?.color || '',
      image: product.image,
      images: (product.images || []).map((img) => ({
        id: img.id,
        url: img.image_url,
        path: img.image_path,
        is_primary: img.is_primary,
        preview: img.image_url
      })),
      primaryImageIndex: (product.images || []).findIndex(img => img.is_primary) >= 0
        ? (product.images || []).findIndex(img => img.is_primary)
        : 0,
      status: product.status,
      isFeatured: product.isFeatured,
      isNew: product.isNew || false
    }

    // Store a copy of original data to track changes
    originalFormData.value = JSON.parse(JSON.stringify(editForm.value))

    showEditModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const closeEditModal = () => {
  showEditModal.value = false
  editingProductId.value = null
  document.body.style.overflow = ''
}

const saveEditProduct = async () => {
  if (!editForm.value.name || !editForm.value.category || !editForm.value.price || editForm.value.stock === null) {
    alert('Please fill in all required fields')
    return
  }

  if (!editingProductId.value) return

  try {
    const productData: Record<string, any> = {}
    const original = originalFormData.value

    // Helper to check if a value has changed
    const hasChanged = (key: string, newValue: any) => {
      return JSON.stringify(newValue) !== JSON.stringify(original[key])
    }

    // Only include fields that have changed
    if (hasChanged('name', editForm.value.name)) productData.name = editForm.value.name

    const categoryId = typeof editForm.value.category === 'string' ? parseInt(editForm.value.category) : editForm.value.category
    if (hasChanged('category', editForm.value.category)) productData.category_id = categoryId

    if (hasChanged('sku', editForm.value.sku)) productData.sku = editForm.value.sku || null
    if (hasChanged('price', editForm.value.price)) productData.price = editForm.value.price
    if (hasChanged('salePrice', editForm.value.salePrice)) productData.sale_price = editForm.value.salePrice || null
    if (hasChanged('stock', editForm.value.stock)) productData.stock_quantity = editForm.value.stock
    if (hasChanged('description', editForm.value.description)) productData.description = editForm.value.description || ''

    const status = editForm.value.status.toLowerCase()
    if (hasChanged('status', editForm.value.status)) productData.status = status

    if (hasChanged('isFeatured', editForm.value.isFeatured)) productData.is_featured = editForm.value.isFeatured
    if (hasChanged('isNew', editForm.value.isNew)) productData.is_new = editForm.value.isNew

    // Attributes change detection
    const currentAttributes = {
      dimensions: editForm.value.dimensions || '',
      weight: editForm.value.weight || '',
      material: editForm.value.material || '',
      color: editForm.value.color || '',
    }
    const originalAttributes = {
      dimensions: original.dimensions || '',
      weight: original.weight || '',
      material: original.material || '',
      color: original.color || '',
    }

    if (JSON.stringify(currentAttributes) !== JSON.stringify(originalAttributes)) {
      productData.attributes = currentAttributes
    }

    // Images change detection
    const currentImages = editForm.value.images.map((img, index) => ({
      id: img.id,
      image_path: img.path || (img.url && !img.url.startsWith('http') ? img.url : ''),
      is_primary: index === (editForm.value.primaryImageIndex ?? 0),
    })).filter(img => img.image_path || img.id)

    const originalImages = (original.images || []).map((img: any, index: number) => ({
      id: img.id,
      image_path: img.path || (img.url && !img.url.startsWith('http') ? img.url : ''),
      is_primary: index === (original.primaryImageIndex ?? 0),
    })).filter((img: any) => img.image_path || img.id)

    if (JSON.stringify(currentImages) !== JSON.stringify(originalImages)) {
      productData.images = currentImages
    }

    // If no changes, just close and return
    if (Object.keys(productData).length === 0) {
      closeEditModal()
      return
    }

    await productsApi.update(editingProductId.value, productData)
    closeEditModal()
    await loadProducts()
    alert('Product updated successfully!')
  } catch (error: unknown) {
    console.error('Failed to update product:', error)
    const apiError = error as ApiError
    alert(apiError.response?.data?.message || 'Failed to update product')
  }
}

const openEditFromView = () => {
  if (viewingProduct.value) {
    const id = viewingProduct.value.id
    closeViewModal()
    editProduct(id)
  }
}

// Image Upload Functions
const triggerImagePicker = () => {
  if (showEditModal.value) {
    if (editImageFileInput.value) {
      editImageFileInput.value.click()
    }
  } else {
    if (imageFileInput.value) {
      imageFileInput.value.click()
    }
  }
}

const handleImageFileSelect = async (event: Event) => {
  const target = event.target as HTMLInputElement
  const files = target.files

  if (!files || files.length === 0) return

  isUploadingImages.value = true

  try {
    // Upload each file
    const uploadPromises = Array.from(files).map(async (file) => {
      // Create preview
      const preview = URL.createObjectURL(file)

      // Upload to server
      const response = await uploadApi.image(file, 'products')

      if (response.data.success) {
        return {
          file,
          preview,
          url: response.data.data.url,
          path: response.data.data.path,
        }
      } else {
        throw new Error(response.data.message || 'Failed to upload image')
      }
    })

    const uploadedImages = await Promise.all(uploadPromises)

    // Add to appropriate form images array
    if (showEditModal.value) {
      editForm.value.images.push(...uploadedImages)
      if (editForm.value.primaryImageIndex === null || editForm.value.primaryImageIndex === undefined || editForm.value.primaryImageIndex < 0) {
        editForm.value.primaryImageIndex = 0
      }
    } else {
      addForm.value.images.push(...uploadedImages)
      if (addForm.value.primaryImageIndex === null || addForm.value.primaryImageIndex === undefined || addForm.value.primaryImageIndex < 0) {
        addForm.value.primaryImageIndex = 0
      }
    }

    // Reset file inputs
    if (imageFileInput.value) {
      imageFileInput.value.value = ''
    }
    if (editImageFileInput.value) {
      editImageFileInput.value.value = ''
    }

    success('Images Uploaded', `${uploadedImages.length} image(s) uploaded successfully.`)
  } catch (err: unknown) {
    console.error('Failed to upload images:', err)
    const apiError = err as { response?: { data?: { message?: string } }; message?: string }
    showError(
      'Upload Failed',
      apiError.response?.data?.message || apiError.message || 'Failed to upload images. Please try again.'
    )
  } finally {
    isUploadingImages.value = false
  }
}

const removeImage = (index: number) => {
  const form = showEditModal.value ? editForm.value : addForm.value
  const image = form.images[index]

  if (!image) return

  // Revoke object URL if it exists
  if (image.preview) {
    URL.revokeObjectURL(image.preview)
  }

  // Remove from array
  form.images.splice(index, 1)

  // Adjust primary image index if needed
  const currentPrimary = form.primaryImageIndex ?? 0
  if (currentPrimary >= form.images.length) {
    form.primaryImageIndex = form.images.length > 0 ? 0 : 0
  }

  // Optionally delete from server (only for new uploads, maybe don't delete existing ones yet)
  if (image.path && !image.id) {
    uploadApi.deleteFile(image.path).catch((err: unknown) => {
      console.error('Failed to delete image from server:', err)
    })
  }
}

const deleteProduct = (id: number) => {
  const product = products.value.find(p => p.id === id)
  if (product) {
    deletingProduct.value = product
    showDeleteModal.value = true
    document.body.style.overflow = 'hidden'
  }
}

const confirmDelete = async () => {
  if (!deletingProduct.value) return

  try {
    await productsApi.delete(deletingProduct.value.id)

    // Reset to page 1 if current page becomes empty
    if (paginatedProducts.value.length === 0 && currentPage.value > 1) {
      currentPage.value = Math.max(1, currentPage.value - 1)
    }

    closeDeleteModal()

    // Show success notification
    showDeleteSuccess.value = true
    setTimeout(() => {
      showDeleteSuccess.value = false
    }, 4000)

    // Reload products
    await loadProducts()
  } catch (error: unknown) {
    console.error('Failed to delete product:', error)
    const apiError = error as ApiError
    alert(apiError.response?.data?.message || 'Failed to delete product')
  }
}

const closeDeleteModal = () => {
  showDeleteModal.value = false
  deletingProduct.value = null
  document.body.style.overflow = ''
}

const updateFeatured = async (id: number, isFeatured: boolean) => {
  try {
    await productsApi.update(id, { is_featured: isFeatured })
    // Product will be updated via real-time listener
  } catch (error: unknown) {
    console.error('Failed to update featured status:', error)
    // Revert the change on error
    const product = products.value.find(p => p.id === id)
    if (product) {
      product.isFeatured = !isFeatured
    }
    const apiError = error as ApiError
    alert(apiError.response?.data?.message || 'Failed to update featured status')
  }
}

const goToPreviousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
    // Scroll to top of table
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const goToNextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
    // Scroll to top of table
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const exportProducts = () => {
  // Generate CSV content
  const headers = ['ID', 'Name', 'Category', 'Price', 'Sale Price', 'Stock', 'Status', 'Featured']
  const rows = filteredProducts.value.map(p => [
    p.id,
    p.name,
    p.category,
    p.price,
    p.salePrice || '',
    p.stock,
    p.status,
    p.isFeatured ? 'Yes' : 'No'
  ])

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.map(cell => `"${cell}"`).join(','))
  ].join('\n')

  // Create download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const link = document.createElement('a')
  const url = URL.createObjectURL(blob)
  link.setAttribute('href', url)
  link.setAttribute('download', `products_export_${new Date().toISOString().split('T')[0]}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)

  console.log('Products exported successfully')
}

// ═══════════════════════════════════════════════════
// LIFECYCLE
// ═══════════════════════════════════════════════════
// Real-time event handlers
const handleProductCreated = () => loadProducts()
const handleProductUpdated = () => loadProducts()
const handleProductDeleted = () => loadProducts()
const handleStockChanged = (event: Event) => {
  const customEvent = event as CustomEvent
  const data = customEvent.detail
  const product = products.value.find((p: Product) => p.id === data.product_id)
  if (product) {
    product.stock = data.new_quantity
  }
  loadProducts()
}

onMounted(async () => {
  await loadCategories()
  await loadProducts()

  // Set up real-time listeners using window events
  startListening()

  window.addEventListener('realtime:product:created', handleProductCreated)
  window.addEventListener('realtime:product:updated', handleProductUpdated)
  window.addEventListener('realtime:product:deleted', handleProductDeleted)
  window.addEventListener('realtime:stock:changed', handleStockChanged)
})

onUnmounted(() => {
  window.removeEventListener('realtime:product:created', handleProductCreated)
  window.removeEventListener('realtime:product:updated', handleProductUpdated)
  window.removeEventListener('realtime:product:deleted', handleProductDeleted)
  window.removeEventListener('realtime:stock:changed', handleStockChanged)
  stopListening()
})

</script>

<style scoped>
.admin-products-page {
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
  position: relative;
  z-index: 1;
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
  color: #4b5563;
  margin: 0;
  transition: color 0.3s ease;
}


.header-actions {
  display: flex;
  gap: 0.75rem;
  position: relative;
  z-index: 10;
}

.btn-primary,
.btn-secondary {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border-radius: 10px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
  border: none;
  font-size: 0.9rem;
}

.btn-primary {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #92400e;
  border: 2px solid rgba(201, 160, 80, 0.4);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  box-shadow: 0 2px 8px rgba(201, 160, 80, 0.15);
  position: relative;
  z-index: 11;
  overflow: hidden;
}

.btn-primary::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%);
  opacity: 0;
  transition: opacity 0.3s ease;
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(201, 160, 80, 0.35);
  border-color: var(--gold);
  color: #1a1d29;
}

.btn-primary:hover::before {
  opacity: 0.15;
}

.btn-primary svg,
.btn-primary span {
  position: relative;
  z-index: 1;
}

.btn-secondary {
  background: var(--white);
  color: var(--dark);
  border: 2px solid #e5e7eb;
  transition: all 0.3s ease;
  position: relative;
  z-index: 11;
}

.btn-secondary:hover {
  border-color: var(--gold);
  color: var(--gold);
}


.btn-primary svg,
.btn-secondary svg {
  width: 18px;
  height: 18px;
}

.filters-bar {
  display: flex;
  gap: 1rem;
  margin-bottom: 1.5rem;
  flex-wrap: wrap;
}

.search-box {
  flex: 1;
  min-width: 250px;
  position: relative;
}

.search-box svg {
  position: absolute;
  left: 1rem;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: #9ca3af;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem 0.75rem 3rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.9rem;
  outline: none;
  transition: border-color 0.2s, background-color 0.3s ease, color 0.3s ease;
  background: var(--white);
  color: #1a1d29;
}


.search-input:focus {
  border-color: var(--gold);
}

.filter-select {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  font-size: 0.9rem;
  cursor: pointer;
  outline: none;
  min-width: 150px;
}

.filter-select:focus {
  border-color: var(--gold);
}

.btn-clear {
  padding: 0.75rem 1.5rem;
  background: #f3f4f6;
  border: none;
  border-radius: 10px;
  font-weight: 600;
  color: #374151;
  cursor: pointer;
  transition: all 0.2s;
}

.btn-clear:hover {
  background: #e5e7eb;
  color: #1f2937;
}

.table-card {
  background: var(--white);
  border-radius: 16px;
  overflow: hidden;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  color: var(--dark);
  transition: color 0.3s ease;
}


.data-table thead {
  background: #f9fafb;
  transition: background-color 0.3s ease;
}


.data-table th {
  padding: 1rem;
  text-align: left;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  color: #374151;
  border-bottom: 2px solid #e5e7eb;
}

.data-table td {
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
  color: #1f2937;
  font-weight: 500;
  transition: color 0.3s ease, border-color 0.3s ease;
}


.data-table tbody tr:hover {
  background: #f9fafb;
  transition: background-color 0.3s ease;
}



.product-cell {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.product-thumb {
  width: 50px;
  height: 50px;
  object-fit: cover;
  border-radius: 8px;
}

.product-name {
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 0.25rem;
  transition: color 0.3s ease;
}


.product-id {
  font-size: 0.75rem;
  color: #6b7280;
  transition: color 0.3s ease;
}


.price-cell {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.price-main {
  font-weight: 700;
  color: #1a1d29;
  font-size: 0.95rem;
  transition: color 0.3s ease;
}

.price-sale {
  font-size: 0.8rem;
  color: #b91c1c;
  font-weight: 500;
  text-decoration: line-through;
}

.low-stock {
  color: #dc2626;
  font-weight: 600;
}

.status-badge {
  display: inline-block;
  padding: 0.35rem 0.75rem;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.status-badge.active {
  background: #d1fae5;
  color: #065f46;
}

.status-badge.hidden {
  background: #f3f4f6;
  color: #374151;
}

.status-badge.out_of_stock {
  background: #fee2e2;
  color: #991b1b;
}

.toggle-switch {
  position: relative;
  display: inline-block;
  width: 44px;
  height: 24px;
}

.toggle-switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.toggle-slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.3s;
  border-radius: 24px;
}

.toggle-slider:before {
  position: absolute;
  content: "";
  height: 18px;
  width: 18px;
  left: 3px;
  bottom: 3px;
  background-color: white;
  transition: 0.3s;
  border-radius: 50%;
}

.toggle-switch input:checked + .toggle-slider {
  background-color: var(--gold);
}

.toggle-switch input:checked + .toggle-slider:before {
  transform: translateX(20px);
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.action-btn {
  width: 36px;
  height: 36px;
  border-radius: 10px;
  border: 2px solid transparent;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.action-btn::before {
  content: '';
  position: absolute;
  inset: 0;
  background: currentColor;
  opacity: 0;
  transition: opacity 0.3s ease;
  border-radius: 8px;
}

.action-btn svg {
  width: 16px;
  height: 16px;
  position: relative;
  z-index: 1;
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.action-btn:hover svg {
  transform: scale(1.15);
}

.action-btn:active {
  transform: scale(0.92);
}

/* View Button - Modern Blue */
.action-btn.view {
  background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
  color: #2563eb;
  border-color: rgba(37, 99, 235, 0.1);
  box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);
}

.action-btn.view:hover {
  background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
  border-color: rgba(37, 99, 235, 0.3);
  box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
  transform: translateY(-2px);
}

.action-btn.view:hover::before {
  opacity: 0.08;
}

.action-btn.view:hover svg {
  animation: pulse-view 0.6s ease;
}

@keyframes pulse-view {
  0%, 100% { transform: scale(1.15); }
  50% { transform: scale(1.25); }
}

/* Edit Button - Modern Gold/Amber */
.action-btn.edit {
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #d97706;
  border-color: rgba(217, 119, 6, 0.1);
  box-shadow: 0 2px 4px rgba(217, 119, 6, 0.1);
}

.action-btn.edit:hover {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-color: rgba(217, 119, 6, 0.3);
  box-shadow: 0 4px 12px rgba(217, 119, 6, 0.25);
  transform: translateY(-2px);
}

.action-btn.edit:hover::before {
  opacity: 0.08;
}

.action-btn.edit:hover svg {
  animation: wiggle 0.4s ease;
}

@keyframes wiggle {
  0%, 100% { transform: scale(1.15) rotate(0deg); }
  25% { transform: scale(1.15) rotate(-8deg); }
  75% { transform: scale(1.15) rotate(8deg); }
}

/* Delete Button - Modern Red */
.action-btn.delete {
  background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
  color: #dc2626;
  border-color: rgba(220, 38, 38, 0.1);
  box-shadow: 0 2px 4px rgba(220, 38, 38, 0.1);
}

.action-btn.delete:hover {
  background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
  border-color: rgba(220, 38, 38, 0.3);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.25);
  transform: translateY(-2px);
}

.action-btn.delete:hover::before {
  opacity: 0.08;
}

.action-btn.delete:hover svg {
  animation: shake 0.4s ease;
}

@keyframes shake {
  0%, 100% { transform: scale(1.15) translateX(0); }
  25% { transform: scale(1.15) translateX(-2px); }
  75% { transform: scale(1.15) translateX(2px); }
}

/* Tooltip styles */
.action-btn[title] {
  position: relative;
}

.action-btn[title]::after {
  content: attr(title);
  position: absolute;
  bottom: calc(100% + 8px);
  left: 50%;
  transform: translateX(-50%) translateY(4px);
  padding: 6px 10px;
  background: #1a1d29;
  color: #ffffff;
  font-size: 0.7rem;
  font-weight: 600;
  white-space: nowrap;
  border-radius: 6px;
  opacity: 0;
  visibility: hidden;
  transition: all 0.2s ease;
  pointer-events: none;
  z-index: 100;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.action-btn[title]:hover::after {
  opacity: 1;
  visibility: visible;
  transform: translateX(-50%) translateY(0);
}

.pagination {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 1rem;
  margin-top: 2rem;
}

.page-btn {
  padding: 0.75rem 1.5rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  background: var(--white);
  color: var(--dark);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s;
}

.page-btn:hover:not(:disabled) {
  border-color: var(--gold);
  color: var(--gold);
}

.page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-info {
  color: #374151;
  font-weight: 600;
}

.empty-state {
  padding: 4rem 2rem;
  text-align: center;
  color: #4b5563;
}

.empty-state svg {
  width: 64px;
  height: 64px;
  margin: 0 auto 1.5rem;
  color: #9ca3af;
  opacity: 0.5;
}

.empty-state h3 {
  font-size: 1.25rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.empty-state p {
  margin: 0 0 1.5rem;
  color: #4b5563;
}

.action-btn:active {
  transform: scale(0.95);
}

.page-btn:active:not(:disabled) {
  transform: scale(0.98);
}

@media (max-width: 768px) {
  .page-header {
    flex-direction: column;
    gap: 1rem;
  }

  .filters-bar {
    flex-direction: column;
  }

  .data-table {
    font-size: 0.85rem;
  }
}

/* ═══════════════════════════════════════════════════
   ADD PRODUCT MODAL - REFACTORED
   ═══════════════════════════════════════════════════ */
.add-modal {
  max-width: 950px;
  width: 100%;
  background: #f8f9fa;
  border-radius: 20px;
  overflow: hidden;
}

.add-modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem 2rem;
  background: linear-gradient(135deg, #1a1d29 0%, #2d3142 100%);
}

.add-header-left {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.add-icon-wrapper {
  width: 48px;
  height: 48px;
  background: linear-gradient(135deg, rgba(201, 160, 80, 0.2) 0%, rgba(201, 160, 80, 0.1) 100%);
  border: 1px solid rgba(201, 160, 80, 0.3);
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.add-icon-wrapper svg {
  width: 24px;
  height: 24px;
  color: #c9a050;
}

.add-modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.35rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.add-modal-desc {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0.25rem 0 0;
}

.add-close-btn {
  width: 38px;
  height: 38px;
  border-radius: 10px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.add-close-btn svg {
  width: 18px;
  height: 18px;
}

.add-close-btn:hover {
  background: rgba(220, 53, 69, 0.2);
  border-color: rgba(220, 53, 69, 0.3);
  color: #dc3545;
  transform: rotate(90deg);
}

.add-modal-body {
  padding: 1.5rem 2rem;
  max-height: 65vh;
  overflow-y: auto;
}

.add-form {
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.add-form-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem;
}

.add-form-card {
  background: #ffffff;
  border-radius: 14px;
  border: 1px solid #e5e7eb;
  overflow: hidden;
  transition: all 0.3s ease;
}

.add-form-card:hover {
  border-color: rgba(201, 160, 80, 0.3);
  box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.card-header {
  display: flex;
  align-items: center;
  gap: 0.6rem;
  padding: 0.9rem 1.25rem;
  background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
  border-bottom: 1px solid #e5e7eb;
  font-weight: 700;
  font-size: 0.85rem;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.card-header svg {
  width: 18px;
  height: 18px;
  color: var(--gold);
}

.card-header.pricing svg { color: #10b981; }
.card-header.specs svg { color: #6366f1; }
.card-header.media svg { color: #f59e0b; }

.card-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.input-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.input-group label {
  font-size: 0.75rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.3px;
}

.input-group .req {
  color: #dc2626;
}

.input-group input,
.input-group select,
.input-group textarea {
  padding: 0.75rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 8px;
  font-size: 0.9rem;
  color: #1f2937;
  background: #ffffff;
  transition: all 0.2s ease;
}

.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {
  outline: none;
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(201, 160, 80, 0.1);
}

.input-group input::placeholder,
.input-group textarea::placeholder {
  color: #9ca3af;
}

.input-group textarea {
  resize: vertical;
  min-height: 80px;
}

.input-row {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.price-input {
  position: relative;
  display: flex;
  align-items: center;
}

.price-input .currency {
  position: absolute;
  left: 1rem;
  color: #374151;
  font-weight: 600;
  font-size: 0.9rem;
}

.price-input input {
  padding-left: 2.25rem;
  width: 100%;
}

.toggle-options {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.toggle-option {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.85rem 1rem;
  background: #f9fafb;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  border: 2px solid transparent;
}

.toggle-option:hover {
  background: #f3f4f6;
}

.toggle-option input {
  width: 18px;
  height: 18px;
  accent-color: var(--gold);
}

.toggle-option input:checked + .toggle-content .toggle-title {
  color: var(--gold);
}

.toggle-content {
  display: flex;
  flex-direction: column;
  gap: 0.15rem;
}

.toggle-title {
  font-weight: 600;
  font-size: 0.85rem;
  color: #1f2937;
  transition: color 0.2s ease;
}

.toggle-desc {
  font-size: 0.75rem;
  color: #6b7280;
}

.add-modal-footer {
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
  padding: 1.25rem 2rem;
  background: #ffffff;
  border-top: 1px solid #e5e7eb;
}

.add-btn-cancel {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.5rem;
  border: 2px solid #d1d5db;
  border-radius: 10px;
  background: #ffffff;
  color: #374151;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.2s ease;
}

.add-btn-cancel svg {
  width: 16px;
  height: 16px;
}

.add-btn-cancel:hover {
  border-color: #9ca3af;
  background: #f9fafb;
  color: #1f2937;
}

.add-btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1.75rem;
  border: 2px solid rgba(201, 160, 80, 0.4);
  border-radius: 10px;
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #92400e;
  font-weight: 700;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(201, 160, 80, 0.15);
}

.add-btn-save svg {
  width: 18px;
  height: 18px;
  color: #92400e;
}

.add-btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(201, 160, 80, 0.3);
  border-color: var(--gold);
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
}

/* Add Modal Responsive */
@media (max-width: 900px) {
  .add-form-row {
    grid-template-columns: 1fr;
  }

  .add-modal-body {
    padding: 1rem 1.5rem;
  }
}

@media (max-width: 600px) {
  .input-row {
    grid-template-columns: 1fr;
  }

  .add-modal-header {
    padding: 1rem 1.25rem;
  }

  .add-modal-footer {
    flex-direction: column;
  }

  .add-btn-cancel,
  .add-btn-save {
    width: 100%;
    justify-content: center;
  }
}

/* ═══════════════════════════════════════════════════
   MODAL STYLES
   ═══════════════════════════════════════════════════ */
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

.modal-container {
  background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
  border-radius: 24px;
  width: 100%;
  max-height: 90vh;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  transform: scale(0.9) translateY(20px);
  transition: transform 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(201, 160, 80, 0.1);
}

.modal-overlay.active .modal-container {
  transform: scale(1) translateY(0);
}

.modal-large {
  max-width: 800px;
}

.modal-view {
  max-width: 900px;
}

.modal-header {
  padding: 1.75rem 2rem;
  background: linear-gradient(135deg, #1a1d29 0%, #2d3142 100%);
  display: flex;
  justify-content: space-between;
  align-items: center;
  position: relative;
  overflow: hidden;
}

.modal-header::before {
  content: '';
  position: absolute;
  top: 0;
  right: 0;
  width: 200px;
  height: 200px;
  background: radial-gradient(circle, rgba(201, 160, 80, 0.15) 0%, transparent 70%);
  pointer-events: none;
}

.modal-header-content {
  display: flex;
  align-items: center;
  gap: 1rem;
  position: relative;
  z-index: 1;
}

.modal-icon {
  width: 52px;
  height: 52px;
  border-radius: 14px;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s ease;
}

.modal-icon svg {
  width: 26px;
  height: 26px;
}

.modal-icon.add {
  background: linear-gradient(135deg, rgba(201, 160, 80, 0.2) 0%, rgba(201, 160, 80, 0.1) 100%);
  color: #c9a050;
  border: 1px solid rgba(201, 160, 80, 0.3);
}

.modal-icon.edit {
  background: linear-gradient(135deg, rgba(217, 119, 6, 0.2) 0%, rgba(217, 119, 6, 0.1) 100%);
  color: #f59e0b;
  border: 1px solid rgba(217, 119, 6, 0.3);
}

.modal-icon.view {
  background: linear-gradient(135deg, rgba(37, 99, 235, 0.2) 0%, rgba(37, 99, 235, 0.1) 100%);
  color: #3b82f6;
  border: 1px solid rgba(37, 99, 235, 0.3);
}

.modal-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 700;
  color: #ffffff;
  margin: 0;
}

.modal-subtitle {
  font-size: 0.85rem;
  color: rgba(255, 255, 255, 0.7);
  margin: 0.25rem 0 0;
}

.modal-close {
  width: 40px;
  height: 40px;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.1);
  background: rgba(255, 255, 255, 0.05);
  color: rgba(255, 255, 255, 0.7);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  z-index: 1;
}

.modal-close svg {
  width: 20px;
  height: 20px;
}

.modal-close:hover {
  background: rgba(220, 53, 69, 0.2);
  border-color: rgba(220, 53, 69, 0.3);
  color: #dc3545;
  transform: rotate(90deg);
}

.modal-body {
  flex: 1;
  overflow-y: auto;
  padding: 2rem;
}

.modal-footer {
  padding: 1.25rem 2rem;
  background: #f8f9fa;
  border-top: 1px solid #e5e7eb;
  display: flex;
  justify-content: flex-end;
  gap: 1rem;
}

/* Form Styles */
.product-form {
  display: flex;
  flex-direction: column;
  gap: 2rem;
}

.form-section {
  background: #ffffff;
  border: 1px solid #e5e7eb;
  border-radius: 16px;
  padding: 1.5rem;
  transition: all 0.3s ease;
}

.form-section:hover {
  border-color: rgba(201, 160, 80, 0.3);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.08);
}

.section-title {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-size: 1rem;
  font-weight: 700;
  color: #1a1d29;
  margin: 0 0 1.25rem;
  padding-bottom: 0.75rem;
  border-bottom: 2px solid #f0f0f0;
}

.section-title svg {
  width: 20px;
  height: 20px;
  color: var(--gold);
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group.full-width {
  grid-column: span 2;
}

.form-label {
  font-size: 0.8rem;
  font-weight: 700;
  color: #1f2937;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.required {
  color: #dc2626;
}

.form-input,
.form-select,
.form-textarea {
  padding: 0.875rem 1rem;
  border: 2px solid #e5e7eb;
  border-radius: 10px;
  font-size: 0.95rem;
  color: #1a1d29;
  background: #ffffff;
  outline: none;
  transition: all 0.3s ease;
}

.form-input:focus,
.form-select:focus,
.form-textarea:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.form-input::placeholder,
.form-textarea::placeholder {
  color: #9ca3af;
}

.form-textarea {
  resize: vertical;
  min-height: 100px;
}

.form-select {
  cursor: pointer;
}

.image-input-wrapper {
  display: flex;
  gap: 0.5rem;
  align-items: stretch;
}

.image-url-input {
  flex: 1;
  cursor: pointer;
}

.image-url-input:hover {
  border-color: var(--gold);
}

.image-picker-btn {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.25rem;
  border: 2px solid var(--gold);
  border-radius: 10px;
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #92400e;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
  white-space: nowrap;
}

.image-picker-btn svg {
  width: 18px;
  height: 18px;
}

.image-picker-btn:hover {
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  border-color: #b8860b;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.2);
}

/* Image Upload Section Styles */
.image-upload-section {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.image-upload-area {
  min-height: 200px;
  max-height: 400px;
  border: 2px dashed #d1d5db;
  border-radius: 12px;
  background: #f9fafb;
  cursor: pointer;
  transition: all 0.3s ease;
  overflow-y: auto;
  padding: 1rem;
}

.image-upload-area:hover {
  border-color: var(--gold);
  background: #fef3c7;
}

.upload-placeholder {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  height: 100%;
  min-height: 180px;
  color: #6b7280;
  text-align: center;
}

.upload-placeholder svg {
  width: 64px;
  height: 64px;
  color: #9ca3af;
}

.upload-placeholder p {
  font-size: 1rem;
  font-weight: 600;
  color: #374151;
  margin: 0;
}

.upload-placeholder span {
  font-size: 0.875rem;
  color: #6b7280;
}

.image-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
  gap: 1rem;
}

.image-preview-item {
  position: relative;
  aspect-ratio: 1;
  border-radius: 8px;
  overflow: hidden;
  border: 2px solid #e5e7eb;
  background: #ffffff;
}

.image-preview-item img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.remove-image-btn {
  position: absolute;
  top: 4px;
  right: 4px;
  width: 28px;
  height: 28px;
  border-radius: 50%;
  background: rgba(220, 38, 38, 0.9);
  border: none;
  color: #ffffff;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
  z-index: 2;
}

.remove-image-btn:hover {
  background: rgba(220, 38, 38, 1);
  transform: scale(1.1);
}

.remove-image-btn svg {
  width: 14px;
  height: 14px;
}

.primary-checkbox {
  position: absolute;
  bottom: 4px;
  left: 4px;
  right: 4px;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.25rem;
  padding: 0.25rem 0.5rem;
  background: rgba(0, 0, 0, 0.6);
  border-radius: 6px;
  font-size: 0.75rem;
  color: #ffffff;
  cursor: pointer;
  z-index: 2;
}

.primary-checkbox input[type="radio"] {
  margin: 0;
  cursor: pointer;
}

.primary-checkbox span {
  font-weight: 600;
  font-size: 0.7rem;
}

.add-more-images {
  aspect-ratio: 1;
  border: 2px dashed #d1d5db;
  border-radius: 8px;
  background: #f9fafb;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  cursor: pointer;
  transition: all 0.3s ease;
  color: #6b7280;
}

.add-more-images:hover {
  border-color: var(--gold);
  background: #fef3c7;
  color: var(--gold);
}

.add-more-images svg {
  width: 32px;
  height: 32px;
}

.add-more-images span {
  font-size: 0.75rem;
  font-weight: 600;
}

.upload-hint {
  font-size: 0.875rem;
  color: #6b7280;
  margin: 0;
  text-align: center;
}

.form-toggles {
  margin-top: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  cursor: pointer;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 10px;
  border: 2px solid transparent;
  transition: all 0.3s ease;
}

.toggle-label:hover {
  background: #f0f0f0;
}

.toggle-label input {
  width: 20px;
  height: 20px;
  accent-color: var(--gold);
  margin-top: 2px;
}

.toggle-text {
  font-weight: 600;
  color: #1a1d29;
}

.toggle-hint {
  display: block;
  font-size: 0.8rem;
  color: #4b5563;
  margin-top: 0.25rem;
}

/* Modal Buttons */
.btn-cancel {
  padding: 0.875rem 1.75rem;
  border: 2px solid #d1d5db;
  border-radius: 12px;
  background: #ffffff;
  color: #374151;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel:hover {
  border-color: #9ca3af;
  background: #f3f4f6;
  color: #1f2937;
}

.btn-save {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: 2px solid rgba(201, 160, 80, 0.4);
  border-radius: 12px;
  background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
  color: #92400e;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(201, 160, 80, 0.2);
}

.btn-save svg {
  width: 18px;
  height: 18px;
  color: #92400e;
}

.btn-save:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(201, 160, 80, 0.35);
  border-color: var(--gold);
  background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
  color: #78350f;
}

.btn-edit {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border: 2px solid rgba(217, 119, 6, 0.4);
  border-radius: 12px;
  background: linear-gradient(135deg, #fffbeb 0%, #fed7aa 100%);
  color: #9a3412;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
}

.btn-edit svg {
  width: 18px;
  height: 18px;
  color: #9a3412;
}

.btn-edit:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(245, 158, 11, 0.35);
  border-color: #d97706;
  background: linear-gradient(135deg, #fed7aa 0%, #fdba74 100%);
  color: #7c2d12;
}

/* View Modal Styles */
.view-body {
  padding: 0;
}

.view-layout {
  display: grid;
  grid-template-columns: 1fr 1fr;
  min-height: 450px;
}

.view-image-section {
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  position: relative;
}

.view-image-container {
  position: relative;
  width: 100%;
  max-width: 350px;
}

.view-product-image {
  width: 100%;
  height: auto;
  border-radius: 16px;
  box-shadow: 0 8px 30px rgba(0, 0, 0, 0.12);
}

.view-badge {
  position: absolute;
  top: 12px;
  padding: 6px 14px;
  border-radius: 8px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.view-badge.featured {
  left: 12px;
  background: linear-gradient(135deg, var(--gold) 0%, #b8860b 100%);
  color: #ffffff;
}

.view-badge.low-stock {
  right: 12px;
  background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
  color: #ffffff;
}

.view-details-section {
  padding: 2rem;
  display: flex;
  flex-direction: column;
  gap: 1.5rem;
}

.view-header-info {
  border-bottom: 1px solid #000000;
  padding-bottom: 1.5rem;
}

.view-category {
  display: inline-block;
  padding: 4px 12px;
  background: rgba(201, 160, 80, 0.1);
  color: #000000;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1px;
  margin-bottom: 0.75rem;
}

.view-product-name {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 700;
  color: #000000;
  margin: 0 0 0.5rem;
}

.view-product-name::selection {
  background-color: rgba(0, 0, 0, 0.1);
  color: #000000;
}

.view-product-name::-moz-selection {
  background-color: rgba(0, 0, 0, 0.1);
  color: #000000;
}

.view-product-id {
  color: #000000;
  font-size: 0.85rem;
  font-weight: 500;
}

.view-price-section {
  background: #ffffff;
  padding: 1.25rem;
  border-radius: 12px;
  border: 1px solid #e5e7eb;
}

.view-price-main {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 700;
  color: #000000;
}

.view-price-main::selection {
  background-color: rgba(0, 0, 0, 0.1);
  color: #000000;
}

.view-price-main::-moz-selection {
  background-color: rgba(0, 0, 0, 0.1);
  color: #000000;
}

.view-price-sale {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.5rem;
}

.sale-label {
  font-size: 0.8rem;
  color: #000000;
  font-weight: 500;
}

.sale-amount {
  font-weight: 700;
  color: #000000;
  text-decoration: line-through;
}

.view-info-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1rem;
}

.view-info-card {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 1rem;
  background: #f8f9fa;
  border-radius: 12px;
  transition: all 0.3s ease;
}

.view-info-card:hover {
  background: #f0f0f0;
  transform: translateX(4px);
}

.info-icon {
  width: 40px;
  height: 40px;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.info-icon svg {
  width: 20px;
  height: 20px;
}

.info-icon.stock {
  background: rgba(16, 185, 129, 0.1);
  color: #10b981;
}

.info-icon.status {
  background: rgba(37, 99, 235, 0.1);
  color: #3b82f6;
}

.info-icon.featured {
  background: rgba(201, 160, 80, 0.1);
  color: var(--gold);
}

.info-icon.category {
  background: rgba(139, 92, 246, 0.1);
  color: #8b5cf6;
}

.info-content {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.info-label {
  font-size: 0.7rem;
  font-weight: 700;
  color: #374151;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-value {
  font-weight: 600;
  color: #1a1d29;
}

.info-value.text-danger {
  color: #dc2626;
}

.view-description {
  padding-top: 1rem;
  border-top: 1px solid #e5e7eb;
}

.view-description h4 {
  font-size: 0.85rem;
  font-weight: 700;
  color: #000000;
  margin: 0 0 0.5rem;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.view-description p {
  color: #000000;
  line-height: 1.6;
  margin: 0;
}

/* Modal Responsive */
@media (max-width: 768px) {
  .modal-overlay {
    padding: 1rem;
  }

  .modal-container {
    max-height: 95vh;
  }

  .modal-header {
    padding: 1.25rem 1.5rem;
  }

  .modal-body {
    padding: 1.5rem;
  }

  .modal-footer {
    padding: 1rem 1.5rem;
    flex-direction: column;
  }

  .form-grid {
    grid-template-columns: 1fr;
  }

  .form-group.full-width {
    grid-column: span 1;
  }

  .view-layout {
    grid-template-columns: 1fr;
  }

  .view-image-section {
    padding: 1.5rem;
  }

  .view-info-grid {
    grid-template-columns: 1fr;
  }
}

/* ═══════════════════════════════════════════════════
   DELETE CONFIRMATION MODAL
   ═══════════════════════════════════════════════════ */
.delete-modal {
  max-width: 480px;
  width: 100%;
  background: #ffffff;
  border-radius: 20px;
  overflow: hidden;
  box-shadow:
    0 25px 50px -12px rgba(0, 0, 0, 0.25),
    0 0 0 1px rgba(220, 53, 69, 0.1);
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
  0%, 100% {
    transform: scale(1);
    box-shadow: 0 0 0 0 rgba(220, 53, 69, 0.4);
  }
  50% {
    transform: scale(1.05);
    box-shadow: 0 0 0 8px rgba(220, 53, 69, 0);
  }
}

@keyframes ripple {
  0% {
    transform: scale(1);
    opacity: 1;
  }
  100% {
    transform: scale(1.3);
    opacity: 0;
  }
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

.delete-product-name {
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

/* ═══════════════════════════════════════════════════
   DELETE SUCCESS NOTIFICATION
   ═══════════════════════════════════════════════════ */
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
  box-shadow:
    0 10px 25px -5px rgba(0, 0, 0, 0.15),
    0 0 0 1px rgba(16, 185, 129, 0.1);
  border-left: 4px solid #10b981;
  min-width: 320px;
  max-width: 420px;
  animation: slideInRight 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes slideInRight {
  from {
    transform: translateX(400px);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
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
  0%, 100% {
    transform: scale(1);
  }
  50% {
    transform: scale(1.15);
  }
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

/* Delete Modal Responsive */
@media (max-width: 768px) {
  .delete-modal-content {
    padding: 2rem 1.5rem;
  }

  .delete-icon-circle {
    width: 70px;
    height: 70px;
  }

  .delete-icon-circle svg {
    width: 32px;
    height: 32px;
  }

  .delete-title {
    font-size: 1.5rem;
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

@media (max-width: 480px) {
  .delete-modal {
    max-width: 100%;
    margin: 1rem;
  }

  .delete-modal-content {
    padding: 1.5rem 1.25rem;
  }

  .delete-title {
    font-size: 1.25rem;
  }

  .delete-message {
    font-size: 0.9rem;
  }
}
</style>
