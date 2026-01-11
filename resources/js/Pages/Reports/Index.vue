<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from '@/helpers';

const props = defineProps({
    appointments: Object,
    professionals: Array,
    filters: Object,
});

const form = ref({
    professional_id: props.filters.professional_id || '',
    start_date: props.filters.start_date || '',
    end_date: props.filters.end_date || '',
});

watch(form, debounce(() => {
    router.get(route('admin.reports.index'), form.value, {
        preserveState: true,
        replace: true
    });
}, 500), { deep: true });

const resetFilters = () => {
    form.value = {
        professional_id: '',
        start_date: '',
        end_date: '',
    };
};

const downloadDailyPdf = () => {
    const params = new URLSearchParams({
        professional_id: form.value.professional_id || '',
        date: form.value.start_date || new Date().toISOString().split('T')[0]
    });
    window.location.href = route('admin.reports.daily-pdf') + '?' + params.toString();
};

const downloadWeeklyPdf = () => {
    const params = new URLSearchParams({
        professional_id: form.value.professional_id || '',
        date: form.value.start_date || new Date().toISOString().split('T')[0]
    });
    window.location.href = route('admin.reports.weekly-pdf') + '?' + params.toString();
};

const downloadFilteredPdf = () => {
    const params = new URLSearchParams({
        professional_id: form.value.professional_id || '',
        start_date: form.value.start_date || '',
        end_date: form.value.end_date || ''
    });
    window.location.href = route('admin.reports.filtered-pdf') + '?' + params.toString();
};

const formatDateTime = (dateTime) => {
    return new Date(dateTime).toLocaleString('es-CL', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'scheduled': return 'bg-blue-100 text-blue-800';
        case 'confirmed': return 'bg-green-100 text-green-800';
        case 'cancelled': return 'bg-red-100 text-red-800';
        case 'completed': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const getStatusLabel = (status) => {
    switch (status) {
        case 'scheduled': return 'Programada';
        case 'confirmed': return 'Confirmada';
        case 'cancelled': return 'Cancelada';
        case 'completed': return 'Completada';
        default: return status;
    }
};
</script>

<template>
    <Head title="Reportes" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Reportes de Citas</h2>
                <div class="space-x-2">
                    <button @click="downloadDailyPdf" class="bg-gray-800 text-white px-4 py-2 rounded text-sm hover:bg-gray-700">
                        Agenda Diaria
                    </button>
                    <button @click="downloadWeeklyPdf" class="bg-gray-600 text-white px-4 py-2 rounded text-sm hover:bg-gray-500">
                        Agenda Semanal
                    </button>
                    <button @click="downloadFilteredPdf" class="bg-primary text-white px-4 py-2 rounded text-sm hover:bg-blue-700">
                        PDF Filtrado
                    </button>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-b border-gray-200">
                        
                        <!-- Filters -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Profesional</label>
                                <select v-model="form.professional_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary">
                                    <option value="">Todos</option>
                                    <option v-for="prof in professionals" :key="prof.id" :value="prof.id">{{ prof.name }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Desde</label>
                                <input v-model="form.start_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hasta</label>
                                <input v-model="form.end_date" type="date" class="w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring-primary" />
                            </div>
                            <div class="flex items-end">
                                <button @click="resetFilters" class="text-gray-600 hover:text-gray-900 text-sm mb-2">Limpiar Filtros</button>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha/Hora</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Paciente</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Profesional</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notas</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="appointment in appointments.data" :key="appointment.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDateTime(appointment.start_at) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ appointment.client?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ appointment.professional?.name || 'N/A' }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusBadgeClass(appointment.status)">
                                                {{ getStatusLabel(appointment.status) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{{ appointment.notes || '-' }}</td>
                                    </tr>
                                    <tr v-if="appointments.data.length === 0">
                                        <td colspan="5" class="px-6 py-10 text-center text-gray-500">No se encontraron citas para los filtros seleccionados.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <div v-if="appointments.links.length > 3" class="mt-6 flex justify-center">
                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                <Link
                                    v-for="(link, index) in appointments.links"
                                    :key="index"
                                    :href="link.url || '#'"
                                    v-html="link.label"
                                    class="relative inline-flex items-center px-4 py-2 border text-sm font-medium"
                                    :class="[
                                        link.active ? 'z-10 bg-primary border-primary text-white' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                                        !link.url ? 'cursor-not-allowed opacity-50' : ''
                                    ]"
                                />
                            </nav>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
