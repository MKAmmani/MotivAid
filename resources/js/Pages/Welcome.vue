<script setup>
import {Link } from '@inertiajs/vue3';

</script>


<template>
    <body class="bg-background-light dark:bg-background-dark font-display text-slate-800 dark:text-slate-200 antialiased min-h-screen flex flex-col">

  <!-- Full-screen container with background image -->
  <div class="relative flex min-h-screen w-full items-center justify-center overflow-hidden">

    <!-- Request Demo Button -->
    <button class="absolute top-4 right-4 z-30 rounded-lg bg-primary px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
      Request Demo
    </button>

    <!-- Background Image -->
    <div 
      class="absolute inset-0 z-0 bg-cover bg-center"
      style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuAgfaVgnl-MBbwxT8MJSwEzmnw4doKyFgYFH0hpSQZqFkPcpXlw1-HVR1Q9rK9rES9OG9-ChvfDjd5z1kfnHivl17iOVArP6lHOKX1qowN2t62gXofx4-0U1Pf4DkUS7HgGXcNTUIuuiXxSVWZ8VTpK2JIZQD2GFXJknbGbi2INaA05f-OHcTvLU4Z5RLt3t7ipG5KbfIkBWDQClUZNnpgsCr3g_Extea7F6OnRyQjZFprLCsjLHs3yWGdhIZW9j5-pKezMRCXoD2o');">
    </div>

    <!-- Dark overlay -->
    <div class="absolute inset-0 bg-black/60 z-10"></div>

    <!-- Main content -->
    <main class="relative z-20 mx-auto flex h-full w-full max-w-md flex-col justify-between p-4 sm:p-6">

      <!-- Hero Text Section -->
      <div class="flex flex-1 flex-col items-center justify-center pt-16 text-center">
        <h1 class="text-5xl font-bold text-white">
          Motiv<span class="text-primary">Aid</span>
        </h1>
        <p class="mt-4 max-w-sm text-lg text-slate-200">
          Empowering healthcare providers to manage postpartum hemorrhage effectively.
        </p>
      </div>

      <!-- Action Buttons Card -->
      <div class="w-full space-y-6 pb-6 pt-8">
        <div class="space-y-4">
          <!-- Log In Button -->
          <Link :href="route('login')"
             class="flex w-full items-center justify-center rounded-lg bg-primary px-5 py-3.5 text-base font-semibold text-white shadow-sm transition-colors hover:bg-primary/90 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-primary">
            Log In
          </Link>

          <!-- Register Button (Glassmorphism effect) -->
          <Link :href="route('register')"
             class="flex w-full items-center justify-center rounded-lg border border-white/50 bg-white/10 px-5 py-3.5 text-base font-semibold text-white backdrop-blur-sm transition-colors hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            Register
          </Link>
        </div>

        <!-- Footer Text -->
        <p class="text-center text-xs text-slate-300">
          Your trusted partner in PPH management.
        </p>
      </div>
    </main>
  </div>
</body>
</template>