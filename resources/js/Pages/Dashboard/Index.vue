<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import KpiCard from '@/Components/KpiCard.vue';
import CompactAppointmentList from '@/Components/Dashboard/CompactAppointmentList.vue';
import StatsChart from '@/Components/Dashboard/StatsChart.vue';

const props = defineProps({
    stats: Object,
    role: String,
});

const isAdmin = ['admin', 'coordinator'].includes(props.role);

const getOccupancyColor = (val) => {
    if (val > 80) return 'red';
    if (val > 50) return 'blue';
    return 'green';
};
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                    Dashboard {{ isAdmin ? 'Operativo' : 'Personal' }}
                </h2>
                <div class="flex space-x-2">
                    <Link :href="route('calendar.index')" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 shadow-sm transition">
                        Ir a Agenda
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-8">
                
                <!-- ADMIN / COORDINATOR DASHBOARD -->
                <template v-if="isAdmin">
                    <!-- Top KPIs -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                        <KpiCard title="Citas Hoy" :value="stats.kpis.total_today" icon="calendar" color="blue" />
                        <KpiCard title="Confirmadas" :value="stats.kpis.confirmed_today" icon="check" color="green" />
                        <KpiCard title="Canceladas" :value="stats.kpis.cancelled_today" color="red">
                            <template #icon>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </template>
                        </KpiCard>
                        <KpiCard title="No Show" :value="stats.kpis.no_show_today" color="gray">
                            <template #icon>
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </template>
                        </KpiCard>
                        <KpiCard title="Ocupación" :value="stats.kpis.occupancy + '%'" :color="getOccupancyColor(stats.kpis.occupancy)" />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <!-- Chart -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Tendencia de Citas</h3>
                            <StatsChart :data="stats.chart_data" label="Citas por Mes" />
                        </div>

                        <!-- Today's Appointments -->
                        <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Próximas Citas Hoy</h3>
                            <CompactAppointmentList :appointments="stats.recent_appointments" />
                        </div>
                    </div>

                    <!-- Professionals List -->
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg border border-gray-100 dark:border-gray-700">
                        <div class="p-6 text-gray-900 dark:text-gray-100">
                            <h3 class="text-lg font-medium mb-4">Rendimiento de Profesionales (Hoy)</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                    <thead class="bg-gray-50 dark:bg-gray-900/50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nombre</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Hoy</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Semana</th>
                                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Ocupación</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                                        <tr v-for="pro in stats.professionals" :key="pro.id">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">{{ pro.name }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">{{ pro.appointments_today }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">{{ pro.appointments_week }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-center">
                                                <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700 max-w-[100px] mx-auto">
                                                    <div class="bg-blue-600 h-2.5 rounded-full" :style="{ width: pro.occupancy + '%' }"></div>
                                                </div>
                                                <span class="text-[10px] text-gray-500 mt-1 block">{{ pro.occupancy }}%</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                                <span :class="['px-2 py-1 text-xs rounded-full', pro.occupancy < 20 ? 'bg-gray-100 text-gray-600' : 'bg-green-100 text-green-800']">
                                                    {{ pro.occupancy < 20 ? 'Baja' : (pro.occupancy > 80 ? 'Alta' : 'Media') }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- PROFESSIONAL DASHBOARD -->
                <template v-else>
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <KpiCard title="Citas Hoy" :value="stats.kpis.today_count" icon="calendar" color="blue" />
                        <KpiCard title="Esta Semana" :value="stats.kpis.week_count" icon="users" color="indigo" />
                        <KpiCard title="Completadas (Mes)" :value="stats.kpis.month_completed" icon="check" color="green" />
                        <KpiCard title="Ocupación Hoy" :value="stats.kpis.occupancy + '%'" :color="getOccupancyColor(stats.kpis.occupancy)" />
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                        <!-- Next Appointment -->
                        <div class="lg:col-span-1 bg-gradient-to-br from-primary to-blue-700 p-6 rounded-lg shadow-lg text-white">
                            <h3 class="text-lg font-medium opacity-90 mb-4">Próxima Cita</h3>
                            <div v-if="stats.next_appointment">
                                <p class="text-3xl font-bold">{{ stats.next_appointment.client?.name }}</p>
                                <p class="text-lg mt-1 opacity-90">{{ new Date(stats.next_appointment.start_at).toLocaleTimeString('es-CL', { hour: '2-digit', minute: '2-digit'}) }} hrs</p>
                                <div class="mt-6">
                                    <Link :href="route('calendar.index')" class="bg-white text-primary px-4 py-2 rounded-md text-sm font-bold hover:bg-gray-100 transition">
                                        Iniciar Atención
                                    </Link>
                                </div>
                            </div>
                            <div v-else class="py-10 text-center opacity-80">
                                No hay más citas programadas hoy.
                            </div>
                        </div>

                        <!-- Agenda Today -->
                        <div class="lg:col-span-2 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">Agenda de Hoy</h3>
                            <CompactAppointmentList :appointments="stats.agenda" :showProfessional="false" />
                        </div>
                    </div>

                    <!-- Recent Clients -->
                    <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm border border-gray-100 dark:border-gray-700">
                        <h3 class="text-lg font-medium mb-4">Pacientes Recientes</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                            <div v-for="client in stats.recent_clients" :key="client.id" class="p-4 bg-gray-50 dark:bg-gray-900/40 rounded-lg flex flex-col items-center text-center">
                                <div class="w-12 h-12 bg-gray-200 dark:bg-gray-700 rounded-full flex items-center justify-center mb-2">
                                    <span class="text-gray-600 dark:text-gray-300 font-bold">{{ client.name.charAt(0) }}</span>
                                </div>
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ client.name }}</p>
                                <Link :href="route('admin.clients.edit', client.id)" class="text-xs text-primary mt-1 hover:underline">Ver Historial</Link>
                            </div>
                            <div v-if="stats.recent_clients.length === 0" class="col-span-full py-4 text-center text-gray-500">
                                Sin actividad reciente.
                            </div>
                        </div>
                    </div>
                </template>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
