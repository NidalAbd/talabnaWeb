<template>
  <div class="small-box" :class="`bg-${stat.color}`">
    <div class="inner">
      <h3>{{ stat.value }}</h3>
      <p>{{ stat.label }}</p>
    </div>
    <div class="icon">
      <i :class="stat.icon"></i>
    </div>
  </div>
</template>

<script setup>
defineProps({
  stat: {
    type: Object,
    required: true
  }
})
</script>
