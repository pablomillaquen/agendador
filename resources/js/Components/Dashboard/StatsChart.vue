<script setup>
import { Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale
} from 'chart.js';

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale);

const props = defineProps({
    data: {
        type: Array,
        required: true
    },
    label: {
        type: String,
        default: 'Citas'
    }
});

const chartData = {
    labels: props.data.map(d => d.month),
    datasets: [
        {
            label: props.label,
            backgroundColor: '#1e88e5',
            data: props.data.map(d => d.count),
            borderRadius: 6,
        }
    ]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            }
        }
    }
};
</script>

<template>
    <div class="h-64">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>
