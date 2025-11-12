<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';
import { useSpeechSynthesis } from '@/Composables/speech.js';

// Initialize speech synthesis
const {
  isSupported,
  isSpeaking,
  speak,
  cancel,
  setOnSpeechCompleted,
  allowUserInteraction
} = useSpeechSynthesis();

// Function to speak text when UI elements appear or are interacted with
const speakText = (text) => {
  if (isSupported.value) {
    speak(text);
  }
};

// Reactive data
const timeString = ref('Date:');

// Auto-start speech control
const autoStartEnabled = ref(false);
const showAutoStartHint = ref(true);

// State for the diagnostic process
const currentStep = ref(1);
const showTonicityFollowup = ref(false);
const showTissueFollowup = ref(false);
const showTearFollowup = ref(false);
const showThrombinFollowup = ref(false);
const showThrombinYesGroup = ref(false);
const showThrombinNoGroup = ref(false);
const showDoneButton = ref(false);
const showCompletionPopup = ref(false);

// Checkbox states
const tonicityMassage = ref(false);
const tonicityEmpty = ref(false);
const tissueRemove = ref(false);
const tearRepair = ref(false);
const thrombinYesAdvance = ref(false);
const thrombinNoContinue = ref(false);
const thrombinNoMonitor = ref(false);
const thrombinNoVitals = ref(false);
const thrombinNoBf = ref(false);

let timeInterval = null;

// Function to format time in Lagos timezone
const formatLagosTime = (now) => {
  const opts = { 
    timeZone: 'Africa/Lagos', 
    hour: 'numeric', 
    minute: '2-digit', 
    second: '2-digit', 
    hour12: true, 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  };
  
  const parts = new Intl.DateTimeFormat('en-US', opts).formatToParts(now).reduce((acc, p) => (acc[p.type] = p.value, acc), {});
  const time = parts.hour + ':' + parts.minute + ':' + parts.second + (parts.dayPeriod ? ' ' + parts.dayPeriod : '');
  const date = parts.month + ' ' + parts.day + ', ' + parts.year;
  return time + ', ' + date;
};

// Timer function to update time every second
const updateTime = () => {
  timeString.value = 'Last Updated: ' + formatLagosTime(new Date());
};

// Function to check if all checkboxes in a section are checked
const areAllCheckboxesChecked = (checkboxes) => {
  return checkboxes.every(checkbox => checkbox.value === true);
};

// Function to update visibility of the done button for thrombin step
const updateThrombinDoneButton = () => {
  if (showThrombinYesGroup.value) {
    showDoneButton.value = areAllCheckboxesChecked([thrombinYesAdvance]);
  } else if (showThrombinNoGroup.value) {
    showDoneButton.value = areAllCheckboxesChecked([thrombinNoContinue, thrombinNoMonitor, thrombinNoVitals, thrombinNoBf]);
  }
};

// Step 1: Tonicity handlers
const handleTonicityYes = () => {
  showTonicityFollowup.value = false;
  currentStep.value = 2;
  
  if (isSupported.value) {
    speakText("Step 2: TISSUE, Select YES or NO");
  }
};

const handleTonicityNo = () => {
  showTonicityFollowup.value = true;
  currentStep.value = 1;
  
  if (isSupported.value) {
    speakText("Follow these steps. Massage the uterus. If full bladder, empty it.");
  }
};

// Watch for changes in tonicity checkboxes
const checkTonicityAllChecked = () => {
  if (areAllCheckboxesChecked([tonicityMassage, tonicityEmpty])) {
    currentStep.value = 2;
    
    if (isSupported.value) {
      speakText("Step 2: TISSUE, Select YES or NO");
    }
  }
  
  // Speak individual checkbox selections
  if (tonicityMassage.value && isSupported.value) {
    speakText("Massaging the uterus checked");
  }
  if (tonicityEmpty.value && isSupported.value) {
    speakText("Emptying full bladder checked");
  }
};

// Step 2: Tissue handlers
const handleTissueYes = () => {
  showTissueFollowup.value = true;
  currentStep.value = 2;
  
  if (isSupported.value) {
    speakText("Follow these steps. Remove Manually.");
  }
};

const handleTissueNo = () => {
  showTissueFollowup.value = false;
  currentStep.value = 3;
  
  if (isSupported.value) {
    speakText("Step 3: TEAR, Select YES or NO");
  }
};

