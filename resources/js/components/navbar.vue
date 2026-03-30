<script setup>
// ✅ Vue
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'

// ✅ PrimeVue
import Button from 'primevue/button'

// ✅ i18n
import { useI18n } from 'vue-i18n'

const router = useRouter()

// -------------------- i18n (LANG) --------------------
const { locale, t } = useI18n()

const languages = [
  { label: 'Français', value: 'fr', flag: '🇫🇷' },
  { label: 'English', value: 'en', flag: '🇬🇧' },
  { label: '中文', value: 'zh', flag: '🇨🇳' },
  { label: 'Русский', value: 'ru', flag: '🇷🇺' },
]

const selectedLang = computed(() => languages.find((l) => l.value === locale.value) ?? languages[0])

const isLangOpen = ref(false)

const setLocale = (value) => {
  locale.value = value
  localStorage.setItem('public-locale', value)
  isLangOpen.value = false
}

// -------------------- MENU UI --------------------
const isMenuOpen = ref(false)
const isDropdownOpen = ref(false)

// -------------------- NAV --------------------
const navItems = [
  { key: 'nav.home', to: '/' },
  { key: 'nav.posts', to: '/portfolio' },
  { key: 'nav.comops', to: '/com-ops' },
]

const categories = [
  { label: "Armée de Terre", to: '/EtatmajorAT' },
  { label: "Armée de l'Air", to: '/EtatmajorAA' },
  { label: "Garde Nationale", to: '/EtatmajorGarde' },
  { label: 'D.T.T.I.A', to: '/Dttia' },
  { label: 'D.M.H.T.A', to: '/Dmhta' },
  { label: 'D.C.S.S.A', to: '/Dcssa' },
  { label: 'Génie Militaire', to: '/Dgm' },
  { label: 'Gendarmerie Nationale', to: '/Gendarmerie' },
  { label: 'Police Nationale', to: '/police' },
]

const contactItem = { key: 'nav.contact', to: '/contact' }
const aboutItem = { key: 'nav.about', to: '/about' }

