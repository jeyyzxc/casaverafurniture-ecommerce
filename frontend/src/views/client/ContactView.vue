<template>
  <div class="contact-page">
    <!-- Hero Section -->
    <HeroSection
      title="Get in Touch"
      subtitle="We'd love to hear from you—message us or visit our store for the perfect piece."
      size="large"
    />

    <!-- Main Content -->
    <section class="contact-main">
      <div class="contact-container">
        <div class="contact-grid">
          <!-- Contact Form -->
          <div class="contact-form-card rise-up">
            <div class="form-header">
              <span class="form-label">Message Us</span>
              <h2 class="form-title">How can we help?</h2>
            </div>
            
            <form @submit.prevent="submitForm" class="contact-form" novalidate>
              <div class="form-group">
                <input 
                  type="text" 
                  v-model="form.name" 
                  @input="handleNameInput"
                  @blur="validateName"
                  @keydown="preventLeadingSpace"
                  placeholder=" "
                  class="form-input"
                  :class="{ 'input-error': errors.name }"
                  id="name"
                >
                <label for="name" class="form-label-float">Full Name</label>
                <span v-if="errors.name" class="error-text">{{ errors.name }}</span>
              </div>

              <div class="form-group">
                <input 
                  type="email" 
                  v-model="form.email" 
                  @input="handleEmailInput"
                  @blur="validateEmail"
                  @keydown="preventSpaceInEmail"
                  placeholder=" "
                  class="form-input"
                  :class="{ 'input-error': errors.email }"
                  id="email"
                >
                <label for="email" class="form-label-float">Email Address</label>
                <span v-if="errors.email" class="error-text">{{ errors.email }}</span>
              </div>

              <div class="form-group">
                <textarea 
                  v-model="form.message" 
                  @input="handleMessageInput"
                  @blur="validateMessage"
                  @keydown="preventSpaceInMessage"
                  placeholder=" "
                  class="form-input form-textarea"
                  :class="{ 'input-error': errors.message }"
                  id="message"
                ></textarea>
                <label for="message" class="form-label-float">Message</label>
                <span v-if="errors.message" class="error-text">{{ errors.message }}</span>
              </div>

              <button type="submit" class="submit-btn" :disabled="isSubmitting">
                <svg v-if="isSubmitting" class="spinner" viewBox="0 0 24 24">
                  <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" fill="none" stroke-dasharray="32" stroke-linecap="round"/>
                </svg>
                <template v-else-if="isSuccess">
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="20 6 9 17 4 12"/>
                  </svg>
                  Message Sent!
                </template>
                <template v-else>
                  Send Message
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/>
                  </svg>
                </template>
              </button>
            </form>
          </div>

          <!-- Contact Info -->
          <div class="contact-info rise-up-delay-2">
            <!-- Showrooms -->
            <div class="info-card">
              <div class="info-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                  <circle cx="12" cy="10" r="3"/>
                </svg>
              </div>
              <div class="info-content">
                <h5 class="info-title">Our Showrooms</h5>
                <div class="showroom-list">
                  <address class="showroom">
                    <strong>BGC Flagship</strong>
                    <span>Unit 1205, The Forum BGC, Taguig</span>
                  </address>
                  <address class="showroom">
                    <strong>Makati Gallery</strong>
                    <span>2nd Floor, Greenbelt 5, Makati City</span>
                  </address>
                  <address class="showroom">
                    <strong>Laguna Outlet</strong>
                    <span>G/F Solstice Lifestyle Center, San Pedro</span>
                  </address>
                </div>
              </div>
            </div>

            <!-- Business Hours -->
            <div class="info-card">
              <div class="info-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <circle cx="12" cy="12" r="10"/>
                  <polyline points="12 6 12 12 16 14"/>
                </svg>
              </div>
              <div class="info-content">
                <h5 class="info-title">Business Hours</h5>
                <ul class="hours-list">
                  <li>
                    <span>Mon – Fri</span>
                    <span>10:00 AM – 7:00 PM</span>
                  </li>
                  <li>
                    <span>Saturday</span>
                    <span>10:00 AM – 6:00 PM</span>
                  </li>
                  <li>
                    <span>Sun & Holidays</span>
                    <span>By Appointment</span>
                  </li>
                </ul>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="action-cards">
              <a href="mailto:hello@casavera.com" class="action-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                  <polyline points="22,6 12,13 2,6"/>
                </svg>
                <h6>Email Us</h6>
                <span>hello@casavera.com</span>
              </a>
              <a href="tel:+639123456789" class="action-card">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                  <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                </svg>
                <h6>Call Us</h6>
                <span>+63 912 345 6789</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Map Section -->
    <section class="map-section">
      <div class="map-container">
        <iframe 
          src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.889369324636!2d121.04879531535497!3d14.54840298983577!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3397c8f258a62e01%3A0x628047913079250!2sBonifacio%20Global%20City!5e0!3m2!1sen!2sph!4v1674291234567!5m2!1sen!2sph" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade"
        ></iframe>
        <div class="map-overlay-guard"></div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import HeroSection from '@/components/HeroSection.vue'

