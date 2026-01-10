<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    businessHours: Array,
});

const daysOfWeek = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];

// Map props to form state
// If props.businessHours comes from DB (has id, start_time, etc.), we map it.
// If it comes from the seed logic in controller (virtual), we also map it.
// We need to merge with a full week structure if gaps exist, but controller logic did that.

const form = useForm({
    hours: props.businessHours.map(h => ({
        day_of_week: h.day_of_week,
        start_time: h.start_time ? h.start_time.slice(0, 5) : '',
        end_time: h.end_time ? h.end_time.slice(0, 5) : '',
        is_enabled: !!h.start_time, // if start_time exists, it's enabled by default logic
    }))
});

// Ensure we have all 7 days in order
// The controller sorts by day_of_week, so iterating 0..6 matching index is checking logic.
// But better safe:
const weekDaysForm = ref(Array(7).fill(null).map((_, i) => {
    const existing = form.hours.find(h => h.day_of_week === i);
    return existing || {
        day_of_week: i,
        start_time: '09:00',
        end_time: '17:00',
        is_enabled: false
    };
}));

// We replace form.hours with our reactive structure for submission
// Actually, form.hours should be the source of truth.
// Let's re-initialize form.hours to ensure 7 items properly ordered.
form.hours = Array(7).fill(null).map((_, i) => {
    const existing = props.businessHours.find(h => h.day_of_week === i);
    return {
        day_of_week: i,
        start_time: existing?.start_time ? existing.start_time.slice(0, 5) : '09:00',
        end_time: existing?.end_time ? existing.end_time.slice(0, 5) : '17:00',
        is_enabled: !!existing?.start_time,
    };
});

const submit = () => {
    // Filter out disabled days or send nulls?
    // Controller logic: "if (!empty($hour['start_time'])...)"
    // So if disabled, we can clear times or just send them and let controller ignore if we change logic?
    // The controller currently: if !empty(start) && !empty(end) -> create.
    // So if disabled, we should probably set start/end to null.
    
    // Better: Prepare data for submission
    const dataToSubmit = form.hours.map(h => ({
        day_of_week: h.day_of_week,
        start_time: h.is_enabled ? h.start_time : null,
        end_time: h.is_enabled ? h.end_time : null,
    }));
    
    // Use a temp form or just modify form? modifying form might affect UI.
    // Let's use form.transform
    form.transform((data) => ({
        hours: data.hours.map(h => ({
            day_of_week: h.day_of_week,
            start_time: h.is_enabled ? h.start_time : null,
            end_time: h.is_enabled ? h.end_time : null,
        }))
    })).post(route('admin.business-hours.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Toast?
        },
    });
};
</script>

<template>
    <Head title="Horarios de Atención" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Horarios de Atención</h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <form @submit.prevent="submit" class="space-y-4">
                            <div v-for="(day, index) in form.hours" :key="day.day_of_week" class="flex items-center space-x-4 border-b border-gray-100 dark:border-gray-700 pb-4 last:border-0 last:pb-0">
                                <div class="w-32 flex items-center">
                                    <input 
                                        type="checkbox" 
                                        v-model="day.is_enabled" 
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    >
                                    <span class="ml-2">{{ daysOfWeek[day.day_of_week] }}</span>
                                </div>
                                
                                <div class="flex items-center space-x-2" :class="{ 'opacity-50 pointer-events-none': !day.is_enabled }">
                                    <input 
                                        type="time" 
                                        v-model="day.start_time"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                    <span>-</span>
                                    <input 
                                        type="time" 
                                        v-model="day.end_time"
                                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                    >
                                </div>
                            </div>

                            <div class="flex justify-end pt-4">
                                <button 
                                    type="submit" 
                                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
                                    :disabled="form.processing"
                                >
                                    Guardar Horarios
                                </button>
                            </div>
                        
                            <div v-if="form.recentlySuccessful" class="text-sm text-green-600">
                                Guardado correctamente.
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
