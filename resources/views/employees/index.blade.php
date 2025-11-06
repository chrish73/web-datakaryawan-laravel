<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Data Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    {{-- <script src="{{ asset('js/theme-switcher.js') }}"></script> --}}
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    {{-- Awal Body: Tetap fixed-top dan logika Dark Mode sama --}}
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

                {{-- START: DARK MODE TOGGLE (Tetap sama, hanya penyesuaian CSS) --}}
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
        {{-- START: BIRTHDAY NOTIFICATION AREA --}}
        <div id="birthday-notification-area" class="mt-3 mb-4" style="display: none;">
            <div class="alert alert-warning d-flex justify-content-between align-items-center border-0 shadow-sm" role="alert">
                <div class="d-flex align-items-center">
                    <i class="bi bi-gift-fill me-2 fs-4"></i>
                    <span id="birthday-message" class="fw-bold"></span>
                </div>
                <a href="{{ route('employees.today_birthdays') }}" class="btn btn-sm btn-info fw-bold">Lihat Detail</a>
            </div>
        </div>
        {{-- END: BIRTHDAY NOTIFICATION AREA --}}

        <h3 class="page-title"><i class="bi bi-people-fill me-2"></i> Daftar Karyawan</h3>

        {{-- Alerts (dibiarkan sama) --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-x-octagon-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Import & Search Area (diubah agar lebih terpisah dan jelas) --}}
        <div class="row mb-5">
            <div class="col-lg-6 mb-3 mb-lg-0">
                 <h5 class="fw-bold text-muted mb-3"><i class="bi bi-database-up me-2"></i>Opsi Import Data</h5>
                <div class="d-flex flex-column gap-3">
                    {{-- Form Import Data Utama --}}
                    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex import-form shadow-sm p-3 rounded-3 border border-light-subtle">
                        @csrf
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control form-control-sm me-2" required>
                        <button type="submit" class="btn btn-primary btn-sm text-nowrap" id="importButton">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import Data Karyawan
                        </button>
                    </form>

                    {{-- Form input data TC  --}}
                    <form action="{{ route('employees.import_tc') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex import-form shadow-sm p-3 rounded-3 border border-light-subtle">
                        @csrf
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control form-control-sm me-2" required>
                        <button type="submit" class="btn btn-secondary btn-sm text-nowrap" id="importTcButton">
                            <i class="bi bi-person-check-fill me-1"></i> Import Data TC dan Unit
                        </button>
                    </form>

                    {{-- Form Import Data Ulang Tahun --}}
                    <form action="{{ route('employees.import_birthday') }}" method="POST" enctype="multipart/form-data"
                        class="d-flex import-form shadow-sm p-3 rounded-3 border border-light-subtle">
                        @csrf
                        <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control form-control-sm me-2" required>
                        <button type="submit" class="btn btn-info btn-sm text-nowrap" id="importBirthdayButton">
                            <i class="bi bi-gift-fill me-1"></i> Import Data Tanggal Lahir
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <h5 class="fw-bold text-muted mb-3"><i class="bi bi-search me-2"></i>Pencarian Data</h5>
                <form method="GET" action="{{ route('employees.index') }}" class="d-flex search-form shadow-sm p-3 rounded-3 border border-light-subtle">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama / NIK..."
                            value="{{ $search }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i></a>
                    </div>
                </form>
            </div>

            {{-- START: NEW EXPORT BUTTON --}}
                <div class="d-flex flex-column gap-3 mt-3">
                    <h5 class="fw-bold text-muted mb-1"><i class="bi bi-download me-2"></i>Opsi Export Data</h5>
                    <a href="{{ route('employees.export') }}" class="btn btn-success btn-sm text-nowrap shadow-sm p-3 rounded-3">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export Semua Data Karyawan
                    </a>
                </div>
                {{-- END: NEW EXPORT BUTTON --}}
        </div>

        {{-- Tampilan Data Karyawan dalam bentuk Card --}}
        <div class="row employee-list">
            @forelse($employees as $employee)
                @php
                    $status = strtolower($employee->status_eligibility);
                    $statusTextClass = '';
                    if ($status === 'eligible') {
                        $statusTextClass = 'card-status-eligible';
                    } elseif ($status === 'not eligible') {
                        $statusTextClass = 'card-status-not-eligible';
                    }
                @endphp
                <div class="col-sm-12 col-md-6 col-lg-4 d-flex">
                    <div class="card employee-card flex-fill mb-4">
                        <div class="card-header-custom">
                            <span class="fw-bold">NIK: {{ $employee->nik }}</span>
                            <span class="badge rounded-pill {{ $statusTextClass }}">{{ $employee->status_eligibility }}</span>
                        </div>
                        <div class="card-body-custom">
                            <h4 class="card-title-name">{{ $employee->nama }}</h4>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-briefcase-fill"></i>Unit Kerja</span>
                                <span class="card-item-value">{{ $employee->nama_unit }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-clock-history"></i>Lama Band Posisi</span>
                                <span class="card-item-value">{{ $employee->lama_band_posisi }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-award-fill"></i>Nilai Kinerja</span>
                                <span class="card-item-value badge text-bg-warning">{{ $employee->nilai_kinerja }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-lightbulb-fill"></i>Nilai Kompetensi</span>
                                <span class="card-item-value badge text-bg-success">{{ $employee->nilai_kompetensi }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-heart-fill"></i>Nilai Behavior</span>
                                <span class="card-item-value badge text-bg-info">{{ $employee->nilai_behavior }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-person-check-fill"></i>Talent Charter</span>
                                <span class="card-item-value talent-charter-text" style="font-size: 0.85em;">{{ $employee->tc }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4 border-0 shadow-sm rounded-3">
                        <i class="bi bi-info-circle fs-4 me-2"></i> **Tidak ada data karyawan yang ditemukan.** Coba dengan kata kunci pencarian lain atau reset filter.
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $employees->links() }}
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Logika untuk mencegah double-click pada tombol Import (main.js)
            const setupImportButton = (buttonId) => {
                const button = document.getElementById(buttonId);
                const form = button ? button.closest('form') : null;
                if (form) {
                    form.addEventListener('submit', function() {
                        if (!button.disabled) {
                            button.disabled = true;
                            button.innerHTML =
                                '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                        }
                    });
                }
            };
            setupImportButton('importButton');
            setupImportButton('importBirthdayButton');

            // Logika Dark Mode (theme-switcher.js)
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

            // Logika Notifikasi Ulang Tahun
            fetch('{{ route('employees.birthdays_notification') }}')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const area = document.getElementById('birthday-notification-area');
                    const badge = document.getElementById('birthday-badge');
                    const message = document.getElementById('birthday-message');

                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                        area.style.display = 'block';
                        message.innerHTML = `Hari ini ada **${count}** karyawan yang berulang tahun! 🎉`;
                    }
                });
        });
    </script>
</body>

</html>