// -------------------- THEME --------------------
const theme = ref('dark')
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
  if (savedTheme === 'light' || savedTheme === 'dark') theme.value = savedTheme
  else theme.value = 'dark'
  applyThemeToDom(theme.value)

  const savedLocale = localStorage.getItem('public-locale')
  if (savedLocale && languages.some((l) => l.value === savedLocale)) {
    locale.value = savedLocale
  } else {
    locale.value = 'fr'
  }
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
          <li v-for="item in navItems" :key="item.to">
            <router-link :to="item.to">{{ t(item.key) }}</router-link>
          </li>

          <li>
            <router-link :to="contactItem.to">{{ t(contactItem.key) }}</router-link>
          </li>

          <li>
            <router-link :to="aboutItem.to">{{ t(aboutItem.key) }}</router-link>
          </li>

          <li class="nav-tools lang-wrap" @mouseleave="isLangOpen = false">
            <button
              class="lang-pill"
              type="button"
              @click="isLangOpen = !isLangOpen"
              :aria-label="selectedLang.label"
            >
              <span class="flag">{{ selectedLang.flag }}</span>
              <i class="pi pi-chevron-down caret"></i>
            </button>

            <transition name="fade-slide">
              <div v-if="isLangOpen" class="lang-menu">
                <button
                  v-for="l in languages"
                  :key="l.value"
                  class="lang-item"
                  type="button"
                  @click="setLocale(l.value)"
                >
                  <span class="flag">{{ l.flag }}</span>
                  <span class="lang-text">{{ l.label }}</span>
                  <i v-if="l.value === locale" class="pi pi-check check"></i>
                </button>
              </div>
            </transition>
          </li>

          <li class="nav-tools">
            <Button
              :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
              class="theme-btn"
              text
              @click="toggleTheme"
              :aria-label="isDark ? t('common.light') : t('common.dark')"
            />
          </li>
        </ul>

        <!-- MOBILE TOP BAR -->
        <div class="right-actions">
          <div class="mini-tools">
            <Button
              :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
              class="theme-btn top-mobile-btn"
              text
              @click="toggleTheme"
            />

            <div class="lang-wrap">
              <button class="lang-pill mini" type="button" @click="isLangOpen = !isLangOpen">
                <span class="flag">{{ selectedLang.flag }}</span>
              </button>

              <transition name="fade-slide">
                <div v-if="isLangOpen" class="lang-menu mini">
                  <button
                    v-for="l in languages"
                    :key="l.value"
                    class="lang-item"
                    type="button"
                    @click="setLocale(l.value)"
                  >
                    <span class="flag">{{ l.flag }}</span>
                    <span class="lang-text">{{ l.label }}</span>
                    <i v-if="l.value === locale" class="pi pi-check check"></i>
                  </button>
                </div>
              </transition>
            </div>
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
            :label="isDark ? t('common.dark') : t('common.light')"
            :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
            class="mobile-tool-btn"
            @click="toggleTheme"
          />

          <div class="mobile-lang">
            <div class="mobile-lang-title">Langue</div>
            <div class="mobile-lang-grid">
              <button
                v-for="l in languages"
                :key="l.value"
                class="mobile-lang-btn"
                type="button"
                @click="setLocale(l.value)"
                :class="{ active: l.value === locale }"
              >
                <span class="flag">{{ l.flag }}</span>
                <span class="code">{{ l.value.toUpperCase() }}</span>
              </button>
            </div>
          </div>
        </div>

        <div class="menu-items">
          <router-link
            v-for="item in navItems"
            :key="item.to"
            :to="item.to"
            class="mobile-link"
            @click="isMenuOpen = false"
          >
            {{ t(item.key) }}
          </router-link>

          <div class="mobile-category-section">
            <div class="mobile-link mobile-dropdown-trigger" @click="isDropdownOpen = !isDropdownOpen">
              <span>{{ t('nav.staff') }}</span>
              <i :class="isDropdownOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
            </div>

            <!-- <transition name="fade-slide">
              <div v-if="isDropdownOpen" class="mobile-sub-menu">
                <router-link
                  v-for="cat in categories"
                  :key="cat.to"
                  :to="cat.to"
                  class="sub-link"
                  @click="isMenuOpen = false"
                >
                  {{ cat.label }}
                </router-link>
              </div>
            </transition> -->
          </div>

          <router-link :to="aboutItem.to" class="mobile-link" @click="isMenuOpen = false">
            {{ t(aboutItem.key) }}
          </router-link>

          <router-link :to="contactItem.to" class="mobile-link" @click="isMenuOpen = false">
            {{ t(contactItem.key) }}
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
.nav-links a.router-link-active {
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

.lang-wrap {
  position: relative;
}

.lang-pill {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  min-height: 38px;
  padding: 0 12px;
  border-radius: 999px;
  border: 1px solid rgba(255, 215, 0, 0.22);
  background: transparent;
  color: #cbd5e1;
  cursor: pointer;
  transition: 0.25s ease;
}

.lang-pill:hover {
  border-color: rgba(255, 215, 0, 0.42);
  background: rgba(255, 255, 255, 0.1);
}

.lang-pill.mini {
  min-height: 40px;
  min-width: 40px;
  padding: 0 10px;
}

.flag {
  font-size: 1rem;
  line-height: 1;
}

.caret {
  font-size: 0.7rem;
  opacity: 0.85;
}

.lang-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  width: 210px;
  background: #243125;
  border: 1px solid rgba(255, 215, 0, 0.2);
  box-shadow: 0 12px 18px rgba(0, 0, 0, 0.35);
  border-radius: 14px;
  overflow: hidden;
  z-index: 3000;
}

.lang-menu.mini {
  width: 210px;
}

.lang-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 14px;
  background: transparent;
  border: none;
  color: #e2e8f0;
  cursor: pointer;
  text-align: left;
  transition: 0.2s ease;
}

.lang-item:hover {
  background: rgba(255, 215, 0, 0.1);
}

.lang-text {
  font-weight: 800;
  font-size: 0.92rem;
}

.check {
  margin-left: auto;
  color: #ffd700;
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
  right: -420px;
  top: 0;
  width: min(92vw, 390px);
  height: 100%;
  background: linear-gradient(180deg, #1f2d20 0%, #162719 100%);
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
  padding: 24px 22px;
  display: flex;
  justify-content: space-between;
  align-items: flex-start;
  gap: 14px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.1);
}

.mobile-logo .fama {
  font-size: 2rem;
}

