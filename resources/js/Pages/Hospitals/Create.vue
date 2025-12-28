<script setup>
import { useForm } from "@inertiajs/vue3";
import { ref, onMounted, computed } from "vue";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";
import { Link, usePage } from "@inertiajs/vue3";

const page = usePage();

const form = useForm({
    name: "",
    location: "",
    contact_info: "",
});

const clockTime = ref("");

const updateClock = () => {
    const now = new Date();
    const hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, "0");
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
        errors.name = "Hospital name is required";
    }

    // Location validation
    if (!form.location.trim()) {
        errors.location = "Location is required";
    }

    // Contact info validation
    if (!form.contact_info.trim()) {
        errors.contact_info = "Contact information is required";
    }

    return Object.keys(errors).length === 0;
});

// Modal state
const showSuccessModal = ref(false);

// Helper function to handle 419 errors by refreshing the page
const handle419Error = () => {
    console.log("419 error detected, refreshing page to get new CSRF token");
    window.location.reload();
};

const addHospital = () => {
    // Only submit if form is valid
    if (isFormValid.value) {
        form.post(route("hospitals.store"), {
            onSuccess: () => {
                console.log("successfully");
                showSuccessModal.value = true; // Show success modal instead of alert
            },
            onError: (errors) => {
                console.log("Errors occurred:", errors);

                // Check if the error appears to be a 419 (CSRF token mismatch)
                const hasGenericError =
                    errors._other ||
                    (form.errors &&
                        Object.keys(form.errors).length === 0 &&
                        form.wasSuccessful === false);

                // If there's no specific validation errors but the request still failed,
                // it might be a 419 error
                if (hasGenericError || Object.keys(errors).length === 0) {
                    setTimeout(() => {
                        if (window.location.href.includes("419")) {
                            handle419Error();
                        }
                    }, 100);
                }
            },
            onFinish: () => {
                // As a fallback, check if the submission might have encountered a 419 error
                if (
                    form.hasErrors &&
                    !Object.keys(form.errors).some((key) => key !== "_other")
                ) {
                    if (
                        form.errors._other &&
                        form.errors._other.includes("419")
                    ) {
                        handle419Error();
                    }
                }
            },
        });
    } else {
        console.log("Form validation failed");
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
                        class="w-10 h-10 object-contain rounded-full"
                    />
                </div>
                <span class="text-xl font-bold text-motivaid-teal"
                    >MotivAid</span
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

                <div class="text-sm font-medium text-gray-500">
                    {{ clockTime }}
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 overflow-y-auto px-4 py-6">
            <div class="max-w-4xl mx-auto">
                <h2 class="text-2xl font-semibold text-gray-800 mb-6">
                    Hospital Information
                </h2>

                <section class="bg-white rounded-xl shadow-lg p-6 mb-6 border border-gray-100">
                    <div class="flex items-center mb-6">
                        <div class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 p-3 rounded-lg mr-4">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-800">Add New Hospital</h3>
                            <p class="text-sm text-gray-500">Enter the hospital details below</p>
                        </div>
                    </div>

                    <form @submit.prevent="addHospital" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-motivaid-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        Hospital Name
                                    </label>
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        :class="{
                                            'border-red-500': form.errors.name,
                                            'border-gray-300': !form.errors.name,
                                        }"
                                        class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:border-transparent transition duration-200 shadow-sm"
                                        placeholder="Enter hospital name"
                                    />
                                    <div
                                        v-if="form.errors.name"
                                        class="text-red-500 text-sm mt-1 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ form.errors.name }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-motivaid-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Location
                                    </label>
                                    <input
                                        v-model="form.location"
                                        type="text"
                                        :class="{
                                            'border-red-500': form.errors.location,
                                            'border-gray-300': !form.errors.location,
                                        }"
                                        class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:border-transparent transition duration-200 shadow-sm"
                                        placeholder="Enter hospital location"
                                    />
                                    <div
                                        v-if="form.errors.location"
                                        class="text-red-500 text-sm mt-1 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ form.errors.location }}
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-4 md:col-span-2">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-gray-700 mb-2 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-motivaid-teal" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                        </svg>
                                        Contact Information
                                    </label>
                                    <input
                                        v-model="form.contact_info"
                                        type="text"
                                        :class="{
                                            'border-red-500': form.errors.contact_info,
                                            'border-gray-300':
                                                !form.errors.contact_info,
                                        }"
                                        class="w-full px-4 py-3 rounded-lg border focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:border-transparent transition duration-200 shadow-sm"
                                        placeholder="Enter contact information (phone, email, etc.)"
                                    />
                                    <div
                                        v-if="form.errors.contact_info"
                                        class="text-red-500 text-sm mt-1 flex items-center"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        {{ form.errors.contact_info }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="pt-4 flex justify-end">
                            <button
                                :class="{
                                    'opacity-50 cursor-not-allowed': form.processing || !isFormValid,
                                }"
                                :disabled="form.processing || !isFormValid"
                                type="submit"
                                class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-6 py-3 rounded-lg hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink shadow-lg hover:shadow-xl transition-all duration-200 flex items-center"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                Save Hospital
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>

        <div class="mt-8 text-center text-gray-400 text-sm">
            &copy; 2025 MotivAid. All rights reserved.
        </div>

        <!-- Success Modal -->
        <div
            v-if="showSuccessModal"
            class="fixed inset-0 z-50 flex items-center justify-center"
        >
            <div
                class="absolute inset-0 bg-black/30"
                @click="closeAndReset"
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
                    Hospital Saved!
                </h3>
                <p class="text-gray-600 text-center mb-6">
                    The hospital information has been successfully saved.
                </p>
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
