<template>
  <div class="about-page">
    <HeroSection
      title="CASA VÉRA"
      subtitle="The Art of Premium Living"
      tagline="Crafting Excellence in Every Detail"
      size="large"
    />

    <section class="story-section">
      <div class="section-container">
        <div class="story-grid">
          <div class="story-content rise-up">
            <span class="badge-elegant">Our Journey</span>
            <h2 class="story-title">The CASA VÉRA Story</h2>
            <p class="story-lead">Founded on the belief that luxury furniture and premium products should be accessible to everyone, CASA VÉRA emerged as a beacon of elegance and quality in the world of contemporary living.</p>
            <p class="story-text">What started as a passion project to curate the finest furniture pieces has blossomed into a globally recognized brand. We've spent years perfecting our craft, building relationships with master artisans and designers, and sourcing materials from the most reputable suppliers around the world.</p>
            <p class="story-text">Today, CASA VÉRA stands as a testament to the fusion of tradition and innovation, offering our discerning clients an unparalleled selection of timeless pieces that elevate any space into a sanctuary of style and comfort.</p>
            <router-link to="/products" class="btn-primary">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
                <line x1="3" y1="6" x2="21" y2="6"/>
                <path d="M16 10a4 4 0 0 1-8 0"/>
              </svg>
              Explore Our Collection
            </router-link>
          </div>
          <div class="story-stats rise-up-delay-1">
            <div class="stats-grid">
              <div class="stat-card" v-for="(stat, index) in stats" :key="stat.label" :class="`rise-up-delay-${Math.min(index + 2, 5)}`">
                <div class="stat-number" ref="statRefs">
                  <span class="stat-value">{{ stat.displayValue }}</span>
                  <span v-if="stat.suffix" class="stat-suffix">{{ stat.suffix }}</span>
                </div>
                <div class="stat-label">{{ stat.label }}</div>
                <p class="stat-desc">{{ stat.desc }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="mission-section">
      <div class="section-container">
        <div class="section-header rise-up">
          <span class="badge-elegant">Our Values</span>
          <h2 class="section-title">Mission & Vision</h2>
          <p class="section-desc">Our commitment to excellence guides every decision we make</p>
        </div>
        <div class="mission-grid">
          <div class="mission-card">
            <div class="mission-icon">
              <svg viewBox="0 0 512 512" fill="currentColor">
                <path d="M464 256A208 208 0 1 0 48 256a208 208 0 1 0 416 0zM0 256a256 256 0 1 1 512 0A256 256 0 1 1 0 256zm306.7-101.3L182.2 194.2 142.7 318.8l124.5-39.5 39.5-124.5zM256 272a16 16 0 1 0 0-32 16 16 0 1 0 0 32z"/>
              </svg>
            </div>
            <h3 class="mission-title">Our Mission</h3>
            <p class="mission-text">To revolutionize the way people discover and purchase premium furniture and lifestyle products by combining exceptional quality with uncompromising customer service.</p>
            <ul class="mission-list">
              <li><strong>Quality First:</strong> Rigorous standards</li>
              <li><strong>Customer Centric:</strong> Your satisfaction priority</li>
              <li><strong>Sustainability:</strong> Ethical sourcing</li>
              <li><strong>Innovation:</strong> Constantly evolving</li>
            </ul>
          </div>
          <div class="mission-card">
            <div class="mission-icon">
              <svg viewBox="0 0 576 512" fill="currentColor">
                <path d="M288 32c-80.8 0-145.5 36.8-192.6 80.6C48.6 156 17.3 208 2.5 243.7c-3.3 7.9-3.3 16.7 0 24.6C17.3 304 48.6 356 95.4 399.4C142.5 443.2 207.2 480 288 480s145.5-36.8 192.6-80.6c46.8-43.5 78.1-95.4 93-131.1c3.3-7.9 3.3-16.7 0-24.6c-14.9-35.7-46.2-87.7-93-131.1C433.5 68.8 368.8 32 288 32zM144 256a144 144 0 1 1 288 0 144 144 0 1 1 -288 0zm144-64c0 35.3-28.7 64-64 64c-7.1 0-13.9-1.2-20.3-3.3c-5.5-1.8-11.9 1.6-11.7 7.4c.3 6.9 1.3 13.8 3.2 20.7c13.7 51.2 66.4 81.6 117.6 67.9s81.6-66.4 67.9-117.6c-11.1-41.5-47.8-69.4-88.6-71.1c-5.8-.2-9.2 6.1-7.4 11.7c2.1 6.4 3.3 13.2 3.3 20.3z"/>
              </svg>
            </div>
            <h3 class="mission-title">Our Vision</h3>
            <p class="mission-text">To become the world's most trusted luxury furniture and lifestyle brand, where elegance meets affordability, and where every customer feels valued and celebrated.</p>
            <ul class="mission-list vision">
              <li><strong>Global Excellence:</strong> Recognized worldwide</li>
              <li><strong>Design Innovation:</strong> Setting trends</li>
              <li><strong>Community:</strong> Design enthusiasts</li>
              <li><strong>Legacy:</strong> Creating timeless pieces</li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <section class="advantages-section">
      <div class="section-container">
        <div class="section-header rise-up">
          <span class="badge-elegant">Our Advantages</span>
          <h2 class="section-title">Why Choose CASA VÉRA?</h2>
        </div>
        <div class="advantages-grid">
          <div class="advantage-card" v-for="(advantage, index) in advantages" :key="advantage.title" :class="`rise-up-delay-${Math.min(index + 1, 5)}`">
            <div class="advantage-icon" v-html="advantage.icon"></div>
            <h4 class="advantage-title">{{ advantage.title }}</h4>
            <p class="advantage-desc">{{ advantage.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="testimonials-section">
      <div class="section-container">
        <div class="section-header rise-up">
          <span class="badge-elegant">Customer Love</span>
          <h2 class="section-title">What Our Customers Say</h2>
        </div>
        <div class="testimonials-grid">
          <div class="testimonial-card" v-for="(testimonial, index) in testimonials" :key="testimonial.name" :class="`rise-up-delay-${Math.min(index + 1, 5)}`">
            <div class="testimonial-stars">
              <svg v-for="i in 5" :key="i" viewBox="0 0 24 24" fill="currentColor">
                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
              </svg>
            </div>
            <p class="testimonial-text">"{{ testimonial.text }}"</p>
            <div class="testimonial-author">
              <strong>{{ testimonial.name }}</strong>
              <span>{{ testimonial.role }}</span>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="team-section">
      <div class="section-container">
        <div class="section-header rise-up">
          <span class="badge-elegant">The Minds</span>
          <h2 class="section-title">Meet the Creators</h2>
          <p class="section-desc">The passionate team architecting the digital luxury of CASA VÉRA.</p>
        </div>
        <div class="team-grid">
          <div
            class="team-card"
            v-for="(member, index) in team"
            :key="member.name"
            :class="{ 'lead-card': member.isLead, [`rise-up-delay-${Math.min(index + 1, 5)}`]: true }"
          >
            <div class="team-img-wrapper">
              <img :src="member.img" :alt="member.name" class="team-img">
            </div>
            <span class="team-role" :class="{ 'text-gold': member.isLead }">{{ member.role }}</span>
            <h5 class="team-name">{{ member.name }}</h5>
            <p class="team-desc">{{ member.desc }}</p>
          </div>
        </div>
      </div>
    </section>

    <section class="cta-section">
      <div class="section-container">
        <h2 class="cta-title rise-up">Ready to Elevate Your Space?</h2>
        <p class="cta-desc rise-up-delay-1">Explore our curated collection of premium furniture and lifestyle products.</p>
        <div class="cta-buttons rise-up-delay-2">
          <router-link to="/products" class="btn-gold">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
              <line x1="3" y1="6" x2="21" y2="6"/>
              <path d="M16 10a4 4 0 0 1-8 0"/>
            </svg>
            Shop Now
          </router-link>
          <router-link to="/contact" class="btn-outline">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            Get in Touch
          </router-link>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import HeroSection from '@/components/HeroSection.vue'

import regoyaImg from '@/assets/images/regoya.png'
import tumolingImg from '@/assets/images/tumoling.png'
import roncalesImg from '@/assets/images/roncales.png'
import javierImg from '@/assets/images/javier.png'
import sapuntoImg from '@/assets/images/sapunto.jpg'

const stats = ref([
  { value: 3, displayValue: 0, label: 'Years of Excellence', desc: 'Building trust through quality', suffix: '' },
  { value: 50, displayValue: 0, label: 'Happy Customers', desc: 'Across 80+ countries', suffix: 'K+' },
  { value: 1000, displayValue: 0, label: 'Premium Products', desc: 'Handpicked artisans', suffix: '+' },
  { value: 99, displayValue: 0, label: 'Satisfaction', desc: 'Excellence committed', suffix: '%' }
])

let hasAnimatedStats = false

const animateStats = () => {
  if (hasAnimatedStats) return
  hasAnimatedStats = true

  stats.value.forEach(stat => {
    const duration = 2000
    const increment = stat.value / (duration / 16)
    let current = 0

    const updateCount = () => {
      current += increment
      if (current < stat.value) {
        stat.displayValue = Math.ceil(current)
        requestAnimationFrame(updateCount)
      } else {
        stat.displayValue = stat.value
      }
    }
    updateCount()
  })
}

const advantages = ref([
  { icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>', title: 'Premium Quality', desc: 'Meticulously crafted using the finest materials and techniques.' },
  { icon: '<svg viewBox="0 0 384 512" fill="currentColor"><path d="M384 312.7c-55.1 136.7-187.1 54-187.1 54-40.5 81.8-107.4 134.4-184.6 134.7-16.1 0-16.6-24.4 0-24.4 64.4-.3 120.5-42.7 157.2-110.1-41.1 15.9-118.6 27.9-161.6-82.2 109-44.9 159.1 11.2 178.3 45.5 9.9-24.4 17-50.9 21.6-79.7 0 0-139.7 21.9-149.5-98.1 119.1-47.9 152.6 76.7 152.6 76.7 1.6-18.7 2.5-37.9 2.5-57.7 0-24.1-2.5-46.8-7.2-67.5C201 6.4 196.6 2.6 192 2.9c-4.6-.3-9 3.5-14.8 25.8-4.7 20.7-7.2 43.4-7.2 67.5 0 19.8 .9 39 2.5 57.7 0 0 33.5-124.6 152.6-76.7-9.8 120-149.5 98.1-149.5 98.1 4.6 28.8 11.7 55.3 21.6 79.7 19.2-34.3 69.3-90.4 178.3-45.5-43 110.1-120.5 98.1-161.6 82.2 36.7 67.4 92.8 109.8 157.2 110.1 16.6 0 16.1 24.4 0 24.4-77.2-.3-144.1-52.9-184.6-134.7 0 0-132 82.7-187.1-54 115.9-99.2 159.1 14.9 162.4 23 3.4-8.1 46.5-122.2 162.4-23z"/></svg>', title: 'Sustainable Practice', desc: 'Partnering with eco-conscious suppliers and manufacturers.' },
  { icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 18H3c-.6 0-1-.4-1-1V7c0-.6.4-1 1-1h10c.6 0 1 .4 1 1v11"/><path d="M14 9h4l4 4v4c0 .6-.4 1-1 1h-2"/><circle cx="7" cy="18" r="2"/><path d="M9 18h5"/><circle cx="16" cy="18" r="2"/></svg>', title: 'Fast & Free Shipping', desc: 'Complimentary worldwide shipping with express delivery options.' },
  { icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2Z"/></svg>', title: 'Award-Winning Design', desc: 'Curated collections featuring recognized designs worldwide.' },
  { icon: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/><path d="M21 3v5h-5"/><path d="M12 7v5l3 3"/></svg>', title: 'Money-Back Guarantee', desc: 'Return any item within 60 days for a full refund.' },
  { icon: '<svg viewBox="0 0 512 512" fill="currentColor"><path d="M256 48C141.1 48 48 141.1 48 256l0 40c0 13.3-10.7 24-24 24s-24-10.7-24-24l0-40C0 114.6 114.6 0 256 0S512 114.6 512 256l0 144.1c0 46.9-38 84.9-84.9 84.9L352 485c0 14.9-12.1 27-27 27l-66 0c-14.9 0-27-12.1-27-27s12.1-27 27-27l66 0 0-64 75.1 0c17 0 30.9-13.9 30.9-30.9L432 256c0-97.2-78.8-176-176-176zM144 208l0 48c0 44.2 35.8 80 80 80l16 0c17.7 0 32-14.3 32-32l0-112c0-17.7-14.3-32-32-32l-48 0c-26.5 0-48 21.5-48 48zm224-48l-48 0c-17.7 0-32 14.3-32 32l0 112c0 17.7 14.3 32 32 32l16 0c44.2 0 80-35.8 80-80l0-48c0-26.5-21.5-48-48-48z"/></svg>', title: 'Expert Support 24/7', desc: 'Our dedicated team of specialists is always available.' }
])

const testimonials = ref([
  { name: 'Sarah Mitchell', role: 'Interior Designer, New York', text: 'CASA VÉRA transformed my living space into a sanctuary. The quality is exceptional and the attention to detail is remarkable.' },
  { name: 'James Richardson', role: 'Homeowner, London', text: 'The customer service was outstanding. From selection to delivery, everything was smooth. My sofa is a masterpiece!' },
  { name: 'David Park', role: 'Real Estate Developer, Seoul', text: 'From browsing to delivery, the entire experience was luxurious. CASA VÉRA has set a new standard for online furniture shopping!' }
])

const team = ref([
  { name: 'Elmer Roncales', role: 'UI/UX Developer', img: roncalesImg, desc: 'Designs clean, user-focused interfaces with attention to detail.', isLead: false },
  { name: 'Jonathan Javier', role: 'Full Stack Dev', img: javierImg, desc: 'Develops efficient systems that bring design and functionality together.', isLead: false },
  { name: 'Jerome Regoya', role: 'Lead Developer', img: regoyaImg, desc: 'Leads development and ensures technical quality across the platform.', isLead: true },
  { name: 'Mike Yuan Tumoling', role: 'Full Stack Dev', img: tumolingImg, desc: 'Builds scalable solutions with a focus on performance and reliability.', isLead: false },
  { name: 'Rhoniell Sapunto', role: 'UI/UX Developer', img: sapuntoImg, desc: 'Crafts intuitive and refined digital experiences.', isLead: false }
])

let statsObserver: IntersectionObserver | null = null

onMounted(() => {
  const statsSection = document.querySelector('.story-stats')
  if (statsSection) {
    statsObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          animateStats()
        }
      })
    }, { threshold: 0.5 })
    statsObserver.observe(statsSection)
  }
})

onUnmounted(() => {
  if (statsObserver) statsObserver.disconnect()
})
</script>

<style scoped>
.about-page {
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

/* Section Container */
.section-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 3rem;
}

.section-header {
  text-align: center;
  margin-bottom: 3rem;
}

.section-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.75rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0.75rem 0 0.5rem;
}

.section-desc {
  color: var(--gray);
  font-size: 1rem;
  max-width: 600px;
  margin: 0 auto;
}

/* Badge */
.badge-elegant {
  display: inline-block;
  padding: 8px 16px;
  border: 1px solid var(--gold);
  color: var(--gold-dark);
  background: rgba(255, 215, 0, 0.05);
  border-radius: 50px;
  font-size: 0.7rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 2px;
}

/* Story Section */
.story-section {
  padding: 5rem 0;
  background: var(--white);
}

.story-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 5rem;
  align-items: center;
}

