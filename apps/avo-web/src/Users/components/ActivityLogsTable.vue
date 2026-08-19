<template>
  <div class="activity-logs">
    <h3>Activity Logs</h3>
    <table>
      <thead>
        <tr>
          <th>User</th>
          <th>Action</th>
          <th>Description</th>
          <th>Date</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="log in logs" :key="log.id">
          <td>{{ log.user ? log.user.name : 'Unknown' }}</td>
          <td>{{ log.action }}</td>
          <td>{{ log.description }}</td>
          <td>{{ new Date(log.created_at).toLocaleString() }}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script setup>
defineProps({
  logs: {
    type: Array,
    required: true,
    default: () => []
  }
});
</script>

<style scoped>
.activity-logs table {
  width: 100%;
  border-collapse: collapse;
}
.activity-logs th, .activity-logs td {
  border: 1px solid #ccc;
  padding: 8px;
  text-align: left;
}
</style>
