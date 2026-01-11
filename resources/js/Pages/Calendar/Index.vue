<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import esLocale from '@fullcalendar/core/locales/es';
import { ref, watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    events: Array,
    professionals: {
        type: Array,
        default: () => []
    },
    selectedProfessionalId: Number,
    auth: Object,
    clients: Array,
});

// Professional Selector Logic
const selectedProfessional = ref(props.selectedProfessionalId);

const changeProfessional = () => {
    router.get(route('calendar.index'), {
        professional_id: selectedProfessional.value
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['events', 'selectedProfessionalId'] 
    });
};

// Calendar Logic
const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: esLocale,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
    },
    events: props.events,
    editable: true, 
    selectable: true,
    select: handleSelect,
    eventDrop: handleEventDrop,
    eventResize: handleEventResize,
    eventClick: handleEventClick,
};

// Helper for date formatting
const formatDate = (date) => {
    if (!date) return null;
    const offset = date.getTimezoneOffset() * 60000;
    return (new Date(date - offset)).toISOString().slice(0, 19).replace('T', ' ');
};

function handleEventDrop(info) {
    if (info.event.extendedProps.type === 'blocked') {
        info.revert(); // Cannot drag blocked periods yet
        return;
    }
    updateAppointment(info.event, info.revert);
}

function handleEventResize(info) {
     if (info.event.extendedProps.type === 'blocked') {
        info.revert();
        return;
    }
    updateAppointment(info.event, info.revert);
}

function updateAppointment(event, revert) {
    axios.patch(route('appointments.update', event.id), {
        start_at: formatDate(event.start),
        end_at: formatDate(event.end),
        _method: 'PATCH'
    })
    .catch(error => {
        console.error('Update failed', error);
        revert();
    });
}

function handleEventClick(info) {
    if (info.event.extendedProps.type === 'blocked') {
        if (confirm('¿Eliminar bloqueo de horario?')) {
            axios.post(route('blocked-periods.destroy', info.event.id), {
                _method: 'DELETE'
            }).then(() => {
                info.event.remove();
            }).catch(error => {
                 alert('No autorizado o error.');
            });
        }
    } else {
        // Appointment details?
        // alert('Cita de: ' + info.event.title);
    }
}

// Block Time Modal Logic
const showBlockModal = ref(false);
const blockForm = useForm({
    user_id: props.selectedProfessionalId,
    start_at: '',
    end_at: '',
    reason: 'Vacaciones/Personal'
});

const openBlockModal = () => {
    blockForm.user_id = selectedProfessional.value;
    showBlockModal.value = true;
};

const closeBlockModal = () => {
    showBlockModal.value = false;
    blockForm.reset();
};

const saveBlock = () => {
    blockForm.post(route('blocked-periods.store'), {
        onSuccess: () => {
            closeBlockModal();
        }
    });
};

// Manual Appointment Modal Logic
const showAppointmentModal = ref(false);
const appointmentForm = useForm({
    client_id: '',
    professional_id: props.selectedProfessionalId,
    start_at: '',
    end_at: '',
    notes: '',
    status: 'scheduled',
});

function handleSelect(info) {
    appointmentForm.professional_id = selectedProfessional.value;
    appointmentForm.start_at = info.startStr.slice(0, 16);
    appointmentForm.end_at = info.endStr.slice(0, 16);
    showAppointmentModal.value = true;
}

const closeAppointmentModal = () => {
    showAppointmentModal.value = false;
    appointmentForm.reset();
};

const saveAppointment = () => {
    appointmentForm.post(route('admin.appointments.store'), {
        onSuccess: () => {
            closeAppointmentModal();
        }
    });
};


</script>

<template>
    <Head title="Calendario" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Calendario</h2>
                
                <div class="flex items-center gap-4">
                    <!-- Professional Selector -->
                    <div v-if="props.professionals.length > 0">
                        <select 
                            v-model="selectedProfessional"
                            @change="changeProfessional"
                            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300"
                        >
                            <option v-for="pro in props.professionals" :key="pro.id" :value="pro.id">
                                {{ pro.name }}
                            </option>
                        </select>
                    </div>

                    <PrimaryButton @click="openBlockModal">
                        Bloquear Horario
                    </PrimaryButton>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 dark:text-gray-100">
                        <FullCalendar :options="calendarOptions" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Block Time Modal -->
        <Modal :show="showBlockModal" @close="closeBlockModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Bloquear Horario
                </h2>

                <div class="mt-6 space-y-4">
                    <div>
                        <InputLabel for="start_at" value="Inicio" />
                        <TextInput
                            id="start_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                            v-model="blockForm.start_at"
                            required
                        />
                    </div>

                    <div>
                        <InputLabel for="end_at" value="Fin" />
                        <TextInput
                            id="end_at"
                            type="datetime-local"
                            class="mt-1 block w-full"
                            v-model="blockForm.end_at"
                            required
                        />
                    </div>

                    <div>
                        <InputLabel for="reason" value="Razón" />
                        <TextInput
                            id="reason"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="blockForm.reason"
                            placeholder="Vacaciones, Trámites, etc."
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeBlockModal"> Cancelar </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': blockForm.processing }"
                        :disabled="blockForm.processing"
                        @click="saveBlock"
                    >
                        Guardar Bloqueo
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Manual Appointment Modal -->
        <Modal :show="showAppointmentModal" @close="closeAppointmentModal">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                    Nueva Cita Manual
                </h2>

                <div class="mt-6 space-y-4">
                    <div>
                        <div class="flex justify-between items-center mb-1">
                            <InputLabel for="client_id" value="Seleccionar Cliente" />
                            <Link :href="route('admin.clients.create')" class="text-xs text-primary hover:underline">
                                + Crear Nuevo Cliente
                            </Link>
                        </div>
                        <select
                            id="client_id"
                            v-model="appointmentForm.client_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300"
                            required
                        >
                            <option value="">Seleccione un cliente...</option>
                            <option v-for="client in clients" :key="client.id" :value="client.id">
                                {{ client.name }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="appointmentForm.errors.client_id" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel for="appt_start" value="Inicio" />
                            <TextInput
                                id="appt_start"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                v-model="appointmentForm.start_at"
                                required
                            />
                            <InputError class="mt-2" :message="appointmentForm.errors.start_at" />
                        </div>
                        <div>
                            <InputLabel for="appt_end" value="Fin" />
                            <TextInput
                                id="appt_end"
                                type="datetime-local"
                                class="mt-1 block w-full"
                                v-model="appointmentForm.end_at"
                                required
                            />
                            <InputError class="mt-2" :message="appointmentForm.errors.end_at" />
                        </div>
                    </div>

                    <div>
                        <InputLabel for="appt_status" value="Estado" />
                        <select
                            id="appt_status"
                            v-model="appointmentForm.status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 dark:bg-gray-900 dark:border-gray-700 dark:text-gray-300"
                        >
                            <option value="scheduled">Programada</option>
                            <option value="confirmed">Confirmada</option>
                            <option value="completed">Completada</option>
                            <option value="cancelled">Cancelada</option>
                        </select>
                    </div>

                    <div>
                        <InputLabel for="appt_notes" value="Notas" />
                        <TextInput
                            id="appt_notes"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="appointmentForm.notes"
                            placeholder="Motivo de consulta, etc."
                        />
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <SecondaryButton @click="closeAppointmentModal"> Cancelar </SecondaryButton>

                    <PrimaryButton
                        class="ml-3"
                        :class="{ 'opacity-25': appointmentForm.processing }"
                        :disabled="appointmentForm.processing"
                        @click="saveAppointment"
                    >
                        Guardar Cita
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
