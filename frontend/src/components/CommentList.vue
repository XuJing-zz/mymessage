<script setup>
defineProps({
  comments: { type: Array, default: () => [] },
  canDelete: { type: Boolean, default: false },
})
defineEmits(['delete'])
</script>

<template>
  <div class="comment-list">
    <p v-if="comments.length === 0" class="muted">还没有评论。</p>
    <div class="comment" v-for="c in comments" :key="c.id">
      <div class="msg-head">
        <strong>{{ c.username }}</strong>
        <time>{{ c.created_at }}</time>
        <a v-if="canDelete" class="del" @click="$emit('delete', c.id)">删除</a>
      </div>
      <p style="white-space: pre-wrap">{{ c.content }}</p>
    </div>
  </div>
</template>