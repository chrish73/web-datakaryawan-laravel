<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Karyawan Spesial Ulang Tahun Hari Ini</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/stchart.css') }}">

    {{-- START: GAYA KHUSUS ULANG TAHUN DAN PERBAIKAN SPASI --}}
    <style>
        /* Animasi kilauan pada Badge Usia */
        @keyframes glowing {
            0% { box-shadow: 0 0 5px var(--clr-accent); }
            50% { box-shadow: 0 0 20px var(--clr-accent), 0 0 10px white; }
            100% { box-shadow: 0 0 5px var(--clr-accent); }
        }
        /* Animasi berkedip pada Card Header */
        @keyframes subtle-pulse {
            0% { opacity: 0.95; }
            50% { opacity: 1; }
            100% { opacity: 0.95; }
        }
        .employee-card.special-birthday:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 15px 40px rgba(255, 64, 129, 0.5);
        }
        .card-header-custom.birthday-celebration {
            animation: subtle-pulse 2s infinite alternate;
            padding: 18px 20px;
            border-bottom: 5px solid var(--clr-accent) !important;
        }
        .birthday-badge-glow {
            animation: glowing 1.5s infinite alternate;
            background-color: var(--clr-accent) !important;
        }

        /* PERBAIKAN SPACING UTAMA */
        .card-body-custom { padding: 25px !important; }
        .card-title-name {
            font-size: 1.3rem !important;
            margin-bottom: 1.5rem !important;
            padding-bottom: 10px;
        }
        .card-item {
            padding: 15px 0 !important;
            gap: 15px !important;
        }
        .card-item-label { font-size: 1.05em; }
        .card-item-value { text-align: left; }
        .card-item-label i { margin-right: 12px !important; }

        #confetti-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }

        /* KEYFRAMES KONFETI DIPERKUAT DENGAN VENDOR PREFIX */
        @keyframes confetti-fall {
            to {
                transform: translateY(105vh) rotate(1080deg);
            }
        }
        @-webkit-keyframes confetti-fall {
            to {
                -webkit-transform: translateY(105vh) rotate(1080deg);
            }
        }
        @keyframes confetti-wind {
            0% { transform: translateX(0); }
            100% { transform: translateX(80px); }
        }
        @-webkit-keyframes confetti-wind {
            0% { -webkit-transform: translateX(0); }
            100% { -webkit-transform: translateX(80px); }
        }
    </style>
    {{-- END: GAYA KHUSUS ULANG TAHUN DAN PERBAIKAN SPASI --}}
</head>

