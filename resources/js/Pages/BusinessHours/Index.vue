<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    businessHours: Array,
    professionals: Array,
    selectedUserId: Number,
});

const daysOfWeek = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
const selectedProfessionalId = ref(props.selectedUserId);

watch(selectedProfessionalId, (newId) => {
    router.get(route('admin.business-hours.index'), { user_id: newId }, {
        preserveState: true,
        preserveScroll: true,
    });
});

const form = useForm({
    user_id: props.selectedUserId,
    hours: Array(7).fill(null).map((_, i) => {
        const existing = props.businessHours.find(h => h.day_of_week === i);
        return {
            day_of_week: i,
            start_time: existing?.start_time ? existing.start_time.slice(0, 5) : '09:00',
            end_time: existing?.end_time ? existing.end_time.slice(0, 5) : '17:00',
            is_enabled: !!existing?.start_time,
        };
    })
});

// Update form when props change (when professional is switched)
watch(() => props.businessHours, (newHours) => {
    form.user_id = props.selectedUserId;
    form.hours = Array(7).fill(null).map((_, i) => {
        const existing = newHours.find(h => h.day_of_week === i);
        return {
            day_of_week: i,
            start_time: existing?.start_time ? existing.start_time.slice(0, 5) : '09:00',
            end_time: existing?.end_time ? existing.end_time.slice(0, 5) : '17:00',
            is_enabled: !!existing?.start_time,
        };
    });
}, { deep: true });

const submit = () => {
    form.transform((data) => ({
        user_id: data.user_id,
        hours: data.hours.map(h => ({
            day_of_week: h.day_of_week,
            start_time: h.is_enabled ? h.start_time : null,
            end_time: h.is_enabled ? h.end_time : null,
        }))
    })).post(route('admin.business-hours.update'), {
        preserveScroll: true,
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
                
                <div v-if="professionals.length > 0" class="mb-6">
                    <label for="professional-selector" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Seleccionar Profesional</label>
                    <select 
                        id="professional-selector" 
                        v-model="selectedProfessionalId"
                        class="mt-1 block w-full md:w-1/3 border-gray-300 focus:border-primary focus:ring-primary rounded-md shadow-sm"
                    >
                        <option v-for="prof in professionals" :key="prof.id" :value="prof.id">
                            {{ prof.name }} ({{ prof.role }})
                        </option>
                    </select>
                </div>

                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        
                        <div class="mb-4 text-lg font-medium">
                            Configurando horario para: {{ professionals.find(p => p.id === selectedProfessionalId)?.name || $page.props.auth.user.name }}
                        </div>

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
