<template>
  <div style="width: 100%; height: 100%;">
    <canvas ref="chartCanvas" />
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount, watch } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  height: {
    type: Number,
    default: 300
  }
})

const chartCanvas = ref(null)
let chart = null

onMounted(() => {
  console.log('LineChart: onMounted')
  // Use nextTick to ensure DOM is fully rendered
  setTimeout(() => {
    createChart()
  }, 100)
})

onBeforeUnmount(() => {
  console.log('LineChart: onBeforeUnmount')
  if (chart) {
    chart.destroy()
    chart = null
  }
})

watch(() => props.data, () => {
  updateChart()
}, { deep: true })

const createChart = () => {
  console.log('LineChart: createChart called')
  if (!chartCanvas.value || !props.data) {
    console.log('LineChart: Missing canvas or data', { canvas: !!chartCanvas.value, data: !!props.data })
    return
  }

  // Destroy existing chart if it exists
  if (chart) {
    console.log('LineChart: Destroying existing chart')
    chart.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')

  // Set canvas dimensions before creating chart
  const container = chartCanvas.value.parentElement
  const containerWidth = container.clientWidth
  const containerHeight = container.clientHeight || props.height

  chartCanvas.value.width = containerWidth
  chartCanvas.value.height = containerHeight

  chart = new Chart(ctx, {
    type: 'line',
    data: props.data,
    options: {
      responsive: false,
      maintainAspectRatio: false,
      scales: {
        y: {
          beginAtZero: true
        }
      }
    }
  })

  console.log('LineChart: Chart created successfully', { width: containerWidth, height: containerHeight })
}

const updateChart = () => {
  console.log('LineChart: updateChart called')
  if (!chart || !props.data) {
    console.log('LineChart: Missing chart or data', { chart: !!chart, data: !!props.data })
    return
  }

  chart.data = props.data
  chart.update()
  console.log('LineChart: Chart updated successfully')
}
</script>