// Watch for changes in tissue checkboxes
const checkTissueAllChecked = () => {
  if (areAllCheckboxesChecked([tissueRemove])) {
    currentStep.value = 3;
    
    if (isSupported.value) {
      speakText("Step 3: TEAR, Select YES or NO");
    }
  }
  
  // Speak individual checkbox selections
  if (tissueRemove.value && isSupported.value) {
    speakText("Removing manually checked");
  }
};

// Step 3: Tear handlers
const handleTearYes = () => {
  showTearFollowup.value = true;
  currentStep.value = 3;
  
  if (isSupported.value) {
    speakText("Follow these steps. Repair.");
  }
};

const handleTearNo = () => {
  showTearFollowup.value = false;
  currentStep.value = 4;
  
  if (isSupported.value) {
    speakText("Step 4: THROMBIN, Select YES or NO");
  }
};

// Watch for changes in tear checkboxes
const checkTearAllChecked = () => {
  if (areAllCheckboxesChecked([tearRepair])) {
    currentStep.value = 4;
    
    if (isSupported.value) {
      speakText("Step 4: THROMBIN, Select YES or NO");
    }
  }
  
  // Speak individual checkbox selections
  if (tearRepair.value && isSupported.value) {
    speakText("Repairing checked");
  }
};

// Step 4: Thrombin handlers
const handleThrombinYes = () => {
  showThrombinFollowup.value = true;
  showThrombinYesGroup.value = true;
  showThrombinNoGroup.value = false;
  
  // Reset checkboxes in both groups
  thrombinYesAdvance.value = false;
  thrombinNoContinue.value = false;
  thrombinNoMonitor.value = false;
  thrombinNoVitals.value = false;
  thrombinNoBf.value = false;
  
  currentStep.value = 4;
  showDoneButton.value = false;
  
  // Speak the follow-up instruction after a short delay
  if (isSupported.value) {
    setTimeout(() => {
      speakText("Follow these steps. Seek advanced care.");
    }, 300);
  }
};

const handleThrombinNo = () => {
  showThrombinFollowup.value = true;
  showThrombinYesGroup.value = false;
  showThrombinNoGroup.value = true;
  
  // Reset checkboxes in both groups
  thrombinYesAdvance.value = false;
  thrombinNoContinue.value = false;
  thrombinNoMonitor.value = false;
  thrombinNoVitals.value = false;
  thrombinNoBf.value = false;
  
  currentStep.value = 4;
  showDoneButton.value = false;
  
  // Speak the follow-up instructions for No path after a short delay
  if (isSupported.value) {
    setTimeout(() => {
      speakText("Follow these steps. Continue care. Monitor bleeding. Check vital signs and tone. Encourage breastfeeding.");
    }, 300);
  }
};

// Watch for changes in thrombin checkboxes
const checkThrombinAllChecked = () => {
  updateThrombinDoneButton();
  
  // Check which group is active and speak appropriate completion message
  if (showThrombinYesGroup.value && areAllCheckboxesChecked([thrombinYesAdvance])) {
    if (isSupported.value) {
      setTimeout(() => {
        speakText("Diagnostic steps completed successfully. Continue to treatment.");
      }, 300);
    }
  } else if (showThrombinNoGroup.value && areAllCheckboxesChecked([thrombinNoContinue, thrombinNoMonitor, thrombinNoVitals, thrombinNoBf])) {
    if (isSupported.value) {
      setTimeout(() => {
        speakText("Diagnostic steps completed successfully. Continue to treatment.");
      }, 300);
    }
  }
  
  // Speak individual checkbox selections for Thrombin Yes group
  if (thrombinYesAdvance.value && showThrombinYesGroup.value && isSupported.value) {
    setTimeout(() => {
      speakText("Seeking advanced care checked");
    }, 300);
  }
  
  // Speak individual checkbox selections for Thrombin No group
  if (thrombinNoContinue.value && showThrombinNoGroup.value && isSupported.value) {
    setTimeout(() => {
      speakText("Continuing care checked");
    }, 300);
  }
  if (thrombinNoMonitor.value && showThrombinNoGroup.value && isSupported.value) {
    setTimeout(() => {
      speakText("Monitoring bleeding checked");
    }, 300);
  }
  if (thrombinNoVitals.value && showThrombinNoGroup.value && isSupported.value) {
    setTimeout(() => {
      speakText("Checking vital signs and tone checked");
    }, 300);
  }
  if (thrombinNoBf.value && showThrombinNoGroup.value && isSupported.value) {
    setTimeout(() => {
      speakText("Encouraging breastfeeding checked");
    }, 300);
  }
};

// Handle Done button click
const handleDoneClick = () => {
  showCompletionPopup.value = true;
  
  if (isSupported.value) {
    speakText("Diagnostic steps completed successfully.");
  }
};