.story-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.75rem;
  font-weight: 600;
  color: var(--dark);
  margin: 1rem 0 1.75rem;
}

.story-lead {
  font-size: 1.2rem;
  color: var(--gray);
  line-height: 1.8;
  margin-bottom: 1.5rem;
}

.story-text {
  font-size: 1.05rem;
  color: #888;
  line-height: 1.8;
  margin-bottom: 1.25rem;
}

.btn-primary {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 1.5rem;
  padding: 0.875rem 1.75rem;
  background: var(--gold);
  color: var(--dark);
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-primary:hover {
  background: var(--gold-dark);
  color: var(--white);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(201, 160, 80, 0.3);
}

.btn-primary svg {
  width: 18px;
  height: 18px;
}

/* Stats Grid */
.stats-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 2rem;
}

.stat-card {
  background: var(--white);
  padding: 2.5rem 2rem;
  border-radius: 20px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
  text-align: center;
  transition: all 0.4s ease;
  min-height: 180px;
  display: flex;
  flex-direction: column;
  justify-content: center;
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 50px rgba(0,0,0,0.12);
  border-color: rgba(255, 215, 0, 0.4);
}

.stat-number {
  font-family: 'Playfair Display', serif;
  font-size: 3.5rem;
  font-weight: 700;
  color: var(--gold);
  line-height: 1;
  margin-bottom: 0.75rem;
}

