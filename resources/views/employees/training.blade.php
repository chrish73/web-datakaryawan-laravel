<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- Judul Halaman Diubah agar Sesuai dengan Konten Training --}}
    <title>Dashboard HR - Data Pelatihan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    {{-- Catatan: Pastikan file style.css yang diunggah tersedia di path yang benar --}}
</head>

<body>
    {{-- Awal Body: Navbar tetap fixed-top dan logika Dark Mode sama --}}
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
                    {{-- START: LINK EVENTS (Diaktifkan saat berada di halaman ini) --}}
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.training_input') ? 'active' : '' }}"
                            href="{{ route('employees.training_input') }}">
                            <i class="bi bi-calendar-event-fill me-1"></i> History Events
                        </a>
                    </li>
                    {{-- END: LINK EVENTS --}}
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
        {{-- START: BIRTHDAY NOTIFICATION AREA (Diambil dari index.blade.php) --}}
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

        {{-- START: KONTEN ASLI TRAINING.BLADE.PHP --}}
        <h3 class="page-title"><i class="bi bi-mortarboard-fill me-2"></i> Input Data Pelatihan Karyawan</h3>

        {{-- BAGIAN BARU: IMPORT DAN SEARCH DIGABUNG --}}
        <div class="row mb-5">
            {{-- BAGIAN 1: FORM IMPORT & EXPORT EXCEL --}}
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h5 class="fw-bold text-muted mb-3"><i class="bi bi-database-up me-2"></i>Opsi Import & Export Data</h5>
                <div class="card shadow-sm p-3 rounded-3 border border-light-subtle">
                    {{-- Form Import Data Pelatihan --}}
                    <p class="small text-muted mb-2">Import Data Pelatihan Massal (Excel)</p>
                    <form action="{{ route('trainings.import_excel') }}" method="POST" enctype="multipart/form-data" class="d-flex mb-3 pb-3 border-bottom">
                        @csrf
                        <input type="file" name="file" class="form-control form-control-sm me-2" required>
                        <button class="btn btn-success btn-sm text-nowrap" type="submit">
                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> Import Excel
                        </button>
                    </form>
                    @error('file')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror

                    {{-- Export Data Pelatihan --}}
                    <p class="small text-muted mb-2 pt-2">Export Data Pelatihan (Excel)</p>
                    {{-- Tautan untuk export yang memanggil route baru --}}
                    <a href="{{ route('trainings.export') }}" class="btn btn-info btn-sm text-white text-nowrap align-self-start">
                        <i class="bi bi-file-earmark-spreadsheet-fill me-1"></i> Export Semua Data
                    </a>
                </div>
            </div>

            {{-- BAGIAN BARU: PENCARIAN DATA --}}
            <div class="col-lg-6">
                <h5 class="fw-bold text-muted mb-3"><i class="bi bi-search me-2"></i>Pencarian Data Karyawan</h5>
                <form method="GET" action="{{ route('employees.training_input') }}" class="d-flex search-form shadow-sm p-3 rounded-3 border border-light-subtle">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari Nama / NIK..."
                            value="{{ $search }}">
                        <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                        {{-- Link Reset Filter --}}
                        <a href="{{ route('employees.training_input') }}" class="btn btn-outline-danger"><i class="bi bi-x-circle"></i></a>
                    </div>
                </form>
            </div>
        </div>


        {{-- Pesan Success/Error dari Controller --}}
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Tampilkan Error Validasi Manual --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                **Gagal menyimpan data karena kesalahan validasi:**
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="alert alert-info border-0 shadow-sm rounded-3">
            <i class="bi bi-info-circle-fill me-2"></i> Klik "Tambah Pelatihan Baru" untuk memasukkan data. Untuk mengedit atau menghapus riwayat, gunakan tombol aksi di sebelah nama pelatihan.
        </div>

        {{-- DAFTAR PELATIHAN UNTUK DATALIST (Memastikan tersedia secara global di halaman) --}}
        <datalist id="datalistOptions">
            @foreach ($uniqueTrainings as $trainingName)
                <option value="{{ $trainingName }}">
            @endforeach
        </datalist>

        {{-- BAGIAN 2: DAFTAR KARYAWAN DAN INPUT MANUAL --}}
        <div class="row">
            @forelse ($employees as $employee) {{-- Menggunakan forelse untuk handling jika data kosong --}}
                <div class="col-sm-12 col-md-6 mb-4">
                    <div class="card employee-card shadow-sm flex-fill"> {{-- Menggunakan class employee-card dari style.css --}}
                        <div class="card-header-custom">
                            <span class="fw-bold">NIK: {{ $employee->nik }}</span>
                        </div>
                        <div class="card-body">
                            <h5 class="card-title">{{ $employee->nama }}</h5>

                            <hr>

                        <h6>Riwayat Pelatihan ({{ $employee->trainings->count() }})</h6>
                        @if ($employee->trainings->isEmpty())
                            <p class="text-secondary small">Belum ada data pelatihan yang tercatat.</p>
                        @else
                            <ul class="list-group list-group-flush mb-3 small">
                                @foreach ($employee->trainings->sortByDesc('tanggal_mulai') as $training)
                                    <li class="list-group-item p-2 d-flex justify-content-between align-items-center"> {{-- Ganti align-items-start menjadi align-items-center agar tombol sejajar --}}
                                        <div>
                                            <strong>{{ $training->nama_pelatihan }}</strong>
                                            <span class="badge bg-{{ $training->status_pelatihan == 'Online' ? 'primary' : 'success' }}">{{ $training->status_pelatihan ?? 'N/A' }}</span>
                                            <br>
                                            <small class="text-muted d-block">
                                                Mulai: {{ $training->tanggal_mulai ? \Carbon\Carbon::parse($training->tanggal_mulai)->format('d/m/Y') : '-' }}
                                                | Selesai: {{ $training->tanggal_selesai ? \Carbon\Carbon::parse($training->tanggal_selesai)->format('d/m/Y') : '-' }}
                                            </small>
                                        </div>

                                        {{-- ACTIONS BUTTONS YANG DIPERBAIKI --}}
                                        <div class="btn-group btn-group-sm ms-2" role="group" aria-label="Aksi Pelatihan">
                                            {{-- Edit Button (Ditambahkan me-1 untuk memberi jarak di kanan) --}}
                                            <button type="button" class="btn btn-warning btn-sm me-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editTrainingModal"
                                                    data-id="{{ $training->id }}"
                                                    data-nama="{{ $training->nama_pelatihan }}"
                                                    data-mulai="{{ $training->tanggal_mulai }}"
                                                    data-selesai="{{ $training->tanggal_selesai }}"
                                                    data-status="{{ $training->status_pelatihan }}"
                                                    title="Edit Pelatihan">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            {{-- Delete Button --}}
                                            <form action="{{ route('trainings.delete', $training->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pelatihan {{ $training->nama_pelatihan }}?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm" title="Hapus Pelatihan">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                            <button class="btn btn-sm btn-info text-white" type="button" data-bs-toggle="collapse" data-bs-target="#formPelatihan-{{ $employee->id }}" aria-expanded="false" aria-controls="formPelatihan-{{ $employee->id }}">
                                Tambah Pelatihan Baru
                            </button>

                            <div class="collapse mt-3" id="formPelatihan-{{ $employee->id }}">
                                <form action="{{ route('employees.store_training') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="employee_id" value="{{ $employee->id }}">

                                    <div id="pelatihan-container-{{ $employee->id }}">
                                        <div class="card card-body mb-2 p-2 training-item">
                                            <div class="row g-2">
                                                <div class="col-12 mb-2">
                                                    {{-- Diubah menjadi input text dengan datalist (combo box) --}}
                                                    <input type="text" name="trainings[0][nama_pelatihan]"
                                                           class="form-control form-control-sm nama-pelatihan-input"
                                                           placeholder="Pilih atau Ketik Nama Pelatihan Baru"
                                                           list="datalistOptions"
                                                           required>
                                                </div>
                                                {{-- BARU: Input ID Event --}}
                                                <div class="col-12 mb-2">
                                                    <label class="form-label small mb-0">ID Event (Otomatis terisi jika ada)</label>
                                                    <input type="text" name="trainings[0][id_event]"
                                                           class="form-control form-control-sm id-event-input"
                                                           placeholder="ID Event (mis: T001)"
                                                           maxlength="255">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-0">Tgl Mulai</label>
                                                    <input type="date" name="trainings[0][tanggal_mulai]" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-5">
                                                    <label class="form-label small mb-0">Tgl Selesai</label>
                                                    <input type="date" name="trainings[0][tanggal_selesai]" class="form-control form-control-sm">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small mb-0">Status</label>
                                                    <select name="trainings[0][status_pelatihan]" class="form-select form-select-sm">
                                                        <option value="" selected disabled>Pilih</option>
                                                        <option value="Online">Online</option>
                                                        <option value="Offline">Offline</option>
                                                    </select>
                                                </div>
                                                <div class="col-12 text-end">
                                                    <button type="button" class="btn btn-danger btn-sm remove-pelatihan disabled">Hapus</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-success mb-3" onclick="addPelatihanInput({{ $employee->id }})">Tambah Input Lain</button>

                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary btn-sm">Simpan Pelatihan</button>
                                    </div>
                                </form>
                            </div>

                        </div>
                    </div>
                </div>
            @empty {{-- Tampilkan pesan jika tidak ada data setelah difilter --}}
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

        {{-- END: KONTEN ASLI TRAINING.BLADE.PHP --}}
    </div>

    {{-- BAGIAN 3: MODAL EDIT PELATIHAN --}}
    <div class="modal fade" id="editTrainingModal" tabindex="-1" aria-labelledby="editTrainingModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editTrainingModalLabel">Edit Pelatihan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editTrainingForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nama_pelatihan" class="form-label">Nama Pelatihan</label>
                            <input type="text" class="form-control" id="edit_nama_pelatihan" name="nama_pelatihan" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="date" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai">
                            </div>
                            <div class="col-md-6">
                                <label for="edit_tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="date" class="form-control" id="edit_tanggal_selesai" name="tanggal_selesai">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_status_pelatihan" class="form-label">Status Pelatihan</label>
                            <select id="edit_status_pelatihan" name="status_pelatihan" class="form-select">
                                <option value="">-- Pilih Status --</option>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- BAGIAN 4: SCRIPT JAVASCRIPT --}}
    {{-- Scripts dari training.blade.php --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    {{-- Menggunakan script Bootstrap dari index.blade.php (5.3.0) --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Logika Dark Mode (Dari index.blade.php)
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

        // Logika Notifikasi Ulang Tahun (Dari index.blade.php)
        document.addEventListener('DOMContentLoaded', function() {
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

        // BARU: Injeksi data mapping dari Controller ke JavaScript
        const trainingEventMap = @json($trainingEventMap ?? []);

        // Logika Tambah & Hapus Pelatihan Input (Dari training.blade.php)
        let trainingIndex = 1;

        function addPelatihanInput(employeeId) {
            const container = $(`#pelatihan-container-${employeeId}`);
            const newIndex = trainingIndex++;
            const newPelatihanHtml = `
                <div class="card card-body mb-2 p-2 training-item">
                    <div class="row g-2">
                        <div class="col-12 mb-2">
                            {{-- Menggunakan input text dan datalist agar bisa input atau pilih --}}
                            <input type="text" name="trainings[${newIndex}][nama_pelatihan]"
                                   class="form-control form-control-sm nama-pelatihan-input"
                                   placeholder="Pilih atau Ketik Nama Pelatihan Baru"
                                   list="datalistOptions"
                                   required>
                        </div>
                        {{-- BARU: Input ID Event --}}
                        <div class="col-12 mb-2">
                            <label class="form-label small mb-0">ID Event (Otomatis terisi jika ada)</label>
                            <input type="text" name="trainings[${newIndex}][id_event]"
                                   class="form-control form-control-sm id-event-input"
                                   placeholder="ID Event (mis: T001)"
                                   maxlength="255">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small mb-0">Tgl Mulai</label>
                            <input type="date" name="trainings[${newIndex}][tanggal_mulai]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small mb-0">Tgl Selesai</label>
                            <input type="date" name="trainings[${newIndex}][tanggal_selesai]" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small mb-0">Status</label>
                            <select name="trainings[${newIndex}][status_pelatihan]" class="form-select form-select-sm">
                                <option value="" selected disabled>Pilih</option>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-danger btn-sm remove-pelatihan">Hapus</button>
                        </div>
                    </div>
                </div>
            `;
            container.append(newPelatihanHtml);

            // Aktifkan semua tombol hapus setelah ada penambahan
            $(`#pelatihan-container-${employeeId} .training-item .remove-pelatihan`).removeClass('disabled');

            // Fokuskan ke input baru
            $(`input[name="trainings[${newIndex}][nama_pelatihan]"]`).focus();
        }

        // BARU: Event listener untuk lookup ID Event
        $(document).on('input', '.nama-pelatihan-input', function() {
            const inputNamaPelatihan = $(this);
            const namaPelatihan = inputNamaPelatihan.val();

            // Cari id_event input yang sesuai dalam 'training-item' yang sama
            const trainingItem = inputNamaPelatihan.closest('.training-item');
            const idEventInput = trainingItem.find('.id-event-input');

            if (trainingEventMap[namaPelatihan]) {
                // KASUS 1: Pelatihan Ditemukan (Otomatis)
                idEventInput.val(trainingEventMap[namaPelatihan]);
                idEventInput.prop('readonly', true); // Jadikan read-only
            } else {
                // KASUS 2: Pelatihan TIDAK DITEMUKAN (Input Manual Event Baru)
                idEventInput.val('');
                idEventInput.prop('readonly', false); // Hapus read-only (bisa diinput manual)
            }
        });

        // Event listener untuk tombol hapus (untuk input yang dinamis)
        $(document).on('click', '.remove-pelatihan', function() {
            const container = $(this).closest('[id^="pelatihan-container-"]');
            $(this).closest('.training-item').remove();

            // Nonaktifkan tombol hapus pada sisa input jika hanya tersisa satu
            if (container.find('.training-item').length === 1) {
                container.find('.training-item .remove-pelatihan').addClass('disabled');
            }
        });

        // Menangani kasus awal, jika hanya 1 input, nonaktifkan tombol hapusnya
        $('.remove-pelatihan').each(function() {
            const container = $(this).closest('[id^="pelatihan-container-"]');
            if (container.find('.training-item').length === 1) {
                $(this).addClass('disabled');
            }
        });

        // Event listener untuk fokus otomatis saat form dibuka
        $(document).on('shown.bs.collapse', '.collapse', function() {
            // Cari input text nama pelatihan pertama di dalam collapse yang baru terbuka
            $(this).find('input.nama-pelatihan-input:first').focus();
        });

        // Trigger lookup pada input awal saat dokumen siap (misalnya, jika data form diisi dari cache/reload)
        $(document).ready(function() {
            $('.nama-pelatihan-input').trigger('input');
        });

        // Logika untuk mengisi Modal Edit
        const editTrainingModal = document.getElementById('editTrainingModal')
        editTrainingModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget
            const trainingId = button.getAttribute('data-id')
            const nama = button.getAttribute('data-nama')
            const mulai = button.getAttribute('data-mulai')
            const selesai = button.getAttribute('data-selesai')
            const status = button.getAttribute('data-status')

            const modalTitle = editTrainingModal.querySelector('.modal-title')
            const modalBodyInputNama = editTrainingModal.querySelector('#edit_nama_pelatihan')
            const modalBodyInputMulai = editTrainingModal.querySelector('#edit_tanggal_mulai')
            const modalBodyInputSelesai = editTrainingModal.querySelector('#edit_tanggal_selesai')
            const modalBodySelectStatus = editTrainingModal.querySelector('#edit_status_pelatihan')
            const modalForm = editTrainingModal.querySelector('#editTrainingForm')

            // Update Modal content
            modalTitle.textContent = `Edit Pelatihan: ${nama}`
            modalBodyInputNama.value = nama
            modalBodyInputMulai.value = mulai
            modalBodyInputSelesai.value = selesai
            // Pastikan dropdown status terpilih
            if(modalBodySelectStatus.querySelector(`option[value="${status}"]`)){
                modalBodySelectStatus.value = status;
            } else {
                modalBodySelectStatus.value = ''; // Reset jika status null/invalid
            }

            // Update Form Action URL
            const updateRoute = "{{ route('trainings.update', ['training' => ':id']) }}";
            modalForm.action = updateRoute.replace(':id', trainingId);
        })
    </script>
</body>

</html>