.mobile-logo .sub {
  font-size: 0.7rem;
  letter-spacing: 2px;
  margin-top: 2px;
}

.close-btn {
  color: #ffd700 !important;
  flex-shrink: 0;
}

:deep(.close-btn.p-button) {
  width: 50px;
  height: 50px;
  border-radius: 14px;
}

.mobile-tools {
  padding: 20px 20px 22px;
  display: flex;
  flex-direction: column;
  gap: 18px;
  border-bottom: 1px solid rgba(255, 215, 0, 0.1);
}

.mobile-tool-btn {
  background: rgba(255, 215, 0, 0.12) !important;
  border: 1px solid rgba(255, 215, 0, 0.3) !important;
  color: #ffd700 !important;
  font-weight: 800 !important;
  min-height: 56px !important;
  border-radius: 14px !important;
  font-size: 1rem !important;
}

.mobile-lang {
  margin-top: 2px;
}

.mobile-lang-title {
  color: #cbd5e1;
  font-weight: 900;
  font-size: 0.9rem;
  letter-spacing: 1px;
  text-transform: uppercase;
  margin-bottom: 12px;
}

.mobile-lang-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}

.mobile-lang-btn {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 7px;
  min-height: 68px;
  padding: 10px 0;
  border-radius: 14px;
  border: 1px solid rgba(255, 215, 0, 0.18);
  background: rgba(255, 255, 255, 0.06);
  color: #e2e8f0;
  cursor: pointer;
  transition: 0.2s ease;
}

.mobile-lang-btn.active {
  border-color: rgba(255, 215, 0, 0.5);
  background: rgba(255, 215, 0, 0.1);
}

.mobile-lang-btn .flag {
  font-size: 1.1rem;
}

.mobile-lang-btn .code {
  font-size: 0.74rem;
  font-weight: 900;
  opacity: 0.95;
}

.menu-items {
  padding: 12px 0 16px;
  overflow-y: auto;
  flex: 1;
}

.mobile-link {
  padding: 19px 22px;
  color: #fff;
  text-decoration: none;
  font-size: 1.15rem;
  font-weight: 900;
  display: flex;
  justify-content: space-between;
  align-items: center;
  cursor: pointer;
  transition: 0.2s ease;
  border-left: 4px solid transparent;
  line-height: 1.25;
}

.mobile-link:hover,
.mobile-link.router-link-active {
  background: rgba(255, 215, 0, 0.08);
  border-left-color: #ffd700;
  color: #ffd700;
}

.mobile-dropdown-trigger i {
  font-size: 0.95rem;
}

.mobile-sub-menu {
  background: rgba(0, 0, 0, 0.18);
  padding: 8px 0 12px;
}

.sub-link {
  display: block;
  padding: 15px 18px 15px 30px;
  color: #d7e0d8;
  text-decoration: none;
  font-size: 1rem;
  font-weight: 700;
  border-left: 3px solid rgba(255, 215, 0, 0.28);
  margin-left: 22px;
  transition: 0.2s ease;
  line-height: 1.4;
}

.sub-link:hover,
.sub-link.router-link-active {
  color: #ffd700;
  background: rgba(255, 215, 0, 0.05);
}

.menu-footer {
  margin-top: auto;
  padding: 20px 16px 22px;
  text-align: center;
  color: #8a938d;
  font-size: 0.72rem;
  border-top: 1px solid rgba(255, 215, 0, 0.1);
  letter-spacing: 0.14em;
  line-height: 1.7;
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
    padding: 0 18px;
  }

  .nav-content {
    min-height: 88px;
  }

  .nav-links {
    display: none !important;
  }

  .menu-mobile-btn {
    display: inline-flex;
  }

  .mini-tools {
    display: flex;
    gap: 10px;
  }

  .mobile-wrapper {
    display: block;
  }

  .fama {
    font-size: 1.9rem;
    letter-spacing: 1.8px;
  }

  .sub {
    font-size: 0.68rem;
    letter-spacing: 2px;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 50px;
    height: 50px;
    border-radius: 14px;
  }

  .lang-pill.mini {
    min-height: 46px;
    min-width: 46px;
    padding: 0 10px;
  }

  .lang-pill.mini .flag {
    font-size: 1.05rem;
  }
}

