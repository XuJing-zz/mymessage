<script setup>
defineProps({
  page: { type: Number, default: 1 },
  pages: { type: Number, default: 1 },
})
defineEmits(['change'])
</script>

<template>
  <div class="pager" v-if="pages > 1">
    <a
      v-for="i in pages"
      :key="i"
      :class="{ active: i === page }"
      @click="$emit('change', i)"
    >{{ i }}</a>
  </div>
</template>