<body>
    {{-- ELEMEN KONFETI HARUS DI SINI --}}
    <div id="confetti-container"></div>

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
                        <a class="nav-link {{ Request::routeIs('employees.band_position_monthly_chart') ? 'active' : '' }}"
                            href="{{ route('employees.band_position_monthly_chart') }}"><i class="bi bi-calendar-check-fill me-1"></i> Promosi Band Posisi Bulanan</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart' ) }}"><i class="bi bi-graph-up me-1"></i> Data Kelompok Usia</a>
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
                        <a class="nav-link active" href="{{ route('employees.today_birthdays') }}">
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

    {{-- Konten Utama --}}
    <div class="container main-content">

        <h3 class="page-title"><i class="bi bi-gift-fill me-2"></i> Karyawan Ulang Tahun Hari Ini</h3>

        <div class="row employee-list">
            @forelse($employees as $employee)
                <div class="col-sm-12 col-md-6 col-lg-4 d-flex">
                    <div class="card employee-card flex-fill mb-4 special-birthday">

                        {{-- CARD HEADER --}}
                        <div class="card-header-custom birthday-celebration">
                            <span class="fw-bold">NIK: {{ $employee->nik }}</span>
                            <span class="badge rounded-pill birthday-badge-glow">{{ $employee->age }} Tahun</span>
                        </div>

                        <div class="card-body-custom">
                            <h4 class="card-title-name">{{ $employee->nama }}</h4>

                            {{-- Detail Tanggal Lahir --}}
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-calendar-heart-fill" style="color: var(--clr-accent) !important;"></i>Tanggal Lahir</span>
                                <span class="card-item-value">{{ \Carbon\Carbon::parse($employee->tgl_lahir)->isoFormat('D MMMM YYYY') }}</span>
                            </div>

                            {{-- Detail Unit --}}
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-briefcase-fill" style="color: var(--clr-accent) !important;"></i>Unit Kerja</span>
                                <span class="card-item-value">{{ $employee->nama_unit }}</span>
                            </div>

                            {{-- Detail Kota Lahir --}}
                            <div class="card-item">
                                <span class="card-item-label"><i class="bi bi-geo-alt-fill" style="color: var(--clr-accent) !important;"></i>Kota Lahir</span>
                                <span class="card-item-value">{{ $employee->kota_lahir ?? '-' }}</span>
                            </div>

                            {{-- Ucapan Selamat yang disorot --}}
                            <div class="mt-5 text-center p-3 rounded" style="background-color: var(--clr-accent); color: white; font-size: 1.1em; font-weight: bold;">
                                Selamat Ulang Tahun! 🎉
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4 border-0 shadow-sm rounded-3">
                        <i class="bi bi-info-circle fs-4 me-2"></i> **Tidak ada karyawan yang berulang tahun hari ini.**
                    </div>
                </div>
            @endforelse
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Logika Dark Mode dan Notifikasi --}}
    <script>
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

            // Logika Notifikasi Ulang Tahun (untuk badge di Navbar)
            fetch('{{ route('employees.birthdays_notification') }}')
                .then(response => response.json())
                .then(data => {
                    const count = data.count;
                    const badge = document.getElementById('birthday-badge');
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = 'inline-block';
                        // Panggil confetti HANYA jika ada ulang tahun
                        launchConfetti();
                    }
                });

            // START: FUNGSI JAVASCRIPT CONFETTI SPESIAL (Hanya membuat elemen konfeti)
            function launchConfetti() {
                const container = document.getElementById('confetti-container');
                if (!container) return;

                const colors = ['#ff4081', '#3f51b5', '#ff80ab', '#8c9eff', 'white', '#ffd700', '#00bcd4'];
                const pieceCount = 200;

                for (let i = 0; i < pieceCount; i++) {
                    const piece = document.createElement('div');

                    const size = 6 + Math.random() * 8;
                    const isCircle = Math.random() > 0.5;
                    const width = size;
                    const height = isCircle ? size : (size / 2) + (Math.random() * (size/2));

                    // Kita gunakan style yang sudah memanggil keyframes yang ada di blok <style>
                    // Menggunakan Math.random() untuk variasi durasi dan arah angin
                    const fallDuration = 2 + Math.random() * 2;
                    const windDuration = 1 + Math.random() * 1;
                    const windReverse = Math.random() > 0.5 ? 'reverse' : '';

                    piece.style.cssText = `
                        position: absolute;
                        width: ${width}px;
                        height: ${height}px;
                        background-color: ${colors[Math.floor(Math.random() * colors.length)]};
                        border-radius: ${isCircle ? '50%' : '2px'};
                        left: ${Math.random() * 100}vw;
                        top: -20px;
                        opacity: ${0.7 + Math.random() * 0.3};
                        transform: rotate(${Math.random() * 360}deg);
                        animation: confetti-fall ${fallDuration}s linear infinite,
                                   confetti-wind ${windDuration}s ease-in-out infinite alternate ${windReverse};
                        animation-delay: ${Math.random() * 1.5}s;
                    `;
                    container.appendChild(piece);
                }
            }
            // END: FUNGSI JAVASCRIPT CONFETTI SPESIAL
        });
    </script>
</body>

</html>