@media (max-width: 576px) {
  .nav-container {
    padding: 0 14px;
  }

  .nav-content {
    min-height: 84px;
    gap: 10px;
  }

  .fama {
    font-size: 1.72rem;
    letter-spacing: 1.5px;
  }

  .sub {
    font-size: 0.62rem;
    letter-spacing: 1.5px;
    margin-top: 0;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 48px;
    height: 48px;
  }

  .lang-pill.mini {
    min-height: 44px;
    min-width: 44px;
    padding: 0 8px;
  }

  .flag {
    font-size: 1rem;
  }

  .side-menu {
    width: min(94vw, 380px);
  }

  .menu-header {
    padding: 22px 18px;
  }

  .mobile-logo .fama {
    font-size: 1.85rem;
  }

  .mobile-logo .sub {
    font-size: 0.66rem;
    letter-spacing: 1.7px;
  }

  .mobile-tools {
    padding: 18px;
  }

  .mobile-tool-btn {
    min-height: 54px !important;
    font-size: 0.98rem !important;
  }

  .mobile-lang-title {
    font-size: 0.84rem;
  }

  .mobile-lang-grid {
    gap: 9px;
  }

  .mobile-lang-btn {
    min-height: 64px;
  }

  .mobile-link {
    padding: 18px 18px;
    font-size: 1.08rem;
  }

  .sub-link {
    padding: 14px 16px 14px 24px;
    margin-left: 18px;
    font-size: 0.96rem;
  }

  .lang-menu,
  .lang-menu.mini {
    width: 210px;
  }

  .lang-item {
    padding: 12px 13px;
  }

  .lang-text {
    font-size: 0.92rem;
  }
}

