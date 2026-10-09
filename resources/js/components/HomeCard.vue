<script setup>
defineProps({
  home: { type: Object, required: true },
  isFavorite: { type: Boolean, default: false }
})

const emit = defineEmits(['toggle-favorite'])
</script>

<template>
  <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
    <h3>{{ home.address }}</h3>
    <p>Price: ${{ home.price.toLocaleString() }}</p>
    <p>Bedrooms: {{ home.bedrooms }}</p>
    <p>Community: {{ home.community.name }}</p>

    <button @click="emit('toggle-favorite', home.id)">
      {{ isFavorite ? '★ Favorited' : '☆ Add to favorites' }}
    </button>
  </div>
</template>