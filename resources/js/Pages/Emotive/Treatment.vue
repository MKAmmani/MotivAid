<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';

// Get page object to access props
const page = usePage();

// Reactive data
const timerText = ref('15:00');
const showAlertPopup = ref(false);
const showSuccessPopup = ref(false);
const showEscalateSection = ref(false);
const showStillBleedingSection = ref(false);

// Get patient ID from page props if available
const patientId = page.props.patient ? page.props.patient.id : null;

// Timer state
let timerInterval = null;
let totalSeconds = 15 * 60; // 15 minutes
let alertInterval = null;
let alertTimeout = null;
const alertCountdown = ref(60);

// Stepper state
const steps = ref([
  { label: 'Massage Uterus', icon: '✋', completed: false },
  { label: 'Give Oxytocin', icon: '💉', completed: false },
  { label: 'Administer TXA', icon: '💊', completed: false },
  { label: 'Start IV Fluids', icon: '💉', completed: false }
]);
const currentStepIndex = ref(-1); // -1 means no steps completed initially

// Format time in MM:SS format
const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return String(minutes).padStart(2, '0') + ':' + String(remainingSeconds).padStart(2, '0');
};

// Timer functions
const startTimer = () => {
  // Initialize the timer display
  timerText.value = formatTime(totalSeconds);

  timerInterval = setInterval(() => {
    totalSeconds--;
    timerText.value = formatTime(totalSeconds);

    if (totalSeconds <= 0) {
      timerText.value = '00:00';
      clearInterval(timerInterval);
    }
  }, 1000);
};

// Update visibility of the "Still Bleeding?" section based on stepper progress
const updateStillBleedingVisibility = () => {
  const allStepsCompleted = steps.value.every(step => step.completed);
  showStillBleedingSection.value = allStepsCompleted;
};

// Handle step click
const handleStepClick = (index) => {
  // Only allow toggling this step or earlier (prevents skipping)
  if (index > currentStepIndex.value + 1) return;

  // Toggle step completion
  if (steps.value[index].completed) {
    // Unmark this step and reset all subsequent steps
    for (let i = index; i < steps.value.length; i++) {
      steps.value[i].completed = false;
    }
    currentStepIndex.value = index - 1;
  } else {
    // Mark this step done
    steps.value[index].completed = true;
    currentStepIndex.value = index;
  }
  
  updateStillBleedingVisibility();
};

// Handle PPH Yes click
const handlePphYes = () => {
  showEscalateSection.value = true;
  showPphAlertPopup();
};

// Handle PPH No click
const handlePphNo = () => {
  hideAlertPopup();
  showSuccessPopup.value = true;
};

// Close success popup
const closeSuccessPopup = () => {
  showSuccessPopup.value = false;
  setTimeout(() => {
    // Redirect to the summary page for the current patient if we have the ID, otherwise to patient index
    if (patientId) {
      router.visit(route('emotive.steps.patient', { patientId: patientId }));
    } else {
      // Fallback to patient index if no patient ID is available
      router.visit(route('documentation.index'));
    }
  }, 300); // Small delay to allow modal to close smoothly
};

// Show alert popup with countdown
const showPphAlertPopup = () => {
  alertCountdown.value = 60;
  showAlertPopup.value = true;
  
  // Clear any existing intervals/timers
  if (alertInterval) clearInterval(alertInterval);
  if (alertTimeout) clearTimeout(alertTimeout);
  
  // Set up countdown interval
  alertInterval = setInterval(() => {
    alertCountdown.value--;
    if (alertCountdown.value <= 0) {
      hideAlertPopup();
    }
  }, 1000);
  
  // Also set a timeout to ensure it closes after 60 seconds
  alertTimeout = setTimeout(() => {
    hideAlertPopup();
  }, 60000);
};

// Hide alert popup
const hideAlertPopup = () => {
  showAlertPopup.value = false;
  if (alertInterval) {
    clearInterval(alertInterval);
    alertInterval = null;
  }
  if (alertTimeout) {
    clearTimeout(alertTimeout);
    alertTimeout = null;
  }
};

// Handle alert doctor click
const handleAlertDoctor = () => {
  // Call the API to send the SMS alert using axios
  axios.get(route('send.sms'))
    .then(response => {
      if (response.data.success) {
        console.log('SMS alert sent successfully:', response.data.message);
      } else {
        console.error('Failed to send SMS alert:', response.data.message);
        alert('Failed to send alert: ' + response.data.message);
      }
    })
    .catch(error => {
      console.error('Error sending SMS alert:', error);
      if (error.response && error.response.data && error.response.data.message) {
        alert('An error occurred while sending the alert: ' + error.response.data.message);
      } else {
        alert('An error occurred while sending the alert.');
      }
    });
  
  hideAlertPopup();
};

// Handle dismiss button
const handleDismiss = () => {
  hideAlertPopup();
};

