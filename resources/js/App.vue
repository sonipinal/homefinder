<script setup>
// ===== IMPORTS =====
// ref: reactive data (page updates when it changes)
// computed: value calculated from other data (recalculates automatically)
// watch: runs code when a value changes (used for the search filters)
// onMounted: runs code once when the page first loads
import { ref, computed, watch, onMounted } from 'vue'
import axios from 'axios'                          // makes HTTP requests to our Laravel API
import HomeCard from './components/HomeCard.vue'   // child component that shows one home

// ===== PAGE STATE =====
const homes = ref([])        // homes for the CURRENT page only (comes from the API)
const loading = ref(true)    // true while waiting for the API
const error = ref(null)      // error message if the API call fails

// ===== PAGINATION =====
// The API Resource puts pagination info inside "meta"
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)         // total homes that match the filters (ALL pages)

// ===== FAVORITES =====
// The parent (this file) OWNS the favorites list.
// HomeCard only sends an event (emit); it never changes this list itself.
const favorites = ref([])    // list of favorite home IDs

function toggleFavorite(id) {
  if (favorites.value.includes(id)) {
    favorites.value = favorites.value.filter(favId => favId !== id)
  } else {
    favorites.value.push(id)
  }
}

// ===== SEARCH FILTERS =====
// Connected to the inputs with v-model (two-way binding).
const city = ref('')
const maxPrice = ref('')
const minBedrooms = ref('')

// true if any filter has a value (used to show the "Clear filters" button)
const hasFilters = computed(() => city.value !== '' || maxPrice.value !== '' || minBedrooms.value !== '')

function clearFilters() {
  city.value = ''
  maxPrice.value = ''
  minBedrooms.value = ''
  // the watch below notices the change and reloads the homes
}

// ===== LOAD HOMES FROM THE API =====
// Filtering happens in Laravel (server-side), so it works across ALL pages.
async function loadHomes(page = 1) {
  loading.value = true
  error.value = null

  try {
    // GET /api/homes?page=1&city=Frisco&max_price=400000  ->  HomeController@index
    // "|| undefined" = if the box is empty, don't send that filter at all
    const response = await axios.get('/api/homes', {
      params: {
        page,
        city: city.value || undefined,
        max_price: maxPrice.value || undefined,
        min_bedrooms: minBedrooms.value || undefined,
      },
    })

    homes.value = response.data.data
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
    total.value = response.data.meta.total
  } catch (e) {
    error.value = 'We could not load homes. Check your connection and try again.'
  } finally {
    loading.value = false
  }
}

// ===== DEBOUNCED SEARCH =====
// Wait 400 ms after the user STOPS typing, then search (1 request instead of 1 per letter).
// Always go back to page 1, because the old page number may not exist anymore.
let timer = null
watch([city, maxPrice, minBedrooms], () => {
  clearTimeout(timer)
  timer = setTimeout(() => loadHomes(1), 400)
})

// ===== START =====
onMounted(() => loadHomes())
</script>

