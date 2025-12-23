<script setup>
import { ref, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const page = usePage();
const hospital = ref(page.props.hospital || {});

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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">
                    Hospital Details
                </h2>
                <div class="space-x-4">
                    <Link
                        :href="route('hospitals.edit', hospital.id)"
                        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                        Edit Hospital
                    </Link>
                    <Link
                        :href="route('hospitals.index')"
                        class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                    >
                        Back to List
                    </Link>
                </div>
            </div>

            <section class="bg-white rounded-lg shadow-sm p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Hospital Name
                        </label>
                        <p class="text-gray-900">{{ hospital.name }}</p>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Location
                        </label>
                        <p class="text-gray-900">{{ hospital.location }}</p>
                    </div>
                    <div class="md:col-span-2">
                        <label
                            class="block text-sm font-medium text-gray-700 mb-1"
                        >
                            Contact Information
                        </label>
                        <p class="text-gray-900">{{ hospital.contact_info }}</p>
                    </div>
                </div>
            </section>
        </main>

        <div class="mt-8 text-center text-gray-400 text-sm">
            &copy; 2025 MotivAid. All rights reserved.
        </div>
    </body>
</template>
