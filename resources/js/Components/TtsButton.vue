<template>
  <button
    @click="handleClick"
    :disabled="!isSupported || isSpeaking"
    :class="[
      'flex items-center justify-center p-2 rounded-full focus:outline-none focus:ring-2 focus:ring-motivaid-pink transition-colors',
      isSpeaking 
        ? 'bg-gray-300 text-gray-500' 
        : 'bg-motivaid-pink text-white hover:bg-hotpink-800',
      { 'opacity-50 cursor-not-allowed': !isSupported || isSpeaking }
    ]"
    :title="isSupported ? (isSpeaking ? 'Currently speaking...' : 'Read aloud') : 'TTS not supported'"
    aria-label="Text-to-speech"
  >
    <svg
      v-if="!isSpeaking"
      xmlns="http://www.w3.org/2000/svg"
      class="h-4 w-4"
      viewBox="0 0 20 20"
      fill="currentColor"
    >
      <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM14.657 2.929a1 1 0 011.414 0A9.972 9.972 0 0119 10a9.972 9.972 0 01-2.929 7.071 1 1 0 01-1.414-1.414A7.971 7.971 0 0017 10c0-2.21-.894-4.208-2.343-5.657a1 1 0 010-1.414zm-2.829 2.828a1 1 0 011.415 0A5.983 5.983 0 0115 10a5.984 5.984 0 01-1.757 4.243 1 1 0 01-1.415-1.415A3.984 3.984 0 0013 10a3.983 3.983 0 00-1.172-2.828 1 1 0 010-1.415z" clip-rule="evenodd" />
    </svg>
    <svg
      v-else
      xmlns="http://www.w3.org/2000/svg"
      class="h-4 w-4 animate-pulse"
      viewBox="0 0 20 20"
      fill="currentColor"
    >
      <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
    </svg>
  </button>
</template>

<script setup>
import { useSpeechSynthesis } from '@/Composables/speech.js';

// Define props
const props = defineProps({
  text: {
    type: String,
    required: true
  },
  showText: {
    type: Boolean,
    default: true
  }
});

// Initialize the speech composable
const {
  isSupported,
  isSpeaking,
  speak,
  pause,
  resume,
  cancel
} = useSpeechSynthesis();

// Handle button click
const handleClick = () => {
  if (!isSupported.value) return;

  if (isSpeaking.value) {
    // If currently speaking, cancel speech
    cancel();
  } else {
    // If not speaking, start speaking the provided text
    speak(props.text);
  }
};
</script>