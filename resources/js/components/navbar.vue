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

// ✅ Liste langues (on garde les drapeaux)
const languages = [
  { label: 'Français', value: 'fr', flag: '🇫🇷' },
  { label: 'English', value: 'en', flag: '🇬🇧' },
  { label: '中文', value: 'zh', flag: '🇨🇳' },
  { label: 'Русский', value: 'ru', flag: '🇷🇺' },
]

// ✅ Drapeau courant
const selectedLang = computed(() => languages.find(l => l.value === locale.value) ?? languages[0])

// ✅ Ouvrir/fermer le mini menu langue
const isLangOpen = ref(false)

// ✅ Appliquer une langue (i18n + storage)
const setLocale = (value) => {
  locale.value = value
  localStorage.setItem('public-locale', value)
  isLangOpen.value = false
}

// -------------------- MENU UI --------------------
const isMenuOpen = ref(false)
const isDropdownOpen = ref(false) // Etat-major dropdown

// -------------------- NAV (les labels viennent de i18n) --------------------
const navItems = [
  { key: 'nav.home', to: '/' },
  { key: 'nav.posts', to: '/portfolio' },
  { key: 'nav.comops', to: '/com-ops' },
]

const categories = [
  { label: "Armée de Terre", to: '/EtatmajorAT' },
  { label: "Armée de l'Air", to: '/EtatmajorAA' },
  { label: "Garde Nationale", to: '/EtatmajorGarde' },
  { label: "D.T.T.I.A", to: '/Dttia' },
  { label: "D.M.H.T.A", to: '/Dmhta' },
  { label: "D.C.S.S.A", to: '/Dcssa' },
  { label: "Génie Militaire", to: '/Dgm' },
  { label: "Gendarmerie Nationale", to: '/Gendarmerie' },
  { label: "Police Nationale", to: '/police' },
]

const contactItem = { key: 'nav.contact', to: '/contact' }
const aboutItem = { key: 'nav.about', to: '/about' }

// -------------------- THEME (dark / light) --------------------
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

// ✅ Watch : si theme change ailleurs
watch(theme, (v) => {
  localStorage.setItem('public-theme', v)
  applyThemeToDom(v)
})

// ✅ Au montage : récupérer theme + locale depuis storage
onMounted(() => {
  // Theme
  const savedTheme = localStorage.getItem('public-theme')
  if (savedTheme === 'light' || savedTheme === 'dark') theme.value = savedTheme
  else theme.value = 'dark'
  applyThemeToDom(theme.value)

  // Langue
  const savedLocale = localStorage.getItem('public-locale')
  if (savedLocale && languages.some(l => l.value === savedLocale)) {
    locale.value = savedLocale
  } else {
    locale.value = 'fr'
  }
})
</script>

