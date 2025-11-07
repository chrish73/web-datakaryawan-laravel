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
                            href="{{ route('employees.index') }}">
                            <i class="bi bi-grid-fill me-1"></i> Data Karyawan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.chart') ? 'active' : '' }}"
                            href="{{ route('employees.chart') }}">
                            <i class="bi bi-bar-chart-line-fill me-1"></i> Data Band Posisi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart') }}">
                            <i class="bi bi-graph-up me-1"></i> Data Kelompok Usia
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('employees/band') ? 'active' : '' }}" href="/employees/band">
                            <i class="bi bi-layers-fill me-1"></i>Lama Band Posisi
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.today_birthdays') }}">
                            <i class="bi bi-gift-fill me-1"></i> Ulang Tahun Hari Ini
                            <span id="birthday-badge" class="badge text-bg-warning rounded-pill ms-1"
                                style="display: none;"></span>
                        </a>
                    </li>
                </ul>

                {{-- DARK MODE TOGGLE --}}
                <div class="d-flex align-items-center ms-lg-3">
                    <i class="bi bi-sun-fill me-2 text-warning" id="light-icon"></i>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeToggle" role="switch">
                    </div>
                    <i class="bi bi-moon-stars-fill ms-2 text-primary" id="dark-icon"></i>
                </div>
            </div>
        </div>
    </nav>

    {{-- === MAIN CONTENT === --}}
    <div class="container main-content">
        <h3 class="page-title"><i class="bi bi-graph-up me-2"></i> Distribusi Karyawan berdasarkan Kelompok Usia per
            Unit</h3>

        {{-- === FILTER KELOMPOK USIA === --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Kelompok Usia yang Ingin Ditampilkan:</label>
            <div id="ageGroupFilterContainer" class="d-flex flex-wrap gap-2"></div>
        </div>

        <div class="chart-card">
            <div class="row">
                <div class="col-md-12">
                    <canvas id="ageGroupChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- === MODAL DETAIL KARYAWAN === --}}
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Detail Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="employeeList">Memuat data...</div>
                </div>
            </div>
        </div>
    </div>

    {{-- === JS === --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // === DARK MODE ===
            (function() {
                const getStoredTheme = () => localStorage.getItem('theme');
                const setStoredTheme = theme => localStorage.setItem('theme', theme);
                const htmlElement = document.documentElement;
                const storedTheme = getStoredTheme();
                htmlElement.setAttribute('data-bs-theme', storedTheme || 'light');
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) {
                    toggle.checked = storedTheme === 'dark';
                    toggle.addEventListener('change', () => {
                        const newTheme = toggle.checked ? 'dark' : 'light';
                        htmlElement.setAttribute('data-bs-theme', newTheme);
                        setStoredTheme(newTheme);
                    });
                }
            })();

            // === NOTIFIKASI ULANG TAHUN ===
            fetch('{{ route('employees.birthdays_notification') }}')
                .then(res => res.json())
                .then(data => {
                    if (data.count > 0) {
                        const badge = document.getElementById('birthday-badge');
                        badge.textContent = data.count;
                        badge.style.display = 'inline-block';
                    }
                });

            // === CHART DATA ===
            fetch('{{ route('employees.age_group_chart_data') }}')
                .then(res => res.json())
                .then(data => {
                    const {
                        age_groups,
                        units,
                        data: aggregatedData
                    } = data;

                    const colors = [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)',
                    ];

                    const datasets = age_groups.map((group, index) => ({
                        label: group,
                        data: units.map(unit => aggregatedData[unit][group] || 0),
                        backgroundColor: colors[index % colors.length],
                        borderColor: colors[index % colors.length].replace('0.8', '1'),
                        borderWidth: 1
                    }));

                    // === TAMBAHKAN CHECKBOX UNTUK FILTER USIA ===
                    const container = document.getElementById('ageGroupFilterContainer');
                    age_groups.forEach((group, i) => {
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input me-1';
                        checkbox.id = `ageFilter-${i}`;
                        checkbox.value = group;
                        checkbox.checked = true;

                        const label = document.createElement('label');
                        label.className = 'form-check-label me-3';
                        label.setAttribute('for', `ageFilter-${i}`);
                        label.textContent = group;

                        container.appendChild(checkbox);
                        container.appendChild(label);
                    });

                    // === BUAT CHART ===
                    const ctx = document.getElementById('ageGroupChart').getContext('2d');
                    const chart = new Chart(ctx, {
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
                                    },
                                    ticks: {
                                        stepSize: 1,
                                        callback: v => Number.isInteger(v) ? v : null
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Distribusi Kelompok Usia per Unit'
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
                                    formatter: (value) => value > 0 ? value : ''
                                }
                            }
                        },
                        plugins: [ChartDataLabels]
                    });

                    // === FILTER EVENT ===
                    document.querySelectorAll('#ageGroupFilterContainer input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', () => {
                            const activeGroups = Array.from(document.querySelectorAll(
                                    '#ageGroupFilterContainer input[type="checkbox"]:checked'
                                    ))
                                .map(c => c.value);
                            chart.data.datasets = datasets.filter(ds => activeGroups.includes(ds
                                .label));
                            chart.update();
                        });
                    });

                    // === KLIK BAR CHART ===
                    document.getElementById('ageGroupChart').onclick = function(evt) {
                        const points = chart.getElementsAtEventForMode(evt, 'nearest', {
                            intersect: true
                        }, true);
                        if (!points.length) return;

                        const point = points[0];
                        const unit = chart.data.labels[point.index];
                        const group = chart.data.datasets[point.datasetIndex].label;

                        fetch(
                                `{{ url('employees/age-group-detail') }}/${encodeURIComponent(unit)}/${encodeURIComponent(group)}`)
                            .then(res => res.json())
                            .then(employees => {
                                const modalTitle = document.getElementById('employeeModalLabel');
                                const modalBody = document.getElementById('employeeList');

                                modalTitle.textContent = `Daftar Karyawan (${group}) - ${unit}`;

                                if (employees.length > 0) {
                                    modalBody.innerHTML = `
                                        <ul class="list-group">
                                            ${employees.map(emp => `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        ${emp.nama}
                                                        <span class="badge bg-primary rounded-pill">${emp.usia} th</span>
                                                    </li>`).join('')}
                                        </ul>`;
                                } else {
                                    modalBody.innerHTML =
                                        '<p class="text-muted">Tidak ada data karyawan untuk kategori ini.</p>';
                                }

                                new bootstrap.Modal(document.getElementById('employeeModal')).show();
                            })
                            .catch(err => console.error('Gagal memuat data detail:', err));
                    };
                });
        });
    </script>
</body>

</html>
