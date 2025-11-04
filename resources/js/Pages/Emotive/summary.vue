<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, router } from '@inertiajs/vue3';

// Define props
const props = defineProps({
  patient: {
    type: [Number, String],
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
  if (!emotiveSteps.value || emotiveSteps.value.length === 0) {
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
      // window.location.href = route('patient.index');
    router.visit(route('patient.index'));
};
</script>

<template>
       <body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-8 h-8 object-contain rounded-full">
            </div>
            <a href="patient.php" class="text-xl font-bold text-motivaid-teal">MotivAid</a>
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
            <h2 class="text-2xl font-semibold mb-2">Emotive Steps</h2>
            <p class="text-motivaid-teal/90 font-medium">Case Summary</p>
        </section>

        <!-- Alert Banner -->
        <section class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">Case Summary - Patient Emotive Steps</p>
                </div>
            </div>
        </section>

        <!-- Case Summary Table -->
        <section class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Case Summary</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-for="step in emotiveSteps" :key="step.id">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ step.action }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">Done</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ new Date(step.completed_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}</td>
                        </tr>
                        <tr v-if="!hasSteps">
                            <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">
                                No steps recorded yet
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Time Performance Message -->
        <section v-if="hasSteps && timePerformance" class="mb-6">
            <div v-if="timePerformance.withinTimeLimit" class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-green-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800">{{ timePerformance.message }}</p>
                    </div>
                </div>
            </div>
            <div v-else class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400 mt-0.5" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ timePerformance.message }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Action Button -->
        <div class="mb-6 flex justify-end">
            <button 
                class="bg-motivaid-teal text-white font-medium px-4 py-2 rounded-md hover:bg-motivaid-teal-dark focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                @click="handleDoneClick"
            >
                Done
            </button>
        </div>
    </main>



    <!-- Add Patient Modal -->
    <div v-if="showAddPatientModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" @click="closeModal"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Add another patient?</h3>
            <p class="text-gray-600 text-center mb-6">Do you want to add another patient?</p>
            <div class="flex justify-center space-x-4">
                <button 
                    @click="closeModal"
                    class="bg-gray-200 text-gray-800 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-200"
                >
                    No
                </button>
                <button 
                    @click="redirectToPatientList"
                    class="bg-motivaid-teal text-white px-4 py-2 rounded-md hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                >
                    Yes
                </button>
            </div>
        </div>
    </div>
    <div class="mt-8 text-center text-gray-400 text-sm">
        &copy; 2025 MotivAid. All rights reserved.
    </div>
</body>
</template>