<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Band Posisi Bulanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <style>
        .spin {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    {{-- Menggunakan struktur Navbar yang konsisten --}}
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
                        <a class="nav-link"
                            href="{{ route('employees.index') }}"><i class="bi bi-grid-fill me-1"></i> Data Karyawan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('employees.chart') }}"><i class="bi bi-bar-chart-line-fill me-1"></i> Data Band Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active"
                            href="{{ route('employees.band_position_monthly_chart') }}"><i class="bi bi-calendar-check-fill me-1"></i> Promosi Band Posisi Bulanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('employees.age_group_chart' ) }}"><i class="bi bi-graph-up me-1"></i> Data Kelompok Usia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="/employees/band"><i class="bi bi-layers-fill me-1"></i>Lama Band Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link"
                            href="{{ route('employees.training_input') }}">
                            <i class="bi bi-calendar-event-fill me-1"></i> History Events
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.today_birthdays') }}">
                            <i class="bi bi-gift-fill me-1"></i> Ulang Tahun Hari Ini <span id="birthday-badge" class="badge text-bg-warning rounded-pill ms-1" style="display: none;"></span>
                        </a>
                    </li>
                </ul>

                {{-- START: DARK MODE TOGGLE --}}
                <div class="d-flex align-items-center ms-lg-3">
                    <i class="bi bi-sun-fill me-2 text-warning" id="light-icon"></i>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="darkModeToggle" role="switch" aria-label="Toggle Dark Mode">
                    </div>
                    <i class="bi bi-moon-stars-fill ms-2 text-primary" id="dark-icon"></i>
                </div>
                {{-- END: DARK MODE TOGGLE --}}
            </div>
        </div>
    </nav>

    <div class="container main-content">
        <h3 class="page-title"><i class="bi bi-calendar-event-fill me-2"></i> Data Promosi Band Posisi Bulanan</h3>

        <div class="row mb-4 align-items-end">
            <div class="col-md-4">
                <label for="filterYear" class="form-label">Filter Tahun:</label>
                {{-- Filter tahun dari data yang dilempar oleh Controller --}}
                <select id="filterYear" class="form-select">
                    @foreach ($availableYears as $year)
                        <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-bar-chart-line-fill me-2"></i> Grafik Promosi Band Posisi Bulanan</h5>
            </div>
            <div class="card-body">
                <div style="height: 400px;"><canvas id="monthlyBandChart"></canvas></div>
            </div>
        </div>
    </div>

    {{-- Modal Detail Karyawan --}}
    <div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="detailModalLabel">Detail Karyawan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="detailModalInfo" class="fw-bold"></p>
                    <div class="table-responsive">
                        <table class="table table-striped table-sm">
                            <thead>
                                <tr>
                                    <th>NIK</th>
                                    <th>Nama</th>
                                    <th>Band Posisi</th>
                                    <th>Tgl Band Posisi</th>
                                </tr>
                            </thead>
                            <tbody id="employeeDetailsTableBody">
                                <tr><td colspan="4" class="text-center text-muted">Memuat data...</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    {{-- BARU: Tambahkan CDN plugin datalabels --}}
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2.2.0/dist/chartjs-plugin-datalabels.min.js"></script>

    <script>
        const MONTHS = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        let monthlyBandChart;

        function updateChart(year) {
            $.ajax({
                url: '{{ route('employees.band_position_monthly_data') }}',
                method: 'GET',
                data: { year: year },
                success: function(data) {
                    if (monthlyBandChart) {
                        monthlyBandChart.destroy();
                    }

                    // BARU: Daftarkan plugin Datalabels sebelum membuat chart
                    Chart.register(ChartDataLabels);

                    const datasets = [];
                    const allBands = data.bands;
                    const bandColors = {
                        'I': 'rgb(255, 99, 132)', 'II': 'rgb(255, 159, 64)', 'III': 'rgb(256, 205, 86)',
                        'IV': 'rgb(75, 192, 192)', 'V': 'rgb(54, 162, 235)', 'VI': 'rgb(153, 102, 255)'
                    };

                    allBands.forEach(band => {
                        const monthlyData = MONTHS.map((month, index) => {
                            const monthKey = index + 1;
                            return data.monthly_data[monthKey] ? (data.monthly_data[monthKey][band] || 0) : 0;
                        });

                        // Hanya tambahkan dataset jika ada data (count > 0)
                        if (monthlyData.some(count => count > 0)) {
                            datasets.push({
                                label: `Band ${band}`,
                                data: monthlyData,
                                backgroundColor: bandColors[band] || 'rgba(128, 128, 128, 0.5)',
                                borderColor: bandColors[band] ? bandColors[band].replace('rgb', 'rgba').replace(')', ', 1)') : 'rgb(128, 128, 128)',
                                borderWidth: 1,
                                type: 'bar',
                                stack: 'stack1'
                            });
                        }
                    });

                    const ctx = document.getElementById('monthlyBandChart').getContext('2d');
                    monthlyBandChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: MONTHS,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                title: {
                                    display: true,
                                    text: `Promosi Band Posisi per Bulan Tahun ${year}`
                                },
                                tooltip: {
                                    mode: 'index',
                                    intersect: false
                                },
                                // BARU: Konfigurasi Datalabels
                                datalabels: {
                                    color: '#fff', // Warna teks label
                                    font: {
                                        weight: 'bold',
                                        size: 10
                                    },
                                    formatter: function(value, context) {
                                        // Hanya tampilkan label jika nilainya lebih dari 0
                                        return value > 0 ? value : null;
                                    },
                                    anchor: 'center', // Posisi label di tengah segmen
                                    align: 'center'
                                }
                            },
                            scales: {
                                x: {
                                    stacked: true,
                                    title: {
                                        display: true,
                                        text: 'Bulan Promosi Band Posisi'
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
                                        callback: function(value) {
                                            if (value % 1 === 0) {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            },
                            onClick: (e) => {
                                // Menggunakan mode 'nearest' untuk mendeteksi segmen tunggal yang paling dekat dengan titik klik.
                                const activePoints = monthlyBandChart.getElementsAtEventForMode(e, 'nearest', { intersect: true }, true);

                                if (activePoints.length > 0) {
                                    const firstPoint = activePoints[0]; // Ini adalah segmen yang spesifik diklik
                                    const monthIndex = firstPoint.index;
                                    const datasetIndex = firstPoint.datasetIndex;

                                    const month = monthIndex + 1; // 1-based month index
                                    // Ambil Band yang diklik dari label dataset
                                    const bandClicked = monthlyBandChart.data.datasets[datasetIndex].label.replace('Band ', '');
                                    const count = monthlyBandChart.data.datasets[datasetIndex].data[monthIndex];

                                    // Hanya lanjutkan jika jumlahnya > 0
                                    if (count > 0 && bandClicked) {
                                        showDetailsModal(month, year, bandClicked);
                                    }
                                }
                            }
                        }
                    });
                },
                error: function(xhr) {
                    $('#monthlyBandChart').parent().html('<div class="alert alert-danger">Gagal memuat data grafik: ' + (xhr.responseJSON ? xhr.responseJSON.message : xhr.statusText) + '</div>');
                }
            });
        }

        function showDetailsModal(month, year, band) {
             $.ajax({
                url: `/employees/band-position-monthly-detail/${year}/${month}`,
                method: 'GET',
                data: { band: band },
                beforeSend: function() {
                    $('#detailModalInfo').text(`Detail Karyawan Promosi ke Band ${band} pada Bulan ${MONTHS[month - 1]} Tahun ${year}:`);
                    $('#employeeDetailsTableBody').html('<tr><td colspan="4" class="text-center text-muted"><i class="bi bi-arrow-clockwise spin me-2"></i>Memuat data...</td></tr>');
                    new bootstrap.Modal(document.getElementById('detailModal')).show();
                },
                success: function(data) {
                    const tbody = $('#employeeDetailsTableBody');
                    tbody.empty();
                    if (data.length > 0) {
                        data.forEach(employee => {
                            const date = new Date(employee.tgl_band_posisi);
                            const tglBandPosisi = date.toLocaleDateString('id-ID', { year: 'numeric', month: '2-digit', day: '2-digit' });

                            tbody.append(`
                                <tr>
                                    <td>${employee.nik}</td>
                                    <td>${employee.nama}</td>
                                    <td>${employee.band_posisi}</td>
                                    <td>${tglBandPosisi}</td>
                                </tr>
                            `);
                        });
                    } else {
                        tbody.html('<tr><td colspan="4" class="text-center text-muted">Tidak ada data karyawan yang ditemukan.</td></tr>');
                    }
                },
                error: function() {
                    $('#employeeDetailsTableBody').html('<tr><td colspan="4" class="text-center text-danger">Gagal memuat detail karyawan.</td></tr>');
                }
            });
        }


        // Event Listener untuk filter tahun
        document.getElementById('filterYear').addEventListener('change', function() {
            const selectedYear = this.value;
            updateChart(selectedYear);
        });

        // Load chart saat halaman pertama kali dimuat & Dark Mode
        document.addEventListener('DOMContentLoaded', function() {
            // Logika Dark Mode
            (function() {
                const getStoredTheme = () => localStorage.getItem('theme');
                const setStoredTheme = theme => localStorage.setItem('theme', theme);
                const htmlElement = document.documentElement;
                const storedTheme = getStoredTheme();
                if (storedTheme) { htmlElement.setAttribute('data-bs-theme', storedTheme); }
                else { htmlElement.setAttribute('data-bs-theme', 'light'); setStoredTheme('light'); }
                const updateToggleUI = (theme) => {
                    const toggle = document.getElementById('darkModeToggle');
                    if (toggle) { toggle.checked = theme === 'dark'; }
                };
                updateToggleUI(getStoredTheme() || 'light');
                const toggle = document.getElementById('darkModeToggle');
                if (toggle) {
                    toggle.addEventListener('change', () => {
                        const newTheme = toggle.checked ? 'dark' : 'light';
                        htmlElement.setAttribute('data-bs-theme', newTheme);
                        setStoredTheme(newTheme);
                    });
                }
            })();

            const initialYear = document.getElementById('filterYear').value;
            if (initialYear) {
                updateChart(initialYear);
            }
        });
    </script>
</body>
</html>
