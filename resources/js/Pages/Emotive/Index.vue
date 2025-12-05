<script setup>
import { ref, onMounted, onUnmounted, computed } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link, router } from "@inertiajs/vue3";
import { useSpeechSynthesis } from "@/Composables/speech.js";

// Define props
const props = defineProps({
    emotiveSteps: {
        type: Array,
        required: true,
    },
    patientId: {
        type: [Number, String],
        required: false,
        default: null,
    },
});

const form = ref({
    bp: null,
    pulse: null,
});

// Computed property to check if BP is in valid format (Number/Number)
const isBpValid = computed(() => {
    if (!form.value.bp) return true; // Valid if empty
    const parts = form.value.bp.split("/");
    return (
        parts.length === 2 &&
        !isNaN(parseFloat(parts[0])) &&
        !isNaN(parseFloat(parts[1]))
    );
});

// Computed property to calculate shock index (pulse / systolic BP)
const shockIndex = computed(() => {
    if (!form.value.bp || !isBpValid.value || !form.value.pulse) return null;
    const systolic = parseFloat(form.value.bp.split("/")[0]);
    const pulse = parseFloat(form.value.pulse);
    if (isNaN(pulse) || systolic === 0) return null;
    return (pulse / systolic).toFixed(2);
});

const submitShockIndex = () => {
    // Backend removed, do nothing on submit for now.
    console.log("Form submitted (no backend)", form.value);
};

// Initialize speech synthesis
const {
    isSupported,
    isSpeaking,
    speak,
    cancel,
    setOnSpeechCompleted,
    allowUserInteraction,
} = useSpeechSynthesis();

// TTS repetition interval
let ttsInterval = null;

// Auto-start speech control
const autoStartEnabled = ref(false);
const showAutoStartHint = ref(true);

// Nigeria time
const nigeriaTime = ref("");

// Live timer
const liveTimer = ref("15:00");
const timerStatus = ref("Time is off");
const timerStatusVisible = ref(false);
const timerColor = ref("text-gray-800");
const DURATION = 15 * 60; // 15 minutes in seconds
let remaining = DURATION;
let tickInterval = null;

// Checklist - Initialize with data from props
const currentIndex = ref(0);
const nextBtnEnabled = ref(false);
const nextBtnText = ref("Next");
const hardcodedChecked = ref(false);
const allItems = ref(
    props.emotiveSteps.map((step, index) => ({
        ...step,
        visible: index === 0, // Only first item visible initially
        checked: step.completed,
    }))
);

// Computed property to check if all steps are completed
const allStepsCompleted = computed(() => {
    return allItems.value.every((item) => item.checked);
});

// Timer interval references
let nigeriaTimeInterval = null;

// Update Nigeria time
const updateNigeriaTime = () => {
    const options = {
        hour: "2-digit",
        minute: "2-digit",
        hour12: false,
        timeZone: "Africa/Lagos",
    };
    const now = new Date();
    nigeriaTime.value = now.toLocaleTimeString("en-GB", options);
};

// Format time for live timer
const formatTime = (s) => {
    const mm = String(Math.floor(s / 60)).padStart(2, "0");
    const ss = String(s % 60).padStart(2, "0");
    return mm + ":" + ss;
};

// Update live timer UI
const updateLiveTimerUI = () => {
    liveTimer.value = formatTime(Math.max(0, remaining));
    if (remaining <= 0) {
        timerStatus.value = "Time is off";
        timerStatusVisible.value = true;
        timerColor.value = "text-red-600";
        stopLiveTimer();
    } else {
        timerStatusVisible.value = false;
        timerColor.value = "text-gray-800";
    }
};

// Start live timer
const startLiveTimer = () => {
    if (tickInterval) return;
    tickInterval = setInterval(() => {
        remaining = Math.max(0, remaining - 1);
        updateLiveTimerUI();
    }, 1000);
};

// Stop live timer
const stopLiveTimer = () => {
    if (tickInterval) {
        clearInterval(tickInterval);
        tickInterval = null;
    }
};

