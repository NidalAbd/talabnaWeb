import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'

// Tree-shaking: components auto-imported by vite-plugin-vuetify
// No need for: import * as components from 'vuetify/components'

const lightTheme = {
  dark: false,
  colors: {
    background: '#F8F9FA',
    surface: '#FFFFFF',
    'surface-variant': '#F1F3F5',
    'surface-bright': '#FAFBFC',
    primary: '#6366F1',
    'primary-darken-1': '#4F46E5',
    'primary-lighten-1': '#818CF8',
    secondary: '#64748B',
    'secondary-lighten-1': '#94A3B8',
    success: '#10B981',
    warning: '#F59E0B',
    error: '#EF4444',
    info: '#06B6D4',
    'on-background': '#0F172A',
    'on-surface': '#1E293B',
  },
}

const darkTheme = {
  dark: true,
  colors: {
    background: '#0F172A',
    surface: '#1E293B',
    'surface-variant': '#334155',
    'surface-bright': '#475569',
    primary: '#818CF8',
    'primary-darken-1': '#6366F1',
    'primary-lighten-1': '#A5B4FC',
    secondary: '#94A3B8',
    'secondary-lighten-1': '#CBD5E1',
    success: '#34D399',
    warning: '#FBBF24',
    error: '#F87171',
    info: '#22D3EE',
    'on-background': '#F1F5F9',
    'on-surface': '#E2E8F0',
  },
}

export default createVuetify({
  theme: {
    defaultTheme: 'light',
    themes: {
      light: lightTheme,
      dark: darkTheme,
    },
  },
  defaults: {
    VBtn: { rounded: 'lg' },
    VCard: { rounded: 'lg' },
    VTextField: { variant: 'outlined', density: 'comfortable' },
    VSelect: { variant: 'outlined', density: 'comfortable' },
  },
})