<template>
  <nav class="navbar-tactical">
    <div class="mali-stripe">
      <div class="s-green"></div><div class="s-yellow"></div><div class="s-red"></div>
    </div>

    <div class="nav-container">
      <div class="nav-content">
        <div class="logo-box" @click="router.push('/')" style="cursor: pointer;">
          <span class="fama">FAM<span class="gold">a</span></span>
          <span class="sub">Forces Armées Maliennes</span>
        </div>

        <ul class="nav-links">
          <li v-for="item in navItems" :key="item.to">
            <router-link :to="item.to">{{ t(item.key) }}</router-link>
          </li>

          <!-- Dropdown PC Etat-Major -->
          <li class="dropdown-pc" @mouseenter="isDropdownOpen = true" @mouseleave="isDropdownOpen = false">
            <a href="#" class="dropdown-trigger" @click.prevent>
              {{ t('nav.staff') }} <i class="pi pi-chevron-down"></i>
            </a>

            <transition name="fade-slide">
              <ul v-if="isDropdownOpen" class="dropdown-menu">
                <li v-for="cat in categories" :key="cat.to">
                  <router-link :to="cat.to">{{ cat.label }}</router-link>
                </li>
              </ul>
            </transition>
          </li>

          <li><router-link :to="contactItem.to">{{ t(contactItem.key) }}</router-link></li>
          <li><router-link :to="aboutItem.to">{{ t(aboutItem.key) }}</router-link></li>

          <!-- ✅ LANG (PC) : petit bouton drapeau + mini menu -->
          <li class="nav-tools lang-wrap" @mouseleave="isLangOpen = false">
            <button class="lang-pill" type="button" @click="isLangOpen = !isLangOpen" :aria-label="selectedLang.label">
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

          <!-- ✅ THEME TOGGLE (PC) -->
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

        <!-- Actions à droite (mobile top bar) -->
        <div class="right-actions">
          <div class="mini-tools">
            <Button
              :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
              class="theme-btn"
              text
              @click="toggleTheme"
            />

            <!-- mini lang button mobile -->
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
          <div class="logo-box"><span class="fama">FAM<span class="gold">a</span></span></div>
          <Button icon="pi pi-times" class="close-btn" text @click="isMenuOpen = false" />
        </div>

        <!-- Tools dans le menu mobile -->
        <div class="mobile-tools">
          <Button
            :label="isDark ? t('common.dark') : t('common.light')"
            :icon="isDark ? 'pi pi-moon' : 'pi pi-sun'"
            class="mobile-tool-btn"
            @click="toggleTheme"
          />

          <!-- Lang menu dans mobile (simple et clean) -->
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
            <div class="mobile-link" @click="isDropdownOpen = !isDropdownOpen">
              {{ t('nav.staff') }}
              <i :class="isDropdownOpen ? 'pi pi-chevron-up' : 'pi pi-chevron-down'"></i>
            </div>

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
/* --- Styles de base PC --- */
.navbar-tactical { width: 100%; position: sticky; top: 0; z-index: 1000; background: #1a241b; border-bottom: 1px solid rgba(255, 215, 0, 0.2); }
/* ✅ Si ticker visible, on pousse la navbar juste en dessous */
:global(html.has-ticker) .navbar-tactical {
  top: 40px; /* doit matcher hauteur ticker */}
.mali-stripe { display: flex; height: 4px; }
.s-green { background: #14B82C; flex: 1; }
.s-yellow { background: #FFD700; flex: 1; }
.s-red { background: #CE1126; flex: 1; }
.nav-container { max-width: 1300px; margin: 0 auto; padding: 0 2rem; }
.nav-content { display: flex; justify-content: space-between; align-items: center; height: 80px; }
.fama { font-size: 1.8rem; font-weight: 900; color: #FFD700; letter-spacing: 2px; }
.gold { color: #FFD700; }
.sub { display: block; font-size: 0.6rem; color: #cbd5e1; letter-spacing: 3px; margin-top: -8px; }

.logo-box { display: flex; flex-direction: column; gap: 6px; }
.nav-links { display: flex; gap: 35px; list-style: none; align-items: center; }
.nav-links a { color: #cbd5e1; text-decoration: none; font-weight: 700; font-size: 0.85rem; text-transform: uppercase; transition: 0.3s; }
.nav-links a:hover, .nav-links a.router-link-active { color: #FFD700; }

/* --- STYLE DROPDOWN PC --- */
.dropdown-pc { position: relative; cursor: pointer; }
.dropdown-trigger i { font-size: 0.7rem; margin-left: 5px; transition: 0.3s; }
.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  background: #243125;
  min-width: 180px;
  list-style: none;
  padding: 10px 0;
  border: 1px solid rgba(255, 215, 0, 0.2);
  box-shadow: 0 10px 15px rgba(0,0,0,0.3);
}
.dropdown-menu li a { display: block; padding: 10px 20px; font-size: 0.8rem; color: #fff; }
.dropdown-menu li a:hover { background: rgba(255, 215, 0, 0.1); color: #FFD700; }

/* Animation */
.fade-slide-enter-active, .fade-slide-leave-active { transition: all 0.2s ease; }
.fade-slide-enter-from, .fade-slide-leave-to { opacity: 0; transform: translateY(8px); }

/* ✅ TOOLS */
.nav-tools { display: flex; align-items: center; }
.theme-btn { color: #FFD700 !important; }

/* ✅ Lang élégant (petit) */
.lang-wrap { position: relative; }
.lang-pill {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  height: 34px;
  padding: 0 10px;
  border-radius: 999px;
  border: 1px solid rgba(255, 215, 0, 0.22);
  background: rgba(255, 255, 255, 0.06);
  color: #cbd5e1;
  cursor: pointer;
}
.lang-pill.mini { height: 34px; padding: 0 10px; }
.flag { font-size: 1rem; line-height: 1; }
.caret { font-size: 0.7rem; opacity: 0.8; }

.lang-menu {
  position: absolute;
  right: 0;
  top: calc(100% + 10px);
  width: 200px;
  background: #243125;
  border: 1px solid rgba(255, 215, 0, 0.2);
  box-shadow: 0 12px 18px rgba(0,0,0,0.35);
  border-radius: 12px;
  overflow: hidden;
  z-index: 3000;
}
.lang-menu.mini { width: 200px; }

.lang-item {
  width: 100%;
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 10px 12px;
  background: transparent;
  border: none;
  color: #e2e8f0;
  cursor: pointer;
  text-align: left;
}
.lang-item:hover { background: rgba(255, 215, 0, 0.10); }
.lang-text { font-weight: 800; font-size: 0.9rem; }
.check { margin-left: auto; color: #FFD700; }

/* Right actions */
.right-actions { display: flex; gap: 10px; align-items: center; }
.mini-tools { display: none; align-items: center; gap: 10px; }

/* --- Mobile --- */
.mobile-wrapper { display: none; }
.menu-mobile-btn { display: none; color: #FFD700 !important; }

@media (max-width: 992px) {
  .nav-links { display: none !important; }
  .menu-mobile-btn { display: block; }
  .mini-tools { display: flex; }

  .mobile-wrapper { display: block; position: fixed; top: 0; left: 0; width: 100%; height: 100vh; z-index: 2000; visibility: hidden; opacity: 0; transition: 0.4s; background: rgba(0, 0, 0, 0.8); }
  .mobile-wrapper.is-active { visibility: visible; opacity: 1; }
  .side-menu { position: absolute; right: -300px; top: 0; width: 280px; height: 100%; background: #1a241b; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); display: flex; flex-direction: column; }
  .mobile-wrapper.is-active .side-menu { right: 0; }

  .menu-header { padding: 2rem; display: flex; justify-content: space-between; border-bottom: 1px solid rgba(255, 215, 0, 0.1); }
  .menu-items { padding: 1rem 0; overflow-y: auto; }
  .mobile-link { padding: 1.2rem 2rem; color: #fff; text-decoration: none; font-size: 1.1rem; font-weight: 700; display: flex; justify-content: space-between; cursor: pointer; }

  .mobile-tools { padding: 1rem 1.5rem; display: flex; flex-direction: column; gap: 12px; border-bottom: 1px solid rgba(255, 215, 0, 0.1); }
  .mobile-tool-btn { background: rgba(255,215,0,0.12) !important; border: 1px solid rgba(255,215,0,0.3) !important; color: #FFD700 !important; font-weight: 800; }

  .mobile-sub-menu { background: rgba(0, 0, 0, 0.2); padding-left: 1rem; }
  .sub-link { display: block; padding: 1rem 2rem; color: #cbd5e1; text-decoration: none; font-size: 0.95rem; border-left: 2px solid rgba(255, 215, 0, 0.3); }
  .sub-link:hover { color: #FFD700; }

  .mobile-lang { margin-top: 6px; }
  .mobile-lang-title { color: #cbd5e1; font-weight: 800; font-size: 0.8rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 10px; }
  .mobile-lang-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 8px; }
  .mobile-lang-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 6px;
    padding: 10px 0;
    border-radius: 12px;
    border: 1px solid rgba(255, 215, 0, 0.18);
    background: rgba(255, 255, 255, 0.06);
    color: #e2e8f0;
    cursor: pointer;
  }
  .mobile-lang-btn.active { border-color: rgba(255, 215, 0, 0.5); background: rgba(255, 215, 0, 0.10); }
  .mobile-lang-btn .code { font-size: 0.65rem; font-weight: 900; opacity: 0.9; }

  .menu-footer { margin-top: auto; padding: 2rem; text-align: center; color: #666; font-size: 0.6rem; border-top: 1px solid rgba(255, 215, 0, 0.1); }
}
</style>