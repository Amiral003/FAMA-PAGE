<script setup>
import { ref, computed, onMounted, watch, onBeforeUnmount } from 'vue'
import { useRouter } from 'vue-router'
import Button from 'primevue/button'

const router = useRouter()

const isMenuOpen = ref(false)
const isDropdownOpen = ref(false)
const isCommuniqueOpen = ref(false)

const handleClickOutside = (e) => {
  if (!e.target.closest('.dropdown')) {
    isCommuniqueOpen.value = false
  }
}

const navItems = [
  { label: 'Com-Ops', to: '/com-ops' },
  { label: 'FAMa FM', href: 'https://stream.zeno.fm/wb3sj5pqu55tv' },
  { label: 'FAMa TV', href: 'https://www.youtube.com/@DIRPAFAMa' },
  { label: 'Contact', to: '/contact' },
  { label: 'À propos', to: '/about' },
]

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
  document.addEventListener('click', handleClickOutside)

  const savedTheme = localStorage.getItem('public-theme')
  theme.value = savedTheme === 'dark' ? 'dark' : 'light'
  applyThemeToDom(theme.value)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', handleClickOutside)
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

        <ul class="nav-links">
          <li>
            <router-link to="/">Accueil</router-link>
          </li>

          <li class="dropdown">
<button
  class="dropdown-btn"
  :class="{ 'is-active': isCommuniqueOpen }"
  @click.stop="isCommuniqueOpen = !isCommuniqueOpen"
>              <span>Communiqués</span>
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

          <li v-for="item in navItems" :key="item.to || item.href">
            <router-link v-if="item.to" :to="item.to">
              {{ item.label }}
            </router-link>

            <a v-else :href="item.href" target="_blank" rel="noopener noreferrer">
              {{ item.label }}
            </a>
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
          <router-link to="/" class="mobile-link" @click="isMenuOpen = false">
            Accueil
          </router-link>

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
        </div>

        <div class="menu-footer">
          <p>SÉCURITÉ - UNITÉ - SOUVERAINETÉ</p>
        </div>
      </div>
    </div>
  </nav>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700;800;900&display=swap');

* {
  box-sizing: border-box;
}

.navbar-tactical {
  width: 100%;
  position: sticky;
  top: 0;
  z-index: 1000;
  background: #1a241b;
  border-bottom: 1px solid rgba(255, 215, 0, 0.2);
  font-family: 'Montserrat', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
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
  font-size: 0.6rem;
  color: #cbd5e1;
  letter-spacing: 2.1px;
  line-height: 1.2;
  font-weight: 600;
}

.nav-links {
  display: flex;
  gap: 22px;
  list-style: none;
  align-items: center;
  margin: 0;
  padding: 0;
}

.nav-links li {
  display: flex;
  align-items: center;
}

.nav-links a,
.nav-links .dropdown-btn {
  min-height: 42px;
  display: inline-flex;
  align-items: center;
  color: #cbd5e1;
  text-decoration: none;
  font-family: inherit;
  font-weight: 600;
  font-size: 0.74rem;
  text-transform: uppercase;
  transition: color 0.25s ease;
  letter-spacing: 0.055em;
  line-height: 1;
  white-space: nowrap;
}

.nav-links .dropdown-btn span,
.nav-links .dropdown-btn i {
  color: inherit;
}

.nav-links a:hover,
.nav-links a.router-link-exact-active,
.nav-links .dropdown-btn:hover,
.nav-links .dropdown-btn.is-active {
  color: #ffd700;
}

.dropdown {
  position: relative;
}

.dropdown-btn {
  gap: 6px;
  background: transparent;
  border: none;
  cursor: pointer;
  padding: 0;
}

.dropdown-btn i {
  font-size: 0.64rem;
  line-height: 1;
  display: inline-flex;
  align-items: center;
}

.dropdown-menu {
  position: absolute;
  top: calc(100% + 16px);
  left: 0;
  width: 210px;
  background: #243125;
  border-radius: 14px;
  border: 1px solid rgba(255, 215, 0, 0.18);
  overflow: hidden;
  z-index: 3000;
  box-shadow: 0 18px 34px rgba(0, 0, 0, 0.35);
}

.dropdown-item {
  display: block;
  padding: 13px 15px;
  color: #e2e8f0 !important;
  text-decoration: none;
  text-transform: none !important;
  font-size: 0.9rem !important;
  letter-spacing: 0 !important;
  font-weight: 600 !important;
}

.dropdown-item:hover,
.dropdown-item.router-link-exact-active {
  background: rgba(255, 215, 0, 0.09);
  color: #ffd700 !important;
}

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

.menu-mobile-btn {
  display: none;
  color: #ffd700 !important;
}

:deep(.menu-mobile-btn.p-button) {
  width: 46px;
  height: 46px;
  border-radius: 12px;
}

.mobile-wrapper {
  position: fixed;
  inset: 0;
  z-index: 2000;
  visibility: hidden;
  opacity: 0;
  display: none;
  transition: 0.3s ease;
  background: rgba(0, 0, 0, 0.72);
}

.mobile-wrapper.is-active {
  visibility: visible;
  opacity: 1;
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
  font-size: 0.56rem;
  letter-spacing: 1.6px;
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
  font-weight: 700 !important;
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
  color: #ffffff;
  text-decoration: none;
  font-size: 0.9rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  text-transform: uppercase;
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
  font-size: 0.8rem;
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
  font-size: 0.88rem;
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

.fade-slide-enter-active,
.fade-slide-leave-active {
  transition: all 0.2s ease;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
  opacity: 0;
  transform: translateY(8px);
}

@media (max-width: 1180px) {
  .nav-links {
    gap: 16px;
  }

  .nav-links a,
  .dropdown-btn {
    font-size: 0.68rem;
    letter-spacing: 0.045em;
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
    font-size: 0.54rem;
    letter-spacing: 1.6px;
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
    font-size: 0.48rem;
    letter-spacing: 1.1px;
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
    font-size: 0.88rem;
  }

  .sub-link {
    padding: 12px 14px 12px 22px;
    margin-left: 14px;
    font-size: 0.86rem;
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
    font-size: 0.43rem;
    letter-spacing: 0.8px;
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

/* FIX Communiqués dark mode */
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn span,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn i {
  color: #cbd5e1 !important;
}

:global(html.dark) .navbar-tactical .nav-links .dropdown-btn:hover,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn:hover span,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn:hover i,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn.is-active,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn.is-active span,
:global(html.dark) .navbar-tactical .nav-links .dropdown-btn.is-active i {
  color: #ffd700 !important;
}
</style>