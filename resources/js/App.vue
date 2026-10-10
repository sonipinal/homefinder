<script setup>
// ===== IMPORTS =====
// ref: reactive data (page updates when it changes)
// computed: value calculated from other data (recalculates automatically)
// onMounted: runs code once when the page first loads
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'                          // makes HTTP requests to our Laravel API
import HomeCard from './components/HomeCard.vue'   // child component that shows one home

// ===== PAGE STATE =====
const homes = ref([])        // homes for the CURRENT page only (comes from the API)
const loading = ref(true)    // true while waiting for the API, shows "Loading..."
const error = ref(null)      // error message if the API call fails

// ===== PAGINATION =====
// Laravel's paginate() returns current_page and last_page in the JSON
const currentPage = ref(1)
const lastPage = ref(1)

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

// ===== LOAD HOMES FROM THE API =====
// One function reused for the first load AND every page change.
// page = 1 is the default if no page number is given.
async function loadHomes(page = 1) {
  loading.value = true
  error.value = null   // clear any old error before trying again

  try {
    // GET /api/homes?page=2  ->  Laravel HomeController@index
    const response = await axios.get('/api/homes', { params: { page } })

    // response.data      = the JSON from Laravel (axios puts it here)
    // response.data.data = the list of homes (paginate() puts it inside "data")
    homes.value = response.data.data
    currentPage.value = response.data.meta.current_page
    lastPage.value = response.data.meta.last_page
  } catch (e) {
    error.value = 'Could not load homes.'
  } finally {
    // finally ALWAYS runs, success or error, so loading never gets stuck
    loading.value = false
  }
}

// ===== SEARCH FILTERS =====
// Connected to the inputs with v-model (two-way binding)
const city = ref('')
const maxPrice = ref('')
const minBedrooms = ref('')

// Homes that match ALL filters. Recalculates automatically when any filter changes.
// NOTE: this only filters the CURRENT page (browser-side).
// For thousands of homes, filtering should move to the Laravel API (Week 2, Day 1).
const filteredHomes = computed(() => {
  return homes.value.filter(home => {
    // Empty box = no filter (every home passes). Otherwise partial, case-insensitive match.
    const matchesCity = city.value === '' ||
      home.community.city.toLowerCase().includes(city.value.toLowerCase())

    // Number() turns the typed text into a real number before comparing
    const matchesPrice = maxPrice.value === '' ||
      home.price <= Number(maxPrice.value)

    const matchesBeds = minBedrooms.value === '' ||
      home.bedrooms >= Number(minBedrooms.value)

    // Keep the home only if it matches ALL three (&& = AND)
    return matchesCity && matchesPrice && matchesBeds
  })
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
      <!-- homes.length is the CURRENT page only, not all homes (fixed in Week 2) -->
      <p>Showing {{ filteredHomes.length }} of {{ homes.length }} homes</p>
    </div>

    <!-- ===== STATUS MESSAGES ===== -->
    <!-- Only ONE of these shows: v-if / v-else-if work like if / else if -->
    <p v-if="loading">Loading homes...</p>
    <p v-else-if="error">{{ error }}</p>
    <p v-else-if="filteredHomes.length === 0">No homes found.</p>

    <!-- ===== HOME CARDS ===== -->
    <!-- v-for: one HomeCard per home. :key helps Vue track each card efficiently.
         :home and :is-favorite = props (data DOWN to the child)
         @toggle-favorite      = event listener (event UP from the child) -->
    <HomeCard
      v-for="home in filteredHomes"
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