<template>
  <div class="row">
    <div class="col-md-3">
      <div class="form-group">
        <label>Status:</label>
        <select
          :value="status"
          @change="$emit('update:status', $event.target.value)"
          class="form-control"
        >
          <option value="">All Statuses</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="banned">Banned</option>
        </select>
      </div>
    </div>
    <div class="col-md-3">
      <div class="form-group">
        <label>Role:</label>
        <select
          :value="role"
          @change="$emit('update:role', $event.target.value)"
          class="form-control"
        >
          <option value="">All Roles</option>
          <option v-for="r in roles" :key="r.id" :value="r.id">
            {{ r.display_name || r.name }}
          </option>
        </select>
      </div>
    </div>
    <div class="col-md-4">
      <div class="form-group">
        <label>Search:</label>
        <input
          type="text"
          :value="search"
          @input="$emit('update:search', $event.target.value)"
          class="form-control"
          placeholder="Search by name, email, or ID..."
        >
      </div>
    </div>
    <div class="col-md-2">
      <div class="form-group">
        <label>&nbsp;</label>
        <button
          @click="$emit('reset')"
          class="btn btn-default btn-block"
        >
          <i class="fas fa-undo"></i> Reset
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  status: String,
  role: String,
  search: String,
  roles: Array
})

defineEmits(['update:status', 'update:role', 'update:search', 'reset'])
</script>