// Form State
const form = ref({
  name: '',
  email: '',
  message: ''
})

const errors = ref({
  name: '',
  email: '',
  message: ''
})

const isSubmitting = ref(false)
const isSuccess = ref(false)

// Prevent space key from being entered at the start of any field
const preventLeadingSpace = (event: KeyboardEvent) => {
  const target = event.target as HTMLInputElement
  // If field is empty and user tries to type space, prevent it
  if (event.key === ' ' && target.value.length === 0) {
    event.preventDefault()
  }
}

// Strictly prevent spaces in email field
const preventSpaceInEmail = (event: KeyboardEvent) => {
  // Prevent space key from being entered at all in email field
  if (event.key === ' ') {
    event.preventDefault()
  }
}

// Strictly prevent spaces in message field
const preventSpaceInMessage = (event: KeyboardEvent) => {
  // Prevent space key from being entered at all in message field
  if (event.key === ' ') {
    event.preventDefault()
  }
}

// Handle name input - prevent leading spaces and consecutive spaces
const handleNameInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  
  // Block spaces at the start - must start with a letter
  if (value.length > 0 && value[0] === ' ') {
    value = value.trimStart()
  }
  
  // Replace consecutive spaces with single space (allow only one space at a time)
  value = value.replace(/\s{2,}/g, ' ')
  
  form.value.name = value
  errors.value.name = ''
}

// Handle email input - remove any spaces that might have been pasted
const handleEmailInput = (event: Event) => {
  const target = event.target as HTMLInputElement
  let value = target.value
  
  // Remove all spaces from email (in case user pastes text with spaces)
  value = value.replace(/\s/g, '')
  
  form.value.email = value
  errors.value.email = ''
}

// Handle message input - remove any spaces that might have been pasted
const handleMessageInput = (event: Event) => {
  const target = event.target as HTMLTextAreaElement
  let value = target.value
  
  // Remove all spaces from the message (in case user pastes text with spaces)
  value = value.replace(/\s/g, '')
  
  form.value.message = value
  errors.value.message = ''
}

// Validate name field
const validateName = () => {
  errors.value.name = ''
  
  // Check if field is empty
  if (!form.value.name.trim()) {
    errors.value.name = 'Full Name is required'
    return false
  }
  
  // Check minimum length (6 characters)
  if (form.value.name.trim().length < 6) {
    errors.value.name = 'Full Name must be at least 6 characters'
    return false
  }
  
  // Check if starts with space (shouldn't happen due to input handler, but double-check)
  if (form.value.name.trim().startsWith(' ')) {
    errors.value.name = 'Full Name cannot start with a space'
    return false
  }
  
  // Check if starts with a letter (not a space or number)
  if (form.value.name.trim().length > 0 && !/^[a-zA-Z]/.test(form.value.name.trim())) {
    errors.value.name = 'Full Name must start with a letter'
    return false
  }
  
  // Check for consecutive spaces
  if (/\s{2,}/.test(form.value.name)) {
    errors.value.name = 'Full Name cannot contain consecutive spaces'
    return false
  }
  
  return true
}

const validateEmail = () => {
  errors.value.email = ''
  
  // Check if field is empty
  if (!form.value.email.trim()) {
    errors.value.email = 'Email Address is required'
    return false
  }
  
  // Check if email contains any spaces (strictly not allowed)
  if (/\s/.test(form.value.email)) {
    errors.value.email = 'Email Address cannot contain spaces'
    return false
  }
  
  // Check if starts with a letter (not a space or number)
  if (form.value.email.trim().length > 0 && !/^[a-zA-Z]/.test(form.value.email.trim())) {
    errors.value.email = 'Email Address must start with a letter'
    return false
  }
  
  // Email format validation
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  if (!emailRegex.test(form.value.email.trim())) {
    errors.value.email = 'Please enter a valid email address'
    return false
  }
  
  return true
}

