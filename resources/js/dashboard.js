import {
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PieController,
    PointElement,
    Tooltip,
} from 'chart.js';

/*
|--------------------------------------------------------------------------
| Graphical Dashboard (Chart.js)
|--------------------------------------------------------------------------
| Setiap <canvas data-chart="..."> berisi konfigurasi JSON yang disiapkan
| oleh komponen Blade <x-dashboard.chart-card>. Hanya modul Chart.js yang
| dipakai yang diregistrasi (tree-shaking) agar bundle tetap ringan.
*/

Chart.register(
    ArcElement,
    BarController,
    BarElement,
    CategoryScale,
    DoughnutController,
    Filler,
    Legend,
    LinearScale,
    LineController,
    LineElement,
    PieController,
    PointElement,
    Tooltip,
);

const PALETTE = {
    blue: '#3B82F6',
    emerald: '#10B981',
    amber: '#F59E0B',
    rose: '#EF4444',
    sky: '#38BDF8',
    violet: '#8B5CF6',
    teal: '#14B8A6',
    pink: '#EC4899',
    orange: '#F97316',
    indigo: '#6366F1',
    lime: '#84CC16',
    slate: '#64748B',
};

const SERIES_ORDER = ['blue', 'emerald', 'amber', 'rose', 'sky', 'violet', 'teal', 'pink', 'orange', 'indigo', 'lime', 'slate'];

const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

const numberFormatter = new Intl.NumberFormat('id-ID');
const rupiahFormatter = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 });

Chart.defaults.color = '#94A3B8';
Chart.defaults.borderColor = 'rgba(255, 255, 255, 0.06)';
Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.font.size = 11;

function resolveColor(color, index = 0) {
    return PALETTE[color] ?? color ?? PALETTE[SERIES_ORDER[index % SERIES_ORDER.length]];
}

function withAlpha(hex, alpha) {
    const value = hex.replace('#', '');
    const red = parseInt(value.substring(0, 2), 16);
    const green = parseInt(value.substring(2, 4), 16);
    const blue = parseInt(value.substring(4, 6), 16);

    return `rgba(${red}, ${green}, ${blue}, ${alpha})`;
}

function compactRupiah(value) {
    const absolute = Math.abs(value);

    if (absolute >= 1e9) {
        return `Rp ${numberFormatter.format(+(value / 1e9).toFixed(1))} M`;
    }

    if (absolute >= 1e6) {
        return `Rp ${numberFormatter.format(+(value / 1e6).toFixed(1))} jt`;
    }

    if (absolute >= 1e3) {
        return `Rp ${numberFormatter.format(+(value / 1e3).toFixed(1))} rb`;
    }

    return `Rp ${numberFormatter.format(value)}`;
}

function formatValue(value, isCurrency) {
    return isCurrency ? rupiahFormatter.format(value) : numberFormatter.format(value);
}

function areaGradient(color) {
    return (context) => {
        const { ctx, chartArea } = context.chart;

        if (!chartArea) {
            return withAlpha(color, 0.2);
        }

        const gradient = ctx.createLinearGradient(0, chartArea.top, 0, chartArea.bottom);
        gradient.addColorStop(0, withAlpha(color, 0.35));
        gradient.addColorStop(1, withAlpha(color, 0.02));

        return gradient;
    };
}

function buildDataset(dataset, index, config) {
    const isArc = ['doughnut', 'pie'].includes(config.type);

    if (isArc) {
        const colors = (dataset.colors ?? SERIES_ORDER).map((color, colorIndex) => resolveColor(color, colorIndex));

        return {
            label: dataset.label,
            data: dataset.data,
            backgroundColor: dataset.data.map((_, dataIndex) => colors[dataIndex % colors.length]),
            hoverBackgroundColor: dataset.data.map((_, dataIndex) => colors[dataIndex % colors.length]),
            borderColor: '#111827',
            borderWidth: 2,
            hoverOffset: 6,
        };
    }

    const color = resolveColor(dataset.color, index);
    const type = dataset.type ?? config.type;

    if (type === 'line') {
        const isArea = dataset.fill ?? config.options?.area ?? false;

        return {
            type: 'line',
            label: dataset.label,
            data: dataset.data,
            borderColor: color,
            backgroundColor: isArea ? areaGradient(color) : color,
            fill: isArea ? 'origin' : false,
            tension: 0.35,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
            pointBackgroundColor: color,
            pointBorderColor: '#111827',
            yAxisID: dataset.axis ?? 'y',
            order: 0,
        };
    }

    return {
        type: 'bar',
        label: dataset.label,
        data: dataset.data,
        backgroundColor: withAlpha(color, 0.75),
        hoverBackgroundColor: color,
        borderRadius: 4,
        maxBarThickness: 38,
        yAxisID: config.options?.horizontal ? undefined : (dataset.axis ?? 'y'),
        order: 1,
    };
}

