<script setup>;
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

/*const submit = () => {
    form.transform(data => ({
        ...data,
        _token: document.querySelector('meta[name="csrf-token"]').content
    })).post(route('login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
}; */

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Sign in" />
    <!-- Login Form -->
<body class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen flex flex-col justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <!-- Logo -->
        <div class="flex flex-col items-center">
            <div class="bg-white p-2 rounded-full shadow-md border border-gray-200">
                <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="w-24 h-24 object-contain rounded-full">
            </div>
            <h2 class="mt-4 text-2xl font-bold text-gray-800">Welcome to MotivAid</h2>
        </div>

        <!-- Toggle between Login and Signup -->
        <div class="flex bg-white rounded-lg shadow-sm overflow-hidden" role="tablist">
            <Link :href="route('login')" id="login-tab" type="button" class="px-6 py-3 text-sm font-medium text-motivaid-teal border-b-2 border-motivaid-teal bg-white" role="tab" aria-selected="true" aria-controls="login-form" tabindex="0">Sign in</Link>
            <Link :href="route('register')" id="signup-tab" type="button" class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 bg-gray-50" role="tab" aria-selected="false" aria-controls="signup-form" tabindex="-1">Sign up</Link>
        </div>
        <!-- Login Form -->
        <form @submit.prevent="submit" class="bg-white px-8 pt-6 pb-8 rounded-xl shadow-lg space-y-6">
            <div>
                <label for="login-email" class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input id="login-email" v-model="form.email" type="email" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-1 transition duration-200">
                <InputError class="mt-2" :message="form.errors.email" />
            </div>
            <div>
                <label for="login-password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                <input id="login-password" v-model="form.password" type="password" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-motivaid-teal focus:ring-offset-1 transition duration-200">
                <InputError class="mt-2" :message="form.errors.password" />
            </div>
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input id="remember" name="remember" v-model="form.remember" type="checkbox" class="h-4 w-4 text-motivaid-teal focus:ring-motivaid-teal border-gray-300 rounded">
                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                </div>
                <div class="text-sm">
                    <Link v-if="canResetPassword"
                    :href="route('password.request')"
                    class="font-medium text-motivaid-teal hover:text-teal-600 transition duration-200">Forgot password?</Link>
                </div>
            </div>
            <button :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                    type="submit" class="w-full bg-gradient-to-r from-motivaid-pink to-hotpink-800 text-white py-3 px-4 rounded-lg font-medium hover:from-motivaid-pink hover:to-motivaid-pink focus:outline-none focus:ring-2 focus:ring-motivaid-pink focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200 transform hover:scale-[1.02]">
                Sign in
            </button>
        </form>

        <div class="text-center text-sm text-gray-600">
            By signing up, you agree to our <a href="#" class="text-motivaid-teal hover:underline transition duration-200">Terms of Service</a> and <a href="#" class="text-motivaid-teal hover:underline transition duration-200">Privacy Policy</a>.
        </div>
    </div>


</body>
</template>
