<template>
  <div style="width: 100%; height: 100%; display: flex; justify-content: center; align-items: center;">
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
let mountCount = 0

onMounted(() => {
  mountCount++
  console.log(`PieChart: onMounted (mount #${mountCount})`, {
    hasData: !!props.data,
    dataKeys: props.data ? Object.keys(props.data) : []
  })
  // Use nextTick to ensure DOM is fully rendered
  setTimeout(() => {
    createChart()
  }, 100)
})

onBeforeUnmount(() => {
  console.log('PieChart: onBeforeUnmount')
  if (chart) {
    chart.destroy()
    chart = null
  }
})

watch(() => props.data, () => {
  updateChart()
}, { deep: true })

const createChart = () => {
  console.log('PieChart: createChart called')
  if (!chartCanvas.value || !props.data) {
    console.log('PieChart: Missing canvas or data', { canvas: !!chartCanvas.value, data: !!props.data })
    return
  }

  // Destroy existing chart if it exists
  if (chart) {
    console.log('PieChart: Destroying existing chart')
    chart.destroy()
  }

  const ctx = chartCanvas.value.getContext('2d')

  // Set canvas dimensions before creating chart
  const container = chartCanvas.value.parentElement
  const containerWidth = container.clientWidth
  const containerHeight = container.clientHeight || props.height

  // Use the smaller dimension to make the chart fit properly
  const size = Math.min(containerWidth, containerHeight, props.height)

  chartCanvas.value.width = size
  chartCanvas.value.height = size

  chart = new Chart(ctx, {
    type: 'pie',
    data: props.data,
    options: {
      responsive: false,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          position: 'bottom'
        }
      }
    }
  })

  console.log('PieChart: Chart created successfully', { width: size, height: size })
}

const updateChart = () => {
  console.log('PieChart: updateChart called')
  if (!chart || !props.data) {
    console.log('PieChart: Missing chart or data', { chart: !!chart, data: !!props.data })
    return
  }

  chart.data = props.data
  chart.update()
  console.log('PieChart: Chart updated successfully')
}
</script>
