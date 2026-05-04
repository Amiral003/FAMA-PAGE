<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

const router = useRouter()

const isMenuOpen = ref(false)
const isDropdownOpen = ref(false)
const isCommuniqueOpen = ref(false)

// 👉 fermeture au clic extérieur (IMPORTANT)
const handleClickOutside = (e) => {
  if (!e.target.closest('.dropdown')) {
    isCommuniqueOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
})

// -------------------- NAV --------------------
const navItems = [
  { label: 'Accueil', to: '/' },
  { label: 'Com-Ops', to: '/com-ops' },
  { label: 'FAMa FM', href: 'https://stream.zeno.fm/wb3sj5pqu55tv' },
  { label: 'FAMa TV', href: 'https://www.youtube.com/@DIRPAFAMa' },
]

const contactItem = { label: 'Contact', to: '/contact' }
const aboutItem = { label: 'À propos', to: '/about' }

// -------------------- THEME --------------------
const theme = ref('light')
const isDark = computed(() => theme.value === 'dark')

const applyThemeToDom = (value) => {
  document.documentElement.classList.toggle('dark', value === 'dark')
}

const setTheme = (value) => {
  theme.value = value
  localStorage.setItem('public-theme', value)
  applyThemeToDom(value)
}

const toggleTheme = () => {
  setTheme(isDark.value ? 'light' : 'dark')
}

watch(theme, (v) => {
  localStorage.setItem('public-theme', v)
  applyThemeToDom(v)
})

onMounted(() => {
  const savedTheme = localStorage.getItem('public-theme')
  theme.value = savedTheme === 'dark' ? 'dark' : 'light'
  applyThemeToDom(theme.value)
})
</script>

<template>
  <nav class="navbar-tactical">
    <div class="mali-stripe">
      <div class="s-green"></div>
      <div class="s-yellow"></div>
      <div class="s-red"></div>
    </div>

    <div class="nav-container">
      <div class="nav-content">
        <div class="logo-box" @click="router.push('/')">
          <span class="fama">FAM<span class="gold">a</span></span>
          <span class="sub">Forces Armées Maliennes</span>
        </div>

        <!-- PC -->
        <ul class="nav-links">
          <li v-for="item in navItems" :key="item.to || item.href">
            <router-link v-if="item.to" :to="item.to">
              {{ item.label }}
            </router-link>

            <a v-else :href="item.href" target="_blank" rel="noopener noreferrer">
              {{ item.label }}
            </a>
          </li>

          <!-- ✅ COMMUNIQUÉS FIX -->
          <li class="dropdown">
            <button
              class="dropdown-btn"
              @click.stop="isCommuniqueOpen = !isCommuniqueOpen"
            >
              Communiqués
              <i :class="isCommuniqueOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
            </button>

            <transition name="fade-slide">
              <div v-if="isCommuniqueOpen" class="dropdown-menu" @click.stop>
                <router-link to="/portfolio" class="dropdown-item" @click="isCommuniqueOpen = false">
                  Actualité
                </router-link>

                <router-link to="/recrutement" class="dropdown-item" @click="isCommuniqueOpen = false">
                  Recrutement
                </router-link>
              </div>
            </transition>
          </li>

          <li>
            <router-link :to="contactItem.to">{{ contactItem.label }}</router-link>
          </li>

          <li>
            <router-link :to="aboutItem.to">{{ aboutItem.label }}</router-link>
          </li>

          <li class="nav-tools">
            <Button
              :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
              class="theme-btn"
              text
              @click="toggleTheme"
            />
          </li>
        </ul>

        <!-- MOBILE -->
        <div class="right-actions">
          <div class="mini-tools">
            <Button
              :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
              class="theme-btn"
              text
              @click="toggleTheme"
            />
          </div>

          <Button icon="pi pi-bars" class="menu-mobile-btn" text @click="isMenuOpen = true" />
        </div>
      </div>
    </div>

    <!-- MOBILE MENU -->
    <div class="mobile-wrapper" :class="{ 'is-active': isMenuOpen }" @click="isMenuOpen = false">
      <div class="side-menu" @click.stop>

        <div class="menu-header">
          <div class="logo-box mobile-logo" @click="router.push('/'); isMenuOpen = false">
            <span class="fama">FAM<span class="gold">a</span></span>
            <span class="sub">Forces Armées Maliennes</span>
          </div>

          <Button icon="pi pi-times" class="close-btn" text @click="isMenuOpen = false" />
        </div>

        <div class="mobile-tools">
          <Button
            :label="isDark ? 'Mode sombre' : 'Mode clair'"
            :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
            class="mobile-tool-btn"
            @click="toggleTheme"
          />
        </div>

        <div class="menu-items">

          <template v-for="item in navItems" :key="item.to || item.href">
            <router-link
              v-if="item.to"
              :to="item.to"
              class="mobile-link"
              @click="isMenuOpen = false"
            >
              {{ item.label }}
            </router-link>

            <a
              v-else
              :href="item.href"
              target="_blank"
              rel="noopener noreferrer"
              class="mobile-link"
              @click="isMenuOpen = false"
            >
              {{ item.label }}
            </a>
          </template>

          <!-- ✅ MOBILE COMMUNIQUÉS -->
          <div class="mobile-link mobile-dropdown-trigger" @click="isDropdownOpen = !isDropdownOpen">
            <span>Communiqués</span>
            <i :class="isDropdownOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
          </div>

          <transition name="fade-slide">
            <div v-if="isDropdownOpen" class="mobile-sub-menu">
              <router-link to="/portfolio" class="sub-link" @click="isMenuOpen = false">
                Actualité
              </router-link>

              <router-link to="/recrutement" class="sub-link" @click="isMenuOpen = false">
                Recrutement
              </router-link>
            </div>
          </transition>

          <router-link :to="aboutItem.to" class="mobile-link" @click="isMenuOpen = false">
            {{ aboutItem.label }}
          </router-link>

          <router-link :to="contactItem.to" class="mobile-link" @click="isMenuOpen = false">
            {{ contactItem.label }}
          </router-link>
        </div>

        <div class="menu-footer">
          <p>SÉCURITÉ - UNITÉ - SOUVERAINETÉ</p>
        </div>

      </div>
    </div>
  </nav>
