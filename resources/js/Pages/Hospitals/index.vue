<script setup>
import { ref, onMounted } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import Dropdown from "@/Components/Dropdown.vue";
import DropdownLink from "@/Components/DropdownLink.vue";

const page = usePage();
const hospitals = ref(page.props.hospitals || []);

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

const deleteHospital = (hospital) => {
    if (confirm(`Are you sure you want to delete ${hospital.name}?`)) {
        // Inertia delete
        // Since it's a resource route, we can use Link with method="delete"
        // But for confirmation, we can use a form or direct delete
        // For simplicity, use Link with method="delete"
    }
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
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-800">Hospitals</h2>
                <Link
                    :href="route('hospitals.create')"
                    class="bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white px-4 py-2 rounded-md hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink"
                >
                    Add New Hospital
                </Link>
            </div>

            <section class="bg-white rounded-lg shadow-sm p-6">
                <div v-if="hospitals.length === 0" class="text-center py-8">
                    <p class="text-gray-500">No hospitals found.</p>
                    <Link
                        :href="route('hospitals.create')"
                        class="text-motivaid-teal hover:underline mt-2 inline-block"
                    >
                        Create your first hospital
                    </Link>
                </div>
                <div v-else class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Name
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Location
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Contact Info
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"
                                >
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr
                                v-for="hospital in hospitals"
                                :key="hospital.id"
                            >
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"
                                >
                                    {{ hospital.name }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ hospital.location }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"
                                >
                                    {{ hospital.contact_info }}
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium"
                                >
                                    <Link
                                        :href="
                                            route('hospitals.show', hospital.id)
                                        "
                                        class="text-indigo-600 hover:text-indigo-900 mr-4"
                                    >
                                        View
                                    </Link>
                                    <Link
                                        :href="
                                            route('hospitals.edit', hospital.id)
                                        "
                                        class="text-blue-600 hover:text-blue-900 mr-4"
                                    >
                                        Edit
                                    </Link>
                                    <Link
                                        :href="
                                            route(
                                                'hospitals.destroy',
                                                hospital.id
                                            )
                                        "
                                        method="delete"
                                        class="text-red-600 hover:text-red-900"
                                        @click="deleteHospital(hospital)"
                                    >
                                        Delete
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>

        <div class="mt-8 text-center text-gray-400 text-sm">
            &copy; 2025 MotivAid. All rights reserved.
        </div>
    </body>
</template>
