<script setup>
defineProps({ article: { type: Object, required: true } })
</script>

<template>
  <article class="card">
    <h3 class="art-title">
      <router-link :to="'/article/' + article.id">{{ article.title }}</router-link>
    </h3>
    <p class="muted">{{ article.username }} · {{ article.created_at }}</p>
    <p class="excerpt">{{ article.content }}</p>
    <div class="msg-foot">
      <span class="like-btn disabled">♡ {{ article.like_count }}</span>
      <span class="like-btn disabled">💬 {{ article.comment_count }}</span>
      <router-link class="del" :to="'/article/' + article.id">阅读全文 →</router-link>
    </div>
  </article>
</template>