function buildScales(config, datasets) {
    const options = config.options ?? {};
    const isCurrency = Boolean(options.currency);
    const usesSecondaryAxis = datasets.some((dataset) => dataset.axis === 'y1');
    const valueTicks = {
        precision: isCurrency ? undefined : 0,
        callback: (value) => (isCurrency ? compactRupiah(value) : numberFormatter.format(value)),
    };
    const gridColor = 'rgba(255, 255, 255, 0.05)';

    if (options.horizontal) {
        return {
            x: { beginAtZero: true, stacked: Boolean(options.stacked), ticks: valueTicks, grid: { color: gridColor } },
            y: { stacked: Boolean(options.stacked), grid: { display: false }, ticks: { autoSkip: false } },
        };
    }

    const scales = {
        x: { stacked: Boolean(options.stacked), grid: { display: false } },
        y: { beginAtZero: true, stacked: Boolean(options.stacked), ticks: valueTicks, grid: { color: gridColor } },
    };

    if (usesSecondaryAxis) {
        scales.y1 = {
            position: 'right',
            beginAtZero: true,
            grid: { drawOnChartArea: false },
            ticks: {
                callback: (value) => (options.secondaryCurrency ? compactRupiah(value) : numberFormatter.format(value)),
                precision: options.secondaryCurrency ? undefined : 0,
            },
        };
    }

    return scales;
}

function buildChart(canvas) {
    const config = JSON.parse(canvas.dataset.chart);
    const options = config.options ?? {};
    const isArc = ['doughnut', 'pie'].includes(config.type);
    const isCurrency = Boolean(options.currency);
    const links = config.links ?? [];
    const datasetCurrency = (dataset) => (dataset.axis === 'y1' ? Boolean(options.secondaryCurrency) : isCurrency);

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: prefersReducedMotion ? false : { duration: 650, easing: 'easeOutQuart' },
        interaction: isArc ? undefined : { mode: 'index', intersect: false },
        indexAxis: options.horizontal ? 'y' : 'x',
        plugins: {
            legend: {
                display: options.legend ?? (isArc || config.datasets.length > 1),
                position: isArc ? 'bottom' : 'top',
                align: isArc ? 'center' : 'end',
                labels: { boxWidth: 10, boxHeight: 10, usePointStyle: true, pointStyle: 'circle', padding: 14 },
            },
            tooltip: {
                backgroundColor: '#0F172A',
                borderColor: 'rgba(59, 130, 246, 0.35)',
                borderWidth: 1,
                titleColor: '#F8FAFC',
                bodyColor: '#CBD5E1',
                padding: 10,
                cornerRadius: 8,
                usePointStyle: true,
                callbacks: {
                    label: (context) => {
                        const rawValue = context.raw ?? 0;
                        const sourceDataset = config.datasets[context.datasetIndex] ?? {};
                        const formatted = formatValue(rawValue, datasetCurrency(sourceDataset));

                        if (isArc) {
                            const total = context.dataset.data.reduce((sum, value) => sum + Number(value), 0);
                            const percentage = total > 0 ? ((rawValue / total) * 100).toFixed(1) : 0;

                            return ` ${context.label}: ${formatted} (${percentage}%)`;
                        }

                        return ` ${context.dataset.label}: ${formatted}`;
                    },
                },
            },
        },
        onClick: (event, elements) => {
            if (!elements.length || !links.length) {
                return;
            }

            const target = links[elements[0].index];

            if (target) {
                window.location.href = target;
            }
        },
        onHover: (event, elements) => {
            if (links.length) {
                event.native.target.style.cursor = elements.length && links[elements[0].index] ? 'pointer' : 'default';
            }
        },
    };

    if (isArc) {
        chartOptions.cutout = config.type === 'doughnut' ? '68%' : 0;
    } else {
        chartOptions.scales = buildScales(config, config.datasets);
    }

    return new Chart(canvas, {
        type: config.type,
        data: {
            labels: config.labels,
            datasets: config.datasets.map((dataset, index) => buildDataset(dataset, index, config)),
        },
        options: chartOptions,
    });
}

function initialiseCharts() {
    const canvases = document.querySelectorAll('canvas[data-chart]');

    if (!canvases.length) {
        return;
    }

    if (!('IntersectionObserver' in window)) {
        canvases.forEach(buildChart);

        return;
    }

    // Render grafik hanya ketika mendekati viewport agar halaman awal tetap ringan.
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                observer.unobserve(entry.target);
                buildChart(entry.target);
            }
        });
    }, { rootMargin: '120px 0px' });

    canvases.forEach((canvas) => observer.observe(canvas));
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initialiseCharts);
} else {
    initialiseCharts();
}

