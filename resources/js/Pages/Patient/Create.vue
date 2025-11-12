<script setup>
import { useForm } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const page = usePage();

const form = useForm({
       name: '',
       age: '',
       history_of_antenatal_visit: '',
       gravida: '',
       history_of_previous_pph: '',
       history_Of_ceaserian_section: '',
       type_of_pregenency: '',
       gestational_age: '',
       hospital: page.props.auth?.user?.hospital?.name || ''
})

const checkAge = (event) => {
              const age = parseInt(event.target.value, 10);
              updateWarningBadge();
};

// Helper function to check if patient has history of antenatal visit NO
const checkHistoryOfAntenatalVisit = () => {
    return form.history_of_antenatal_visit === 'no';
};

// Helper function to check if patient has history of previous PPH YES
const checkHistoryOfPreviousPPH = () => {
    return form.history_of_previous_pph === 'yes';
};

// Helper function to check if patient has history of caesarean section YES
const checkHistoryOfCaesareanSection = () => {
    return form.history_Of_ceaserian_section === 'yes';
};

// Helper function to check gestational age (show warning if <36 or >42)
const checkGestationalAge = () => {
    const gestationalAge = parseFloat(form.gestational_age);
    if (isNaN(gestationalAge)) {
        return false;
    }
    return gestationalAge < 36 || gestationalAge > 42;
};

// Helper function to check gravida/parity (show warning if numerator or denominator > 4)
const checkGravidaParity = () => {
    // Expecting format like "2/3" (gravida/parity)
    const gravidaParity = form.gravida;
    if (!gravidaParity.includes('/')) {
        return false; // Invalid format, assume no risk for now
    }
    
    const [numerator, denominator] = gravidaParity.split('/').map(Number);
    
    // Check if either numerator or denominator is greater than 4
    return (numerator && numerator >= 4) && (denominator && denominator >= 4);
};

// Helper function to check type of pregnancy (show warning for twin, triplet, higher)
const checkTypeOfPregnancy = () => {
    return form.type_of_pregenency !== 'single' && form.type_of_pregenency !== ''; // Only single pregnancy is not high risk
};

// Helper function to update warning badge visibility based on all risk factors
const updateWarningBadge = () => {
    const warningBadge = document.getElementById('warning-badge');
    
    // Check all risk factors
    const isHighRiskAge = !isNaN(parseInt(form.age)) && (parseInt(form.age) < 18 || parseInt(form.age) > 35);
    const hasNoAntenatalVisit = checkHistoryOfAntenatalVisit();
    const hasPreviousPPH = checkHistoryOfPreviousPPH();
    const hasCaesareanSection = checkHistoryOfCaesareanSection();
    const hasAbnormalGestationalAge = checkGestationalAge();
    const hasHighGravidaParity = checkGravidaParity();
    const hasHighRiskPregnancyType = checkTypeOfPregnancy();
    
    // Show warning badge if any risk factor is true
    if (isHighRiskAge || hasNoAntenatalVisit || hasPreviousPPH || hasCaesareanSection || hasAbnormalGestationalAge || hasHighGravidaParity || hasHighRiskPregnancyType) {
        warningBadge.classList.remove('hidden');
    } else {
        warningBadge.classList.add('hidden');
    }
};

const clockTime = ref('');

       const updateClock = () => {
              const now = new Date();
              const hours = now.getHours();
              const minutes = now.getMinutes().toString().padStart(2, '0');
              clockTime.value = `${hours}:${minutes}`;
       };

       onMounted(() => {
              updateClock();
              setInterval(updateClock, 1000);
       });

