<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Band Posisi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- Ganti ke style.css jika Anda menggabungkannya, atau gunakan stchart.css yang baru --}}
    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">
</head>

<body>
    {{-- Awal Navbar: Salinan dari index.blade.php (Pastikan Anda TIDAK menggunakan @extends jika navbar disalin penuh) --}}
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
                            href="{{ route('employees.chart') }}"><i class="bi bi-bar-chart-line-fill me-1"></i> Data Band Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart' ) }}"><i class="bi bi-graph-up me-1"></i> Data Kelompok Usia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('employees/band') ? 'active' : '' }}"
                            href="/employees/band"><i class="bi bi-layers-fill me-1"></i>Lama Band Posisi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('employees.today_birthdays') }}">
                            <i class="bi bi-gift-fill me-1"></i> Ulang Tahun Hari Ini <span id="birthday-badge" class="badge text-bg-warning rounded-pill ms-1" style="display: none;"></span>
                        </a>
                    </li>
                </ul>

                {{-- START: DARK MODE TOGGLE (Salin dari index.blade.php) --}}
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

    {{-- Konten Utama --}}
    <div class="container main-content">

        <h3 class="page-title"><i class="bi bi-bar-chart-line-fill me-2"></i> Jumlah Karyawan berdasarkan Unit dan Band Posisi</h3>

        {{-- Card untuk membungkus Chart agar memiliki latar belakang dan bayangan --}}
        <div class="chart-card">
            <div class="row">
                <div class="col-md-12">
                    <canvas id="bandPosisiChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Logika Dark Mode dan Chart JS (Gabungan dari index.blade.php dan chart.blade.php) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            // START: Logika Dark Mode (Salinan dari index.blade.php)
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
            // END: Logika Dark Mode

            // START: Logika Notifikasi Ulang Tahun (Salinan dari index.blade.php)
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
            // END: Logika Notifikasi Ulang Tahun


            // START: Logika Chart (Dipertahankan dari chart.blade.php)
            fetch('{{ route('employees.chart_data') }}')
                .then(response => response.json())
                .then(data => {
                    const bands = data.bands;
                    const units = data.units;
                    const aggregatedData = data.data;

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
                    new Chart(ctx, {
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
                                        callback: function(value) {
                                            if (Number.isInteger(value)) {
                                                return value;
                                            }
                                        }
                                    }
                                }
                            },
                            plugins: {
                                title: {
                                    display: true,
                                    text: 'Distribusi Band Posisi per Unit'
                                },
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                });
            // END: Logika Chart
        });
    </script>
</body>

</html>
