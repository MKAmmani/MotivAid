<template>

    <div
        class="relative flex h-full min-h-screen w-full flex-col bg-background-dark font-display text-text-main overflow-x-hidden">
        <header
            class="flex items-center justify-between whitespace-nowrap border-b border-slate-700 bg-surface-dark px-6 lg:px-10 py-3 sticky top-0 z-50">
            <div class="flex items-center gap-4">
                <div class="size-8 text-primary">
                    <svg class="h-full w-full" fill="none" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M42.4379 44C42.4379 44 36.0744 33.9038 41.1692 24C46.8624 12.9336 42.2078 4 42.2078 4L7.01134 4C7.01134 4 11.6577 12.932 5.96912 23.9969C0.876273 33.9029 7.27094 44 7.27094 44L42.4379 44Z"
                            fill="currentColor"></path>
                    </svg>
                </div>
                <h2 class="text-white text-lg font-bold leading-tight tracking-tight">MotivAid</h2>
            </div>
            <div class="hidden md:flex flex-1 justify-center gap-8 px-8">
                <a class="text-slate-400 hover:text-primary transition-colors text-sm font-medium leading-normal"
                    href="#">Dashboard</a>
                <a class="text-primary text-sm font-bold leading-normal" href="#">Training Modules</a>
                <a class="text-slate-400 hover:text-primary transition-colors text-sm font-medium leading-normal"
                    href="#">Protocols</a>
                <a class="text-slate-400 hover:text-primary transition-colors text-sm font-medium leading-normal"
                    href="#">Resources</a>
            </div>
            <div class="flex items-center gap-4">
                <button
                    class="flex items-center justify-center rounded-lg size-10 bg-slate-800 text-white hover:bg-slate-700 transition-colors">
                    <span class="material-symbols-outlined text-xl">notifications</span>
                </button>
                <div class="bg-center bg-no-repeat bg-cover rounded-full size-10 border-2 border-slate-700"
                    data-alt="Profile picture of medical professional"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuAMZ2ihe4ggLj4jC2ldRnRXlSrmsHfiUq7fjZc28GD75PJ-5zY_mJVUdO2vq998dZOXYKfNMn88nfaOAV9PTF9--mEFKaQ6XH1L39_UcfGNCt6QOf9ZrvmJa1uRGlwFE9OUhyQpcedlR88cr5JJp_zziWvuRUY-TLA5S9GXiKlSz2yCcLu3PQtypE9m8-6sPlMM71VB4gLFLtYY6t7uLPIXAh55Q0hdvNy4RBpOfbAFWz5wDgXPlKqQJuqu3eVbdqQ3T1c-kjh0tG5Y");'>
                </div>
            </div>
        </header>
        <main class="layout-container flex h-full grow flex-col">
            <div class="flex flex-1 justify-center py-8 px-4 md:px-6 lg:px-40">
                <div class="layout-content-container flex flex-col max-w-[1024px] flex-1 w-full gap-8">
                    <nav aria-label="Breadcrumb" class="flex flex-wrap gap-2 items-center text-sm">
                        <a class="text-slate-400 hover:text-primary transition-colors font-medium" href="/videos">Training</a>
                        <span class="text-slate-600">/</span>
                        <span class="text-white font-semibold">{{ video.name }}</span>
                    </nav>
                    <div class="flex flex-col gap-2">
                        <h1 class="text-white text-3xl md:text-4xl font-black leading-tight tracking-tight">{{ video.name }}</h1>
                        <p class="text-slate-400 text-lg font-normal">Video file: {{ video.filename }}</p>
                    </div>
                    <div
                        class="w-full bg-black rounded-xl overflow-hidden shadow-2xl group relative aspect-video border border-slate-800 ring-1 ring-white/10">
                        <!-- Video Player -->
                        <video
                            :src="video.url"
                            controls
                            class="w-full h-full object-contain bg-black"
                            @loadedmetadata="onLoadedMetadata"
                            ref="videoPlayer">
                            Your browser does not support the video tag.
                        </video>
                        <div
                            class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black via-black/80 to-transparent px-4 py-4 md:px-6 md:py-6 flex flex-col gap-3 z-20 opacity-100">
                            <div
                                class="relative w-full h-1.5 bg-white/20 rounded-full cursor-pointer group/progress">
                                <div
                                    class="absolute top-0 left-0 h-full bg-primary rounded-full shadow-[0_0_10px_rgba(236,72,153,0.5)]"
                                    :style="{ width: currentTimePercent + '%' }">
                                </div>
                                <div
                                    class="absolute top-1/2 left-0 -translate-y-1/2 w-4 h-4 bg-white rounded-full shadow-lg transform -translate-x-1/2"
                                    :style="{ left: currentTimePercent + '%' }">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="flex flex-col-reverse md:flex-row justify-between items-start md:items-center gap-4 py-4 border-b border-slate-800 pb-10">
                        <a :href="'/videos'"
                            class="flex w-full md:w-auto items-center justify-center gap-2 px-6 h-12 rounded-lg border border-slate-700 bg-surface-dark hover:bg-slate-800 hover:border-slate-600 text-slate-300 font-semibold transition-all group">
                            <span
                                class="material-symbols-outlined text-xl transition-transform group-hover:-translate-x-1">arrow_back</span>
                            <div class="text-left flex flex-col items-start leading-none">
                                <span
                                    class="text-[10px] uppercase tracking-wider text-slate-500 font-bold mb-1">Back to</span>
                                <span class="text-sm">Video Library</span>
                            </div>
                        </a>
                    </div>
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 pb-12">
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-surface-dark rounded-xl p-6 border border-slate-700 shadow-sm">
                                <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
                                    <span class="material-symbols-outlined text-primary">info</span>
                                    Video Information
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between border-b border-slate-700 pb-2">
                                        <span class="text-slate-400">File Name:</span>
                                        <span class="text-white">{{ video.filename }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-700 pb-2">
                                        <span class="text-slate-400">File Size:</span>
                                        <span class="text-white">{{ video.size_formatted }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-700 pb-2">
                                        <span class="text-slate-400">Duration:</span>
                                        <span class="text-white">{{ video.duration }}</span>
                                    </div>
                                    <div class="flex justify-between border-b border-slate-700 pb-2">
                                        <span class="text-slate-400">Category:</span>
                                        <span class="text-white">{{ video.category }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-slate-400">Last Modified:</span>
                                        <span class="text-white">{{ video.last_modified }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="lg:col-span-1 space-y-4">
                            <div class="bg-surface-dark rounded-xl p-5 border border-slate-700">
                                <h4 class="text-sm font-bold text-white mb-4 uppercase tracking-wider">Video Details
                                </h4>
                                <div class="flex flex-col gap-3">
                                    <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                                        <div
                                            class="size-10 rounded-full bg-primary/20 text-primary flex items-center justify-center shrink-0 border border-primary/30">
                                            <span class="material-symbols-outlined">movie</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-200">Video File</span>
                                            <span class="text-xs text-slate-500">{{ video.size_formatted }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                                        <div
                                            class="size-10 rounded-full bg-blue-900/30 text-blue-400 flex items-center justify-center shrink-0 border border-blue-900/50">
                                            <span class="material-symbols-outlined">schedule</span>
                                        </div>
                                        <div class="flex flex-col">
                                            <span class="text-sm font-semibold text-slate-200">Duration</span>
                                            <span class="text-xs text-slate-500">{{ video.duration }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-surface-dark rounded-xl p-5 border border-slate-700">
                                <h4 class="text-sm font-bold text-white mb-3">Video Status</h4>
                                <div class="flex items-center gap-3 p-3 bg-slate-800/50 rounded-lg border border-slate-700">
                                    <div class="flex items-center gap-2">
                                        <span class="material-symbols-outlined text-[20px] text-slate-400">radio_button_unchecked</span>
                                        <span class="text-slate-300">Not Started</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { defineProps } from 'vue';

const props = defineProps({
    video: {
        type: Object,
        required: true
    }
});

const videoPlayer = ref(null);
const currentTime = ref(0);
const duration = ref(0);
const currentTimePercent = ref(0);

const onLoadedMetadata = () => {
    if (videoPlayer.value) {
        duration.value = videoPlayer.value.duration || 0;
    }
};

onMounted(() => {
    // Update progress bar as video plays
    if (videoPlayer.value) {
        videoPlayer.value.addEventListener('timeupdate', () => {
            if (videoPlayer.value) {
                currentTime.value = videoPlayer.value.currentTime;
                currentTimePercent.value = duration.value > 0
                    ? (currentTime.value / duration.value) * 100
                    : 0;
            }
        });
    }
});
</script>