import { createI18n } from 'vue-i18n'

// 🔁 Langue sauvegardée
const savedLocale = localStorage.getItem('public-locale') || 'fr'

const messages = {
  fr: {
    nav: {
      home: 'Accueil',
      posts: 'Communiqués',
      comops: 'Com-Ops',          // ✅ AJOUT
      staff: 'ÉTAT-MAJOR',
      contact: 'Contact',
      about: 'À propos'
    },
    common: {
      dark: 'Thème sombre',
      light: 'Thème clair'
    }
  },

  en: {
    nav: {
      home: 'Home',
      posts: 'Press releases',
      comops: 'Com-Ops',          // ✅ AJOUT
      staff: 'STAFF',
      contact: 'Contact',
      about: 'About'
    },
    common: {
      dark: 'Dark theme',
      light: 'Light theme'
    }
  },

  zh: {
    nav: {
      home: '首页',
      posts: '公告',
      comops: 'Com-Ops',          // ✅ AJOUT (tu peux traduire si tu veux)
      staff: '参谋部',
      contact: '联系',
      about: '关于'
    },
    common: {
      dark: '深色主题',
      light: '浅色主题'
    }
  },

  ru: {
    nav: {
      home: 'Главная',
      posts: 'Сообщения',
      comops: 'Ком-ОПС',          // ✅ AJOUT (ou laisse "Com-Ops" si tu préfères)
      staff: 'ШТАБ',
      contact: 'Контакты',
      about: 'О нас'
    },
    common: {
      dark: 'Тёмная тема',
      light: 'Светлая тема'
    }
  }
}

export const i18n = createI18n({
  legacy: false, // IMPORTANT pour Vue 3 setup()
  locale: savedLocale,
  fallbackLocale: 'fr',
  messages
})