// Progress text computed property
const progressText = computed(() => {
  const completedCount = steps.value.filter(step => step.completed).length;
  const totalSteps = steps.value.length;
  const nextStep = Math.min(completedCount + 1, totalSteps);
  
  return `Step ${nextStep} of ${totalSteps}${completedCount > 0 ? ` — ${completedCount} completed` : ''}`;
});

// Initialize on component mount
onMounted(() => {
  startTimer();
});

// Clean up on component unmount
onUnmounted(() => {
  if (timerInterval) {
    clearInterval(timerInterval);
  }
  if (alertInterval) {
    clearInterval(alertInterval);
  }
  if (alertTimeout) {
    clearTimeout(alertTimeout);
  }
});

// Close modal and navigate to summary page or patient index
const closeAndNavigate = () => {
  showSuccessPopup.value = false;
  setTimeout(() => {
    // Redirect to the summary page for the current patient if we have the ID, otherwise to patient index
    if (patientId) {
      router.visit(route('emotive.steps.patient', { patientId: patientId }));
    } else {
      // Fallback to patient index if no patient ID is available
      router.visit(route('documentation.index'));
    }
  }, 300); // Small delay to allow modal to close smoothly
};

</script>

<template>
<body class="bg-gray-100 min-h-screen flex flex-col font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-10 h-10 object-contain rounded-full">
            </div>
            <h1 class="text-2xl font-bold text-motivaid-teal">MotivAid</h1>
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

            <div class="text-sm font-medium text-gray-600">Last Updated: 06:17 PM, Oct 24, 2025</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full px-6 py-8">
        <!-- Title Section -->
        <section class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-3xl font-bold text-motivaid-dark mb-3">Postpartum Hemorrhage (PPH) Treatment</h2>
            <p class="text-lg font-medium text-gray-600">Immediate Actions and Management Protocol</p>
        </section>

        <!-- Bundle Start -->
        <section class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-motivaid-dark">Initiate PPH Bundle</h3>
                <span class="text-sm text-gray-500">This will only take 15 minutes</span>
            </div>
                    
            <div class="flex justify-center">
                <div class="bg-pph-pink text-motivaid-teal px-6 py-3 rounded-lg font-mono text-2xl font-bold">{{ timerText }}</div>
            </div>
        </section>

        <!-- Initial Actions -->
        <section class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-motivaid-dark mb-4">Initial Actions</h3>
            <div class="space-y-4">
              <!-- Render each step -->
              <div v-for="(step, index) in steps" :key="index">
                <!-- Show the step if it's completed or it's the next step to complete -->
                <div v-if="index <= currentStepIndex + 1">
                  <button 
                    @click="handleStepClick(index)"
                    type="button"
                    class="w-full flex items-center justify-between px-4 py-3 rounded-lg font-medium transition focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                    :class="step.completed 
                      ? 'bg-green-100 text-green-800 border border-green-200' 
                      : 'bg-pph-pink text-motivaid-teal border border-motivaid-teal/20 hover:bg-motivaid-teal hover:text-white'"
                  >
                    <div class="flex items-center space-x-3">
                      <span class="text-lg">{{ step.icon }}</span>
                      <span>{{ index + 1 }}. {{ step.label }}</span>
                    </div>
                    <div class="ml-4 flex items-center space-x-2">
                      <span class="text-sm font-medium">{{ step.completed ? '✓ Done' : 'Mark Done' }}</span>
                    </div>
                  </button>
                </div>
              </div>
              
              <!-- Progress text -->
              <div class="text-sm text-gray-500">{{ progressText }}</div>
            </div>
        </section>

        <!-- Still Bleeding Section (hidden until all four steps are completed) -->
        <section v-if="showStillBleedingSection" class="bg-pph-pink rounded-xl p-6 mb-8">
            <h3 class="text-lg font-semibold text-motivaid-teal mb-4 text-center">Still Bleeding?</h3>
            <div class="grid grid-cols-2 gap-4">
              <button @click="handlePphYes" class="bg-white text-motivaid-teal px-4 py-3 rounded-lg font-medium border-2 border-motivaid-teal hover:bg-motivaid-teal hover:text-white focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                Yes
              </button>
              <button @click="handlePphNo" class="bg-white text-motivaid-teal px-4 py-3 rounded-lg font-medium border-2 border-motivaid-teal hover:bg-motivaid-teal hover:text-white focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                No
              </button>
            </div>
        </section>

        <!-- Success Popup -->
        <div v-if="showSuccessPopup" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/30" @click="closeSuccessPopup"></div>
            <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Success!</h3>
                <p class="text-gray-600 text-center mb-6">PPH treatment completed successfully. You just saved a life!</p>
                <div class="flex justify-center">
                    <button 
                        @click="closeSuccessPopup"
                        class="bg-motivaid-teal text-white px-4 py-2 rounded-md hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                    >
                        Check the Documentation
                    </button>
                </div>
            </div>
        </div>

        <!-- Centered Alert Popup (hidden by default) -->
        <div v-if="showAlertPopup" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="absolute inset-0 bg-black/30" @click="handleDismiss"></div>
            <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all">
                <div class="flex items-center justify-center w-16 h-16 mx-auto bg-red-100 rounded-full mb-4">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Alert the Doctor</h3>
                <p class="text-gray-600 text-center mb-6">A clinician should be notified immediately. <span class="font-medium">({{ alertCountdown }}s)</span></p>
                <div class="flex justify-center space-x-3">
                    <button @click="handleAlertDoctor" class="bg-motivaid-teal text-white px-4 py-2 rounded-md hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-motivaid-teal">
                        Alert Doctor
                    </button>
                    <button @click="handleDismiss" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 focus:outline-none">
                        Dismiss
                    </button>
                </div>
            </div>
        </div>

        <!-- Escalate Care Options -->
        <section v-if="showEscalateSection" class="bg-white rounded-xl shadow-sm p-6 mb-8">
            <h3 class="text-lg font-semibold text-motivaid-dark mb-4">Escalation and Advanced Interventions</h3>
            <p role="note" class="text-sm font-medium text-motivaid-dark bg-gradient-to-r from-pph-pink/30 via-white to-pph-pink/10 border border-motivaid-teal/20 rounded-xl px-4 py-3 shadow-md flex items-center space-x-3">
              <span class="text-2xl animate-pulse">🩺</span>
              <span>This section is for <span class="font-semibold text-motivaid-teal">doctors only</span>. Please escalate care if required.</span>
              <span class="ml-auto text-xs text-gray-500">Restricted</span>
            </p>
            
            <!-- Additional Uterotonic -->
            <div class="mb-6">
              <h4 class="text-md font-medium text-gray-700 mb-3">Additional Uterotonic</h4>
              <div class="grid grid-cols-2 gap-4">
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">💉</span>
                      <span>Give Uterotonic</span>
                  </div>
                </button>
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">❤️</span>
                      <span>Manage Shock</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Catheterize Bladder -->
            <div class="mb-6">
              <h4 class="text-md font-medium text-gray-700 mb-3">Bladder Management</h4>
              <div class="grid grid-cols-2 gap-4">
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">🩹</span>
                      <span>Catheterize Bladder</span>
                  </div>
                </button>
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">➡️</span>
                      <span>Apply NASG</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Clear Uterus/Cervix -->
            <div class="mb-6">
              <h4 class="text-md font-medium text-gray-700 mb-3">Uterine/Cervical Management</h4>
              <div class="grid grid-cols-2 gap-4">
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">👐</span>
                      <span>Clear Uterus/Cervix</span>
                  </div>
                </button>
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">➡️</span>
                      <span>Arrange Transfer/Surgery</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Bimanual Compression -->
            <div>
              <h4 class="text-md font-medium text-gray-700 mb-3">Compression and Repair</h4>
              <div class="grid grid-cols-2 gap-4">
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">👐</span>
                      <span>Bimanual Compression</span>
                  </div>
                </button>
                <button class="bg-motivaid-teal text-white px-4 py-3 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">✂️</span>
                      <span>Repair Cervical Tears</span>
                  </div>
                </button>
              </div>
            </div>

            <!-- Record & Save Button -->
            <section class="mt-8">
                <div class="flex justify-center">
                    <button  class="bg-pph-pink text-motivaid-teal px-6 py-4 rounded-lg font-medium border-2 border-motivaid-teal hover:bg-motivaid-teal hover:text-white focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                        Add Another Patient
                    </button>
                </div>
            </section>
        </section>
        
    </main>

    <!-- Bottom Navigation (Mobile) 
    <nav class="bg-white border-t border-gray-200 px-6 py-3 flex justify-around items-center fixed bottom-0 left-0 right-0 md:hidden">
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="text-xs font-medium">Edit</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <span class="text-xs font-medium">Select</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-motivaid-teal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-xs font-medium">Save</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
            </svg>
            <span class="text-xs font-medium">Share</span>
        </button>
    </nav>

     Desktop Navigation 
    <nav class="hidden md:flex justify-center space-x-12 py-4 bg-white border-t border-gray-200">
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="font-medium">Edit</span>
        </button>
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <span class="font-medium">Select</span>
        </button>
        <button class="flex items-center space-x-2 text-motivaid-teal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="font-medium">Save</span>
        </button>
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
            </svg>
            <span class="font-medium">Share</span>
        </button>
    </nav>-->
    <div class="mt-8 text-center text-gray-400 text-sm">
        &copy; 2025 MotivAid. All rights reserved.
    </div>
</body>
</template>