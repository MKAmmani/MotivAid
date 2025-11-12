import { ref, onMounted } from "vue";

export function useSpeechSynthesis() {
    const isSupported = ref(false);
    const isSpeaking = ref(false);
    const isPaused = ref(false);
    const voices = ref([]);
    const currentVoice = ref(null);
    const speechRate = ref(1.0);
    const speechPitch = ref(1.0);
    const speechVolume = ref(1.0);
    const userInteracted = ref(false); // 🟢 Browser permission guard
    let currentUtterance = null; // Store reference to current utterance to prevent conflicts
    let isProcessing = false; // Flag to prevent race conditions
    let queuedSpeech = null; // Queue to handle back-to-back speech requests
    let onSpeechCompleted = null; // Callback when speech finishes

    onMounted(() => {
        isSupported.value = "speechSynthesis" in window;
        if (!isSupported.value) {
            console.warn("Speech synthesis not supported in this browser");
            return;
        }

        loadVoices();
        window.speechSynthesis.onvoiceschanged = loadVoices;

        // Detect first user interaction to unlock audio (click, touch, keyboard, etc.)
        const enableAudio = () => {
            userInteracted.value = true;
            document.removeEventListener("click", enableAudio);
            document.removeEventListener("touchstart", enableAudio);
            document.removeEventListener("keydown", enableAudio);
        };

        document.addEventListener("click", enableAudio, { once: true });
        document.addEventListener("touchstart", enableAudio, { once: true });
        document.addEventListener("keydown", enableAudio, { once: true });
    });

    const loadVoices = () => {
        const v = speechSynthesis.getVoices();
        if (v.length) {
            voices.value = v;
            const en = v.find((voice) => voice.lang.includes("en")) || v[0];
            currentVoice.value = en;
        }
    };

    const processNextInQueue = () => {
        if (queuedSpeech && !isProcessing) {
            const nextSpeech = queuedSpeech;
            queuedSpeech = null; // Clear the queue
            // Process the next speech request
            speak(nextSpeech.text, nextSpeech.options, true); // Skip queue for this call
        }
    };

    const speak = (text, customOptions = {}, skipQueue = false) => {
        if (!isSupported.value || !text) return;
        if (!userInteracted.value) {
            console.warn("Speech blocked until user interacts with the page.");
            return;
        }

        // If we have a request in progress and another comes in, add to queue unless specifically told to skip
        if (isProcessing && !skipQueue) {
            queuedSpeech = { text, options: customOptions };
            return;
        }

        // Mark as processing to prevent race conditions
        isProcessing = true;

        // Create new utterance (don't cancel the old one - let it finish or end gracefully)
        const utterance = new SpeechSynthesisUtterance(text);
        utterance.voice = customOptions.voice || currentVoice.value;
        utterance.rate = customOptions.rate || speechRate.value;
        utterance.pitch = customOptions.pitch || speechPitch.value;
        utterance.volume = customOptions.volume || speechVolume.value;

        // Store reference to current utterance
        currentUtterance = utterance;

        utterance.onstart = () => {
            isSpeaking.value = true;
            isPaused.value = false;
        };

        utterance.onend = () => {
            isSpeaking.value = false;
            isPaused.value = false;
            isProcessing = false;
            // Clear the reference when speech ends
            if (currentUtterance === utterance) {
                currentUtterance = null;
            }
            // Call the completion callback if set
            if (onSpeechCompleted) {
                onSpeechCompleted();
            }
            // Process any queued speech
            processNextInQueue();
        };

        utterance.onerror = (e) => {
            // Only log real errors, not "interrupted" which is expected
            if (e.error !== "interrupted") {
                console.error("Speech error:", e.error);
            }
            isSpeaking.value = false;
            isProcessing = false;
            // Clear the reference when error occurs
            if (currentUtterance === utterance) {
                currentUtterance = null;
            }
            // Call the completion callback even on error
            if (onSpeechCompleted) {
                onSpeechCompleted();
            }
            // Process any queued speech
            processNextInQueue();
        };

        window.speechSynthesis.speak(utterance);
    };

    const pause = () => {
        if (isSupported.value && speechSynthesis.speaking && !isPaused.value) {
            speechSynthesis.pause();
            isPaused.value = true;
        }
    };

    const resume = () => {
        if (isSupported.value && speechSynthesis.paused) {
            speechSynthesis.resume();
            isPaused.value = false;
        }
    };

    const cancel = () => {
        if (isSupported.value) {
            window.speechSynthesis.cancel();
            isSpeaking.value = false;
            isPaused.value = false;
            isProcessing = false;
            queuedSpeech = null; // Clear any queued speech
            // Clear the reference when cancelled
            currentUtterance = null;
        }
    };

    const setVoiceByLang = (lang) => {
        const v = voices.value.find((voice) => voice.lang.includes(lang));
        if (v) currentVoice.value = v;
    };

    const setOnSpeechCompleted = (callback) => {
        onSpeechCompleted = callback;
    };

    const allowUserInteraction = () => {
        userInteracted.value = true;
    };

    return {
        isSupported,
        isSpeaking,
        isPaused,
        voices,
        currentVoice,
        speechRate,
        speechPitch,
        speechVolume,
        userInteracted,
        speak,
        pause,
        resume,
        cancel,
        loadVoices,
        setVoiceByLang,
        setOnSpeechCompleted,
        allowUserInteraction,
    };
}
