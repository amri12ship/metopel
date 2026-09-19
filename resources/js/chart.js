import { Chart } from 'chart.js/auto';

window.renderAttendanceChart = (canvasId, labels, hadir, terlambat) => {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Hadir',
                    data: hadir,
                    backgroundColor: '#10b981',
                    borderRadius: 4,
                },
                {
                    label: 'Terlambat',
                    data: terlambat,
                    backgroundColor: '#ef4444',
                    borderRadius: 4,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom' },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                },
            },
        },
    });
};