<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link } from '@inertiajs/vue3';

// Reactive data
const timeString = ref('Date:');

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
};

const handleTonicityNo = () => {
  showTonicityFollowup.value = true;
  currentStep.value = 1;
};

// Watch for changes in tonicity checkboxes
const checkTonicityAllChecked = () => {
  if (areAllCheckboxesChecked([tonicityMassage, tonicityEmpty])) {
    currentStep.value = 2;
  }
};

// Step 2: Tissue handlers
const handleTissueYes = () => {
  showTissueFollowup.value = true;
  currentStep.value = 2;
};

const handleTissueNo = () => {
  showTissueFollowup.value = false;
  currentStep.value = 3;
};

// Watch for changes in tissue checkboxes
const checkTissueAllChecked = () => {
  if (areAllCheckboxesChecked([tissueRemove])) {
    currentStep.value = 3;
  }
};

// Step 3: Tear handlers
const handleTearYes = () => {
  showTearFollowup.value = true;
  currentStep.value = 3;
};

const handleTearNo = () => {
  showTearFollowup.value = false;
  currentStep.value = 4;
};

// Watch for changes in tear checkboxes
const checkTearAllChecked = () => {
  if (areAllCheckboxesChecked([tearRepair])) {
    currentStep.value = 4;
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
};

// Watch for changes in thrombin checkboxes
const checkThrombinAllChecked = () => {
  updateThrombinDoneButton();
};

// Handle Done button click
const handleDoneClick = () => {
  showCompletionPopup.value = true;
};

// Close completion popup
const closeCompletionPopup = () => {
  showCompletionPopup.value = false;
};

onMounted(() => {
  // Start updating time immediately
  updateTime();
  timeInterval = setInterval(updateTime, 1000);
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
            <svg class="w-8 h-8 text-motivaid-teal" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
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

        <!-- Assess & Record Section -->
        <section class="bg-white rounded-xl shadow-sm p-8 mb-8">
            <h3 class="text-xl font-semibold text-motivaid-dark mb-6 text-red-500">Estimated blood loss greater than 500 mL</h3>
            
            <!-- Step 1: Tonicity -->
            <div class="mb-8" v-if="currentStep >= 1">
                <h4 class="text-lg font-medium text-gray-700 mb-4">Step 1: TONICITY</h4>
                <div class="flex space-x-4">
                    <button @click="handleTonicityYes" class="flex-1 bg-motivaid-teal text-white py-3 px-6 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-2 transition">YES</button>
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
                    <button @click="handleTissueYes" class="flex-1 bg-motivaid-teal text-white py-3 px-6 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-2 transition">YES</button>
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
                    <button @click="handleTearYes" class="flex-1 bg-motivaid-teal text-white py-3 px-6 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-2 transition">YES</button>
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
                    <button @click="handleThrombinYes" class="flex-1 bg-motivaid-teal text-white py-3 px-6 rounded-lg font-medium hover:bg-teal-700 focus:outline-none transition">YES</button>
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
                <button v-if="showDoneButton" @click="handleDoneClick" class="bg-motivaid-teal text-white py-2 px-4 rounded-lg font-medium hover:bg-teal-700 focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-2 transition w-full max-w-xs">Done</button>
            </div>
        </section>
    </main>

    <!-- Bottom Navigation (Mobile) -->
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

    <!-- Desktop Navigation -->
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
    </nav>

    <!-- Completion Popup -->
    <div v-if="showCompletionPopup" class="fixed inset-0 z-50 flex items-center justify-center" role="status" aria-live="polite">
        <div class="bg-white border-l-4 border-green-600 p-6 rounded-lg shadow-md max-w-md w-full transform transition duration-200">
            <div class="flex items-center justify-between">
                <p class="text-base font-medium text-gray-800">Your action has been completed successfully</p>
                <button @click="closeCompletionPopup" class="text-green-600 text-lg focus:outline-none" aria-label="Close">&times;</button>
            </div>
            <button class="mt-4 bg-[#0D9488] hover:bg-[#0C7A70] text-white font-medium text-sm py-2 px-4 rounded w-full transition duration-200">
                Add New Patient
            </button>
        </div>
    </div>
  </body>
</template>