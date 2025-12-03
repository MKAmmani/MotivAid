<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, router } from '@inertiajs/vue3';

// Define props
const props = defineProps({
  patient: {
    type: Object,
    required: false,
    default: null
  },
  steps: {
    type: Array,
    default: () => []
  }
});

// Reactive data
const realtimeClock = ref('');
const emotiveSteps = ref(props.steps || []);

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

// Fetch patient-specific Emotive steps
const fetchPatientSteps = () => {
  if (props.patient) {
    router.get(route('emotive.steps.patient', props.patient.id), {}, {
      preserveState: true,
      preserveScroll: true,
      onSuccess: (page) => {
        if (page.props.steps) {
          emotiveSteps.value = page.props.steps;
        }
      },
      onError: (errors) => {
        console.error('Error fetching patient steps:', errors);
      }
    });
  }
};

// Initialize clock when component mounts
onMounted(() => {
  updateClock(); // Initial call to set the time immediately
  clockInterval = setInterval(updateClock, 1000);

  // Fetch patient steps if we have a patient
  if (props.patient && (!props.steps || props.steps.length === 0)) {
    fetchPatientSteps();
  }
});

// Clean up interval when component unmounts
onUnmounted(() => {
  if (clockInterval) {
    clearInterval(clockInterval);
  }
});

// Computed property to check if we have steps to display
const hasSteps = computed(() => {
  return emotiveSteps.value && emotiveSteps.value.length > 0;
});

// Computed property to calculate time difference and determine performance
const timePerformance = computed(() => {
  if (!emotiveSteps.value || emotiveSteps.value.length < 2) {
    return null;
  }

  // Get first and last step
  const firstStep = emotiveSteps.value[0];
  const lastStep = emotiveSteps.value[emotiveSteps.value.length - 1];

  // Convert to Date objects
  const firstTime = new Date(firstStep.completed_at);
  const lastTime = new Date(lastStep.completed_at);

  // Calculate difference in milliseconds
  const diffMs = lastTime.getTime() - firstTime.getTime();
  
  // Convert to minutes
  const diffMinutes = diffMs / (1000 * 60);
  
  // Determine if time was within 15 minutes
  const withinTimeLimit = diffMinutes <= 15;
  
  return {
    firstTime: firstTime,
    lastTime: lastTime,
    diffMinutes: diffMinutes,
    withinTimeLimit: withinTimeLimit,
    message: withinTimeLimit 
      ? `Great job! You completed all steps in ${diffMinutes.toFixed(2)} minutes (within the 15-minute target).` 
      : `The steps took ${diffMinutes.toFixed(2)} minutes, which is longer than the recommended 15-minute timeframe. Try to complete within 15 minutes for optimal care.`
  };
});

// Computed property to format patient information for display
const displayedPatientInfo = computed(() => {
  if (!props.patient) {
    return {};
  }

  // Helper to capitalize first letter if it's a string
  const capitalizeString = (str) => {
    if (typeof str === 'string' && str.length > 0) {
      return str.charAt(0).toUpperCase() + str.slice(1);
    }
    return str;
  };

  return {
    'Name': props.patient.name,
    'Age': props.patient.age,
    'History of Antenatal Visit': capitalizeString(props.patient.history_of_antenatal_visit),
    'Gravida/Parity': props.patient.gravida,
    'History of Previous PPH': capitalizeString(props.patient.history_of_previous_pph),
    'History of Caesarean Section': capitalizeString(props.patient.history_Of_ceaserian_section),
    'Type of Pregnancy': capitalizeString(props.patient.type_of_pregenency),
    'Gestational Age': props.patient.gestational_age,
    'Hospital': props.patient.hospital,
  };
});

// Modal state
const showAddPatientModal = ref(false);

// Handle done button click
const handleDoneClick = () => {
  showAddPatientModal.value = true;
};

// Close modal function
const closeModal = () => {
  showAddPatientModal.value = false;
};