const validateMessage = () => {
  errors.value.message = ''
  
  // Check if field is empty
  if (!form.value.message.trim()) {
    errors.value.message = 'Message is required'
    return false
  }
  
  // Check minimum length (8 characters)
  if (form.value.message.trim().length < 8) {
    errors.value.message = 'Message must be at least 8 characters'
    return false
  }
  
  // Check if message contains any spaces (shouldn't happen due to input handler, but double-check)
  if (/\s/.test(form.value.message)) {
    errors.value.message = 'Message cannot contain spaces'
    return false
  }
  
  // Check if starts with a letter (not a space or number)
  if (form.value.message.trim().length > 0 && !/^[a-zA-Z]/.test(form.value.message.trim())) {
    errors.value.message = 'Message must start with a letter'
    return false
  }
  
  return true
}

const submitForm = async () => {
  // Immediately validate all fields and show ALL errors at once
  let isValid = true
  
  // Validate name field - set error immediately if empty
  if (!form.value.name || !form.value.name.trim()) {
    errors.value.name = 'Full Name is required'
    isValid = false
  } else {
    // Field has content, validate it
    if (form.value.name.trim().length < 6) {
      errors.value.name = 'Full Name must be at least 6 characters'
      isValid = false
    } else if (form.value.name.trim().startsWith(' ')) {
      errors.value.name = 'Full Name cannot start with a space'
      isValid = false
    } else if (!/^[a-zA-Z]/.test(form.value.name.trim())) {
      errors.value.name = 'Full Name must start with a letter'
      isValid = false
    } else if (/\s{2,}/.test(form.value.name)) {
      errors.value.name = 'Full Name cannot contain consecutive spaces'
      isValid = false
    } else {
      errors.value.name = ''
    }
  }
  
  // Validate email field - set error immediately if empty
  if (!form.value.email || !form.value.email.trim()) {
    errors.value.email = 'Email Address is required'
    isValid = false
  } else {
    // Field has content, validate it
    if (/\s/.test(form.value.email)) {
      errors.value.email = 'Email Address cannot contain spaces'
      isValid = false
    } else if (!/^[a-zA-Z]/.test(form.value.email.trim())) {
      errors.value.email = 'Email Address must start with a letter'
      isValid = false
    } else {
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
      if (!emailRegex.test(form.value.email.trim())) {
        errors.value.email = 'Please enter a valid email address'
        isValid = false
      } else {
        errors.value.email = ''
      }
    }
  }
  
  // Validate message field - set error immediately if empty
  if (!form.value.message || !form.value.message.trim()) {
    errors.value.message = 'Message is required'
    isValid = false
  } else {
    // Field has content, validate it
    if (form.value.message.trim().length < 8) {
      errors.value.message = 'Message must be at least 8 characters'
      isValid = false
    } else if (/\s/.test(form.value.message)) {
      errors.value.message = 'Message cannot contain spaces'
      isValid = false
    } else if (!/^[a-zA-Z]/.test(form.value.message.trim())) {
      errors.value.message = 'Message must start with a letter'
      isValid = false
    } else {
      errors.value.message = ''
    }
  }
  
  // If validation fails, prevent submission (all errors are already set above)
  if (!isValid) {
    return
  }
  
  isSubmitting.value = true
  
  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500))
  
  isSubmitting.value = false
  isSuccess.value = true
  
  // Reset after 3 seconds
  setTimeout(() => {
    form.value = { name: '', email: '', message: '' }
    errors.value = { name: '', email: '', message: '' }
    isSuccess.value = false
  }, 3000)
}
</script>

<style scoped>
.contact-page {
  --gold: #c9a050;
  --gold-light: #d4af37;
  --gold-dark: #B8860B;
  --dark: #1a1a1a;
  --white: #ffffff;
  --light: #f8f8f8;
  --gray: #666;
  
  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Main Content */
.contact-main {
  background: var(--light);
  padding: 4rem 0;
  flex: 1;
}

.contact-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 3rem;
}

.contact-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3.5rem;
}

/* Form Card */
.contact-form-card {
  background: var(--white);
  padding: 3rem;
  border-radius: 16px;
  box-shadow: 0 10px 40px rgba(0,0,0,0.08);
  border: 1px solid rgba(0,0,0,0.03);
}

.form-header {
  margin-bottom: 2.5rem;
}

.form-label {
  color: var(--gold);
  font-size: 0.85rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
}

.form-title {
  font-family: 'Playfair Display', serif;
  font-size: 2rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0.5rem 0 0;
}