@media (max-width: 380px) {
  .nav-content {
    min-height: 78px;
  }

  .fama {
    font-size: 1.52rem;
  }

  .sub {
    font-size: 0.56rem;
    letter-spacing: 1.2px;
  }

  .mini-tools {
    gap: 7px;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 44px;
    height: 44px;
  }

  .lang-pill.mini {
    min-height: 42px;
    min-width: 42px;
  }

  .side-menu {
    width: 96vw;
  }

  .mobile-logo .fama {
    font-size: 1.65rem;
  }

  .mobile-logo .sub {
    font-size: 0.58rem;
  }

  .mobile-link {
    font-size: 1rem;
  }

  .sub-link {
    font-size: 0.92rem;
  }

  .mobile-lang-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* =========================================
   MOBILE MODE MASSIF — À COLLER TOUT EN BAS
========================================= */
@media (max-width: 992px) {
  .nav-container {
    padding: 0 18px !important;
  }

  .nav-content {
    min-height: 110px !important;
    gap: 14px !important;
  }

  .logo-box {
    gap: 6px !important;
  }

  .fama {
    font-size: 2.4rem !important;
    letter-spacing: 1.8px !important;
    line-height: 1 !important;
  }

  .sub {
    font-size: 0.9rem !important;
    letter-spacing: 1.8px !important;
    line-height: 1.2 !important;
    margin-top: 2px !important;
  }

  .mini-tools {
    display: flex !important;
    gap: 14px !important;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 64px !important;
    height: 64px !important;
    border-radius: 16px !important;
    font-size: 1.35rem !important;
  }

  .lang-pill.mini {
    min-width: 60px !important;
    min-height: 60px !important;
    padding: 0 12px !important;
    border-radius: 16px !important;
  }

  .lang-pill.mini .flag,
  .flag {
    font-size: 1.35rem !important;
  }

  .caret {
    font-size: 1rem !important;
  }

  .lang-menu,
  .lang-menu.mini {
    width: 280px !important;
    border-radius: 18px !important;
  }

  .lang-item {
    padding: 18px 18px !important;
    gap: 14px !important;
    min-height: 62px !important;
  }

  .lang-text {
    font-size: 1.15rem !important;
    font-weight: 900 !important;
  }

  .side-menu {
    width: 94vw !important;
    max-width: none !important;
    right: -100vw !important;
  }

  .mobile-wrapper.is-active .side-menu {
    right: 0 !important;
  }

  .menu-header {
    padding: 28px 22px !important;
    gap: 18px !important;
  }

  .mobile-logo .fama {
    font-size: 2.5rem !important;
    letter-spacing: 1.6px !important;
  }

  .mobile-logo .sub {
    font-size: 0.9rem !important;
    letter-spacing: 1.8px !important;
    margin-top: 4px !important;
  }

  :deep(.close-btn.p-button) {
    width: 62px !important;
    height: 62px !important;
    border-radius: 16px !important;
    font-size: 1.35rem !important;
  }

  .mobile-tools {
    padding: 24px 20px 26px !important;
    gap: 20px !important;
  }

  .mobile-tool-btn {
    min-height: 68px !important;
    font-size: 1.18rem !important;
    font-weight: 900 !important;
    border-radius: 16px !important;
  }

  .mobile-lang-title {
    font-size: 1rem !important;
    font-weight: 900 !important;
    margin-bottom: 14px !important;
    letter-spacing: 1px !important;
  }

  .mobile-lang-grid {
    grid-template-columns: repeat(2, 1fr) !important;
    gap: 14px !important;
  }

  .mobile-lang-btn {
    min-height: 84px !important;
    border-radius: 16px !important;
    gap: 10px !important;
    padding: 12px 8px !important;
  }

  .mobile-lang-btn .flag {
    font-size: 1.45rem !important;
  }

  .mobile-lang-btn .code {
    font-size: 0.95rem !important;
    font-weight: 900 !important;
  }

  .menu-items {
    padding: 16px 0 18px !important;
  }

  .mobile-link {
    padding: 24px 22px !important;
    font-size: 1.4rem !important;
    font-weight: 900 !important;
    line-height: 1.3 !important;
    border-left-width: 5px !important;
  }

  .mobile-dropdown-trigger i {
    font-size: 1.2rem !important;
  }

  .mobile-sub-menu {
    padding: 10px 0 16px !important;
  }

  .sub-link {
    padding: 18px 18px 18px 28px !important;
    margin-left: 22px !important;
    font-size: 1.12rem !important;
    font-weight: 700 !important;
    line-height: 1.45 !important;
    border-left-width: 4px !important;
  }

  .menu-footer {
    padding: 22px 16px 24px !important;
    font-size: 0.86rem !important;
    line-height: 1.8 !important;
    letter-spacing: 0.16em !important;
  }
}

/* TÉLÉPHONES MOYENS */
@media (max-width: 576px) {
  .nav-content {
    min-height: 102px !important;
  }

  .fama {
    font-size: 2.15rem !important;
  }

  .sub {
    font-size: 0.8rem !important;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 58px !important;
    height: 58px !important;
    font-size: 1.2rem !important;
  }

  .lang-pill.mini {
    min-width: 56px !important;
    min-height: 56px !important;
  }

  .lang-pill.mini .flag {
    font-size: 1.2rem !important;
  }

  .side-menu {
    width: 96vw !important;
  }

  .mobile-logo .fama {
    font-size: 2.2rem !important;
  }

  .mobile-logo .sub {
    font-size: 0.8rem !important;
  }

  :deep(.close-btn.p-button) {
    width: 56px !important;
    height: 56px !important;
  }

  .mobile-tool-btn {
    min-height: 64px !important;
    font-size: 1.08rem !important;
  }

  .mobile-lang-btn {
    min-height: 78px !important;
  }

  .mobile-link {
    padding: 22px 18px !important;
    font-size: 1.24rem !important;
  }

  .sub-link {
    padding: 17px 16px 17px 24px !important;
    font-size: 1.02rem !important;
    margin-left: 18px !important;
  }

  .menu-footer {
    font-size: 0.78rem !important;
  }
}

/* TRÈS PETITS TÉLÉPHONES */
@media (max-width: 380px) {
  .nav-content {
    min-height: 96px !important;
  }

  .fama {
    font-size: 1.9rem !important;
  }

  .sub {
    font-size: 0.72rem !important;
    letter-spacing: 1.2px !important;
  }

  :deep(.theme-btn.p-button),
  :deep(.menu-mobile-btn.p-button) {
    width: 54px !important;
    height: 54px !important;
  }

  .lang-pill.mini {
    min-width: 52px !important;
    min-height: 52px !important;
  }

  .mobile-logo .fama {
    font-size: 2rem !important;
  }

  .mobile-logo .sub {
    font-size: 0.72rem !important;
  }

  .mobile-link {
    font-size: 1.12rem !important;
  }

  .sub-link {
    font-size: 0.96rem !important;
  }
}
</style>
