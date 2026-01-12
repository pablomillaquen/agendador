<script setup>
defineProps({
    title: String,
    value: [String, Number],
    icon: String,
    color: {
        type: String,
        default: 'blue'
    },
    trend: String,
});
</script>

<template>
    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-100 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    {{ title }}
                </p>
                <div class="mt-1 flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ value }}
                    </p>
                    <span v-if="trend" class="ml-2 text-sm font-medium" :class="trend.startsWith('+') ? 'text-green-600' : 'text-red-600'">
                        {{ trend }}
                    </span>
                </div>
            </div>
            <div :class="`p-3 rounded-full bg-${color}-100 dark:bg-${color}-900/30 text-${color}-600 dark:text-${color}-400`">
                <slot name="icon">
                    <svg v-if="icon === 'calendar'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <svg v-else-if="icon === 'check'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    <svg v-else-if="icon === 'users'" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </slot>
            </div>
        </div>
    </div>
</template>