</template>

<style scoped>
* {
  box-sizing: border-box;
}

/* --- NAVBAR GLOBALE --- */
.navbar-tactical {
  width: 100%;
  position: sticky;
  top: 0;
  z-index: 1000;
  background: #1a241b;
  border-bottom: 1px solid rgba(255, 215, 0, 0.2);
}

:global(html.has-ticker) .navbar-tactical {
  top: 40px;
}

.mali-stripe {
  display: flex;
  height: 4px;
}

.s-green {
  background: #14b82c;
  flex: 1;
}

.s-yellow {
  background: #ffd700;
  flex: 1;
}

.s-red {
  background: #ce1126;
  flex: 1;
}

.nav-container {
  max-width: 1320px;
  margin: 0 auto;
  padding: 0 24px;
}

.nav-content {
  display: flex;
  justify-content: space-between;
  align-items: center;
  min-height: 78px;
  gap: 16px;
}

.logo-box {
  display: flex;
  flex-direction: column;
  gap: 4px;
  cursor: pointer;
  user-select: none;
  min-width: 0;
}

.fama {
  font-size: 1.8rem;
  font-weight: 900;
  color: #ffd700;
  letter-spacing: 2px;
  line-height: 1;
}

.gold {
  color: #ffd700;
}

.sub {
  display: block;
  font-size: 0.62rem;
  color: #cbd5e1;
  letter-spacing: 2.4px;
  line-height: 1.2;
}

/* --- NAV PC --- */
.nav-links {
  display: flex;
  gap: 28px;
  list-style: none;
  align-items: center;
  margin: 0;
  padding: 0;
}

.nav-links a {
  color: #cbd5e1;
  text-decoration: none;
  font-weight: 800;
  font-size: 0.84rem;
  text-transform: uppercase;
  transition: 0.25s ease;
  letter-spacing: 0.04em;
}

.nav-links a:hover,
.nav-links a.router-link-exact-active {
  color: #ffd700;
}

/* --- TOOLS --- */
.nav-tools {
  display: flex;
  align-items: center;
}

.theme-btn {
  color: #ffd700 !important;
}

:deep(.theme-btn.p-button) {
  width: 42px;
  height: 42px;
  border-radius: 999px;
}

/* --- ACTIONS RIGHT --- */
.right-actions {
  display: flex;
  gap: 10px;
  align-items: center;
}

.mini-tools {
  display: none;
  align-items: center;
  gap: 8px;
}

.top-mobile-btn {
  margin: 0;
}

.menu-mobile-btn {
  display: none;
  color: #ffd700 !important;
}

:deep(.menu-mobile-btn.p-button) {
  width: 46px;
  height: 46px;
  border-radius: 12px;
}

/* --- MOBILE WRAPPER --- */
.mobile-wrapper {
  display: none;
}

.side-menu {
  position: absolute;
  right: -360px;
  top: 0;
  width: min(88vw, 340px);
  height: 100%;
  background: #1e2a1f;
  transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
  box-shadow: -12px 0 30px rgba(0, 0, 0, 0.35);
}

.mobile-wrapper.is-active .side-menu {
  right: 0;
}

.mobile-wrapper {
  position: fixed;
  inset: 0;
  z-index: 2000;
  visibility: hidden;
  opacity: 0;
  transition: 0.3s ease;
  background: rgba(0, 0, 0, 0.72);
}

.mobile-wrapper.is-active {
  visibility: visible;
  opacity: 1;
}

.menu-header {
  padding: 20px 18px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.1);
}

.mobile-logo .fama {
  font-size: 1.6rem;
}

.mobile-logo .sub {
  font-size: 0.58rem;
  letter-spacing: 1.8px;
}

.close-btn {
  color: #ffd700 !important;
  flex-shrink: 0;
}

