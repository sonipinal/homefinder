<script setup>
// ===== IMPORTS =====
// ref: reactive data (page updates when it changes)
// watch: runs code when a value changes (used for the search filters)
// onMounted: runs code once when the page first loads
import { ref, watch, onMounted } from 'vue'
import axios from 'axios'                          // makes HTTP requests to our Laravel API
import HomeCard from './components/HomeCard.vue'   // child component that shows one home

// ===== PAGE STATE =====
const homes = ref([])        // homes for the CURRENT page only (comes from the API)
const loading = ref(true)    // true while waiting for the API, shows "Loading..."
const error = ref(null)      // error message if the API call fails

// ===== PAGINATION =====
// The API Resource puts pagination info inside "meta"
const currentPage = ref(1)
const lastPage = ref(1)
const total = ref(0)         // total homes that match the filters (ALL pages, not just this one)

// ===== FAVORITES =====
// The parent (this file) OWNS the favorites list.
// HomeCard only sends an event (emit); it never changes this list itself.
const favorites = ref([])    // list of favorite home IDs

// Called when HomeCard emits 'toggle-favorite' with a home ID
function toggleFavorite(id) {
  if (favorites.value.includes(id)) {
    // Already a favorite: remove it (keep every ID except this one)
    favorites.value = favorites.value.filter(favId => favId !== id)
  } else {
    // Not a favorite yet: add it
    favorites.value.push(id)
  }
}

// ===== SEARCH FILTERS =====
// Connected to the inputs with v-model (two-way binding).
// Declared BEFORE loadHomes and watch, because both use them.
const city = ref('')
const maxPrice = ref('')
const minBedrooms = ref('')

// ===== LOAD HOMES FROM THE API =====
// One function reused for the first load, every page change, and every search.
// The FILTERING now happens in Laravel (server-side), so it works across ALL pages.
async function loadHomes(page = 1) {
  loading.value = true
  error.value = null   // clear any old error before trying again

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

    // response.data      = the JSON from Laravel (axios puts it here)
    // response.data.data = the list of homes for this page
    // response.data.meta = pagination info from the API Resource
    homes.value = response.data.data
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
    total.value = response.data.meta.total
  } catch (e) {
    error.value = 'Could not load homes.'
  } finally {
    // finally ALWAYS runs, success or error, so loading never gets stuck
    loading.value = false
  }
}

// ===== DEBOUNCED SEARCH =====
// When any filter changes, wait 400 ms after the user STOPS typing, then search.
// Typing "Frisco" sends 1 API request instead of 6 (one per letter).
// Always go back to page 1, because the old page number may not exist anymore.
let timer = null
watch([city, maxPrice, minBedrooms], () => {
  clearTimeout(timer)                       // cancel the previous wait
  timer = setTimeout(() => loadHomes(1), 400)
})

// ===== START =====
// Load page 1 when the page opens
onMounted(() => loadHomes())
</script>

<template>
  <div style="padding: 20px; font-family: sans-serif;">
    <h1>Home Finder</h1>
    <p>Favorites: {{ favorites.length }}</p>

    <!-- ===== SEARCH FORM ===== -->
    <!-- v-model = two-way binding: typing updates the variable, and the variable updates the input -->
    <div style="margin-bottom: 15px;">
      <input v-model="city" placeholder="City (e.g. Frisco)" />
      <input v-model="maxPrice" type="number" placeholder="Max price" />
      <select v-model="minBedrooms">
        <option value="">Any bedrooms</option>
        <option value="2">2+</option>
        <option value="3">3+</option>
        <option value="4">4+</option>
      </select>
      <!-- total comes from the API, so it counts matching homes on ALL pages -->
      <p v-if="!loading && !error">Found {{ total }} homes</p>
    </div>

    <!-- ===== STATUS MESSAGES ===== -->
    <!-- Only ONE of these shows: v-if / v-else-if work like if / else if -->
    <p v-if="loading">Loading homes...</p>
    <p v-else-if="error">{{ error }}</p>
    <p v-else-if="homes.length === 0">No homes match your search.</p>

    <!-- ===== HOME CARDS ===== -->
    <!-- v-for: one HomeCard per home. :key helps Vue track each card efficiently.
         :home and :is-favorite = props (data DOWN to the child)
         @toggle-favorite      = event listener (event UP from the child) -->
    <HomeCard
      v-for="home in homes"
      :key="home.id"
      :home="home"
      :is-favorite="favorites.includes(home.id)"
      @toggle-favorite="toggleFavorite"
    />

    <!-- ===== PAGINATION ===== -->
    <!-- Hidden while loading, on error, or when there is only one page -->
    <div v-if="!loading && !error && lastPage > 1" style="margin-top: 15px;">
      <!-- :disabled turns the button off on the first / last page -->
      <button :disabled="currentPage === 1" @click="loadHomes(currentPage - 1)">
        ← Previous
      </button>
      <span style="margin: 0 10px;">Page {{ currentPage }} of {{ lastPage }}</span>
      <button :disabled="currentPage === lastPage" @click="loadHomes(currentPage + 1)">
        Next →
      </button>
    </div>
  </div>
</template>