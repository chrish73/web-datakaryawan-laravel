<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Band Posisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">
</head>

<body>
    {{-- NAVBAR --}}
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
                            href="{{ route('employees.band_position_monthly_chart') }}"><i class="bi bi-calendar-check-fill me-1"></i> Promosi Band Posisi Bulanan</a>
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
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.today_birthdays') }}">
                            <i class="bi bi-gift-fill me-1"></i> Ulang Tahun Hari Ini <span id="birthday-badge"
                                class="badge text-bg-warning rounded-pill ms-1" style="display: none;"></span>
                        </a>
                    </li>
                </ul>

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
        <h3 class="page-title mb-4"><i class="bi bi-bar-chart-line-fill me-2"></i> Jumlah Karyawan berdasarkan Unit dan
            Band
            Posisi</h3>

        {{-- Filter Band --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Pilih Band Posisi yang Ingin Ditampilkan:</label>
            <div id="bandFilterContainer" class="d-flex flex-wrap gap-3"></div>
        </div>

        <div class="row align-items-stretch">
            {{-- CHART --}}
            <div class="col-md-9 d-flex">
                <div class="chart-card flex-fill d-flex align-items-stretch">
                    <canvas id="bandPosisiChart"></canvas>
                </div>
            </div>

            {{-- LIST UNIT --}}
            <div class="col-md-3 d-flex">
                <div class="card chart-card flex-fill">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-lines-fill me-1"></i> Detail Semua Karyawan per Unit
                    </div>
                    <div class="card-body list-group list-group-flush" id="unitButtonContainer">
                        <p class="text-muted text-center my-3">Memuat daftar Unit...</p>
                    </div>
                </div>
            </div>
        </div>


    </div>

    {{-- MODAL DETAIL KARYAWAN --}}
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

    {{-- SCRIPT --}}
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
                toggle.checked = storedTheme === 'dark';
                toggle.addEventListener('change', () => {
                    const newTheme = toggle.checked ? 'dark' : 'light';
                    htmlElement.setAttribute('data-bs-theme', newTheme);
                    setStoredTheme(newTheme);
                });
            })();

            // === DETAIL KARYAWAN PER UNIT ===
            function showEmployeeDetailsByUnit(unit) {
                const modalTitle = document.getElementById('employeeModalLabel');
                const modalBody = document.getElementById('employeeList');
                modalTitle.textContent = `Memuat Daftar Karyawan di Unit: ${unit}...`;
                modalBody.innerHTML =
                    '<div class="text-center"><div class="spinner-border text-primary" role="status"></div></div>';
                new bootstrap.Modal(document.getElementById('employeeModal')).show();

                fetch(`/employees/unit-detail/${encodeURIComponent(unit)}`)
                    .then(res => res.json())
                    .then(employees => {
                        modalTitle.textContent =
                            `Daftar Karyawan di Unit: ${unit} (Total: ${employees.length} orang)`;
                        if (employees.length > 0) {
                            modalBody.innerHTML = `
                                <ul class="list-group">
                                    ${employees.map(emp => `
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <strong>${emp.nama}</strong><br>
                                                        <small class="text-muted">${emp.nama_posisi || 'Posisi Tidak Ada'}</small>
                                                    </div>
                                                    <div class="d-flex flex-column align-items-end">
                                                        <span class="badge bg-info text-dark mb-1">Band ${emp.band_posisi || 'N/A'}</span>
                                                        <span class="badge ${emp.status_eligibility === 'Eligible' ? 'bg-success' :
                                                            emp.status_eligibility === 'Not Eligible' ? 'bg-danger' : 'bg-secondary'}">
                                                            ${emp.status_eligibility || 'N/A'}
                                                        </span>
                                                    </div>
                                                </li>`).join('')}
                                </ul>`;
                        } else {
                            modalBody.innerHTML =
                                '<p class="text-muted">Tidak ada data karyawan untuk Unit ini.</p>';
                        }
                    })
                    .catch(() => {
                        modalBody.innerHTML = '<p class="text-danger">Gagal memuat data karyawan.</p>';
                    });
            }

            // === FETCH DATA CHART & UNIT ===
            Promise.all([
                    fetch('{{ route('employees.chart_data') }}').then(res => res.json()),
                    fetch('{{ route('employees.all_unit_counts') }}').then(res => res.json())
                ])
                .then(([chartData, allUnitCounts]) => {
                    const bands = chartData.bands;
                    const units = chartData.units;
                    const aggregatedData = chartData.data;
                    const unitButtonContainer = document.getElementById('unitButtonContainer');
                    const bandFilterContainer = document.getElementById('bandFilterContainer');

                    const colors = [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)',
                    ];

                    const datasets = bands.map((band, index) => ({
                        label: `Band Posisi ${band}`,
                        data: units.map(unit => aggregatedData[unit][band]),
                        backgroundColor: colors[index % colors.length],
                        borderColor: colors[index % colors.length].replace('0.8', '1'),
                        borderWidth: 1
                    }));

                    const ctx = document.getElementById('bandPosisiChart').getContext('2d');
                    const bandPositionChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: units,
                            datasets: datasets
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
                                        callback: v => Number.isInteger(v) ? v : ''
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Distribusi Band Posisi per Unit (Eligible)'
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

                    // === KLIK BAR CHART untuk Detail Karyawan (BAND SPESIFIK) - LOGIKA ASLI DIPERTAHANKAN ===
                    document.getElementById('bandPosisiChart').onclick = function(evt) {
                        const points = bandPositionChart.getElementsAtEventForMode(evt, 'nearest', {
                            intersect: true
                        }, true);
                        if (!points.length) return;

                        const point = points[0];
                        const unit = bandPositionChart.data.labels[point.index];
                        const fullLabel = bandPositionChart.data.datasets[point.datasetIndex].label;

                        // Ekstrak hanya Band Posisi (misalnya 'I' dari 'Band Posisi I')
                        const band = fullLabel.replace('Band Posisi ', '').trim();

                        // Panggil API endpoint untuk detail Unit dan Band
                        fetch(
                                `/employees/band-position-detail/${encodeURIComponent(unit)}/${encodeURIComponent(band)}`
                            )
                            .then(res => res.json())
                            .then(employees => {
                                const modalTitle = document.getElementById('employeeModalLabel');
                                const modalBody = document.getElementById('employeeList');

                                modalTitle.textContent =
                                    `Daftar Karyawan Band Posisi (${band}) - ${unit}`;

                                if (employees.length > 0) {
                                    // Logika asli yang hanya menampilkan nama, posisi, dan band (tanpa status_eligibility)
                                    modalBody.innerHTML = `
                                        <ul class="list-group justify-content-between">
                                            ${employees.map(emp => `
                                                            <li class="list-group-item d-flex justify-content-between">
                                                                <div class="d-flex flex-column">
                                                                    <span class="fw-semibold">${emp.nama}</span>
                                                                    <small class="text-muted">${emp.nama_posisi}</small>
                                                                </div>
                                                                 <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge bg-primary">Band ${emp.band_posisi || 'N/A'}</span>
                                                                    <span class="badge ${
                                                                        emp.status_eligibility === 'Eligible'
                                                                            ? 'bg-success'
                                                                            : emp.status_eligibility === 'Not Eligible'
                                                                            ? 'bg-danger'
                                                                            : 'bg-secondary'
                                                                    }">
                                                                        ${emp.status_eligibility || 'N/A'}
                                                                    </span>
                                                                </div>
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

                    // === FILTER BAND (CHECKBOX) ===
                    bands.forEach((band, index) => {
                        const checkbox = document.createElement('input');
                        checkbox.type = 'checkbox';
                        checkbox.className = 'form-check-input me-1';
                        checkbox.id = `bandFilter-${index}`;
                        checkbox.value = band;
                        checkbox.checked = true;

                        const label = document.createElement('label');
                        label.className = 'form-check-label me-3';
                        label.setAttribute('for', `bandFilter-${index}`);
                        label.textContent = `Band ${band}`;

                        bandFilterContainer.appendChild(checkbox);
                        bandFilterContainer.appendChild(label);
                    });

                    document.querySelectorAll('#bandFilterContainer input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', () => {
                            const activeBands = Array.from(document.querySelectorAll(
                                    '#bandFilterContainer input[type="checkbox"]:checked'))
                                .map(c => c.value);
                            bandPositionChart.data.datasets = datasets.filter(ds =>
                                activeBands.includes(ds.label.replace('Band Posisi ', ''))
                            );
                            bandPositionChart.update();
                        });
                    });

                    // === TOMBOL UNIT ===
                    unitButtonContainer.innerHTML = '';
                    Object.keys(allUnitCounts).forEach(unit => {
                        const totalEmployees = allUnitCounts[unit] || 0;
                        const button = document.createElement('button');
                        button.className =
                            'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                        button.setAttribute('data-unit', unit);
                        button.innerHTML =
                            `${unit}<span class="badge bg-secondary rounded-pill">${totalEmployees}</span>`;
                        button.addEventListener('click', () => showEmployeeDetailsByUnit(unit));
                        unitButtonContainer.appendChild(button);
                    });

                })
                .catch(error => {
                    console.error(error);
                    document.getElementById('unitButtonContainer').innerHTML =
                        '<p class="text-danger text-center my-3">Gagal memuat data.</p>';
                });
        });
    </script>
</body>

</html>
