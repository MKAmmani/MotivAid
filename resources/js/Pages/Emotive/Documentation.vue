<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, router } from '@inertiajs/vue3';

// Define props
const props = defineProps({
  patients: {
    type: Array,
    required: true,
  },
  meta: {
    type: Object,
    default: () => ({})
  }
});

// Reactive data
const realtimeClock = ref('');

// Clock interval reference
let clockInterval = null;

// Format time to HH:MM format
const formatTime = (date) => {
  let hours = date.getHours();
  let minutes = date.getMinutes();
  if (hours < 10) hours = '0' + hours;
  if (minutes < 10) minutes = '0' + minutes;
  return `${hours}:${minutes}`;
};

// Update the clock
const updateClock = () => {
  realtimeClock.value = formatTime(new Date());
};

// Initialize clock when component mounts
onMounted(() => {
  updateClock(); // Initial call to set the time immediately
  clockInterval = setInterval(updateClock, 1000);
});

// Clean up interval when component unmounts
onUnmounted(() => {
  if (clockInterval) {
    clearInterval(clockInterval);
  }
});

// Pagination functions
const goToPage = (page) => {
  if (page >= 1 && page <= (props.meta.last_page || 1)) {
    router.visit(route('documentation.index', { page: page }));
  }
};

const goToNextPage = () => {
  if (props.meta.current_page < props.meta.last_page) {
    router.visit(route('documentation.index', { page: props.meta.current_page + 1 }));
  }
};

const goToPrevPage = () => {
  if (props.meta.current_page > 1) {
    router.visit(route('documentation.index', { page: props.meta.current_page - 1 }));
  }
};

// Function to get page numbers to display in pagination
const getPageNumbers = () => {
  const pages = [];
  const maxVisiblePages = 5;
  
  let startPage = Math.max(1, props.meta.current_page - Math.floor(maxVisiblePages / 2));
  let endPage = Math.min(props.meta.last_page, startPage + maxVisiblePages - 1);
  
  // Adjust start page if needed to show maxVisiblePages
  if (endPage - startPage + 1 < maxVisiblePages) {
    startPage = Math.max(1, endPage - maxVisiblePages + 1);
  }
  
  for (let i = startPage; i <= endPage; i++) {
    pages.push(i);
  }
  
  return pages;
};


</script>
<template>
  <div class="min-h-screen flex flex-col bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-8 h-8 object-contain rounded-full">
            </div>
            <span class="text-xl font-bold text-motivaid-teal">MotivAid</span>
        </div>

        <div class="flex items-center space-x-4">
            <!-- User dropdown -->
            <div class="relative">
                <Dropdown align="right" width="48">
                    <template #trigger>
                        <button type="button" class="flex items-center text-sm text-gray-500 hover:text-gray-700 focus:outline-none">
                            <span>{{ $page.props.auth.user.name }}</span>
                            <svg class="ml-1 h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </template>

                    <template #content>
                        <DropdownLink :href="route('profile.edit')">
                            Profile
                        </DropdownLink>
                        <DropdownLink :href="route('logout')" method="post" as="button">
                            Log Out
                        </DropdownLink>
                    </template>
                </Dropdown>
            </div>

            <div class="text-sm text-gray-500">{{ realtimeClock }}</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto px-4 py-6">
        <!-- Title Section -->
        <section class="bg-motivaid-teal text-white px-6 py-8 rounded-lg mb-6">
            <h2 class="text-2xl font-semibold mb-2">Documentation</h2>
            <p class="text-motivaid-teal/90 font-medium">Patients Treated by You</p>
        </section>

        <!-- Patient List -->
        <section class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Your Patients</h3>
            
            <div v-if="patients.length > 0" class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Patient Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Age</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hospital</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Created Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="patient in patients" :key="patient.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ patient.name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.age }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ patient.hospital }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(patient.created_at).toLocaleDateString() }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <Link 
                                    :href="route('emotive.steps.patient', { patientId: patient.id })"
                                    class="text-motivaid-teal hover:text-teal-700 font-medium"
                                >
                                    View Summary
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination Controls -->
                <div class="flex items-center justify-between border-t border-gray-200 px-4 py-3 sm:px-6 mt-4">
                    <div class="flex flex-1 justify-between sm:hidden">
                        <button
                            @click="goToPrevPage"
                            :disabled="meta.current_page === 1"
                            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Previous
                        </button>
                        <button
                            @click="goToNextPage"
                            :disabled="meta.current_page === meta.last_page"
                            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                        >
                            Next
                        </button>
                    </div>
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700">
                                Showing
                                <span class="font-medium">{{ meta.from || 0 }}</span>
                                to
                                <span class="font-medium">{{ meta.to || 0 }}</span>
                                of
                                <span class="font-medium">{{ meta.total || 0 }}</span>
                                results
                            </p>
                        </div>
                        <div>
                            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
                                <button
                                    @click="goToPrevPage"
                                    :disabled="meta.current_page === 1"
                                    class="relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="sr-only">Previous</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-5-4.75a.75.75 0 010-1.08l5-4.75a.75.75 0 011.06.02z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <!-- Page numbers -->
                                <button
                                    v-for="page in getPageNumbers()"
                                    :key="page"
                                    @click="goToPage(page)"
                                    :class="[
                                        meta.current_page === page
                                            ? 'z-10 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-teal-600'
                                            : 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50',
                                        'relative inline-flex items-center px-4 py-2 text-sm font-semibold focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2'
                                    ]"
                                >
                                    {{ page }}
                                </button>
                                
                                <button
                                    @click="goToNextPage"
                                    :disabled="meta.current_page === meta.last_page"
                                    class="relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span class="sr-only">Next</span>
                                    <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l5 4.75a.75.75 0 010 1.08l-5 4.75a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>
            
            <div v-else class="text-center py-8">
                <p class="text-gray-500">No patients found. You haven't treated any patients yet.</p>
            </div>
        </section>
    </main>

    <div class="mt-8 text-center text-gray-400 text-sm">
        &copy; 2025 MotivAid. All rights reserved.
    </div>
  </div>
</template>