// Computed property to check if form is valid based on backend validation rules
const isFormValid = computed(() => {
    const errors = {};
    
    // Name validation
    if (!form.name.trim()) {
        errors.name = 'Name is required';
    }
    
    // Age validation
    if (!form.age) {
        errors.age = 'Age is required';
    } else {
        const ageNum = parseInt(form.age);
        if (isNaN(ageNum) || ageNum < 1 || ageNum > 120) {
            errors.age = 'Age must be between 1 and 120';
        }
    }
    
    // History of antenatal visit validation
    if (!form.history_of_antenatal_visit) {
        errors.history_of_antenatal_visit = 'History of antenatal visit is required';
    } else if (!['yes', 'no'].includes(form.history_of_antenatal_visit)) {
        errors.history_of_antenatal_visit = 'Please select a valid option';
    }
    
    // Gravida validation - should be in format number/number
    if (!form.gravida.trim()) {
        errors.gravida = 'Gravida/Parity is required';
    } else {
        // Validate format: number/number
        const gravidaPattern = /^\d+\/\d+$/;
        if (!gravidaPattern.test(form.gravida)) {
            errors.gravida = 'Gravida/Parity must be in format: number/number (e.g., 2/1)';
        }
    }
    
    // History of previous PPH validation
    if (!form.history_of_previous_pph) {
        errors.history_of_previous_pph = 'History of previous PPH is required';
    } else if (!['yes', 'no'].includes(form.history_of_previous_pph)) {
        errors.history_of_previous_pph = 'Please select a valid option';
    }
    
    // History of Caesarean section validation
    if (!form.history_Of_ceaserian_section) {
        errors.history_Of_ceaserian_section = 'History of Caesarean section is required';
    } else if (!['yes', 'no'].includes(form.history_Of_ceaserian_section)) {
        errors.history_Of_ceaserian_section = 'Please select a valid option';
    }
    
    // Type of pregnancy validation
    if (!form.type_of_pregenency) {
        errors.type_of_pregenency = 'Type of pregnancy is required';
    } else if (!['single', 'twin', 'triplet', 'higher'].includes(form.type_of_pregenency)) {
        errors.type_of_pregenency = 'Please select a valid pregnancy type';
    }
    
    // Gestational age validation - should be a number
    if (!form.gestational_age.trim()) {
        errors.gestational_age = 'Gestational age is required';
    } else {
        const gestationalAgeNum = parseFloat(form.gestational_age);
        if (isNaN(gestationalAgeNum) || gestationalAgeNum <= 0) {
            errors.gestational_age = 'Gestational age must be a positive number';
        }
    }
    
    return Object.keys(errors).length === 0;
});

// Modal state
const showSuccessModal = ref(false);

// Helper function to handle 419 errors by refreshing the page
const handle419Error = () => {
    console.log('419 error detected, refreshing page to get new CSRF token');
    window.location.reload();
};

const addPatient = () => {
    // Only submit if form is valid
    if (isFormValid.value) {
        form.post(route('patient.store'), {
            onSuccess: ()=>{
                console.log('successfully');
                showSuccessModal.value = true; // Show success modal instead of alert
                // Hide any warning badges after successful submission
                const warningBadge = document.getElementById('warning-badge');
                if (warningBadge) {
                    warningBadge.classList.add('hidden');
                }
            },
            onError: (errors) => {
                console.log('Errors occurred:', errors);
                
                // Check if the error appears to be a 419 (CSRF token mismatch)
                // Since Inertia.js handles 419 errors differently, we check for common signs
                const hasGenericError = errors._other || 
                                      (form.errors && Object.keys(form.errors).length === 0 && form.wasSuccessful === false);
                
                // If there's no specific validation errors but the request still failed,
                // it might be a 419 error
                if (hasGenericError || Object.keys(errors).length === 0) {
                    // Wait a short time to see if the 419 is detected differently
                    setTimeout(() => {
                        // Check if we're getting a 419 in the page response
                        // This catches cases where 419 error redirects to an error page
                        if (window.location.href.includes('419')) {
                            handle419Error();
                        }
                    }, 100);
                }
                
                // Update warning badge based on risk factors
                updateWarningBadge();
            },
            onFinish: () => {
                // As a fallback, check if the submission might have encountered a 419 error
                // This catches cases where the request fails without clear error messages
                if (form.hasErrors && !Object.keys(form.errors).some(key => key !== '_other')) {
                    // If only a generic error exists, it might be a 419
                    if (form.errors._other && form.errors._other.includes('419')) {
                        handle419Error();
                    }
                }
            }
        });
    } else {
        // Display client-side validation errors - in a real app you might want to show them in the UI
        console.log('Form validation failed');
        // Update warning badge based on risk factors even when validation fails
        updateWarningBadge();
    }
};

