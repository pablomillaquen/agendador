<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';

const props = defineProps({
    events: Array,
});

const calendarOptions = {
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
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
};

function handleEventDrop(info) {
    const event = info.event;
    updateAppointment(event);
}

function handleEventResize(info) {
    const event = info.event;
    updateAppointment(event);
}

function updateAppointment(event) {
    // Format dates to MySQL format YYYY-MM-DD HH:mm:ss
    // FullCalendar dates are Date objects.
    // We need to adjust for timezone or send ISO and handle in backend? 
    // Laravel expects Y-m-d H:i:s usually.
    
    // Simple helper:
    const formatDate = (date) => {
        if (!date) return null;
        // Adjust for local time to avoid UTC shift issues if not handled carefully
        // Or better, use toISOString() and ensuring backend handles it.
        // Let's use a simple manual format to local string
        const offset = date.getTimezoneOffset() * 60000;
        const localISOTime = (new Date(date - offset)).toISOString().slice(0, 19).replace('T', ' ');
        return localISOTime;
    };

    axios.patch(route('appointments.update', event.id), {
        start_at: formatDate(event.start),
        end_at: formatDate(event.end),
        _method: 'PATCH' // sometimes needed if using post but here we use patch
    })
    .then(response => {
        // notification success
        console.log('Update success');
    })
    .catch(error => {
        console.error('Update failed', error);
        info.revert(); // Revert visual change
    });
}

</script>

<template>
    <Head title="Calendario" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Calendario</h2>
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
    </AuthenticatedLayout>
</template>
