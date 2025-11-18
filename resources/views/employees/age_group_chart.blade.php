<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Kelompok Usia</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">

    <style>
        canvas {
            margin-top: 50px !important;
        }
    </style>
</head>

<body>
    {{-- === NAVBAR === --}}
    <nav class="navbar navbar-expand-lg navbar-dark fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('employees.index') }}">
                <i class="bi bi-person-workspace me-2"></i> HR Dashboard
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-4">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.index') ? 'active' : '' }}"
                            href="{{ route('employees.index') }}"><i class="bi bi-grid-fill me-1"></i> Data Karyawan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.chart') ? 'active' : '' }}"
                            href="{{ route('employees.chart') }}"><i class="bi bi-bar-chart-line-fill me-1"></i> Data
                            Band Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.band_position_monthly_chart') ? 'active' : '' }}"
                            href="{{ route('employees.band_position_monthly_chart') }}"><i
                                class="bi bi-calendar-check-fill me-1"></i> Promosi Band Posisi Bulanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart') }}"><i class="bi bi-graph-up me-1"></i> Data
                            Kelompok Usia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('employees/band') ? 'active' : '' }}"
                            href="/employees/band"><i class="bi bi-layers-fill me-1"></i>Lama Band Posisi</a>
                    </li>
                    {{-- START: Tambahan Link Events --}}
                    <li class="nav-item">
                        {{-- Ganti 'events.training' dengan nama route yang sebenarnya jika berbeda --}}
                        <a class="nav-link {{ Request::routeIs('employees.training_input') ? 'active' : '' }}"
                            href="{{ route('employees.training_input') }}">
                            <i class="bi bi-calendar-event-fill me-1"></i> Data Pelatihan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.training_summary_view') ? 'active' : '' }}"
                            href="{{ route('employees.training_summary_view') }}">
                            <i class="bi bi-journal-check me-1"></i> Rekap Data Pelatihan
                        </a>
                    </li>
                    {{-- END: Tambahan Link Events --}}
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.today_birthdays') }}">
                            <i class="bi bi-gift-fill me-1"></i> Ulang Tahun Hari Ini <span id="birthday-badge"
                                class="badge text-bg-warning rounded-pill ms-1" style="display: none;"></span>
                        </a>
                    </li>
                </ul>

                {{-- Toggle Dark Mode --}}
                <div class="d-flex align-items-center ms-lg-3">
                    <i class="bi bi-sun-fill me-2 text-warning" id="light-icon"></i>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeToggle" role="switch"
                            aria-label="Toggle Dark Mode">
                    </div>
                    <i class="bi bi-moon-stars-fill ms-2 text-primary" id="dark-icon"></i>
                </div>
            </div>
        </div>
    </nav>

    {{-- MAIN CONTENT --}}
    <div class="container main-content">

        <h3 class="page-title">
            <i class="bi bi-graph-up me-2"></i>
            Distribusi Karyawan berdasarkan Kelompok Usia per Unit
        </h3>

        {{-- FILTER USIA --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Kelompok Usia:</label>
            <div id="ageGroupFilterContainer" class="d-flex flex-wrap gap-3"></div>
        </div>

        <div class="chart-card">
            <canvas id="ageGroupChart"></canvas>
        </div>

    </div>

    {{-- MODAL DETAIL --}}
    <div class="modal fade" id="employeeModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 id="employeeModalLabel" class="modal-title"></h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="employeeList">Memuat data...</div>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        document.addEventListener("DOMContentLoaded", () => {

            // === DARK MODE ===
            (function() {
                const saved = localStorage.getItem("theme");
                const html = document.documentElement;
                html.setAttribute("data-bs-theme", saved ?? "light");

                const toggle = document.getElementById("darkModeToggle");
                toggle.checked = saved === "dark";

                toggle.addEventListener("change", () => {
                    const newTheme = toggle.checked ? "dark" : "light";
                    html.setAttribute("data-bs-theme", newTheme);
                    localStorage.setItem("theme", newTheme);
                });
            })();

            // === FETCH DATA CHART ===
            fetch(`{{ route('employees.age_group_chart_data') }}`)
                .then(r => r.json())
                .then(json => {

                    const {
                        age_groups,
                        units,
                        data: aggregated
                    } = json;

                    const colors = [
                        "rgba(255,99,132,0.8)",
                        "rgba(54,162,235,0.8)",
                        "rgba(255,206,86,0.8)",
                        "rgba(75,192,192,0.8)",
                        "rgba(153,102,255,0.8)",
                        "rgba(255,159,64,0.8)",
                        "rgba(123,45,34,0.8)",
                    ];

                    const datasets = age_groups.map((g, i) => ({
                        label: g,
                        data: units.map(u => aggregated[u][g] ?? 0),
                        backgroundColor: colors[i % colors.length],
                    }));

                    // LABEL FILTER
                    const filter = document.getElementById("ageGroupFilterContainer");
                    age_groups.forEach((g, i) => {
                        filter.innerHTML += `
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="ag-${i}"
                                       value="${g}" checked>
                                <label for="ag-${i}" class="form-check-label">${g}</label>
                            </div>`;
                    });

                    const ctx = document.getElementById("ageGroupChart").getContext("2d");

                    // === PLUGIN TOTAL PER UNIT ===
                    const totalPlugin = {
                        id: "totalPlugin",
                        afterDatasetsDraw(chart) {
                            const {
                                ctx
                            } = chart;
                            ctx.save();
                            ctx.font = "bold 16px Poppins";
                            ctx.fillStyle = "#ff0000"; // MERAH sesuai permintaan
                            ctx.textAlign = "center";

                            chart.data.labels.forEach((unit, i) => {
                                let total = 0;
                                chart.data.datasets.forEach(ds => {
                                    total += ds.data[i] ?? 0;
                                });

                                const meta = chart.getDatasetMeta(chart.data.datasets.length - 1);
                                const bar = meta.data[i];

                                ctx.fillText(total, bar.x, bar.y - 25);
                            });

                            ctx.restore();
                        }
                    };

                    // === BUILD CHART ===
                    const chart = new Chart(ctx, {
                        type: "bar",
                        data: {
                            labels: units,
                            datasets
                        },
                        plugins: [ChartDataLabels, totalPlugin],
                        options: {
                            responsive: true,
                            layout: {
                                padding: {
                                    top: 50
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true
                                }
                            },
                            plugins: {
                                datalabels: {
                                    color: "#fff",
                                    font: {
                                        weight: "bold"
                                    },
                                    formatter: v => v > 0 ? v : ""
                                },
                                legend: {
                                    position: "bottom"
                                },
                                title: {
                                    display: true,
                                    text: "Distribusi Kelompok Usia per Unit"
                                }
                            }
                        }
                    });

                    // === FILTER CHECKBOX ===
                    document.querySelectorAll("#ageGroupFilterContainer input").forEach(cb => {
                        cb.addEventListener("change", () => {
                            const active = Array
                                .from(document.querySelectorAll(
                                    "#ageGroupFilterContainer input:checked"))
                                .map(el => el.value);

                            chart.data.datasets = datasets.filter(ds => active.includes(ds
                                .label));
                            chart.update();
                        });
                    });
                });
        });
    </script>

</body>

</html>
