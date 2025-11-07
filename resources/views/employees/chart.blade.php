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

                {{-- START: DARK MODE TOGGLE (Salin dari index.blade.php) --}}
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

    {{-- Konten Utama (Diubah menjadi Row untuk Tombol) --}}
    <div class="container main-content">
        <h3 class="page-title"><i class="bi bi-bar-chart-line-fill me-2"></i> Jumlah Karyawan berdasarkan Unit dan Band
            Posisi</h3>

        {{-- Row untuk Chart dan Tombol Unit --}}
        <div class="row">
            {{-- Bagian Chart (8 kolom) --}}
            <div class="col-md-8">
                <div class="chart-card">
                    <canvas id="bandPosisiChart"></canvas>
                </div>
            </div>

            {{-- Bagian Tombol Unit (4 kolom) - BARU --}}
            <div class="col-md-4">
                <div class="card chart-card">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-person-lines-fill me-1"></i> Detail Semua Karyawan per Unit
                    </div>
                    {{-- Daftar tombol Unit akan diisi oleh JavaScript di sini --}}
                    <div class="card-body list-group list-group-flush" id="unitButtonContainer">
                        <p class="text-muted text-center my-3">Memuat daftar Unit...</p>
                    </div>
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
    document.addEventListener('DOMContentLoaded', function() {

        // ... (Logika Dark Mode dan Notifikasi Ulang Tahun di atas dipertahankan) ...

        // FUNGSI BARU UNTUK MENAMPILKAN DETAIL KARYAWAN BERDASARKAN UNIT (Digunakan oleh tombol baru)
        function showEmployeeDetailsByUnit(unit) {
            // ... (Fungsi ini dipertahankan karena sudah benar untuk menampilkan detail Unit) ...
            const modalTitle = document.getElementById('employeeModalLabel');
            const modalBody = document.getElementById('employeeList');
            modalTitle.textContent = `Memuat Daftar Karyawan di Unit: ${unit}...`;
            modalBody.innerHTML =
                '<div class="text-center"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>';
            new bootstrap.Modal(document.getElementById('employeeModal')).show();

            // Memanggil endpoint yang mengambil SEMUA Band dalam Unit
            fetch(`/employees/unit-detail/${encodeURIComponent(unit)}`)
                .then(res => {
                    if (!res.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return res.json();
                })
                .then(employees => {
                    modalTitle.textContent =
                        `Daftar Karyawan di Unit: ${unit} (Total: ${employees.length} orang)`;

                    if (employees.length > 0) {
                        modalBody.innerHTML = `
                                <ul class="list-group">
                                    ${employees.map(emp => `
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <div>
                                                    <strong>${emp.nama}</strong> <br>
                                                    <small class="text-muted">${emp.nama_posisi || 'Posisi Tidak Ada'}</small>
                                                </div>
                                               <div class="d-flex flex-column align-items-end">
                                                    <span class="badge bg-info text-dark mb-1">
                                                        Band ${emp.band_posisi || 'N/A'}
                                                    </span>
                                                    <span class="badge ${emp.status_eligibility === 'Eligible'
                                                        ? 'bg-success'
                                                        : (emp.status_eligibility === 'Not Eligible'
                                                            ? 'bg-danger'
                                                            : 'bg-secondary')}">
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
                .catch(err => {
                    console.error('Gagal memuat data detail:', err);
                    modalTitle.textContent = `Gagal Memuat Data Karyawan`;
                    modalBody.innerHTML =
                        '<p class="text-danger">Terjadi kesalahan saat memuat data. Silakan coba lagi.</p>';
                });
        }

        // START: Logika Chart dan Rendering Tombol Unit
        // Melakukan fetch ke dua endpoint secara bersamaan
        Promise.all([
            fetch('{{ route('employees.chart_data') }}').then(res => {
                if (!res.ok) throw new Error('Failed to fetch chart data');
                return res.json();
            }),
            fetch('{{ route('employees.all_unit_counts') }}').then(res => {
                if (!res.ok) throw new Error('Failed to fetch unit counts');
                return res.json();
            })
        ])
        .then(([chartData, allUnitCounts]) => {
            const bands = chartData.bands;
            const units = chartData.units;
            const aggregatedData = chartData.data; // Data ini hanya berisi yang 'Eligible'
            const unitButtonContainer = document.getElementById('unitButtonContainer');

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

            // Simpan instance chart ke variabel
            const bandPositionChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: units,
                    datasets: datasets
                },
                options: {
                    // ... (options chart dipertahankan) ...
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

            // === KLIK BAR CHART untuk Detail Karyawan (BAND SPESIFIK) - LOGIKA ASLI DIPERTAHANKAN ===
            document.getElementById('bandPosisiChart').onclick = function(evt) {
                // ... (Logika klik chart dipertahankan) ...
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
                            modalBody.innerHTML = `
                                    <ul class="list-group">
                                        ${employees.map(emp => `
                                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                                ${emp.nama} (${emp.nama_posisi})

                                                                <div class="d-flex flex-column align-items-end" >
                                                                    <span class="badge bg-primary mb-1">Band ${emp.band_posisi}</span>
                                                                    <span class="badge ${emp.status_eligibility === 'Eligible'
                                                                        ? 'bg-success'
                                                                        : (emp.status_eligibility === 'Not Eligible'
                                                                            ? 'bg-danger'
                                                                            : 'bg-secondary')}">
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

            // === RENDERING TOMBOL UNIT (BARU) ===
            unitButtonContainer.innerHTML = ''; // Hapus pesan "Memuat daftar Unit..."

            units.forEach(unit => {
                // PERBAIKAN: Gunakan data total karyawan dari endpoint baru
                // allUnitCounts adalah objek { 'UNIT A': 150, 'UNIT B': 80, ... }
                let totalEmployees = allUnitCounts[unit] || 0; // Mengambil total tanpa filter eligible

                const button = document.createElement('button');
                // Menggunakan list-group-item agar terlihat seperti daftar di dalam card
                button.className =
                    'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                button.setAttribute('data-unit', unit);

                // PERBAIKAN: Hapus kata 'Eligible' dan tampilkan totalEmployees yang sebenarnya
                button.innerHTML = `${unit}
                    <span class="badge bg-secondary rounded-pill">${totalEmployees}</span>
                `;

                // Menambahkan event listener ke tombol untuk memanggil fungsi baru
                button.addEventListener('click', function() {
                    const selectedUnit = this.getAttribute('data-unit');
                    // Panggil fungsi untuk menampilkan detail Unit secara keseluruhan
                    showEmployeeDetailsByUnit(selectedUnit);
                });

                unitButtonContainer.appendChild(button);
            });
        })
        .catch(error => {
            // Handle error jika salah satu fetch gagal
            console.error('Error fetching data:', error);
            const unitButtonContainer = document.getElementById('unitButtonContainer');
            unitButtonContainer.innerHTML = '<p class="text-danger text-center my-3">Gagal memuat data unit. Silakan cek koneksi atau server log.</p>';

            // Anda mungkin juga ingin menampilkan pesan error pada chart
            const ctx = document.getElementById('bandPosisiChart').getContext('2d');
            ctx.fillText("Gagal Memuat Data Chart", 10, 50);
        });
        // END: Logika Chart dan Rendering Tombol Unit
    });
</script>
</body>

</html>
