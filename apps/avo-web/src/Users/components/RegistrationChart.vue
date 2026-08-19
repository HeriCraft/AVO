<template>
  <div class="registration-chart">
    <h3>Registration Overview</h3>
    <div class="chart-placeholder">
      <p>Total Users: {{ totalUsers }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  totalUsers: {
    type: Number,
    required: true,
    default: 0
  }
});
</script>

<style scoped>
.registration-chart {
  padding: 16px;
  border: 1px solid #ddd;
  border-radius: 8px;
}
.chart-placeholder {
  height: 150px;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: #f9f9f9;
}
</style>
