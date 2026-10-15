<script setup>
// ===== PROPS (data DOWN from App.vue) =====
const props = defineProps({
  home: { type: Object, required: true },        // one home from the API (HomeResource)
  isFavorite: { type: Boolean, default: false }, // is this home in the favorites list?
})

// ===== EMITS (events UP to App.vue) =====
const emit = defineEmits(['toggle-favorite'])

// A soft "sky" color behind the house drawing, chosen by home id,
// so cards look different from each other until real photos are added.
const skies = ['bg-[#DCE8EF]', 'bg-[#E6EBDD]', 'bg-[#EFE6D8]']
const sky = skies[props.home.id % skies.length]
</script>

<template>
  <article class="overflow-hidden rounded-2xl border border-line bg-white">
    <!-- ===== PICTURE AREA (placeholder until real photos) ===== -->
    <div :class="['relative aspect-[4/3]', sky]">
      <!-- Simple house drawing -->
      <svg viewBox="0 0 200 150" class="absolute inset-x-0 bottom-0 mx-auto h-2/3 text-oak/80" aria-hidden="true">
        <path d="M30 150 V85 L100 35 L170 85 V150 Z" fill="currentColor" />
        <rect x="85" y="105" width="30" height="45" rx="2" fill="white" opacity="0.85" />
        <rect x="50" y="95" width="22" height="20" rx="2" fill="white" opacity="0.6" />
        <rect x="128" y="95" width="22" height="20" rx="2" fill="white" opacity="0.6" />
      </svg>

      <!-- City on the picture, so buyers see the location at a glance -->
      <span class="absolute left-3 top-3 rounded-full bg-white/90 px-3 py-1 text-xs font-semibold text-oak">
        {{ home.community.city }}
      </span>

      <!-- Favorite button: sends the home id UP to App.vue -->
      <button
        type="button"
        @click="emit('toggle-favorite', home.id)"
        :aria-pressed="isFavorite"
        :aria-label="isFavorite ? 'Remove from favorites' : 'Save to favorites'"
        class="absolute right-3 top-3 grid h-10 w-10 place-items-center rounded-full bg-white/90 text-xl leading-none focus:outline-none focus-visible:ring-2 focus-visible:ring-oak"
        :class="isFavorite ? 'text-gold' : 'text-muted hover:text-gold'"
      >
        {{ isFavorite ? '★' : '☆' }}
      </button>
    </div>

    <!-- ===== DETAILS ===== -->
    <div class="p-5">
      <!-- Price is the biggest thing: it's what buyers look at first -->
      <p class="text-2xl font-extrabold tracking-tight text-ink">{{ home.price_formatted }}</p>
      <p class="mt-1 text-sm font-medium text-muted">
        {{ home.bedrooms }} {{ home.bedrooms === 1 ? 'bedroom' : 'bedrooms' }}
      </p>
      <p class="mt-3 font-semibold text-ink">{{ home.address }}</p>
      <p class="text-sm text-muted">{{ home.community.name }}, {{ home.community.city }}</p>
    </div>
  </article>
</template>