// Record step completion
const recordStepCompletion = (stepIndex) => {
    // Only record if we have a patient ID
    if (!props.patientId) return;

    const step = allItems.value[stepIndex];

    router.post(
        route("emotive.steps.store"),
        {
            patient_id: props.patientId,
            action: step.title,
            completed_at: new Date()
                .toISOString()
                .slice(0, 19)
                .replace("T", " "),
        },
        {
            preserveState: true,
            preserveScroll: true,
            onSuccess: () => {
                console.log(`Step "${step.title}" recorded successfully`);
            },
            onError: (errors) => {
                console.error("Error recording step:", errors);
            },
        }
    );
};

// Helper function to speak and repeat a step
const speakStepContent = (stepIndex) => {
    if (!isSupported.value || stepIndex >= allItems.value.length) return;

    const item = allItems.value[stepIndex];
    let textToSpeak;

    if (stepIndex === 0) {
        // For the first step, speak the detailed checklist
        textToSpeak = `Early identification of bleeding. Assess and record BP.  Assess and record pulse. Calculate shock index 
        . Assess blood loss. Initiate Emotive if blood loss is greater than 500ml.`;
    } else {
        textToSpeak = `${item.title}. ${
            item.description ? item.description : ""
        }`;
    }

    // Clear any existing interval to prevent duplicates
    if (ttsInterval) {
        clearInterval(ttsInterval);
        ttsInterval = null;
    }

    // Set up callback to repeat speech when it finishes
    const repeatSpeech = () => {
        if (currentIndex.value === stepIndex && isSupported.value) {
            // Schedule the next speech to start after a 2-second delay
            setTimeout(() => {
                if (currentIndex.value === stepIndex && isSupported.value) {
                    speak(textToSpeak);
                }
            }, 2000);
        }
    };

    setOnSpeechCompleted(repeatSpeech);
    speak(textToSpeak);
};

// Auto-start speech when user enables it
const autoStartSpeech = () => {
    allowUserInteraction(); // Enable speech synthesis
    autoStartEnabled.value = true;
    showAutoStartHint.value = false;
    speak();
    //`Early identification of bleeding  Assess and record BP.  Assess and record pulse. Calculate shock index  ${
    //  shockIndex.value || ""
    //}. Assess blood loss. Initiate Emotive if blood loss is greater than 500ml.`
    if (shockIndex.value) {
        speak("Calculated shock index is " + shockIndex.value);
    }
    speakStepContent(0);
};

// Next button handler for checklist
const handleNextClick = () => {
    if (!nextBtnEnabled.value) return;

    // Cancel any ongoing speech and repetition when moving to next step
    if (isSpeaking.value) {
        cancel();
    }
    if (ttsInterval) {
        clearInterval(ttsInterval);
        ttsInterval = null;
    }

    if (currentIndex.value < allItems.value.length - 1) {
        // Record the completion of the current step
        if (allItems.value[currentIndex.value].checked) {
            recordStepCompletion(currentIndex.value);
        }

        currentIndex.value++;
        allItems.value[currentIndex.value].visible = true;

        // Start TTS for the newly visible step
        speakStepContent(currentIndex.value);

        // Update button state based on current item's checkbox
        updateNextState();

        if (currentIndex.value === allItems.value.length - 1) {
            nextBtnText.value = allItems.value[currentIndex.value].checked
                ? "Done"
                : "Done";
        }
    } else {
        // Last step and checkbox was checked -> finalize
        if (allItems.value[currentIndex.value].checked) {
            recordStepCompletion(currentIndex.value);
        }

        nextBtnEnabled.value = false;
        nextBtnText.value = "Completed";

        // Show success modal after completing all steps
        showSuccessModal.value = true;
    }
};

// Update next button state based on visible items
const updateNextState = () => {
    const visibleItems = allItems.value.filter((item) => item.visible);
    if (visibleItems.length > 0) {
        // Dynamic items are visible, check current item
        nextBtnEnabled.value = allItems.value[currentIndex.value].checked;
        if (currentIndex.value === allItems.value.length - 1) {
            nextBtnText.value = allItems.value[currentIndex.value].checked
                ? "Done"
                : "Done";
        } else {
            nextBtnText.value = "Next";
        }
    } else {
        // No dynamic items visible, check hardcoded
        nextBtnEnabled.value = hardcodedChecked.value;
        nextBtnText.value = "Next";
    }
};