// Close modal and reset form
const closeAndReset = () => {
    showSuccessModal.value = false;
    form.reset(); // Reset the form after closing modal
};

</script>




<template>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200 px-4 py-3 flex items-center justify-between">
        <div class="flex items-center space-x-3">
            <div class="bg-white p-1.5 rounded-full shadow-sm border border-gray-200">
                <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-10 h-10 object-contain rounded-full">
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

            <div class="text-sm font-medium text-gray-500">{{ clockTime }}</div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 overflow-y-auto px-4 py-6">
        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Patient Bio Data</h2>
        <!-- Warning Badge 
        <div id="warning-badge" class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 hidden">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <p class="ml-3 text-sm text-yellow-700">High Risk of Postpartum Hemorrhage</p>
                </div>
       </div> -->
        <section class="bg-white rounded-lg shadow-sm p-6 mb-6">
            <h3 class="text-lg font-medium text-gray-700 mb-4">Patient Information</h3>
            
            <div class="space-y-4">
              <form @submit.prevent="addPatient" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Patient Name</label>
                    <input 
                      v-model="form.name" 
                      type="text" 
                      :class="{
                        'border-red-500': form.errors.name,
                        'border-gray-300': !form.errors.name
                      }"
                      class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                    >
                    <div v-if="form.errors.name" class="text-red-500 text-sm mt-1">{{ form.errors.name }}</div>
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Age</label>
                        <input 
                          v-model="form.age" 
                          @input="updateWarningBadge" 
                          type="text" 
                          :class="{
                            'border-red-500': form.errors.age,
                            'border-gray-300': !form.errors.age
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                        <div v-if="form.errors.age" class="text-red-500 text-sm mt-1">{{ form.errors.age }}</div>
                        <!-- Individual warning badge for age -->
                        <div v-if="!isNaN(parseInt(form.age)) && (parseInt(form.age) < 18 || parseInt(form.age) > 35)" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">History Of Antenatal visit</label>
                        <select 
                          v-model="form.history_of_antenatal_visit" 
                          @change="updateWarningBadge"
                          :class="{
                            'border-red-500': form.errors.history_of_antenatal_visit,
                            'border-gray-300': !form.errors.history_of_antenatal_visit
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <div v-if="form.errors.history_of_antenatal_visit" class="text-red-500 text-sm mt-1">{{ form.errors.history_of_antenatal_visit }}</div>
                        <!-- Individual warning badge for history of antenatal visit -->
                        <div v-if="form.history_of_antenatal_visit === 'no'" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gravida/Parity</label>
                        <input 
                          v-model="form.gravida" 
                          @input="updateWarningBadge"
                          type="text" 
                          :class="{
                            'border-red-500': form.errors.gravida,
                            'border-gray-300': !form.errors.gravida
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                        <div v-if="form.errors.gravida" class="text-red-500 text-sm mt-1">{{ form.errors.gravida }}</div>
                        <!-- Individual warning badge for gravida/parity -->
                        <div v-if="checkGravidaParity()" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">History of Previous PPH</label>
                        <select 
                          v-model="form.history_of_previous_pph" 
                          @change="updateWarningBadge"
                          :class="{
                            'border-red-500': form.errors.history_of_previous_pph,
                            'border-gray-300': !form.errors.history_of_previous_pph
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <div v-if="form.errors.history_of_previous_pph" class="text-red-500 text-sm mt-1">{{ form.errors.history_of_previous_pph }}</div>
                        <!-- Individual warning badge for history of previous PPH -->
                        <div v-if="form.history_of_previous_pph === 'yes'" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">History Of Ceaserian Section</label>
                        <select 
                          v-model="form.history_Of_ceaserian_section" 
                          @change="updateWarningBadge"
                          :class="{
                            'border-red-500': form.errors.history_Of_ceaserian_section,
                            'border-gray-300': !form.errors.history_Of_ceaserian_section
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                            <option value="">Select</option>
                            <option value="yes">Yes</option>
                            <option value="no">No</option>
                        </select>
                        <div v-if="form.errors.history_Of_ceaserian_section" class="text-red-500 text-sm mt-1">{{ form.errors.history_Of_ceaserian_section }}</div>
                        <!-- Individual warning badge for history of caesarean section -->
                        <div v-if="form.history_Of_ceaserian_section === 'yes'" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Type Of Pregnancy</label>
                        <select 
                          v-model="form.type_of_pregenency" 
                          @change="updateWarningBadge"
                          :class="{
                            'border-red-500': form.errors.type_of_pregenency,
                            'border-gray-300': !form.errors.type_of_pregenency
                          }"
                          class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                        >
                            <option value="">Select</option>
                            <option value="single">Single Pregnancy</option>
                            <option value="twin">Twin Pregnancy</option>
                            <option value="triplet">Triplet Pregnancy</option>
                            <option value="higher">Higher Multiple</option>
                        </select>
                        <div v-if="form.errors.type_of_pregenency" class="text-red-500 text-sm mt-1">{{ form.errors.type_of_pregenency }}</div>
                        <!-- Individual warning badge for type of pregnancy -->
                        <div v-if="form.type_of_pregenency !== 'single' && form.type_of_pregenency !== ''" 
                             class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                            <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            High Risk of Postpartum Hemorrhage
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Gestational Age</label>
                    <input 
                      v-model="form.gestational_age" 
                      @input="updateWarningBadge"
                      type="text"  
                      :class="{
                        'border-red-500': form.errors.gestational_age,
                        'border-gray-300': !form.errors.gestational_age
                      }"
                      class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                    >
                    <div v-if="form.errors.gestational_age" class="text-red-500 text-sm mt-1">{{ form.errors.gestational_age }}</div>
                    <!-- Individual warning badge for gestational age -->
                    <div v-if="checkGestationalAge()" 
                         class="bg-yellow-50 border-l-4 border-yellow-400 p-2 mt-1 text-xs text-yellow-700 flex items-center">
                        <svg class="h-4 w-4 mr-1 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                        High Risk of Postpartum Hemorrhage
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hospital</label>
                    <input 
                      v-model="form.hospital" 
                      type="text" 
                      readonly
                      :class="{
                        'border-red-500': form.errors.hospital,
                        'border-gray-300': !form.errors.hospital,
                        'bg-gray-100': true
                      }"
                      class="w-full px-3 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal"
                    >
                    <div v-if="form.errors.hospital" class="text-red-500 text-sm mt-1">{{ form.errors.hospital }}</div>
                </div>
                <div>
                    <div class="flex justify-end">
                        <button
                            :class="{ 
                              'opacity-25': form.processing || !isFormValid,
                              'cursor-not-allowed': !isFormValid
                            }"
                            :disabled="form.processing || !isFormValid"
                            type="submit" 
                            class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink">
                            Save Profile & Proceed to Delivery Mode
                        </button>
                    </div>
                </div>
              </form>
              </div>
        </section>
    </main>

    <!-- Bottom Navigation -->

    <div class="mt-8 text-center text-gray-400 text-sm">
        &copy; 2025 MotivAid. All rights reserved.
    </div>
    <!--<footer class="bg-white border-t border-gray-200 px-4 py-2 flex justify-around items-center fixed bottom-0 left-0 right-0 md:hidden">
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
    </footer>

     Desktop Navigation (Hidden on Mobile) 
    <footer class="hidden md:flex justify-center space-x-8 py-4 bg-white border-t border-gray-200">
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
    </footer> -->
    
    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 flex items-center justify-center">
        <div class="absolute inset-0 bg-black/30" @click="closeAndReset"></div>
        <div class="relative bg-white rounded-lg shadow-xl p-6 w-11/12 max-w-md mx-auto transform transition-all">
            <div class="flex items-center justify-center w-16 h-16 mx-auto bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 text-center mb-2">Patient Profile Saved!</h3>
            <p class="text-gray-600 text-center mb-6">Your patient information has been successfully saved.</p>
            <div class="flex justify-center">
                <button 
                    @click="closeAndReset"
                    class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink"
                >
                    Continue
                </button>
            </div>
        </div>
    </div>
</body>
</template>