.stat-suffix {
  font-size: 3rem;
}

.stat-label {
  font-weight: 700;
  text-transform: uppercase;
  font-size: 0.9rem;
  letter-spacing: 1.5px;
  color: var(--dark);
  margin-bottom: 0.5rem;
}

.stat-desc {
  font-size: 0.9rem;
  color: var(--gray);
  margin: 0;
}

/* Mission Section */
.mission-section {
  padding: 5rem 0;
  background: var(--light);
}

.mission-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 3rem;
}

.mission-card {
  background: var(--white);
  padding: 2.5rem;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
  text-align: center;
  transition: all 0.4s ease;
}

.mission-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
  border-color: rgba(255, 215, 0, 0.3);
}

.mission-icon {
  width: 80px;
  height: 80px;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(201, 160, 80, 0.08));
  border-radius: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 1.75rem;
  border: 1px solid rgba(255, 215, 0, 0.2);
  transition: all 0.4s ease;
}

.mission-card:hover .mission-icon {
  transform: scale(1.05) rotate(3deg);
  box-shadow: 0 15px 40px rgba(201, 160, 80, 0.2);
  border-color: var(--gold);
}

.mission-icon svg {
  width: 40px;
  height: 40px;
  color: var(--gold);
  transition: all 0.3s ease;
}