// Handle checkbox change
const handleCheckboxChange = (index) => {
    allItems.value[index].checked = !allItems.value[index].checked;

    updateNextState();
};

// Initialize checklist
const initializeChecklist = () => {
    // All items are hidden initially
    for (let i = 0; i < allItems.value.length; i++) {
        allItems.value[i].visible = false;
    }

    // Set the initial button state based on hardcoded checkbox
    updateNextState();
};

onMounted(() => {
    // Initialize Nigeria time
    updateNigeriaTime();
    nigeriaTimeInterval = setInterval(updateNigeriaTime, 1000);

    // Initialize live timer
    startLiveTimer();
    updateLiveTimerUI(); // initialize display immediately

    // Initialize checklist
    initializeChecklist();

    // Show hint to enable audio if supported
    if (isSupported.value && allItems.value.length > 0) {
        showAutoStartHint.value = true;
    }
});

// Modal state
const showSuccessModal = ref(false);

// Handle done button click
const handleDoneClick = () => {
    showSuccessModal.value = true;
};

// Close modal and navigate to diagnostic page
const closeAndNavigate = () => {
    showSuccessModal.value = false;
    setTimeout(() => {
        window.location.href = route("diagonestic.index");
    }, 300); // Small delay to allow modal to close smoothly
};

