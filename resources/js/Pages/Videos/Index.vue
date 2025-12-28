<template>

    <div
        class="bg-background-dark text-text-main min-h-screen flex flex-col font-display antialiased selection:bg-primary selection:text-white">
        <header class="w-full bg-surface-darker/80 backdrop-blur-md border-b border-border-dark sticky top-0 z-50">
            <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-18 py-3 gap-4">
                    <div class="flex items-center gap-8">
                        <div class="flex items-center gap-3 text-white group cursor-pointer">
                            <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="h-10 w-10 rounded-lg object-contain" />
                            <div>
                                <h2 class="text-xl font-bold leading-none tracking-tight">MotivAid</h2>
                                <span
                                    class="text-[10px] uppercase tracking-wider font-semibold text-text-secondary">Professional
                                    Suite</span>
                            </div>
                        </div>
                        <nav
                            class="hidden md:flex items-center gap-1 bg-surface-dark px-2 py-1.5 rounded-full border border-border-dark">
                            <a class="px-4 py-1.5 text-text-secondary hover:text-white hover:bg-white/5 rounded-full text-sm font-medium transition-all"
                                href="#">Dashboard</a>
                            <a class="px-4 py-1.5 bg-primary text-white shadow-lg shadow-primary/20 rounded-full text-sm font-medium transition-all"
                                href="#">Training</a>
                            <a class="px-4 py-1.5 text-text-secondary hover:text-white hover:bg-white/5 rounded-full text-sm font-medium transition-all"
                                href="#">My Progress</a>
                            <a class="px-4 py-1.5 text-text-secondary hover:text-white hover:bg-white/5 rounded-full text-sm font-medium transition-all"
                                href="#">Community</a>
                        </nav>
                    </div>
                    <div class="flex items-center gap-4 flex-1 justify-end max-w-lg">
                        <div class="relative hidden sm:flex w-full max-w-xs group">
                            <div
                                class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-secondary group-focus-within:text-primary transition-colors">
                                <span class="material-symbols-outlined text-[20px]">search</span>
                            </div>
                            <input
                                class="block w-full pl-10 pr-3 py-2 border border-border-dark rounded-full leading-5 bg-surface-dark placeholder-text-secondary/70 text-sm focus:outline-none focus:bg-surface-darker focus:ring-2 focus:ring-primary/50 focus:border-primary transition-all duration-200"
                                placeholder="Search simulations..." type="text" />
                        </div>
                        <div class="flex items-center gap-3 pl-3 border-l border-border-dark">
                            <button class="relative p-2 text-text-secondary hover:text-white transition-colors">
                                <span class="material-symbols-outlined text-[22px]">notifications</span>
                                <span
                                    class="absolute top-1.5 right-1.5 h-2 w-2 rounded-full bg-primary ring-2 ring-surface-darker"></span>
                            </button>
                            <div class="bg-center bg-no-repeat bg-cover rounded-full h-9 w-9 border-2 border-surface-dark"
                                style='background-image: url("https://i.pravatar.cc/40")'>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <main class="flex-grow w-full max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <nav aria-label="Breadcrumb" class="flex mb-8">
                <ol
                    class="flex items-center space-x-2 bg-surface-dark/50 px-4 py-2 rounded-lg border border-border-dark/50">
                    <li>
                        <a class="text-text-secondary hover:text-primary text-xs font-medium uppercase tracking-wide transition-colors"
                            href="#">Home</a>
                    </li>
                    <li>
                        <span class="text-border-dark text-xs">/</span>
                    </li>
                    <li>
                        <a class="text-text-secondary hover:text-primary text-xs font-medium uppercase tracking-wide transition-colors"
                            href="#">Training</a>
                    </li>
                    <li>
                        <span class="text-border-dark text-xs">/</span>
                    </li>
                    <li>
                        <span aria-current="page" class="text-white text-xs font-semibold uppercase tracking-wide">Video
                            Library</span>
                    </li>
                </ol>
            </nav>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-6 mb-10">
                <div class="max-w-3xl">
                    <h1
                        class="text-3xl md:text-5xl font-bold text-white tracking-tight mb-4 bg-gradient-to-r from-white to-white/60 bg-clip-text text-transparent">
                        E-MOTIVE Simulation Library
                    </h1>
                    <p class="text-lg text-text-secondary leading-relaxed font-light">
                        Master the E-MOTIVE protocol for postpartum hemorrhage management with our specialized video
                        guides.
                        <span class="text-primary/80">Improve coordination</span> and response times through simulated
                        scenarios.
                    </p>
                </div>
                <button
                    class="flex items-center gap-2 bg-white text-background-dark hover:bg-gray-100 px-5 py-3 rounded-xl font-semibold transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5 whitespace-nowrap">
                    <span class="material-symbols-outlined text-[20px]">play_circle</span>
                    Resume Training
                </button>
            </div>
            <div
                class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 bg-surface-dark p-2 rounded-xl border border-border-dark shadow-xl shadow-black/20">
                <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto p-2">
                    <div class="relative min-w-[200px]">
                        <label class="sr-only" for="category-select">Filter by Category</label>
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-secondary">
                            <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        </div>
                        <select
                            class="form-select block w-full pl-10 pr-10 py-2.5 bg-surface-darker text-sm text-white border-transparent focus:border-primary focus:ring-0 rounded-lg cursor-pointer hover:bg-surface-darker/80 transition-colors"
                            id="category-select">
                            <option>All Categories</option>
                            <option>Early Detection</option>
                            <option>Response Protocol</option>
                            <option>Team Communication</option>
                            <option>Medication Administration</option>
                        </select>
                    </div>
                    <div class="relative min-w-[160px]">
                        <label class="sr-only" for="sort-select">Sort by</label>
                        <div
                            class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-secondary">
                            <span class="material-symbols-outlined text-[18px]">sort</span>
                        </div>
                        <select
                            class="form-select block w-full pl-10 pr-10 py-2.5 bg-surface-darker text-sm text-white border-transparent focus:border-primary focus:ring-0 rounded-lg cursor-pointer hover:bg-surface-darker/80 transition-colors"
                            id="sort-select">
                            <option>Newest First</option>
                            <option>Most Watched</option>
                            <option>Alphabetical</option>
                        </select>
                    </div>
                </div>
                <div
                    class="flex items-center bg-surface-darker rounded-lg p-1 border border-border-dark/50 self-end md:self-auto mr-2">
                    <button
                        class="flex items-center justify-center px-3 py-2 rounded-md bg-surface-dark shadow-sm text-primary text-sm font-medium transition-all border border-border-dark/50">
                        <span class="material-symbols-outlined text-[20px]">grid_view</span>
                    </button>
                    <button
                        class="flex items-center justify-center px-3 py-2 rounded-md text-text-secondary hover:text-white hover:bg-white/5 text-sm font-medium transition-all">
                        <span class="material-symbols-outlined text-[20px]">view_list</span>
                    </button>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                <template v-for="video in videos" :key="video.filename">
                    <div
                        class="group flex flex-col bg-surface-dark rounded-2xl overflow-hidden border border-border-dark hover:border-primary/50 hover:shadow-2xl hover:shadow-primary/10 transition-all duration-300 relative">
                        <div class="relative aspect-video bg-surface-darker overflow-hidden">
                            <!-- Video thumbnail with gradient overlay -->
                            <div class="absolute inset-0 bg-cover bg-center transition-transform duration-700 group-hover:scale-105"
                                :style="{ 'background-color': getVideoColor(video.name) }">
                                <!-- Video icon for visual representation -->
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <span class="material-symbols-outlined text-4xl text-white/30">movie</span>
                                </div>
                            </div>
                            <div class="absolute inset-0 bg-gradient-to-t from-surface-dark to-transparent opacity-80">
                            </div>
                            <div
                                class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity duration-300 backdrop-blur-[1px]">
                                <a :href="`/videos/${video.filename}`"
                                    class="bg-primary/90 text-white rounded-full p-4 shadow-xl shadow-primary/30 transform scale-90 group-hover:scale-100 transition-all duration-300 hover:bg-primary">
                                    <span class="material-symbols-outlined text-3xl">play_arrow</span>
                                </a>
                            </div>
                            <div
                                class="absolute bottom-3 right-3 bg-black/60 backdrop-blur-md text-white text-xs font-semibold px-2.5 py-1 rounded-md border border-white/10">
                                {{ video.duration }}
                            </div>
                        </div>
                        <div class="p-5 flex flex-col flex-1 relative">
                            <div
                                class="absolute top-0 right-5 -mt-4 bg-surface-dark rounded-full p-1 border border-border-dark">
                                <div :class="getStatusInfo(video.status).class" :title="getStatusInfo(video.status).title">
                                    <span class="material-symbols-outlined text-[20px]">{{ getStatusInfo(video.status).icon }}</span>
                                </div>
                            </div>
                            <div class="flex flex-col gap-3 mb-2">
                                <span
                                    :class="`self-start inline-flex items-center rounded-md px-2.5 py-1 text-xs font-semibold ${getCategoryColor(video.category)}`">{{ video.category }}</span>
                                <h3
                                    class="text-lg font-bold text-white leading-snug group-hover:text-primary transition-colors truncate max-w-full">
                                    {{ video.name }}
                                </h3>
                            </div>
                            <p class="text-sm text-text-secondary line-clamp-2 mb-6 font-light">
                                {{ video.size_formatted }} video file
                            </p>
                            <div
                                class="mt-auto pt-4 border-t border-border-dark flex items-center justify-between text-xs font-medium text-text-secondary">
                                <span class="flex items-center gap-1.5 hover:text-white transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span> {{ video.views }} views
                                </span>
                                <span>{{ video.last_modified }}</span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Show message if no videos are found -->
                <div v-if="videos.length === 0" class="col-span-full text-center py-12">
                    <div class="text-text-secondary text-lg">No videos found in the library</div>
                </div>
            </div>
            <div class="mt-12 flex justify-center">
                <button
                    class="px-8 py-3 bg-surface-dark border border-border-dark rounded-xl text-sm font-semibold text-text-secondary hover:text-white hover:border-primary hover:bg-surface-darker transition-all shadow-lg hover:shadow-primary/20 flex items-center gap-2">
                    Load More Videos
                    <span class="material-symbols-outlined text-[18px]">expand_more</span>
                </button>
            </div>
        </main>
        <footer class="bg-surface-darker border-t border-border-dark py-12 mt-auto">
            <div
                class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex flex-col md:flex-row items-center gap-4 text-center md:text-left">
                    <div class="flex items-center gap-2">
                        <img src="/images/motivaid_logo.jpg" alt="MotivAid Logo" class="h-8 w-8 rounded object-contain" />
                        <span class="text-lg font-bold text-white">MotivAid</span>
                    </div>
                    <div class="hidden md:block w-px h-6 bg-border-dark"></div>
                    <span class="text-xs text-text-secondary">© 2023 MotivAid Healthcare Solutions. All rights
                        reserved.</span>
                </div>
                <div class="flex gap-8">
                    <a class="text-xs font-medium text-text-secondary hover:text-primary transition-colors"
                        href="#">Clinical Disclaimer</a>
                    <a class="text-xs font-medium text-text-secondary hover:text-primary transition-colors"
                        href="#">Privacy Policy</a>
                    <a class="text-xs font-medium text-text-secondary hover:text-primary transition-colors"
                        href="#">Support</a>
                </div>
            </div>
        </footer>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
    videos: {
        type: Array,
        default: () => []
    }
});