.mission-card:hover .mission-icon svg {
  transform: scale(1.1);
}

.mission-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.5rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 1rem;
}

.mission-text {
  color: var(--gray);
  line-height: 1.7;
  margin-bottom: 1.5rem;
}

.mission-list {
  list-style: none;
  padding: 0;
  text-align: left;
}

.mission-list li {
  margin-bottom: 0.625rem;
  color: #555;
  font-size: 0.9rem;
}

.mission-list li::before {
  content: '✓';
  color: var(--gold);
  margin-right: 0.5rem;
  font-weight: bold;
}

.mission-list.vision li::before {
  content: '★';
}

/* Advantages Section */
.advantages-section {
  padding: 5rem 0;
  background: var(--white);
}

.advantages-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 2rem;
}

.advantage-card {
  background: var(--white);
  padding: 2rem;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
  text-align: center;
  transition: all 0.4s ease;
}

.advantage-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
  border-color: rgba(255, 215, 0, 0.3);
}

.advantage-icon {
  width: 70px;
  height: 70px;
  background: linear-gradient(135deg, rgba(255, 215, 0, 0.15), rgba(201, 160, 80, 0.1));
  border-radius: 20px;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 1.5rem;
  transition: all 0.4s ease;
  border: 1px solid rgba(255, 215, 0, 0.2);
}

