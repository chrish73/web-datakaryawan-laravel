<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard HR - Rekap Pelatihan Event</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    {{-- Custom CSS untuk Loader/Spin --}}
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
    {{-- Awal Body: Navbar --}}
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
                    {{-- START: LINK EVENTS (Diubah menjadi 2 link terpisah) --}}
                    <li class="nav-item">
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
                    {{-- END: LINK EVENTS --}}
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

    <div class="container main-content">
        {{-- START: KONTEN REKAP EVENT --}}
        <h3 class="page-title"><i class="bi bi-bar-chart-fill me-2"></i> Rekap Partisipasi Pelatihan per Event</h3>

        {{-- Kembali ke Halaman Input --}}
        <a href="{{ route('employees.training_input') }}" class="btn btn-outline-secondary btn-sm mb-4">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Input Pelatihan
        </a>

        {{-- START: Search Bar --}}
        <div class="mb-4">
            <input type="text" id="searchInput" class="form-control form-control-lg" placeholder="Cari berdasarkan Nama Pelatihan atau ID Event...">
        </div>
        {{-- END: Search Bar --}}

        <div class="card shadow-sm rounded-3 border border-light-subtle">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Nama Pelatihan (Event)</th>
                                <th>ID Event</th>
                                <th class="text-center">Total Partisipan Unik</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="trainingSummaryTableBody"> {{-- Tambahkan ID untuk JavaScript --}}
                            @forelse ($summaries as $summary)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>
                                        <strong>{{ $summary->nama_pelatihan }}</strong>
                                    </td>
                                    <td>
                                        {{ $summary->id_event ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary fs-6">{{ $summary->total_participants }}</span>
                                    </td>
                                    <td class="text-center">
                                        {{-- Diubah menjadi Button untuk memicu Modal --}}
                                        <button type="button" class="btn btn-sm btn-info text-white btn-detail-event"
                                            data-bs-toggle="modal" data-bs-target="#eventDetailModal"
                                            data-event-name="{{ $summary->nama_pelatihan }}">
                                            <i class="bi bi-eye-fill me-1"></i> Detail
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4" id="noResultsRow">
                                        <i class="bi bi-info-circle fs-5 me-2"></i> Tidak ada data pelatihan yang tercatat untuk membuat rekap event.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- END: KONTEN REKAP EVENT --}}
    </div>

    {{-- START: MODAL REKAP EVENT DETAIL --}}
    <div class="modal fade" id="eventDetailModal" tabindex="-1" aria-labelledby="eventDetailModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="eventDetailModalLabel"><i class="bi bi-person-lines-fill me-2"></i> Detail Partisipan Event: <span id="modalEventName"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    {{-- NAV TABS UNTUK ONLINE DAN OFFLINE --}}
                    <ul class="nav nav-tabs mb-3" id="trainingStatusTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="online-tab" data-bs-toggle="tab"
                                data-bs-target="#online-pane" type="button" role="tab"
                                aria-controls="online-pane" aria-selected="true">
                                <i class="bi bi-globe me-1"></i> Peserta Online (<span id="onlineCount">0</span>)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="offline-tab" data-bs-toggle="tab"
                                data-bs-target="#offline-pane" type="button" role="tab"
                                aria-controls="offline-pane" aria-selected="false">
                                <i class="bi bi-geo-alt-fill me-1"></i> Peserta Offline (<span
                                    id="offlineCount">0</span>)
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="trainingStatusTabsContent">
                        {{-- TAB ONLINE --}}
                        <div class="tab-pane fade show active" id="online-pane" role="tabpanel"
                            aria-labelledby="online-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>#</th>
                                            <th>NIK</th>
                                            <th>Nama Karyawan</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody id="onlineParticipantsBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- TAB OFFLINE --}}
                        <div class="tab-pane fade" id="offline-pane" role="tabpanel"
                            aria-labelledby="offline-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-hover table-striped table-sm">
                                    <thead class="table-success">
                                        <tr>
                                            <th>#</th>
                                            <th>NIK</th>
                                            <th>Nama Karyawan</th>
                                            <th>Tanggal Mulai</th>
                                            <th>Tanggal Selesai</th>
                                        </tr>
                                    </thead>
                                    <tbody id="offlineParticipantsBody">
                                        <tr>
                                            <td colspan="5" class="text-center text-muted">Memuat data...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    {{-- END: MODAL REKAP EVENT DETAIL --}}

    {{-- SCRIPTS BAWAAN (Dark Mode & Notification) --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Logika Dark Mode (Dari training.blade.php)
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

        // Logika Notifikasi Ulang Tahun (Dari training.blade.php)
        document.addEventListener('DOMContentLoaded', function() {
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
        });

        // LOGIKA BARU: AJAX untuk Modal Detail Event & Search
        $(document).ready(function() {
            const detailModal = document.getElementById('eventDetailModal');

            // === LOGIKA SEARCH BAR ===
            $('#searchInput').on('keyup', function() {
                const searchText = $(this).val().toLowerCase();
                const tableBody = $('#trainingSummaryTableBody');
                let foundResults = 0;

                // Cari semua baris data (mengabaikan baris kosong yang mungkin ada)
                const dataRows = tableBody.find('tr').filter(function() {
                    return $(this).find('td').length === 5; // Asumsi baris data memiliki 5 kolom
                });

                dataRows.each(function() {
                    const row = $(this);
                    // Ambil teks dari kolom Nama Pelatihan (index 1) dan ID Event (index 2)
                    // .eq(0) adalah kolom #, .eq(1) adalah Nama Pelatihan, .eq(2) adalah ID Event
                    const eventName = row.find('td').eq(1).text().toLowerCase();
                    const eventId = row.find('td').eq(2).text().toLowerCase();

                    // Cek apakah ada kecocokan
                    if (eventName.includes(searchText) || eventId.includes(searchText)) {
                        row.show();
                        foundResults++;
                    } else {
                        row.hide();
                    }
                });

                // Jika tidak ada hasil dan tabel tidak kosong, tampilkan pesan 'tidak ada data'
                const noResultsRow = $('#noResultsRow');

                if (foundResults === 0 && tableBody.find('tr').length > 0) {
                    // Cek apakah baris "Tidak ada data" (yang mungkin tersembunyi) ada di DOM
                    if (noResultsRow.length) {
                        noResultsRow.show();
                        noResultsRow.find('td').html('<i class="bi bi-exclamation-circle fs-5 me-2"></i> Tidak ada pelatihan yang cocok dengan pencarian.');
                    } else {
                        // Jika baris awal 'Tidak ada data' dihapus, tambahkan baris sementara
                        // Catatan: Ini harusnya tidak terjadi jika ada data awal
                        tableBody.append(
                            `<tr><td colspan="5" class="text-center text-muted py-4" id="noSearchResults"><i class="bi bi-exclamation-circle fs-5 me-2"></i> Tidak ada pelatihan yang cocok dengan pencarian.</td></tr>`
                        );
                    }
                } else if (noResultsRow.length) {
                    // Jika ada hasil, sembunyikan baris 'Tidak ada data' (kecuali jika itu adalah pesan 'tidak ada data awal')
                    noResultsRow.hide();
                }
            });
            // === END LOGIKA SEARCH BAR ===

            // ... (Kode untuk Modal Detail Event) ...

            detailModal.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const eventName = button.getAttribute('data-event-name');

                const modalTitle = $('#modalEventName');
                const onlineBody = $('#onlineParticipantsBody');
                const offlineBody = $('#offlineParticipantsBody');
                const onlineCountSpan = $('#onlineCount');
                const offlineCountSpan = $('#offlineCount');

                // 1. Update Judul Modal dan tampilkan Loading
                modalTitle.text(eventName);
                const loadingHtml = `<tr><td colspan="5" class="text-center"><i class="bi bi-arrow-clockwise spin me-2"></i>Memuat data...</td></tr>`;
                onlineBody.html(loadingHtml);
                offlineBody.html(loadingHtml);
                onlineCountSpan.text('0');
                offlineCountSpan.text('0');

                // 2. Fungsi helper untuk mengisi tabel
                const fillTable = (body, participants, status) => {
                    body.empty();
                    if (participants.length > 0) {
                        let html = '';
                        participants.forEach((p, index) => {
                            // Helper untuk format tanggal
                            const formatDate = (dateString) => {
                                if (!dateString) return '-';
                                try {
                                    return new Date(dateString).toLocaleDateString('id-ID', {
                                        year: 'numeric',
                                        month: '2-digit',
                                        day: '2-digit'
                                    });
                                } catch (e) {
                                    return '-';
                                }
                            };

                            html += `
                                <tr>
                                    <td>${index + 1}</td>
                                    <td>${p.nik}</td>
                                    <td>${p.nama}</td>
                                    <td>${formatDate(p.mulai)}</td>
                                    <td>${formatDate(p.selesai)}</td>
                                </tr>
                            `;
                        });
                        body.html(html);
                    } else {
                        body.html(
                            `<tr><td colspan="5" class="text-center text-muted">Tidak ada peserta ${status} untuk event ini.</td></tr>`
                            );
                    }
                };

                // 3. Lakukan Panggilan AJAX ke Controller
                $.ajax({
                    url: '{{ route('trainings.summary_by_event') }}',
                    method: 'GET',
                    data: {
                        event_name: eventName
                    },
                    success: function(data) {
                        // Data yang diterima adalah objek { online: [...], offline: [...] }

                        // Update UI
                        fillTable(onlineBody, data.online, 'Online');
                        onlineCountSpan.text(data.online.length);

                        fillTable(offlineBody, data.offline, 'Offline');
                        offlineCountSpan.text(data.offline.length);

                        // Atur tab yang aktif (pilih yang ada data atau default ke online)
                        const tabToShow = (data.online.length > 0) ? document.getElementById(
                            'online-tab') : document.getElementById('offline-tab');
                        if (tabToShow) {
                            // Menggunakan fungsi Bootstrap untuk mengaktifkan tab
                            const tabInstance = bootstrap.Tab.getInstance(tabToShow);
                            if (tabInstance) {
                                tabInstance.show();
                            }
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Terjadi kesalahan saat mengambil data detail event.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = 'Error: ' + xhr.responseJSON.message;
                        } else if (xhr.statusText) {
                            errorMessage = 'Error: ' + xhr.statusText;
                        }
                        onlineBody.html(
                            `<tr><td colspan="5" class="text-center text-danger">${errorMessage}</td></tr>`
                            );
                        offlineBody.html(
                            `<tr><td colspan="5" class="text-center text-danger">${errorMessage}</td></tr>`
                            );
                        onlineCountSpan.text('0');
                        offlineCountSpan.text('0');
                    }
                });
            });

            // Reset tab ke online saat modal ditutup
            detailModal.addEventListener('hidden.bs.modal', function() {
                // Pastikan tab online yang aktif saat ditutup (untuk kali berikutnya)
                bootstrap.Tab.getInstance(document.getElementById('online-tab')).show();
            });
        });
        // END LOGIKA BARU
    </script>
</body>

</html>