// Function to get status icon and class based on video status
const getStatusInfo = (status) => {
    switch (status) {
        case 'Completed':
            return {
                icon: 'check_circle',
                class: 'bg-accent-success/10 text-accent-success',
                title: 'Completed'
            };
        case 'In Progress':
            return {
                icon: 'timelapse',
                class: 'bg-primary/10 text-primary',
                title: 'In Progress'
            };
        default:
            return {
                icon: 'radio_button_unchecked',
                class: 'bg-text-secondary/10 text-text-secondary',
                title: 'Not Started'
            };
    }
};

// Function to get a color based on the video name for visual variety
const getVideoColor = (name) => {
    const colors = [
        '#4f46e5', // indigo
        '#ec4899', // pink
        '#f59e0b', // amber
        '#10b981', // emerald
        '#3b82f6', // blue
        '#8b5cf6', // violet
        '#ef4444', // red
        '#06b6d4', // cyan
    ];

    // Create a simple hash of the name to get consistent colors
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }

    const index = Math.abs(hash) % colors.length;
    return colors[index];
};

// Function to get category color classes
const getCategoryColor = (category) => {
    const colors = {
        'Early Detection': 'bg-blue-500/10 text-blue-400 ring-blue-500/20',
        'Response Protocol': 'bg-purple-500/10 text-purple-400 ring-purple-500/20',
        'Team Communication': 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        'Medication Administration': 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        'Protocol': 'bg-purple-500/10 text-purple-400 ring-purple-500/20',
        'Medication': 'bg-amber-500/10 text-amber-400 ring-amber-500/20',
        'Teamwork': 'bg-teal-500/10 text-teal-400 ring-teal-500/20',
        'Patient Care': 'bg-pink-500/10 text-pink-400 ring-pink-500/20'
    };

    return colors[category] || 'bg-gray-500/10 text-gray-400 ring-gray-500/20';
};
</script>