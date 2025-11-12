<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import axios from 'axios';
import { useSpeechSynthesis } from '@/Composables/speech.js';

// Get page object to access props
const page = usePage();

// Initialize speech synthesis
const {
  isSupported,
  isSpeaking,
  speak,
  cancel
} = useSpeechSynthesis();

// TTS repetition interval
let ttsInterval = null;

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

// Stepper state - define default steps to ensure they reset properly
const defaultSteps = [
  { label: 'Massage Uterus', icon: '✋', completed: false },
  { label: 'Give Oxytocin', icon: '💉', completed: false },
  { label: 'Administer TXA', icon: '💊', completed: false },
  { label: 'Start IV Fluids', icon: '💉', completed: false }
];

const steps = ref([...defaultSteps]); // Create a copy to ensure clean initialization
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

    // Clear current TTS when going backward
    if (ttsInterval) {
      clearInterval(ttsInterval);
      ttsInterval = null;
    }
    // Cancel any ongoing speech
    if (isSpeaking.value) {
      cancel();
    }
  } else {
    // Mark this step done
    steps.value[index].completed = true;
    currentStepIndex.value = index;
    
    // Speak the step when marked as done
    if (isSupported.value) {
      speakText(`Completed ${steps.value[index].label}`);
    }
    
    // Check if this was the last step and ask if still bleeding
    if (index === steps.value.length - 1 && isSupported.value) {
      setTimeout(() => {
        speakText("Is she still bleeding?");
      }, 2000); // Wait 2 seconds after the step completion speech
    }
  }

  updateStillBleedingVisibility();
};

// Handle PPH Yes click
const handlePphYes = () => {
  showEscalateSection.value = true;
  showPphAlertPopup();
  
  if (isSupported.value) {
    speakText("Alert the Doctor");
  }
};

// Handle PPH No click
const handlePphNo = () => {
  hideAlertPopup();
  showSuccessPopup.value = true;
  
  if (isSupported.value) {
    speakText("PPH treatment completed successfully. You just saved a life!");
  }
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

  if (isSupported.value) {
    speakText("Alert the Doctor. A clinician should be notified immediately.");
  }

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
  axios.post(route('make.call'), {
    //to: '+2347082262502', // Default recipient number
    //message: 'Emergency: Postpartum hemorrhage case requires immediate attention. Patient needs urgent care.'
  })
    .then(response => {
      if (response.data.success) {
        console.log('SMS alert sent successfully:', response.data.message);
        // Optionally show a success notification to the user
        if (isSupported.value) {
          speakText("Alert sent successfully!");
        }
        alert('Alert sent successfully!');
      } else {
        console.error('Failed to send SMS alert:', response.data.message);
        if (isSupported.value) {
          speakText("Failed to send alert: " + response.data.message);
        }
        alert('Failed to send alert: ' + response.data.message);
      }
    })
    .catch(error => {
      console.error('Error sending SMS alert:', error);
      if (error.response && error.response.data && error.response.data.message) {
        if (isSupported.value) {
          speakText("An error occurred while sending the alert: " + error.response.data.message);
        }
        alert('An error occurred while sending the alert: ' + error.response.data.message);
      } else {
        if (isSupported.value) {
          speakText("An error occurred while sending the alert.");
        }
        alert('An error occurred while sending the alert.');
      }
    });

  hideAlertPopup();
};

// Handle dismiss button
const handleDismiss = () => {
  hideAlertPopup();
  
  if (isSupported.value) {
    speakText("Alert popup dismissed");
  }
};

// Progress text computed property
const progressText = computed(() => {
  const completedCount = steps.value.filter(step => step.completed).length;
  const totalSteps = steps.value.length;
  const nextStep = Math.min(completedCount + 1, totalSteps);

  return `Step ${nextStep} of ${totalSteps}${completedCount > 0 ? ' — ${completedCount} completed' : ''}`;
});

