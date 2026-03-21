<template>
  <div class="legal-page">
    <HeroSection
      title="Privacy Policy"
      subtitle="Your trust is our ultimate luxury. We are committed to protecting your personal information."
      size="large"
    />

    <section class="legal-content">
      <div class="legal-container">
        <div class="legal-section" v-for="(section, index) in sections" :key="index" :class="`rise-up-delay-${Math.min(index + 1, 5)}`">
          <h3 class="section-heading">{{ section.title }}</h3>
          <p class="section-text" v-if="section.content">{{ section.content }}</p>
          <ul v-if="section.list" class="section-list">
            <li v-for="(item, i) in section.list" :key="i">
              <strong>{{ item.label }}:</strong> {{ item.text }}
            </li>
          </ul>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import HeroSection from '@/components/HeroSection.vue'

const sections = ref([
  {
    title: '1. Information We Collect',
    content: 'At CASA VÉRA, we collect information to provide you with a seamless luxury shopping experience. This includes:',
    list: [
      { label: 'Personal Identification', text: 'Name, email address, shipping address, and phone number when you place an order.' },
      { label: 'Payment Data', text: 'Secure payment credentials processed by our banking partners (we do not store full credit card numbers).' },
      { label: 'Browsing Data', text: 'Cookies and usage data to personalize your gallery experience.' }
    ]
  },
  {
    title: '2. How We Use Your Data',
    content: 'We use your information strictly to fulfill your orders, provide concierge support, and update you on exclusive collections. We honor your privacy by never selling your data to third-party marketing agencies.'
  },
  {
    title: '3. Security',
    content: 'We implement industry-standard encryption and security protocols to protect your personal data. Our digital infrastructure is monitored 24/7 to prevent unauthorized access.'
  },
  {
    title: '4. Cookies',
    content: 'Our website uses cookies to remember your preferences and cart items. You may choose to disable cookies in your browser settings, though this may affect your shopping experience.'
  }
])
</script>

<style scoped>
.legal-page {
  --gold: #c9a050;
  --gold-light: #d4af37;
  --dark: #1a1a1a;
  --white: #ffffff;

  display: flex;
  flex-direction: column;
  min-height: 100vh;
}

/* Content Section */
.legal-content {
  background: var(--white);
  padding: 5rem 0;
  flex: 1;
}

.legal-container {
  max-width: 960px;
  margin: 0 auto;
  padding: 0 3rem;
}

.legal-section {
  margin-bottom: 3rem;
  opacity: 0;
  transform: translateY(20px);
  animation: fadeUp 0.6s ease forwards;
}

.legal-section:nth-child(1) { animation-delay: 0.1s; }
.legal-section:nth-child(2) { animation-delay: 0.2s; }
.legal-section:nth-child(3) { animation-delay: 0.3s; }
.legal-section:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeUp {
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.section-heading {
  font-family: 'Playfair Display', serif;
  font-size: 1.75rem;
  font-weight: 600;
  color: var(--dark);
  margin-bottom: 1.25rem;
}

.section-text {
  color: #666;
  line-height: 1.9;
  font-size: 1.1rem;
}

.section-list {
  list-style: none;
  padding: 0;
  margin: 1.25rem 0 0;
}

.section-list li {
  color: #666;
  line-height: 1.9;
  font-size: 1.1rem;
  margin-bottom: 1rem;
  padding-left: 1.5rem;
  position: relative;
}

.section-list li::before {
  content: '•';
  position: absolute;
  left: 0;
  color: var(--gold);
  font-weight: bold;
}

.section-list li strong {
  color: var(--dark);
}

@media (max-width: 768px) {
  .legal-content {
    padding: 3rem 0;
  }

  .legal-container {
    padding: 0 1.5rem;
  }

  .section-heading {
    font-size: 1.4rem;
  }

  .section-text,
  .section-list li {
    font-size: 1rem;
  }
}
</style>
