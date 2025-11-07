<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Durasi Band Posisi</title>
    {{-- Memuat Bootstrap, Bootstrap Icons, dan Font Poppins --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS tambahan --}}
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">
    <style>
        #durationFilterContainer label {
            cursor: pointer;
            font-weight: 500;
        }

        #durationFilterContainer input {
            transform: scale(1.2);
            margin-right: 5px;
        }
    </style>
</head>

<body>
    {{-- Navbar --}}
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
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart') }}"><i class="bi bi-graph-up me-1"></i> Data
                            Kelompok Usia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('employees/band') ? 'active' : '' }}"
                            href="/employees/band"><i class="bi bi-layers-fill me-1"></i>Lama Band Posisi</a>
                    </li>
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

    {{-- Konten Utama --}}
    <div class="container main-content">

        <h3 class="page-title"><i class="bi bi-hourglass-split me-2"></i> Distribusi Karyawan berdasarkan Durasi Band
            Posisi per Unit</h3>

        {{-- Filter Checkbox --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Durasi Band yang Ingin Ditampilkan:</label>
            <div id="durationFilterContainer" class="d-flex flex-wrap gap-3"></div>
        </div>

        {{-- Card Chart --}}
        <div class="chart-card">
            <div class="row">
                <div class="col-md-12">
                    <canvas id="bandDurationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Karyawan --}}
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Daftar Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="employeeList"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Script --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        // === DARK MODE ===
        (function() {
            const getStoredTheme = () => localStorage.getItem('theme');
            const setStoredTheme = theme => localStorage.setItem('theme', theme);
            const htmlElement = document.documentElement;

            const storedTheme = getStoredTheme() || 'light';
            htmlElement.setAttribute('data-bs-theme', storedTheme);
            setStoredTheme(storedTheme);

            const toggle = document.getElementById('darkModeToggle');
            toggle.checked = storedTheme === 'dark';
            toggle.addEventListener('change', () => {
                const newTheme = toggle.checked ? 'dark' : 'light';
                htmlElement.setAttribute('data-bs-theme', newTheme);
                setStoredTheme(newTheme);
            });
        })();

        // === MAIN LOGIC ===
        document.addEventListener('DOMContentLoaded', function() {

            // Notifikasi ulang tahun
            fetch('{{ route('employees.birthdays_notification') }}')
                .then(res => res.json())
                .then(data => {
                    const badge = document.getElementById('birthday-badge');
                    if (data.count > 0) {
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    }
                });

            // Ambil data chart
            fetch('{{ route('employees.band_duration_data') }}')
                .then(res => res.json())
                .then(data => {
                    const durationGroups = data.duration_groups;
                    const units = data.units;
                    const aggregatedData = data.data;

                    const colors = [
                        'rgba(0, 150, 136, 0.8)',
                        'rgba(255, 152, 0, 0.8)',
                        'rgba(192, 57, 43, 0.8)',
                        'rgba(300, 255, 20, 0.8)',
                    ];

                    const datasets = durationGroups.map((group, i) => ({
                        label: group,
                        data: units.map(unit => aggregatedData[unit][group]),
                        backgroundColor: colors[i % colors.length],
                        borderColor: colors[i % colors.length].replace('0.8', '1'),
                        borderWidth: 1
                    }));

                    const ctx = document.getElementById('bandDurationChart').getContext('2d');
                    const bandDurationChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: units,
                            datasets
                        },
                        options: {
                            responsive: true,
                            scales: {
                                x: {
                                    stacked: true,
                                    title: {
                                        display: true,
                                        text: 'Unit'
                                    }
                                },
                                y: {
                                    stacked: true,
                                    beginAtZero: true,
                                    title: {
                                        display: true,
                                        text: 'Jumlah Karyawan'
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Distribusi Durasi Band Posisi per Unit'
                                },
                                legend: {
                                    position: 'bottom'
                                },
                                datalabels: {
                                    color: '#fff',
                                    anchor: 'center',
                                    align: 'center',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    },
                                    formatter: v => v > 0 ? v : ''
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });

                    // === Checkbox Filter ===
                    const container = document.getElementById('durationFilterContainer');
                    durationGroups.forEach((group, index) => {
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input';
                        checkbox.id = `durationFilter-${index}`;
                        checkbox.value = group;
                        checkbox.checked = true;

                        const label = document.createElement('label');
                        label.className = 'form-check-label';
                        label.setAttribute('for', `durationFilter-${index}`);
                        label.textContent = group;

                        container.appendChild(checkbox);
                        container.appendChild(label);
                    });

                    // Event perubahan checkbox
                    container.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', () => {
                            const active = Array.from(container.querySelectorAll(
                                'input[type="checkbox"]:checked')).map(c => c.value);
                            bandDurationChart.data.datasets = datasets.filter(ds => active
                                .includes(ds.label));
                            bandDurationChart.update();
                        });
                    });

                    // Klik bar untuk detail
                    document.getElementById('bandDurationChart').onclick = evt => {
                        const points = bandDurationChart.getElementsAtEventForMode(evt, 'nearest', {
                            intersect: true
                        }, true);
                        if (!points.length) return;

                        const point = points[0];
                        const unit = bandDurationChart.data.labels[point.index];
                        const group = bandDurationChart.data.datasets[point.datasetIndex].label;

                        fetch(
                                `/employees/band-duration-detail/${encodeURIComponent(unit)}/${encodeURIComponent(group)}`)
                            .then(res => res.json())
                            .then(employees => {
                                const modalTitle = document.getElementById('employeeModalLabel');
                                const modalBody = document.getElementById('employeeList');
                                modalTitle.textContent = `Daftar Karyawan Durasi (${group}) - ${unit}`;

                                modalBody.innerHTML = employees.length ?
                                    `<ul class="list-group">${employees.map(emp => `
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                ${emp.nama}
                                                <span class="badge bg-primary rounded-pill">${emp.lama_band_posisi} bulan</span>
                                            </li>`).join('')}</ul>` :
                                    '<p class="text-muted">Tidak ada data karyawan untuk kategori ini.</p>';

                                new bootstrap.Modal(document.getElementById('employeeModal')).show();
                            });
                    };
                });
        });
    </script>
</body>

</html>
