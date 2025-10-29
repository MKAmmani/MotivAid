<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />
    <!-- Login Form -->
<body class="bg-gray-50 min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="flex flex-col items-center">
            <svg class="w-12 h-12 text-motivaid-teal mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
            </svg>
            <h2 class="text-3xl font-bold text-gray-900">MotivAid</h2>
        </div>

        <!-- Toggle between Login and Signup -->
        <div class="flex bg-white rounded-md shadow-sm" role="tablist">
            <Link :href="route('register')" id="login-tab" type="button" class="px-4 py-2 text-sm font-medium text-motivaid-teal border-b-2 border-motivaid-teal rounded-tl-md" role="tab" aria-selected="true" aria-controls="login-form" tabindex="0">Sing up</Link>
            <Link :href="route('login')" id="signup-tab" type="button" class="px-4 py-2 text-sm font-medium text-gray-500 hover:text-gray-700 rounded-tr-md" role="tab" aria-selected="false" aria-controls="signup-form" tabindex="-1">Sign up</Link>            
        </div>
        <!-- Signup Form -->
        <form id="signup-form" @submit.prevent="submit" class="bg-white px-8 pt-6 pb-8 rounded-md shadow-md space-y-6">
            <div>
                <label for="signup-name" class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                <input id="signup-name" v-model="form.name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal">
                <InputError class="mt-2" :message="form.errors.name" />
            </div>
            <div>
                <label for="signup-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input id="signup-email" v-model="form.email" type="email" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal">
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <label for="signup-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input id="signup-password"  v-model="form.password" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal">
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div>
                <label for="signup-confirm-password" class="block text-sm font-medium text-gray-700 mb-2">Confirm Password</label>
                <input id="signup-confirm-password"  v-model="form.password_confirmation" type="password" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-motivaid-teal">
                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>
            <button :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit" class="w-full bg-motivaid-teal text-white py-2 px-4 rounded-md font-medium hover:bg-teal-600 focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                Sign up
            </button>
        </form>

        <div class="text-center text-sm text-gray-600">
            By signing up, you agree to our <a href="#" class="text-motivaid-teal hover:underline">Terms of Service</a> and <a href="#" class="text-motivaid-teal hover:underline">Privacy Policy</a>.
        </div>
    </div>


</body>
</template>
