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
            // Refresh events -> Inertia automatic reload if preserved state isn't blocking deep props
            // Actually router.reload() might be needed or just let Inertia handle it.
            // But we used a form post which reloads the page content by default.
            // So events should update.
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

    </AuthenticatedLayout>
</template>
