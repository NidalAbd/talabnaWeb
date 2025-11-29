<template>
  <div class="small-box" :class="`bg-${stat.color}`">
    <div class="inner">
      <h3>{{ stat.value }}</h3>
      <p>{{ stat.label }}</p>
    </div>
    <div class="icon">
      <i :class="stat.icon"></i>
    </div>
    <a :href="stat.link" class="small-box-footer">
      More info <i class="fas fa-arrow-circle-right"></i>
    </a>
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