.contact-form {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.form-group {
  position: relative;
}

.error-text {
  display: block;
  color: #dc2626;
  font-size: 0.75rem;
  margin-top: 0.5rem;
  padding-left: 0.5rem;
}

.form-input {
  width: 100%;
  padding: 1rem 1rem 1rem;
  background: var(--light);
  border: 1px solid transparent;
  border-radius: 8px;
  font-size: 1rem;
  outline: none;
  transition: all 0.3s ease;
}

.form-input:invalid:not(:placeholder-shown),
.form-input.input-error {
  border-color: #dc2626;
  background: #fff5f5;
}

.form-input:focus {
  background: var(--white);
  border-color: var(--gold);
  box-shadow: 0 0 0 4px rgba(201, 160, 80, 0.1);
}

.form-textarea {
  min-height: 150px;
  resize: vertical;
}

.form-label-float {
  position: absolute;
  left: 1rem;
  top: 1rem;
  color: var(--gray);
  font-size: 1rem;
  pointer-events: none;
  transition: all 0.2s ease;
}

.form-input:focus + .form-label-float,
.form-input:not(:placeholder-shown) + .form-label-float {
  top: -0.5rem;
  left: 0.75rem;
  font-size: 0.75rem;
  color: var(--gold);
  background: var(--white);
  padding: 0 0.25rem;
}

.submit-btn {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 1rem 2rem;
  background: var(--gold);
  color: var(--dark);
  border: none;
  border-radius: 50px;
  font-size: 0.9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover:not(:disabled) {
  background: var(--gold-dark);
  color: var(--white);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(201, 160, 80, 0.3);
}

.submit-btn:disabled {
  opacity: 0.7;
  cursor: not-allowed;
}

.submit-btn svg {
  width: 18px;
  height: 18px;
}

.spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Info Cards */
.contact-info {
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
}

.info-card {
  background: var(--white);
  padding: 2rem;
  border-radius: 16px;
  display: flex;
  gap: 1.5rem;
  box-shadow: 0 4px 15px rgba(0,0,0,0.04);
  border: 1px solid rgba(0,0,0,0.05);
  transition: all 0.3s ease;
}

.info-card:hover {
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.08);
  border-color: var(--gold);
}

.info-icon {
  width: 56px;
  height: 56px;
  background: rgba(201, 160, 80, 0.1);
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.info-icon svg {
  width: 22px;
  height: 22px;
  color: var(--gold-dark);
}

.info-content {
  flex: 1;
}

.info-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.2rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.75rem;
}

.showroom-list {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.showroom {
  font-style: normal;
  font-size: 0.85rem;
  color: var(--gray);
}

.showroom strong {
  display: block;
  color: var(--dark);
  margin-bottom: 0.125rem;
}

.hours-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.hours-list li {
  display: flex;
  justify-content: space-between;
  padding: 0.5rem 0;
  border-bottom: 1px solid #eee;
  font-size: 0.85rem;
  color: var(--gray);
}

.hours-list li:last-child {
  border-bottom: none;
}

.hours-list li span:first-child {
  font-weight: 600;
  color: var(--dark);
}

/* Action Cards */
.action-cards {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1rem;
}

.action-card {
  background: var(--white);
  padding: 1.5rem;
  text-align: center;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  text-decoration: none;
  transition: all 0.3s ease;
}

.action-card:hover {
  border-color: var(--gold);
  transform: translateY(-3px);
  box-shadow: 0 10px 25px rgba(0,0,0,0.06);
}

.action-card svg {
  width: 28px;
  height: 28px;
  color: var(--gold);
  margin-bottom: 0.75rem;
  transition: transform 0.3s ease;
}

.action-card:hover svg {
  transform: scale(1.15);
}

.action-card h6 {
  font-weight: 700;
  color: var(--dark);
  margin: 0 0 0.25rem;
  font-size: 0.9rem;
}

.action-card span {
  font-size: 0.8rem;
  color: var(--gray);
}

/* Map Section */
.map-section {
  position: relative;
  width: 100%;
  height: 400px;
  background: #f0f0f0;
}

.map-container {
  width: 100%;
  height: 100%;
  position: relative;
}

.map-container iframe {
  width: 100%;
  height: 100%;
  border: 0;
  filter: grayscale(100%) contrast(1.1);
  transition: filter 0.5s ease;
}

.map-container:hover iframe {
  filter: grayscale(0%);
}

.map-overlay-guard {
  position: absolute;
  inset: 0;
  pointer-events: none;
  box-shadow: inset 0 10px 20px rgba(0,0,0,0.1);
}

/* Responsive */
@media (max-width: 991px) {
  .contact-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 768px) {
  .contact-main {
    padding: 3rem 0;
  }

  .contact-form-card {
    padding: 1.75rem;
  }

  .action-cards {
    grid-template-columns: 1fr;
  }

  .map-section {
    height: 300px;
  }
}
</style>