:deep(.close-btn.p-button) {
  width: 42px;
  height: 42px;
  border-radius: 12px;
}

.mobile-tools {
  padding: 16px 16px 18px;
  display: flex;
  flex-direction: column;
  gap: 14px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.1);
}

.mobile-tool-btn {
  background: rgba(255, 215, 0, 0.12) !important;
  border: 1px solid rgba(255, 215, 0, 0.3) !important;
  color: #ffd700 !important;
  font-weight: 800 !important;
  min-height: 46px !important;
  border-radius: 12px !important;
}

.menu-items {
  padding: 10px 0 12px;
  overflow-y: auto;
  flex: 1;
}

.mobile-link {
  padding: 16px 18px;
  color: #fff;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 800;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: 0.2s ease;
  border-left: 3px solid transparent;
}

.mobile-link:hover,
.mobile-link.router-link-exact-active {
  background: rgba(255, 215, 0, 0.06);
  border-left-color: #ffd700;
  color: #ffd700;
}

.mobile-dropdown-trigger i {
  font-size: 0.85rem;
}

.mobile-sub-menu {
  background: rgba(0, 0, 0, 0.2);
  padding: 6px 0 10px;
}

.sub-link {
  display: block;
  padding: 13px 18px 13px 28px;
  color: #cbd5e1;
  text-decoration: none;
  font-size: 0.94rem;
  border-left: 2px solid rgba(255, 215, 0, 0.28);
  margin-left: 18px;
  transition: 0.2s ease;
}

.sub-link:hover,
.sub-link.router-link-active {
  color: #ffd700;
  background: rgba(255, 215, 0, 0.05);
}

.menu-footer {
  margin-top: auto;
  padding: 18px 16px 20px;
  text-align: center;
  color: #8a938d;
  font-size: 0.65rem;
  border-top: 1px solid rgba(255, 215, 0, 0.1);
  letter-spacing: 0.14em;
  line-height: 1.6;
}

/* --- ANIMATIONS --- */
.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

/* --- RESPONSIVE --- */
@media (max-width: 1100px) {
  .nav-links {
    gap: 20px;
  }

  .nav-links a {
    font-size: 0.8rem;
  }
}

@media (max-width: 992px) {
  .nav-container {
    padding: 0 16px;
  }

  .nav-content {
    min-height: 72px;
  }

  .nav-links {
    display: none !important;
  }

  .menu-mobile-btn {
    display: inline-flex;
  }

  .mini-tools {
    display: flex;
  }

  .mobile-wrapper {
    display: block;
  }

  .fama {
    font-size: 1.55rem;
  }

  .sub {
    font-size: 0.56rem;
    letter-spacing: 1.8px;
  }
}

@media (max-width: 576px) {
  .nav-container {
    padding: 0 12px;
  }

  .nav-content {
    min-height: 68px;
    gap: 10px;
  }

  .fama {
    font-size: 1.35rem;
    letter-spacing: 1.4px;
  }

  .sub {
    font-size: 0.5rem;
    letter-spacing: 1.2px;
    margin-top: -2px;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 42px;
    height: 42px;
  }

  .side-menu {
    width: min(92vw, 320px);
  }

  .menu-header {
    padding: 16px 14px;
  }

  .mobile-tools {
    padding: 14px;
  }

  .mobile-link {
    padding: 15px 14px;
    font-size: 0.98rem;
  }

  .sub-link {
    padding: 12px 14px 12px 22px;
    margin-left: 14px;
    font-size: 0.9rem;
  }
}

@media (max-width: 380px) {
  .nav-content {
    min-height: 64px;
  }

  .fama {
    font-size: 1.2rem;
  }

  .sub {
    font-size: 0.45rem;
    letter-spacing: 0.9px;
  }

  .mini-tools {
    gap: 6px;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 40px;
    height: 40px;
  }
}

.dropdown {
  position: relative;
}

.dropdown-btn {
  display: flex;
  align-items: center;
  gap: 6px;
  background: transparent;
  border: none;
  color: #cbd5e1;
  font-weight: 800;
  font-size: 0.84rem;
  cursor: pointer;
  text-transform: uppercase;
  transition: 0.25s ease;
  letter-spacing: 0.04em;
}

.dropdown-btn:hover {
  color: #ffd700;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 10px);
  left: 0;
  width: 200px;
  background: #243125;
  border-radius: 12px;
  border: 1px solid rgba(255, 215, 0, 0.2);
  overflow: hidden;
  z-index: 3000;
  box-shadow: 0 12px 18px rgba(0, 0, 0, 0.35);
}

.dropdown-item {
  display: block;
  padding: 12px 14px;
  color: #e2e8f0 !important;
  text-decoration: none;
  text-transform: none !important;
  font-size: 0.92rem !important;
  letter-spacing: 0 !important;
}

.dropdown-item:hover,
.dropdown-item.router-link-exact-active {
  background: rgba(255, 215, 0, 0.1);
  color: #ffd700 !important;
}
</style>