onUnmounted(() => {
    if (nigeriaTimeInterval) {
        clearInterval(nigeriaTimeInterval);
    }
    if (tickInterval) {
        clearInterval(tickInterval);
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
</script>

<template>
    <div class="bg-gray-50 min-h-screen flex flex-col">
        <!-- Header -->
        <header
            class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between"
        >
            <div class="flex items-center space-x-3">
                <div
                    class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200"
                >
                    <img
                        src="/images/motivaid_logo.jpg"
                        alt="MotivAid Logo"
                        class="w-8 h-8 object-contain rounded-full"
                    />
                </div>
                <a
                    href="patient.php"
                    class="text-xl font-bold text-motivaid-teal"
                    >MotivAid</a
                >
            </div>

            <div class="flex items-center space-x-4">
                <!-- User dropdown -->
                <div class="relative">
                    <Dropdown align="right" width="48">
                        <template #trigger>
                            <button
                                type="button"
                                class="flex items-center text-sm text-gray-500 hover:text-gray-700 focus:outline-none"
                            >
                                <span>{{ $page.props.auth.user.name }}</span>
                                <svg
                                    class="ml-1 h-4 w-4"
                                    xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20"
                                    fill="currentColor"
                                >
                                    <path
                                        fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd"
                                    />
                                </svg>
                            </button>
                        </template>

                        <template #content>
                            <DropdownLink :href="route('profile.edit')">
                                Profile
                            </DropdownLink>
                            <DropdownLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </DropdownLink>
                        </template>
                    </Dropdown>
                </div>

                <div class="text-sm text-gray-500">{{ nigeriaTime }}</div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto px-4 py-6">
            <!-- Title Section -->
            <section class="bg-white px-6 py-8 rounded-lg mb-6">
                <h2 class="text-2xl font-semibold mb-2">
                    Steps For Prevention
                </h2>
                <p class="text-motivaid-teal/90 font-medium">(Delivery Mode)</p>
            </section>

            <!-- Auto-start Audio Hint -->
            <div
                v-if="showAutoStartHint && isSupported"
                class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6 flex items-center justify-between"
            >
                <div class="flex items-center space-x-3">
                    <svg
                        class="h-5 w-5 text-blue-600"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M18 3a1 1 0 00-1.447-.894L8.763 6H5a2 2 0 00-2 2v4a2 2 0 002 2h3.763l8.789 5.894A1 1 0 0018 17V3z"
                        />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">
                            Audio Guide Available
                        </p>
                        <p class="text-xs text-blue-700">
                            Click below to start hearing the step-by-step
                            instructions
                        </p>
                    </div>
                </div>
                <button
                    @click="autoStartSpeech"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-medium transition"
                >
                    Start Audio
                </button>
            </div>
            <section class="bg-white rounded-lg shadow-sm p-4 mb-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">
                    Live Timer
                </h3>
                <div
                    class="flex justify-between items-center text-lg font-semibold"
                >
                    <div class="flex items-center space-x-3">
                        <span :class="timerColor">{{ liveTimer }}</span>
                        <span
                            v-if="timerStatusVisible"
                            class="text-sm text-red-600"
                            >{{ timerStatus }}</span
                        >
                    </div>
                    <div class="flex items-center space-x-3">
                        <!-- Controls removed; timer starts automatically on page load -->
                    </div>
                </div>
                <!--<span class="text-motivaid-teal">600 ml</span>-->
            </section>

            <!-- Alert Banner -->
            <section
                class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6"
            >
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <svg
                            class="h-5 w-5 text-red-400 mt-0.5"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">
                            Possible Postpartum Hemorrhage – Begin E-MOTIVE
                            steps now!
                        </p>
                    </div>
                </div>
            </section>

            <!-- Checklist Card (show one item at a time with Next button) -->
            <section class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-700">
                        E-MOTIVE Step Checklist
                    </h3>
                    <!-- Audio playing indicator -->
                    <div v-if="isSpeaking" class="flex items-center space-x-2">
                        <div class="flex space-x-1">
                            <span
                                class="h-2 w-2 bg-blue-600 rounded-full animate-bounce"
                                style="animation-delay: 0s"
                            ></span>
                            <span
                                class="h-2 w-2 bg-blue-600 rounded-full animate-bounce"
                                style="animation-delay: 0.2s"
                            ></span>
                            <span
                                class="h-2 w-2 bg-blue-600 rounded-full animate-bounce"
                                style="animation-delay: 0.4s"
                            ></span>
                        </div>
                        <span class="text-xs text-blue-600 font-medium"
                            >Now Speaking</span
                        >
                    </div>
                </div>
                <ul class="space-y-3">
                    <li
                        class="flex items-start p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition"
                    >
                        <input
                            type="checkbox"
                            :checked="hardcodedChecked"
                            @change="
                                hardcodedChecked = !hardcodedChecked;
                                updateNextState();
                            "
                            class="h-5 w-5 text-motivaid-teal focus:ring-motivaid-teal rounded mt-0.5 mr-3"
                        />
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 mb-2">
                                Early identification of bleeding
                            </h4>
                            <ul class="space-y-1">
                                <li
                                    class="text-sm text-gray-700 flex items-center"
                                >
                                    <span class="text-green-600 mr-2">✓</span>
                                    Assess and record BP.
                                    <input
                                        v-model="form.bp"
                                        type="text"
                                        placeholder="e.g., 120/80"
                                        :class="[
                                            'ml-2 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-motivaid-teal',
                                            isBpValid
                                                ? 'border-gray-300'
                                                : 'border-red-500',
                                        ]"
                                    />
                                </li>
                                <li
                                    class="text-sm text-gray-700 flex items-center"
                                >
                                    <span class="text-green-600 mr-2">✓</span>
                                    Assess and record pulse.
                                    <input
                                        v-model="form.pulse"
                                        type="text"
                                        placeholder="e.g., 72"
                                        class="ml-2 px-2 py-1 text-xs border border-gray-300 rounded focus:outline-none focus:ring-1 focus:ring-motivaid-teal"
                                    />
                                </li>
                                <li
                                    class="text-sm text-gray-700 flex items-center"
                                >
                                    <span class="text-green-600 mr-2">✓</span>
                                    Calculated shock index
                                    <strong
                                        class="ml-2 cursor-pointer hover:text-blue-600"
                                        @click="
                                            shockIndex
                                                ? speak(
                                                      'Calculated shock index is ' +
                                                          shockIndex
                                                  )
                                                : null
                                        "
                                        >{{
                                            shockIndex ? shockIndex : ""
                                        }}</strong
                                    >.
                                </li>
                                <li
                                    class="text-sm text-gray-700 flex items-center"
                                >
                                    <span class="text-green-600 mr-2">✓</span>
                                    Assess blood loss.
                                </li>
                                <li
                                    class="text-sm text-gray-700 flex items-center"
                                >
                                    <span class="text-green-600 mr-2">✓</span>
                                    Initiate Emotive if blood loss is greater
                                    than 500ml.
                                </li>
                            </ul>
                        </div>
                    </li>
                    <li
                        v-for="(item, index) in allItems"
                        :key="item.id"
                        v-show="item.visible"
                        class="flex items-start p-3 bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition"
                    >
                        <input
                            type="checkbox"
                            :checked="item.checked"
                            @change="handleCheckboxChange(index)"
                            class="h-5 w-5 text-motivaid-teal focus:ring-motivaid-teal rounded mt-0.5 mr-3"
                        />
                        <div>
                            <span class="text-sm text-gray-700"
                                ><strong>{{ item.title }}</strong></span
                            >
                            <!-- Content for all steps using controller description -->
                            <div v-if="item.description" class="mt-2">
                                <!-- For step 1, split description by periods and show each with a checkmark -->
                                <div class="space-y-2">
                                    <p
                                        v-for="(part, index) in item.description
                                            .split('. ')
                                            .filter((p) => p.trim() !== '')"
                                        :key="index"
                                        class="text-sm text-gray-700 flex items-start"
                                    >
                                        <span class="text-green-600 mr-2"
                                            >✓</span
                                        >
                                        <span
                                            >{{ part
                                            }}{{
                                                !part.endsWith(".") ? "." : ""
                                            }}</span
                                        >
                                    </p>
                                </div>
                                <!-- For other steps, show description as a single paragraph -->
                            </div>
                        </div>
                    </li>
                </ul>

                <div class="mt-4 flex justify-end">
                    <button
                        type="button"
                        @click="handleNextClick"
                        :disabled="!nextBtnEnabled"
                        :class="[
                            'bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2',
                            {
                                'opacity-50 cursor-not-allowed':
                                    !nextBtnEnabled,
                            },
                        ]"
                    >
                        {{ nextBtnText }}
                    </button>
                </div>
            </section>

            <!-- Action Button -->
            <div
                v-if="allStepsCompleted"
                class="mb-6 flex justify-start space-x-4"
            >
                <button
                    class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white font-medium px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink"
                    @click="handleDoneClick"
                >
                    Complete
                </button>
                <button
                    class="bg-gradient-to-r from-blue-500 to-blue-700 text-white font-medium px-4 py-2 rounded-md hover:from-blue-600 hover:to-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    @click="closeAndNavigate"
                >
                    Next
                </button>
            </div>
        </main>

        <!-- Bottom Navigation 
    <nav class="bg-white border-t border-gray-200 px-4 py-2 flex justify-around items-center fixed bottom-0 left-0 right-0 md:hidden">
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span class="text-xs">Edit</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <span class="text-xs">Select</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-motivaid-teal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span class="text-xs">Save</span>
        </button>
        <button class="flex flex-col items-center space-y-1 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
            </svg>
            <span class="text-xs">Share</span>
        </button>
    </nav>

    <!-- Desktop Navigation (Hidden on Mobile) 
    <nav class="hidden md:flex justify-center space-x-8 py-4 bg-white border-t border-gray-200">
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
            </svg>
            <span>Edit</span>
        </button>
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7"></path>
            </svg>
            <span>Select</span>
        </button>
        <button class="flex items-center space-x-2 text-motivaid-teal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>Save</span>
        </button>
        <button class="flex items-center space-x-2 text-gray-500 hover:text-motivaid-teal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z"></path>
            </svg>
            <span>Share</span>
        </button>
    </nav> -->

        <!-- Success Modal -->
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div
                class="absolute inset-0 bg-black/30"
                @click="closeAndNavigate"
            ></div>
            <div
                class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all"
            >
                <div
                    class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4"
                >
                    <svg
                        class="w-8 h-8 text-green-600"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            stroke-width="2"
                            d="M5 13l4 4L19 7"
                        ></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 text-center mb-2">
                    Success!
                </h3>
                <p class="text-gray-600 text-center mb-6">
                    Steps completed successfully.
                </p>
                <div class="flex justify-center">
                    <button
                        @click="closeAndNavigate"
                        class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink"
                    >
                        Next
                    </button>
                </div>
            </div>
        </div>
        <div class="mt-8 text-center text-gray-400 text-sm">
            &copy; 2025 MotivAid. All rights reserved.
        </div>
    </div>
</template>