<template>
  <div class="min-h-screen">
    <!-- ===== HEADER ===== -->
    <header class="bg-oak text-white">
      <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6">
        <div class="flex items-center gap-2">
          <!-- small house logo -->
          <svg viewBox="0 0 24 24" class="h-7 w-7 text-gold" fill="currentColor" aria-hidden="true">
            <path d="M12 3 2 11h3v10h6v-6h2v6h6V11h3z" />
          </svg>
          <span class="text-lg font-extrabold tracking-tight">Home Finder</span>
        </div>
        <span class="text-sm font-medium text-white/80">
          <span class="text-gold">★</span> {{ favorites.length }} saved
        </span>
      </div>
    </header>

    <!-- ===== HERO + SEARCH ===== -->
    <section class="bg-oak pb-16 text-white">
      <div class="mx-auto max-w-6xl px-4 pt-8 sm:px-6 sm:pt-12">
        <h1 class="max-w-3xl text-3xl font-extrabold leading-tight tracking-tight sm:text-5xl">
          Find a new home in North Texas
        </h1>
        <p class="mt-3 max-w-xl text-base text-white/75 sm:text-lg">
          Search by city, price, and bedrooms. Results update as you type.
        </p>
      </div>
    </section>

    <!-- Search panel overlaps the bottom of the green hero -->
    <div class="mx-auto -mt-10 max-w-6xl px-4 sm:px-6">
      <form
        @submit.prevent
        class="grid gap-3 rounded-2xl border border-line bg-white p-4 sm:grid-cols-[2fr_1.5fr_1fr_auto] sm:items-end sm:p-5"
      >
        <label class="block">
          <span class="text-sm font-semibold text-ink">City</span>
          <input
            v-model="city"
            placeholder="Frisco, Plano..."
            class="mt-1 h-11 w-full rounded-lg border border-line px-3 text-ink placeholder:text-muted/70 focus:border-oak focus:outline-none focus:ring-2 focus:ring-oak/20"
          />
        </label>

        <label class="block">
          <span class="text-sm font-semibold text-ink">Max price</span>
          <input
            v-model="maxPrice"
            type="number"
            min="0"
            step="10000"
            placeholder="400000"
            class="mt-1 h-11 w-full rounded-lg border border-line px-3 text-ink placeholder:text-muted/70 focus:border-oak focus:outline-none focus:ring-2 focus:ring-oak/20"
          />
        </label>

        <label class="block">
          <span class="text-sm font-semibold text-ink">Bedrooms</span>
          <select
            v-model="minBedrooms"
            class="mt-1 h-11 w-full rounded-lg border border-line bg-white px-3 text-ink focus:border-oak focus:outline-none focus:ring-2 focus:ring-oak/20"
          >
            <option value="">Any</option>
            <option value="2">2+</option>
            <option value="3">3+</option>
            <option value="4">4+</option>
          </select>
        </label>

        <button
          v-if="hasFilters"
          type="button"
          @click="clearFilters"
          class="h-11 rounded-lg px-4 text-sm font-semibold text-oak hover:bg-mist focus:outline-none focus-visible:ring-2 focus-visible:ring-oak"
        >
          Clear filters
        </button>
      </form>
    </div>

    <!-- ===== RESULTS ===== -->
    <main class="mx-auto max-w-6xl px-4 py-8 sm:px-6">
      <!-- Count -->
      <p v-if="!loading && !error" class="mb-4 text-sm font-semibold text-muted">
        {{ total }} {{ total === 1 ? 'home' : 'homes' }} found
      </p>

      <!-- Loading: 3 grey placeholder cards -->
      <div v-if="loading" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3" aria-label="Loading homes">
        <div v-for="n in 3" :key="n" class="overflow-hidden rounded-2xl border border-line bg-white motion-safe:animate-pulse">
          <div class="aspect-[4/3] bg-line/60"></div>
          <div class="space-y-2 p-5">
            <div class="h-6 w-1/2 rounded bg-line"></div>
            <div class="h-4 w-1/3 rounded bg-line"></div>
            <div class="h-4 w-2/3 rounded bg-line"></div>
          </div>
        </div>
      </div>

      <!-- Error -->
      <div v-else-if="error" class="rounded-2xl border border-line bg-white p-8 text-center">
        <p class="font-semibold text-ink">{{ error }}</p>
        <button
          type="button"
          @click="loadHomes(currentPage)"
          class="mt-4 rounded-lg bg-oak px-4 py-2.5 text-sm font-semibold text-white hover:bg-oak-light focus:outline-none focus-visible:ring-2 focus-visible:ring-oak focus-visible:ring-offset-2"
        >
          Try again
        </button>
      </div>

      <!-- Empty -->
      <div v-else-if="homes.length === 0" class="rounded-2xl border border-line bg-white p-8 text-center">
        <p class="font-semibold text-ink">No homes match your search.</p>
        <p class="mt-1 text-sm text-muted">Try a higher price or fewer bedrooms.</p>
        <button
          v-if="hasFilters"
          type="button"
          @click="clearFilters"
          class="mt-4 rounded-lg bg-oak px-4 py-2.5 text-sm font-semibold text-white hover:bg-oak-light focus:outline-none focus-visible:ring-2 focus-visible:ring-oak focus-visible:ring-offset-2"
        >
          Clear filters
        </button>
      </div>

      <!-- Home cards: 1 column on phones, 2 on tablets, 3 on desktop -->
      <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <HomeCard
          v-for="home in homes"
          :key="home.id"
          :home="home"
          :is-favorite="favorites.includes(home.id)"
          @toggle-favorite="toggleFavorite"
        />
      </div>

      <!-- ===== PAGINATION ===== -->
      <nav
        v-if="!loading && !error && lastPage > 1"
        class="mt-10 flex items-center justify-center gap-4"
        aria-label="Pages"
      >
        <button
          type="button"
          :disabled="currentPage === 1"
          @click="loadHomes(currentPage - 1)"
          class="rounded-lg border border-line bg-white px-4 py-2 text-sm font-semibold text-ink hover:border-oak disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-oak"
        >
          Previous
        </button>
        <span class="text-sm font-medium text-muted">Page {{ currentPage }} of {{ lastPage }}</span>
        <button
          type="button"
          :disabled="currentPage === lastPage"
          @click="loadHomes(currentPage + 1)"
          class="rounded-lg border border-line bg-white px-4 py-2 text-sm font-semibold text-ink hover:border-oak disabled:cursor-not-allowed disabled:opacity-40 focus:outline-none focus-visible:ring-2 focus-visible:ring-oak"
        >
          Next
        </button>
      </nav>
    </main>

    <!-- ===== FOOTER ===== -->
    <footer class="border-t border-line py-6 text-center text-sm text-muted">
      Home Finder is a personal portfolio project by Pinal Soni. All homes are sample data.
    </footer>
  </div>
</template>