.advantage-icon :deep(svg) {
  width: 32px;
  height: 32px;
  color: var(--gold);
  transition: all 0.3s ease;
}

.advantage-card:hover .advantage-icon {
  transform: scale(1.1) rotate(5deg);
  background: linear-gradient(135deg, var(--gold), var(--gold-dark));
  border-color: var(--gold);
  box-shadow: 0 10px 30px rgba(201, 160, 80, 0.3);
}

.advantage-card:hover .advantage-icon :deep(svg) {
  color: var(--white);
  transform: scale(1.1);
}

.advantage-title {
  font-family: 'Playfair Display', serif;
  font-size: 1.1rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.advantage-desc {
  font-size: 0.85rem;
  color: var(--gray);
  margin: 0;
  line-height: 1.6;
}

/* Testimonials Section */
.testimonials-section {
  padding: 5rem 0;
  background: var(--white);
}

.testimonials-grid {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  gap: 1.5rem;
}

.testimonial-card {
  background: var(--white);
  padding: 2rem;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 4px 20px rgba(0,0,0,0.04);
  transition: all 0.4s ease;
}

.testimonial-card:hover {
  transform: translateY(-6px);
  box-shadow: 0 15px 40px rgba(0,0,0,0.1);
  border-color: rgba(255, 215, 0, 0.3);
}

.testimonial-stars {
  display: flex;
  gap: 0.25rem;
  margin-bottom: 1rem;
}

.testimonial-stars svg {
  width: 16px;
  height: 16px;
  color: #ffc107;
}

.testimonial-text {
  font-style: italic;
  color: var(--gray);
  line-height: 1.8;
  margin: 0 0 1.25rem;
}

.testimonial-author {
  border-top: 1px solid #eee;
  padding-top: 1rem;
}

.testimonial-author strong {
  display: block;
  font-family: 'Playfair Display', serif;
  color: var(--dark);
  font-size: 0.95rem;
}

.testimonial-author span {
  font-size: 0.8rem;
  color: var(--gray);
}

/* Team Section */
.team-section {
  padding: 5rem 0;
  background: var(--white);
}

.team-grid {
  display: flex;
  justify-content: center;
  align-items: center;
  flex-wrap: wrap;
  gap: 2rem;
}

.team-card {
  background: var(--white);
  padding: 2rem 1.5rem;
  border-radius: 16px;
  border: 1px solid rgba(0,0,0,0.05);
  box-shadow: 0 10px 30px rgba(0,0,0,0.05);
  text-align: center;
  flex: 1 1 220px;
  max-width: 260px;
  transition: all 0.5s cubic-bezier(0.25, 0.8, 0.25, 1);
  position: relative;
  z-index: 1;
}

.team-card:hover {
  transform: translateY(-12px) scale(1.03);
  box-shadow: 0 20px 50px rgba(255, 215, 0, 0.2);
  border-color: var(--gold);
  z-index: 10;
}

.team-card.lead-card {
  transform: scale(1.08);
  border: 2px solid rgba(255, 215, 0, 0.3);
  box-shadow: 0 20px 50px rgba(0,0,0,0.1);
  z-index: 5;
  background: linear-gradient(to bottom, #fff 0%, #fffcf5 100%);
}

.team-card.lead-card:hover {
  transform: scale(1.12) translateY(-8px);
  box-shadow: 0 30px 70px rgba(255, 215, 0, 0.35);
}

.team-img-wrapper {
  width: 100px;
  height: 100px;
  margin: 0 auto 1.25rem;
}

.lead-card .team-img-wrapper {
  width: 120px;
  height: 120px;
}

.team-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 50%;
  border: 3px solid rgba(255, 215, 0, 0.1);
  padding: 3px;
  background: var(--white);
  transition: all 0.5s ease;
}

.team-card:hover .team-img {
  border-color: var(--gold);
  transform: rotate(5deg);
}

.team-role {
  display: block;
  color: var(--gold-dark);
  font-size: 0.65rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 1.5px;
  margin-bottom: 0.375rem;
}

.text-gold {
  color: var(--gold) !important;
}

.team-name {
  font-family: 'Playfair Display', serif;
  font-size: 1rem;
  font-weight: 600;
  color: var(--dark);
  margin: 0 0 0.5rem;
}

.lead-card .team-name {
  font-size: 1.15rem;
}

.team-desc {
  font-size: 0.75rem;
  color: var(--gray);
  line-height: 1.5;
  margin: 0;
}

/* CTA Section */
.cta-section {
  padding: 5rem 0;
  background: var(--light);
  text-align: center;
}

.cta-title {
  font-family: 'Playfair Display', serif;
  font-size: 2.5rem;
  font-weight: 600;
  color: var(--gold);
  margin: 0 0 1rem;
}

.cta-desc {
  color: var(--gray);
  font-size: 1.1rem;
  max-width: 550px;
  margin: 0 auto 2rem;
}

.cta-buttons {
  display: flex;
  justify-content: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.btn-gold, .btn-outline {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.875rem 1.75rem;
  border-radius: 50px;
  font-size: 0.85rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.3s ease;
}

.btn-gold {
  background: var(--gold);
  color: var(--dark);
}

.btn-gold:hover {
  background: var(--gold-dark);
  color: var(--white);
  transform: translateY(-2px);
  box-shadow: 0 10px 25px rgba(201, 160, 80, 0.3);
}

.btn-outline {
  background: transparent;
  border: 2px solid var(--dark);
  color: var(--dark);
}

.btn-outline:hover {
  background: var(--dark);
  color: var(--white);
  transform: translateY(-2px);
}

.btn-gold svg, .btn-outline svg {
  width: 18px;
  height: 18px;
}

/* Responsive */
@media (max-width: 991px) {
  .story-grid, .mission-grid {
    grid-template-columns: 1fr;
    gap: 3rem;
  }

  .advantages-grid, .testimonials-grid {
    grid-template-columns: repeat(2, 1fr);
  }

  .team-card.lead-card {
    transform: scale(1);
  }

  .team-card.lead-card:hover {
    transform: translateY(-10px);
  }
}

@media (max-width: 768px) {
  .advantages-grid, .testimonials-grid {
    grid-template-columns: 1fr;
  }

  .stats-grid {
    grid-template-columns: 1fr 1fr;
    gap: 1.25rem;
  }

  .stat-card {
    padding: 2rem 1.5rem;
    min-height: 160px;
  }

  .stat-number {
    font-size: 2.75rem;
  }

  .stat-suffix {
    font-size: 2.5rem;
  }

  .cta-title {
    font-size: 2rem;
  }

  .cta-buttons {
    flex-direction: column;
    align-items: center;
  }

  .btn-gold, .btn-outline {
    width: 100%;
    max-width: 280px;
    justify-content: center;
  }
}

@media (max-width: 576px) {
  .stats-grid {
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
  }

  .stat-card {
    padding: 1.5rem 1rem;
    min-height: 140px;
  }

  .stat-number {
    font-size: 2.25rem;
  }

  .stat-suffix {
    font-size: 2rem;
  }

  .stat-label {
    font-size: 0.75rem;
  }

  .stat-desc {
    font-size: 0.8rem;
  }
}
</style>