//redirect to patient list after adding another patient
const redirectToPatientList = () => {
    router.visit(route('patient.index'));
};
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                        <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-10 h-10 object-contain rounded-full">
                    </div>
                    <h1 class="text-2xl font-bold text-gray-800">MotivAid</h1>
                </div>
                
                <div class="hidden md:flex items-center space-x-2 text-sm text-gray-500">
                    <span class="px-2.5 py-0.5 rounded-full bg-gray-100 text-gray-700 font-medium">Summary</span>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-600 font-medium">Case Summary</span>
                </div>
            </div>

            <div class="flex items-center space-x-6">
                <!-- User dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button type="button" class="flex items-center space-x-2 text-sm text-gray-700 hover:text-motivaid-pink focus:outline-none transition-colors">
                                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                                    <span class="font-medium text-gray-700">{{ $page.props.auth.user.name.charAt(0) }}</span>
                                </div>
                                <span>{{ $page.props.auth.user.name }}</span>
                                <svg class="ml-1 h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
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

                <div class="flex items-center space-x-2 text-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                    <div class="text-gray-600 font-medium">{{ realtimeClock }}</div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-6 py-8">
            <!-- Title Section -->
            <section class="mb-8">
                <div class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 rounded-2xl shadow-lg p-8 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-3xl font-bold mb-2">Case Summary</h2>
                            <p class="text-motivaid-pink-light font-medium">Patient Motive Steps Completion</p>
                        </div>
                        <div class="hidden md:block">
                            <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4">
                                <div class="text-center">
                                    <div class="text-3xl font-bold">{{ emotiveSteps.length }}</div>
                                    <div class="text-sm opacity-90">Steps Completed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column -->
                <div class="lg:col-span-1 space-y-8">
                    <!-- Patient Information Section -->
                    <section v-if="props.patient" class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 rounded-full bg-motivaid-pink/10 flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-motivaid-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-800">Patient Information</h3>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-4">
                            <div v-for="(value, label) in displayedPatientInfo" :key="label" class="space-y-1">
                                <p class="text-sm text-gray-500">{{ label }}</p>
                                <p class="text-base font-medium text-gray-900">{{ value }}</p>
                            </div>
                        </div>
                    </section>

                    <!-- Time Performance Message -->
                    <section v-if="hasSteps && timePerformance">
                        <div v-if="timePerformance.withinTimeLimit" class="bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-2xl p-6 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-green-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-green-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-green-800">Performance Summary</h4>
                                    <p class="text-green-700 mt-1">{{ timePerformance.message }}</p>
                                </div>
                            </div>
                        </div>
                        <div v-else class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl p-6 shadow-sm">
                            <div class="flex items-start">
                                <div class="flex-shrink-0">
                                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <h4 class="text-lg font-semibold text-red-800">Performance Summary</h4>
                                    <p class="text-red-700 mt-1">{{ timePerformance.message }}</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Right Column -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Case Summary Table -->
                    <section class="bg-white rounded-2xl shadow-sm p-8 border border-gray-100">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-xl font-semibold text-gray-800 flex items-center">
                                <span class="w-10 h-10 rounded-full bg-motivaid-pink/10 flex items-center justify-center mr-3">
                                    <svg class="w-5 h-5 text-motivaid-pink" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </span>
                                Case Summary
                            </h3>
                            <div class="text-sm text-gray-500">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <svg class="mr-1.5 h-2 w-2 text-green-600" fill="currentColor" viewBox="0 0 8 8">
                                        <circle cx="4" cy="4" r="3" />
                                    </svg>
                                    {{ emotiveSteps.length }} Steps Complete
                                </span>
                            </div>
                        </div>
                        
                        <div class="overflow-x-auto rounded-lg border border-gray-200">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Action</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-700 uppercase tracking-wider">Time</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="step in emotiveSteps" :key="step.id" class="hover:bg-gray-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ step.action }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <svg class="mr-1.5 h-2 w-2 text-green-600" fill="currentColor" viewBox="0 0 8 8">
                                                    <circle cx="4" cy="4" r="3" />
                                                </svg>
                                                Done
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(step.completed_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</td>
                                    </tr>
                                    <tr v-if="!hasSteps">
                                        <td colspan="3" class="px-6 py-12 text-center">
                                            <div class="flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                </svg>
                                                <p class="text-sm text-gray-500">No steps recorded yet</p>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <!-- Action Button -->
                    <div class="flex justify-end">
                        <button 
                            class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white font-semibold px-6 py-3 rounded-xl hover:from-motivaid-pink hover:to-motivaid-pink shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2"
                            @click="handleDoneClick"
                        >
                            <span class="flex items-center">
                                <svg class="mr-2 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                Complete Summary
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Add Patient Modal -->
        <div v-if="showAddPatientModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" @click="closeModal"></div>
            <div class="relative bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md mx-auto transform transition-all">
                <div class="flex flex-col items-center">
                    <div class="w-16 h-16 rounded-full bg-green-100 flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 text-center mb-2">Add Another Patient?</h3>
                    <p class="text-gray-600 text-center mb-8">Would you like to add another patient to the system?</p>
                    <div class="flex space-x-4 w-full">
                        <button 
                            @click="closeModal"
                            class="flex-1 bg-gray-100 text-gray-800 px-6 py-3 rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-300 font-medium transition-colors"
                        >
                            No
                        </button>
                        <button 
                            @click="redirectToPatientList"
                            class="flex-1 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-6 py-3 rounded-xl hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink font-medium transition-all"
                        >
                            Yes
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <footer class="py-8 text-center text-gray-500 text-sm">
            <div class="max-w-7xl mx-auto px-6">
                <div class="border-t border-gray-200 pt-8">
                    <p>&copy; {{ new Date().getFullYear() }} MotivAid. All rights reserved.</p>
                    <p class="mt-2 text-gray-400">Advanced Healthcare Management System</p>
                </div>
            </div>
        </footer>
    </div>
</template>
