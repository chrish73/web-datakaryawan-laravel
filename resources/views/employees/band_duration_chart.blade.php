<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Band Posisi dan Durasi</title>
    {{-- Memuat Bootstrap, Bootstrap Icons, dan Font Poppins --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- CSS tambahan --}}
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">
    <style>
        /* Gaya umum untuk container filter */
        #durationFilterContainer label,
        #bandFilterContainer label {
            cursor: pointer;
            font-weight: 500;
        }

        #durationFilterContainer input,
        #bandFilterContainer input {
            transform: scale(1.2);
            margin-right: 5px;
        }

        /* Gaya untuk menata checkbox dalam baris */
        .form-check-inline {
            margin-right: 1.5rem;
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

        <h3 class="page-title"><i class="bi bi-hourglass-split me-2"></i> Distribusi Karyawan berdasarkan Band Posisi
            (Difilter Durasi) per Unit</h3>

        {{-- Filter Checkbox Durasi Band --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Filter Durasi Band:</label>
            <div id="durationFilterContainer" class="d-flex flex-wrap gap-3"></div>
        </div>

        {{-- FILTER BARU: Band Posisi --}}
        <div class="mb-4">
            <label class="form-label fw-semibold">Filter Band Posisi:</label>
            <div id="bandFilterContainer" class="d-flex flex-wrap gap-3">
            </div>
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

            let bandDurationChart = null;
            let allRawData = []; // NEW: stores the detailed data from the server (Unit, Band, Duration, Count)
            let availableBands = []; // NEW: Bands available in the current fetched data
            const durationFilterContainer = document.getElementById('durationFilterContainer');
            const bandFilterContainer = document.getElementById('bandFilterContainer');
            const chartCtx = document.getElementById('bandDurationChart').getContext('2d');

            // Variabel global untuk menyimpan Band Posisi yang aktif saat ini (dari checkbox Band Posisi)
            let currentActiveBands = [];

            const colors = [
                'rgba(0, 150, 136, 0.8)', // I
                'rgba(255, 152, 0, 0.8)', // II
                'rgba(192, 57, 43, 0.8)', // III
                'rgba(300, 255, 20, 0.8)', // IV
                'rgba(60, 180, 75, 0.8)', // V
                'rgba(70, 240, 240, 0.8)', // VI
                'rgba(245, 130, 48, 0.8)', // N/A
            ];

            // Map untuk mendapatkan warna yang konsisten berdasarkan nama Band
            const bandColorMap = {};
            ['I', 'II', 'III', 'IV', 'V', 'VI'].forEach((band, index) => {
                bandColorMap[`Band ${band}`] = colors[index % colors.length];
            });

            // Fungsi baru untuk mengagregasi raw data (pivot)
            function aggregateData(rawData, units, activeDurations) {
                const aggregatedData = {};
                const bandsInChart = new Set();

                // 1. Initialize aggregatedData structure
                units.forEach(unit => {
                    aggregatedData[unit] = {};
                    availableBands.forEach(band => { // Initialize only for bands available in data
                        aggregatedData[unit][band] = 0;
                    });
                });

                // 2. Aggregate the raw data based on active duration filter
                rawData.forEach(item => {
                    const unit = item.UNIT;
                    const band = item.band_posisi;
                    const duration = item.calculated_duration_group;
                    const count = item.count;

                    // Apply Duration Filter: ONLY include counts where the duration group is active
                    if (activeDurations.includes(duration)) {
                        if (aggregatedData[unit] && aggregatedData[unit][band] !== undefined) {
                            aggregatedData[unit][band] += count;
                        }
                        bandsInChart.add(band);
                    }
                });

                // 3. Prepare datasets (Grouped by Band Posisi)
                const sortedBands = Array.from(bandsInChart).sort();

                const allBandDatasets = sortedBands.map((band) => {
                    const label = `Band ${band}`;
                    const color = bandColorMap[label] || colors[sortedBands.indexOf(band) % colors.length];

                    return {
                        label: label,
                        data: units.map(unit => aggregatedData[unit][band] || 0),
                        backgroundColor: color,
                        borderColor: color.replace('0.8', '1'),
                        borderWidth: 1
                    };
                });

                return allBandDatasets;
            }

            // Fungsi untuk membuat dan memperbarui chart (Sekarang menggunakan Band Posisi sebagai dataset)
            function updateChart(data) {
                const units = data.units;

                // 1. Save raw data and available bands from the server response
                if (data.raw_data) {
                    allRawData = data.raw_data;
                    availableBands = data.bands;
                }

                // 2. Tentukan filter durasi yang aktif (Client-Side filtering)
                const activeDurationGroups = Array.from(durationFilterContainer.querySelectorAll(
                    'input[type="checkbox"]:checked')).map(c => c.value);

                // 3. Agregasi dan buat datasets baru (datasets adalah Band Posisi)
                const filteredDatasets = aggregateData(allRawData, units, activeDurationGroups);

                if (bandDurationChart) {
                    // Perbarui chart yang sudah ada
                    bandDurationChart.data.labels = units;
                    bandDurationChart.data.datasets = filteredDatasets;
                    bandDurationChart.options.plugins.title.text = 'Distribusi Karyawan berdasarkan Band Posisi (Difilter Durasi) per Unit';
                    bandDurationChart.update();
                } else {
                    // Buat chart baru
                    bandDurationChart = new Chart(chartCtx, {
                        type: 'bar',
                        data: {
                            labels: units,
                            datasets: filteredDatasets
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
                                    text: 'Distribusi Karyawan berdasarkan Band Posisi (Difilter Durasi) per Unit' // Updated Title
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

                    // Siapkan event klik bar untuk modal detail (hanya sekali)
                    document.getElementById('bandDurationChart').onclick = evt => {
                        const points = bandDurationChart.getElementsAtEventForMode(evt, 'nearest', {
                            intersect: true
                        }, true);
                        if (!points.length) return;

                        // Cek apakah ada data di bar yang diklik
                        if (bandDurationChart.data.datasets[points[0].datasetIndex].data[points[0].index] ===
                            0) {
                            return; // Jangan tampilkan modal jika data 0
                        }

                        const point = points[0];
                        const unit = bandDurationChart.data.labels[point.index];

                        // Ambil Band Posisi yang diklik
                        const bandLabel = bandDurationChart.data.datasets[point.datasetIndex].label;
                        const clickedBand = bandLabel.replace('Band ', '').trim(); // 'I', 'II', etc.

                        // Ambil Durasi Band yang sedang aktif (Client-Side filter)
                        const activeDurationGroups = Array.from(durationFilterContainer.querySelectorAll(
                            'input[type="checkbox"]:checked')).map(c => c.value);

                        // Ambil Durasi Band yang sedang aktif (Client-Side filter)
                        const durationQuery = activeDurationGroups.map(dur => `durations[]=${encodeURIComponent(dur)}`).join('&');

                        // Logika Panggilan Detail:
                        // Kita panggil endpoint detail *per* Durasi Band yang aktif, tetapi hanya untuk Band Posisi yang diklik.

                        const detailPromises = activeDurationGroups.map(durationGroup => {
                            // forcedBandQuery memastikan backend HANYA memfilter band yang diklik
                            const forcedBandQuery = `bands[]=${encodeURIComponent(clickedBand)}`;

                            const fetchUrl = `/employees/band-duration-detail/${encodeURIComponent(unit)}/${encodeURIComponent(durationGroup)}?${forcedBandQuery}`;

                            return fetch(fetchUrl)
                                .then(res => res.json())
                                .then(employees => employees.map(emp => ({...emp, duration_group: durationGroup})))
                                .catch(error => {
                                    console.error(`Error fetching detail for ${durationGroup}:`, error);
                                    return [];
                                });
                        });

                        Promise.all(detailPromises)
                            .then(results => {
                                const employees = results.flat();
                                const modalTitle = document.getElementById('employeeModalLabel');
                                const modalBody = document.getElementById('employeeList');

                                const bandsUsed = clickedBand;
                                const durationsUsed = activeDurationGroups.join(', ');

                                modalTitle.textContent =
                                    `Daftar Karyawan Band (${bandsUsed}) - ${unit} (Durasi: ${durationsUsed})`;

                                modalBody.innerHTML = employees.length ?
                                    `<ul class="list-group">${employees.map(emp => `
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong>${emp.nama}</strong><br>
                                            </div>
                                            <div class="ms-auto text-end">
                                                <span class="badge bg-primary rounded-pill">${emp.lama_band_posisi} bulan</span>
                                                <span class="badge text-bg-info rounded-pill">Band ${emp.band_posisi || 'N/A'}</span>
                                            </div>
                                        </li>`).join('')}</ul>` :
                                    '<p class="text-muted">Tidak ada data karyawan untuk kategori ini.</p>';

                                new bootstrap.Modal(document.getElementById('employeeModal')).show();
                            })
                            .catch(error => console.error('Error combining employee details:', error));
                    };
                }
            }

            // Fungsi untuk membuat elemen checkbox secara dinamis
            function generateCheckboxes(container, items, type) {
                container.innerHTML = ''; // Hapus elemen yang ada
                items.forEach((item, index) => {
                    const checkbox = document.createElement('input');
                    checkbox.type = 'checkbox';
                    checkbox.className = 'form-check-input';
                    checkbox.id = `${type}Filter-${index}`;
                    checkbox.value = item;
                    checkbox.checked = true; // Default: semua dipilih

                    const label = document.createElement('label');
                    label.className = 'form-check-label';
                    label.setAttribute('for', `${type}Filter-${index}`);
                    label.textContent = item;

                    const div = document.createElement('div');
                    div.className = 'form-check form-check-inline';
                    div.appendChild(checkbox);
                    div.appendChild(label);

                    container.appendChild(div);
                });
            }

            // Fungsi untuk mengambil data baru dari server berdasarkan filter Band Posisi
            function fetchDataAndRedrawChart() {
                const activeBands = Array.from(bandFilterContainer.querySelectorAll(
                    'input[type="checkbox"]:checked')).map(c => c.value);

                // UPDATE: Simpan Band Posisi yang aktif ke variabel global
                currentActiveBands = activeBands;

                // Jika tidak ada band yang dipilih, kirim data kosong
                if (activeBands.length === 0) {
                    updateChart({
                        units: [],
                        duration_groups: [],
                        raw_data: [],
                        bands: []
                    });
                    return;
                }

                // Bangun query string untuk Band Posisi yang dipilih
                const bandQuery = activeBands.map(band => `bands[]=${encodeURIComponent(band)}`).join('&');
                const fetchUrl = `{{ route('employees.band_duration_data') }}${bandQuery ? '?' + bandQuery : ''}`;

                fetch(fetchUrl)
                    .then(res => res.json())
                    .then(data => {
                        // Data yang diterima kini adalah raw_data granular
                        updateChart(data);
                    })
                    .catch(error => console.error('Error fetching filtered data:', error));
            }

            // Fungsi untuk menyiapkan event listener Durasi Band (Client-Side)
            function setupDurationFilterListener() {
                // Hapus listener yang mungkin sudah ada
                durationFilterContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.removeEventListener('change', handleDurationChange);
                });

                // Tambahkan listener baru
                durationFilterContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                    cb.addEventListener('change', handleDurationChange);
                });
            }

            // Handler perubahan filter Durasi Band (Client-side filtering)
            function handleDurationChange() {
                // Panggil updateChart untuk memicu re-aggregation dan re-render
                const chartDataForUpdate = {
                    units: bandDurationChart ? bandDurationChart.data.labels : [],
                    duration_groups: Array.from(durationFilterContainer.querySelectorAll('input[type="checkbox"]')).map(c => c.value),
                    bands: availableBands,
                    raw_data: allRawData
                };
                if (chartDataForUpdate.units.length > 0) {
                    updateChart(chartDataForUpdate);
                }
            }


            // Ambil data chart awal dan siapkan filter
            fetch('{{ route('employees.band_duration_data') }}')
                .then(res => res.json())
                .then(data => {

                    // 1. Setup Filter Durasi Band (Client-Side)
                    generateCheckboxes(durationFilterContainer, data.duration_groups, 'duration');
                    setupDurationFilterListener(); // Pasang listener untuk filter durasi

                    // 2. Setup Filter Band Posisi (Server-Side)
                    const allBands = data.all_bands || ['I', 'II', 'III', 'IV', 'V', 'VI'];
                    generateCheckboxes(bandFilterContainer, allBands, 'band');

                    // Inisialisasi variabel global
                    currentActiveBands = allBands;
                    availableBands = data.bands;

                    // Pasang listener untuk filter band (akan memicu re-fetch data)
                    bandFilterContainer.querySelectorAll('input[type="checkbox"]').forEach(cb => {
                        cb.addEventListener('change', fetchDataAndRedrawChart);
                    });

                    // 3. Render Chart Awal
                    updateChart(data);

                })
                .catch(error => console.error('Error fetching initial data:', error));
        });
    </script>
</body>

</html>
