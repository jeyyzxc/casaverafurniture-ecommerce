<template>
  <div class="legal-page">
    <!-- Hero Section -->
    <HeroSection
      title="Terms of Service"
      subtitle="Please read these terms carefully before utilizing our services."
      size="large"
    />

    <!-- Content Section -->
    <section class="legal-content">
      <div class="legal-container">
        <div class="legal-section" v-for="(section, index) in sections" :key="index" :class="`rise-up-delay-${Math.min(index + 1, 5)}`">
          <h3 class="section-heading">{{ section.title }}</h3>
          <p class="section-text" v-html="section.content"></p>
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
    title: '1. Agreement to Terms',
    content: 'By accessing our website and purchasing from CASA VÉRA, you agree to be bound by these Terms and Conditions. If you disagree with any part of these terms, you may not access the service.'
  },
  {
    title: '2. Products & Pricing',
    content: 'All products are subject to availability. We reserve the right to discontinue any product at any time. Prices for our products are subject to change without notice. We make every effort to display the colors and images of our products accurately, but cannot guarantee your monitor\'s display will be exact.'
  },
  {
    title: '3. Shipping & Delivery',
    content: 'Delivery times are estimates and start from the date of shipping, rather than the date of order. We are not responsible for delays caused by customs clearance or other unforeseen circumstances.'
  },
  {
    title: '4. Intellectual Property',
    content: 'The content, organization, graphics, design, and other matters related to the Site are protected under applicable copyrights and other proprietary laws. The copying, redistribution, use, or publication by you of any such matters is strictly prohibited.'
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

  .section-text {
    font-size: 1rem;
  }
}
</style>
