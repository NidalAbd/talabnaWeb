<template>
  <v-card
    :color="color"
    :to="to"
    hover
    class="stat-card"
  >
    <v-card-text>
      <v-row align="center" no-gutters>
        <v-col>
          <div class="text-h4 font-weight-bold text-white mb-2">
            {{ formattedValue }}
          </div>
          <div class="text-subtitle-1 text-white text-opacity-90">
            {{ title }}
          </div>
        </v-col>
        <v-col cols="auto">
          <v-avatar
            size="56"
            :color="`${color}-lighten-1`"
          >
            <v-icon size="32" color="white">
              {{ icon }}
            </v-icon>
          </v-avatar>
        </v-col>
      </v-row>
    </v-card-text>

    <v-divider color="white" opacity="0.2" />

    <v-card-actions class="text-white text-opacity-90 px-4 py-2">
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
</style>
