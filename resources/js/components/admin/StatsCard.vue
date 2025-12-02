<template>
  <v-card
    :color="color"
    :to="to"
    hover
    class="stat-card"
    :class="{ 'dark-text': isLightColor }"
  >
    <v-card-text>
      <v-row align="center" no-gutters>
        <v-col>
          <div class="text-h4 font-weight-bold mb-2" :class="textColorClass">
            {{ formattedValue }}
          </div>
          <div class="text-subtitle-1" :class="textColorClass" style="opacity: 0.9;">
            {{ title }}
          </div>
        </v-col>
        <v-col cols="auto">
          <v-avatar
            size="56"
            :color="avatarBgColor"
          >
            <v-icon size="32" :color="iconColor">
              {{ icon }}
            </v-icon>
          </v-avatar>
        </v-col>
      </v-row>
    </v-card-text>

    <v-divider :color="dividerColor" :opacity="0.3" />

    <v-card-actions class="px-4 py-2" :class="textColorClass" style="opacity: 0.9;">
      <span class="text-caption">More info</span>
      <v-spacer />
      <v-icon size="small">mdi-arrow-right</v-icon>
    </v-card-actions>
  </v-card>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  value: {
    type: [Number, String],
    required: true
  },
  icon: {
    type: String,
    required: true
  },
  color: {
    type: String,
    default: 'primary'
  },
  to: {
    type: [Object, String],
    default: null
  }
})

// Light colors that need dark text
const lightColors = ['warning', 'yellow', 'lime', 'light-green', 'amber', 'orange', 'cyan', 'teal', 'white', 'grey-lighten-3', 'grey-lighten-4', 'grey-lighten-5']

const isLightColor = computed(() => {
  return lightColors.some(c => props.color.toLowerCase().includes(c.toLowerCase()))
})

const textColorClass = computed(() => {
  return isLightColor.value ? 'text-grey-darken-4' : 'text-white'
})

const iconColor = computed(() => {
  return isLightColor.value ? 'grey-darken-3' : 'white'
})

const avatarBgColor = computed(() => {
  if (isLightColor.value) {
    return 'rgba(0,0,0,0.1)'
  }
  return `${props.color}-lighten-1`
})

const dividerColor = computed(() => {
  return isLightColor.value ? 'grey-darken-2' : 'white'
})

const formattedValue = computed(() => {
  if (typeof props.value === 'number') {
    return new Intl.NumberFormat().format(props.value)
  }
  return props.value
})
</script>

<style scoped>
.stat-card {
  transition: transform 0.2s ease-in-out;
}

.stat-card:hover {
  transform: translateY(-4px);
}

.stat-card.dark-text .v-card-actions {
  color: #212121;
}
</style>
