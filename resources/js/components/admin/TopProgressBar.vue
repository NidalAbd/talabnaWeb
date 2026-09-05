<template>
  <div class="top-progress-track" v-show="isLoading">
    <div class="top-progress-fill" :style="{ width: progress + '%' }"></div>
  </div>
</template>

<script setup>
import { useTopProgress } from '../../composables/useTopProgress'

const { isLoading, progress } = useTopProgress()
</script>

<style scoped>
.top-progress-track {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  height: 3px;
  z-index: 99999;
  pointer-events: none;
}

.top-progress-fill {
  height: 100%;
  background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
  box-shadow: 0 0 8px rgba(102, 126, 234, 0.6);
  transition: width 0.25s ease;
}
</style>
