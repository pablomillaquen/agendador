<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    appointments: Array,
    showProfessional: {
        type: Boolean,
        default: true
    }
});

const getStatusBadgeClass = (status) => {
    switch (status) {
        case 'scheduled': return 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400';
        case 'confirmed': return 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400';
        case 'cancelled': return 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400';
        case 'completed': return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatTime = (time) => {
    return new Date(time).toLocaleTimeString('es-CL', {
        hour: '2-digit',
        minute: '2-digit'
    });
};

const updateStatus = (id, newStatus) => {
    if (confirm(`¿Cambiar estado de la cita a ${newStatus}?`)) {
        router.patch(route('admin.appointments.update', id), {
            status: newStatus
        }, {
            preserveScroll: true
        });
    }
};

import { router } from '@inertiajs/vue3';
</script>

<template>
    <div class="flow-root">
        <ul role="list" class="-my-5 divide-y divide-gray-200 dark:divide-gray-700">
            <li v-for="appt in appointments" :key="appt.id" class="py-4">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="text-sm font-bold text-gray-900 dark:text-gray-100">
                            {{ formatTime(appt.start_at) }}
                        </div>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-medium text-gray-900 dark:text-gray-100">
                            {{ appt.client?.name || 'Cita sin cliente' }}
                        </p>
                        <p v-if="showProfessional" class="truncate text-sm text-gray-500 dark:text-gray-400">
                            {{ appt.professional?.name || 'N/A' }}
                        </p>
                    </div>
                    <div class="flex items-center space-x-2">
                        <!-- Quick Actions -->
                        <div v-if="appt.status !== 'completed' && appt.status !== 'cancelled'" class="flex space-x-1 mr-2">
                            <button @click="updateStatus(appt.id, 'confirmed')" v-if="appt.status === 'scheduled'" class="p-1 text-green-600 hover:bg-green-50 rounded" title="Confirmar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </button>
                            <button @click="updateStatus(appt.id, 'completed')" v-if="appt.status === 'confirmed' || appt.status === 'scheduled'" class="p-1 text-blue-600 hover:bg-blue-50 rounded" title="Finalizar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </button>
                            <button @click="updateStatus(appt.id, 'cancelled')" class="p-1 text-red-600 hover:bg-red-50 rounded" title="Anular">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', getStatusBadgeClass(appt.status)]">
                            {{ appt.status }}
                        </span>
                    </div>
                </div>
            </li>
            <li v-if="appointments.length === 0" class="py-10 text-center text-gray-500 text-sm">
                No hay citas registradas hoy.
            </li>
        </ul>
    </div>
</template>