// Initialize on component mount
onMounted(() => {
  // Ensure clean state on mount
  currentStepIndex.value = -1;
  // Reset steps to default state
  steps.value = [...defaultSteps];
  
  startTimer();

  // Start TTS for the first step on every page load, similar to how the timer restarts
  if (isSupported.value && steps.value.length > 0) {
    // Clear any existing interval first
    if (ttsInterval) {
      clearInterval(ttsInterval);
      ttsInterval = null;
    }

    // Always start with the first step when page loads/refreshes, like the timer
    const firstStepLabel = steps.value[0].label;

    if (isSupported.value) {
      // Cancel any ongoing speech first
      if (isSpeaking.value) {
        cancel();
      }
      
      // Start speaking immediately
      speak(firstStepLabel);

      // Set up interval to repeat the current step until it's completed
      ttsInterval = setInterval(() => {
        // Determine which step should be announced based on current progress
        let targetStepIndex = currentStepIndex.value + 1;
        
        if (targetStepIndex < steps.value.length && isSupported.value) {
          // Cancel any ongoing speech first
          if (isSpeaking.value) {
            cancel();
          }
          // Speak the step that should be completed next
          speak(steps.value[targetStepIndex].label);
        } else {
          // If we've completed all steps, clear the interval
          clearInterval(ttsInterval);
          ttsInterval = null;
        }
      }, 5000); // Repeat every 5 seconds
    }
  }
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
  // Clear TTS interval to prevent memory leaks
  if (ttsInterval) {
    clearInterval(ttsInterval);
    ttsInterval = null;
  }
  // Cancel any ongoing speech
  if (isSpeaking.value) {
    cancel();
  }
});

// Function to speak text when UI elements appear or are interacted with
const speakText = (text) => {
  if (isSupported.value) {
    // Cancel any ongoing speech first
    if (isSpeaking.value) {
      cancel();
    }
    speak(text);
  }
};

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

            <div class="text-sm font-medium text-gray-600"></div>
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
                <h3 class="text-lg font-semibold text-motivaid-dark">Initiate Motive Bundle</h3>
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
                      : 'bg-pph-pink text-motivaid-teal border border-motivaid-teal/20 hover:bg-hotpink-800 hover:text-white'"
                  >
                    <div class="flex items-center justify-between flex-1">
                      <div class="flex items-center space-x-3">
                        <span class="text-lg">{{ step.icon }}</span>
                        <span>{{ index + 1 }}. {{ step.label }}</span>
                      </div>
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
              <button @click="handlePphYes" class="bg-white text-motivaid-teal px-4 py-3 rounded-lg font-medium border-2 border-motivaid-teal hover:bg-hotpink-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
                Yes
              </button>
              <button @click="handlePphNo" class="bg-white text-motivaid-teal px-4 py-3 rounded-lg font-medium border-2 border-motivaid-teal hover:bg-hotpink-800 hover:text-white focus:outline-none focus:ring-2 focus:ring-motivaid-teal transition">
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
                        class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink"
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
                    <button @click="handleAlertDoctor" class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink">
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
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">💉</span>
                      <span>Give Uterotonic</span>
                  </div>
                </button>
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
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
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">🩹</span>
                      <span>Catheterize Bladder</span>
                  </div>
                </button>
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
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
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">👐</span>
                      <span>Clear Uterus/Cervix</span>
                  </div>
                </button>
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
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
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
                  <div class="flex items-center justify-center space-x-2">
                      <span class="text-lg">👐</span>
                      <span>Bimanual Compression</span>
                  </div>
                </button>
                <button class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-3 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
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
                    <button  class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-6 py-4 rounded-lg font-medium border-2 border-motivaid-teal hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition">
                        Add Another Patient
                    </button>
                </div>
            </section>
        </section>

    </main>

    <!-- Bottom Navigation (Mobile)
    <nav class="bg-white border-t border-gray-200 px-6 py-3 flex justify-around items-center fixed bottom-0 left-0 right-0 md:hidden">
        <button class="flex flex-col items-center text-xs text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span>Home</span>
        </button>
        <button class="flex flex-col items-center text-xs text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
            </svg>
            <span>Records</span>
        </button>
        <button class="flex flex-col items-center text-xs text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span>Patients</span>
        </button>
        <button class="flex flex-col items-center text-xs text-gray-500">
            <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span>Settings</span>
        </button>
    </nav>
    -->

</body>
</template>