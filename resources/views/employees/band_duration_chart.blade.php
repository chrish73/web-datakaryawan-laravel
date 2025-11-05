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

    {{-- Memastikan menggunakan CSS yang sama untuk desain --}}
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">
</head>

<body>
    {{-- Awal Navbar: Sama persis dengan halaman chart lainnya --}}
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

                {{-- START: DARK MODE TOGGLE --}}
                <div class="d-flex align-items-center ms-lg-3">
                    <i class="bi bi-sun-fill me-2 text-warning" id="light-icon"></i>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeToggle" role="switch"
                            aria-label="Toggle Dark Mode">
                    </div>
                    <i class="bi bi-moon-stars-fill ms-2 text-primary" id="dark-icon"></i>
                </div>
                {{-- END: DARK MODE TOGGLE --}}
            </div>
        </div>
    </nav>

    {{-- Konten Utama --}}
    <div class="container main-content">

        {{-- Judul Halaman Sesuai Desain --}}
        <h3 class="page-title"><i class="bi bi-hourglass-split me-2"></i> Distribusi Karyawan berdasarkan Durasi Band
            Posisi per Unit</h3>

        {{-- Card untuk membungkus Chart --}}
        <div class="chart-card">
            <div class="row">
                <div class="col-md-12">
                    <canvas id="bandDurationChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- START: Modal Detail Karyawan --}}
    <div class="modal fade" id="employeeModal" tabindex="-1" aria-labelledby="employeeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="employeeModalLabel">Daftar Karyawan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="employeeList">
                    {{-- Daftar karyawan akan diisi oleh JavaScript --}}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END: Modal Detail Karyawan --}}

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

    {{-- Logika Dark Mode dan Chart JS --}}
    <script>
        // Logika Dark Mode dan Notifikasi Ulang Tahun dipertahankan persis sama

        // Logic Dark Mode (IIFE)
        (function() {
            const getStoredTheme = () => localStorage.getItem('theme');
            const setStoredTheme = theme => localStorage.setItem('theme', theme);
            const htmlElement = document.documentElement;

            const storedTheme = getStoredTheme();
            if (storedTheme) {
                htmlElement.setAttribute('data-bs-theme', storedTheme);
            } else {
                htmlElement.setAttribute('data-bs-theme', 'light');
                setStoredTheme('light');
            }

            const updateToggleUI = (theme) => {
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) {
                    toggle.checked = theme === 'dark';
                }
            };

            updateToggleUI(getStoredTheme() || 'light');

            window.addEventListener('load', () => {
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) {
                    toggle.addEventListener('change', () => {
                        const newTheme = toggle.checked ? 'dark' : 'light';
                        htmlElement.setAttribute('data-bs-theme', newTheme);
                        setStoredTheme(newTheme);
                    });
                }
            });
        })();


        document.addEventListener('DOMContentLoaded', function() {

            // Logika Notifikasi Ulang Tahun
            fetch('{{ route('employees.birthdays_notification') }}')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const badge = document.getElementById('birthday-badge');
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                    }
                });

            // Logika Chart: Dijalankan di sini untuk memastikan DOM sudah siap
            fetch('{{ route('employees.band_duration_data') }}')
                .then(response => response.json())
                .then(data => {
                    const durationGroups = data.duration_groups;
                    const units = data.units;
                    const aggregatedData = data.data;

                    // Warna untuk setiap kelompok durasi (tidak diubah)
                    const colors = [
                        'rgba(0, 150, 136, 0.8)', // Hijau Teal: < 2 Tahun
                        'rgba(255, 152, 0, 0.8)', // Oranye: 2 - 5 Tahun
                        'rgba(192, 57, 43, 0.8)', // Merah Bata: > 5 Tahun
                        'rgba(39, 174, 96, 0.8)',
                    ];

                    const datasets = durationGroups.map((group, index) => ({
                        label: group,
                        data: units.map(unit => aggregatedData[unit][group]),
                        backgroundColor: colors[index % colors.length],
                        borderColor: colors[index % colors.length].replace('0.8', '1'),
                        borderWidth: 1
                    }));

                    const ctx = document.getElementById('bandDurationChart').getContext('2d');

                    // Simpan instance chart ke variabel
                    const bandDurationChart = new Chart(ctx, {
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
                                        stepSize: 1
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
                                    color: '#fff', // warna teks label
                                    anchor: 'center',
                                    align: 'center',
                                    font: {
                                        weight: 'bold',
                                        size: 12
                                    },
                                    formatter: function(value, context) {
                                        if (value > 0) {
                                            return value; // tampilkan angka jika > 0
                                        } else {
                                            return ''; // jangan tampilkan angka nol
                                        }
                                    }
                                }
                            }
                        },
                        plugins: [ChartDataLabels] // daftar plugin di sini
                    });


                    // === KLIK BAR CHART untuk Detail Karyawan ===
                    document.getElementById('bandDurationChart').onclick = function(evt) {
                        // Gunakan instance chart yang sudah disimpan
                        const points = bandDurationChart.getElementsAtEventForMode(evt, 'nearest', {
                            intersect: true
                        }, true);
                        if (!points.length) return;

                        const point = points[0];
                        const unit = bandDurationChart.data.labels[point.index];
                        const group = bandDurationChart.data.datasets[point.datasetIndex].label;

                        // Panggil API endpoint baru
                        // Asumsi route di Laravel adalah: employees/band-duration-detail/{unit}/{group}
                        fetch(
                                `/employees/band-duration-detail/${encodeURIComponent(unit)}/${encodeURIComponent(group)}`)
                            .then(res => res.json())
                            .then(employees => {
                                const modalTitle = document.getElementById('employeeModalLabel');
                                const modalBody = document.getElementById('employeeList');

                                modalTitle.textContent =
                                    `Daftar Karyawan Durasi Band Posisi (${group}) - ${unit}`;

                                if (employees.length > 0) {
                                    modalBody.innerHTML = `
                                        <ul class="list-group">
                                            ${employees.map(emp => `
                                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                                        ${emp.nama}
                                                        <span class="badge bg-primary rounded-pill">${emp.lama_band_posisi} bulan</span>
                                                    </li>`).join('')}
                                        </ul>`;
                                } else {
                                    modalBody.innerHTML =
                                        '<p class="text-muted">Tidak ada data karyawan untuk kategori ini.</p>';
                                }

                                // Tampilkan modal
                                new bootstrap.Modal(document.getElementById('employeeModal')).show();
                            })
                            .catch(err => console.error('Gagal memuat data detail:', err));
                    };
                });
            // END: Logika Chart
        });
    </script>
</body>

</html>