// Close completion popup and navigate to treatment page
const closeAndNavigate = () => {
  showCompletionPopup.value = false;
  setTimeout(() => {
    window.location.href = route('treatment.index');
  }, 300); // Small delay to allow modal to close smoothly
  
  if (isSupported.value) {
    speakText("Continuing to treatment page.");
  }
};

// Auto-start speech when user enables it
const autoStartSpeech = () => {
  allowUserInteraction(); // Enable speech synthesis
  autoStartEnabled.value = true;
  showAutoStartHint.value = false;
  
  if (isSupported.value) {
    // Speak the main title and description
    speakText("Bleeding After Birth. Steps For Diagnostic. Estimated blood loss greater than 500 mL.");
    
    // Then introduce the first step after a delay
    setTimeout(() => speakText("Step 1: TONICITY, Select YES or NO"), 2000);
  }
};

onMounted(() => {
  // Start updating time immediately
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
  
  // Show hint to enable audio if supported
  if (isSupported.value) {
    showAutoStartHint.value = true;
  }
});

onUnmounted(() => {
  // Clear the interval when component is unmounted
  if (timeInterval) {
    clearInterval(timeInterval);
  }
});
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

            <div class="text-sm font-medium text-gray-600">{{ timeString }}</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 w-full px-6 py-8">
        <!-- Title Section -->
        <section class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h2 class="text-3xl font-bold text-motivaid-dark mb-3">Bleeding After Birth</h2>
            <p class="text-lg font-medium text-gray-600">Steps For Diagnostic</p>
        </section>

        <!-- Auto-start Audio Hint -->
        <div v-if="showAutoStartHint && isSupported" class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a2 2 0 00-2 2v4a2 2 0 002 2h3.763l8.789 5.894A1 1 0 0018 17V3z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-blue-800">Audio Guide Available</p>
                    <p class="text-xs text-blue-700">Click below to start hearing the diagnostic steps</p>
                </div>
            </div>
            <button 
                @click="autoStartSpeech"
                class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition">
                Start Audio
            </button>
        </div>

        <!-- Assess & Record Section -->
        <section class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-motivaid-dark text-red-500">Estimated blood loss greater than 500 mL</h3>
                <!-- Audio playing indicator -->
                <div v-if="isSpeaking" class="flex items-center space-x-2">
                    <div class="flex space-x-1">
                        <span class="h-2 w-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0s"></span>
                        <span class="h-2 w-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></span>
                        <span class="h-2 w-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.4s"></span>
                    </div>
                    <span class="text-xs text-blue-600 font-medium">Now Speaking</span>
                </div>
            </div>
            
            <!-- Step 1: Tonicity -->
            <div class="mb-8" v-if="currentStep >= 1">
                <h4 class="text-lg font-medium text-gray-700 mb-4">Step 1: TONICITY</h4>
                <div class="flex space-x-4">
                    <button @click="handleTonicityYes" class="flex-1 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-3 px-6 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2 transition">YES</button>
                    <button @click="handleTonicityNo" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition">NO</button>
                </div>

                <div v-if="showTonicityFollowup" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="font-semibold text-gray-800">Follow these steps</p>
                    <div class="mt-3 flex items-center text-gray-700">
                        <input id="tonicity-massage" v-model="tonicityMassage" @change="checkTonicityAllChecked" type="checkbox" value="selected" class="mr-3 w-4 h-4" />
                        <label for="tonicity-massage" class="select-none">Massage the uterus</label>
                    </div>
                    <div class="mt-2 flex items-center text-gray-700">
                        <input id="tonicity-empty" v-model="tonicityEmpty" @change="checkTonicityAllChecked" type="checkbox" value="selected" class="mr-3 w-4 h-4" />
                        <label for="tonicity-empty" class="select-none">If full bladder, empty it</label>
                    </div>
                </div>
            </div>

            <!-- Step 2: Tissue -->
            <div class="mb-8" v-if="currentStep >= 2">
                <h4 class="text-lg font-medium text-gray-700 mb-4">Step 2: TISSUE</h4>
                <div class="flex space-x-4">
                    <button @click="handleTissueYes" class="flex-1 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-3 px-6 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2 transition">YES</button>
                    <button @click="handleTissueNo" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition">NO</button>
                </div>
                <div v-if="showTissueFollowup" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="font-semibold text-gray-800">Follow these steps</p>
                    <div class="mt-3 flex items-center text-gray-700">
                        <input id="tissue-remove" v-model="tissueRemove" @change="checkTissueAllChecked" type="checkbox" value="selected" class="mr-3 w-4 h-4" />
                        <label for="tissue-remove" class="select-none">Remove Manually</label>
                    </div>
                </div>
            </div>

            <!-- Step 3: Tear -->
            <div class="mb-8" v-if="currentStep >= 3">
                <h4 class="text-lg font-medium text-gray-700 mb-4">Step 3: TEAR</h4>
                <div class="flex space-x-4">
                    <button @click="handleTearYes" class="flex-1 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-3 px-6 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2 transition">YES</button>
                    <button @click="handleTearNo" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-400 focus:ring-offset-2 transition">NO</button>
                </div>
                <div v-if="showTearFollowup" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="font-semibold text-gray-800">Follow these steps</p>
                    <div class="mt-3 flex items-center text-gray-700">
                        <input id="tear-repair" v-model="tearRepair" @change="checkTearAllChecked" type="checkbox" value="selected" class="mr-3 w-4 h-4" />
                        <label for="tear-repair" class="select-none">Repair</label>
                    </div>
                </div>
            </div>

            <!-- Step 4: Thrombin -->
            <div class="mb-8" v-if="currentStep >= 4">
                <h4 class="text-lg font-medium text-gray-700 mb-4">Step 4: THROMBIN</h4>
                <div class="flex space-x-4 mb-6">
                    <button @click="handleThrombinYes" class="flex-1 bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-3 px-6 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none transition">YES</button>
                    <button @click="handleThrombinNo" class="flex-1 bg-gray-200 text-gray-700 py-3 px-6 rounded-lg font-medium hover:bg-gray-300 focus:outline-none transition">NO</button>
                </div>
                <div v-if="showThrombinFollowup" class="mt-4 bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded">
                    <p class="font-semibold text-gray-800">Follow these steps</p>
                    <div v-if="showThrombinYesGroup" class="mt-3 flex items-center text-gray-700">
                        <input id="thrombin-yes-advance" v-model="thrombinYesAdvance" @change="checkThrombinAllChecked" type="checkbox" value="advance" class="mr-3 w-4 h-4" />
                        <label for="thrombin-yes-advance" class="select-none">Seek advanced care</label>
                    </div>
                    <div v-if="showThrombinNoGroup" class="mt-3 space-y-2 text-gray-700">
                        <div class="flex items-center">
                            <input id="thrombin-no-continue" v-model="thrombinNoContinue" @change="checkThrombinAllChecked" type="checkbox" value="continue" class="mr-3 w-4 h-4" />
                            <label for="thrombin-no-continue" class="select-none">Continue care</label>
                        </div>
                        <div class="flex items-center">
                            <input id="thrombin-no-monitor" v-model="thrombinNoMonitor" @change="checkThrombinAllChecked" type="checkbox" value="monitor" class="mr-3 w-4 h-4" />
                            <label for="thrombin-no-monitor" class="select-none">Monitor bleeding</label>
                        </div>
                        <div class="flex items-center">
                            <input id="thrombin-no-vitals" v-model="thrombinNoVitals" @change="checkThrombinAllChecked" type="checkbox" value="vitals" class="mr-3 w-4 h-4" />
                            <label for="thrombin-no-vitals" class="select-none">Check vital signs & tone</label>
                        </div>
                        <div class="flex items-center">
                            <input id="thrombin-no-bf" v-model="thrombinNoBf" @change="checkThrombinAllChecked" type="checkbox" value="breastfeed" class="mr-3 w-4 h-4" />
                            <label for="thrombin-no-bf" class="select-none">Encourage breastfeeding</label>
                        </div>
                    </div>
                </div>
                
            </div>
            <div class="flex justify-center mt-4">
                <button v-if="showDoneButton" @click="handleDoneClick" class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-2 px-4 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2 transition w-full max-w-xs">Done</button>
            </div>
        </section>
    </main>

    <!-- Completion Popup -->
    <div v-if="showCompletionPopup" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" @click="closeAndNavigate"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Success!</h3>
            <p class="text-gray-600 text-center mb-6">Diagnostic steps completed successfully.</p>
            <div class="flex justify-center">
                <button 
                    @click="closeAndNavigate"
                    class="bg-motivaid-teal text-white px-4 py-2 rounded-md hover:bg-hotpink-800 focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                >
                    Continue to Treatment
                </button>
            </div>
        </div>
    </div>
    <div class="mt-8 text-center text-gray-400 text-sm">
        &copy; 2025 MotivAid. All rights reserved.
    </div>
  </body>
</template>