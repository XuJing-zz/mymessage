<script setup>
defineProps({ status: { type: Number, default: 0 } })

const TEXT = { 0: '待审核', 1: '已通过', 2: '已驳回' }
</script>

<template>
  <span class="tag" :class="'s' + status">{{ TEXT[status] || '未知' }}</span>
</template>