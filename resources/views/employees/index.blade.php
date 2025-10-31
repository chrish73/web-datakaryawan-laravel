<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('employees.index') }}">HR Dashboard</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.index') ? 'active' : '' }}"
                            href="{{ route('employees.index') }}">Data Karyawan</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.chart') ? 'active' : '' }}"
                            href="{{ route('employees.chart') }}">Data Band Posisi</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Request::routeIs('employees.age_group_chart') ? 'active' : '' }}"
                            href="{{ route('employees.age_group_chart') }}">Data Kelompok Usia</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('employees/band') ? 'active' : '' }}"
                            href="/employees/band">Band Posisi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h3>Daftar Karyawan</h3> {{-- Notifikasi (Alerts) --}}
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

        <div class="row mb-4 align-items-center">
            {{-- Form Import Data --}}
            <div class="col-md-5 d-flex gap-2">
                <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex w-100 gap-2">
                    @csrf
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control" required>
                    <button type="submit" class="btn btn-primary text-nowrap" id="importButton">Import Data</button>
                </form>
            </div>

            {{-- Form Import Data Ulang Tahun --}}
            <div class="col-md-5 d-flex gap-2">
                <form action="{{ route('employees.import_birthday') }}" method="POST" enctype="multipart/form-data"
                    class="d-flex w-100 gap-2">
                    @csrf
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control" required>
                    <button type="submit" class="btn btn-info text-nowrap" id="importBirthdayButton">
                        <i class="bi bi-cake"></i> Import Ulang Tahun
                    </button>
                </form>
            </div>

            {{-- Form Pencarian --}}
            <div class="col-md-7">
                <form method="GET" action="{{ route('employees.index') }}" class="d-flex">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Masukan Nama..."
                            value="{{ $search }}">
                        <button class="btn btn-outline-secondary" type="submit">Cari</button>
                        <a href="{{ route('employees.index') }}" class="btn btn-outline-danger">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tampilan Data Karyawan dalam bentuk Card --}}
        <div class="row">
            @forelse($employees as $employee)
                @php
                    $statusClass = '';
                    $statusTextClass = '';
                    $status = strtolower($employee->status_eligibility);
                    if ($status === 'eligible') {
                        $statusTextClass = 'card-status-eligible';
                    } elseif ($status === 'not eligible') {
                        $statusTextClass = 'card-status-not-eligible';
                    }
                @endphp
                <div class="col-md-6 col-lg-4">
                    <div class="card employee-card">
                        <div class="card-header-custom">
                            <span>NIK: {{ $employee->nik }}</span>
                            <span class="{{ $statusTextClass }}">{{ $employee->status_eligibility }}</span>
                        </div>
                        <div class="card-body-custom">
                            <div class="card-item">
                                <span class="card-item-label">Nama</span>
                                <span class="card-item-value">{{ $employee->nama }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Unit</span>
                                <span class="card-item-value">{{ $employee->nama_unit }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Lama Band Posisi</span>
                                <span class="card-item-value">{{ $employee->lama_band_posisi }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Nilai Kinerja</span>
                                <span class="card-item-value">{{ $employee->nilai_kinerja }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Nilai Kompetensi</span>
                                <span class="card-item-value">{{ $employee->nilai_kompetensi }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Nilai Behavior</span>
                                <span class="card-item-value">{{ $employee->nilai_behavior }}</span>
                            </div>
                            <div class="card-item">
                                <span class="card-item-label">Talent Charter</span>
                                <span class="card-item-value" style="font-size: 0.85em;">{{ $employee->tc }}</span>
                            </div>
                            {{-- Contoh Aksi, bisa disesuaikan nanti --}}

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info text-center py-4">
                        <i class="bi bi-info-circle fs-4"></i> Tidak ada data karyawan yang ditemukan.
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
            const importButton = document.getElementById('importButton');
            const importForm = importButton ? importButton.closest('form') : null; // Cek keberadaan tombol

            if (importForm) {
                // Mencegah double-click pada tombol Import
                importForm.addEventListener('submit', function() {
                    // Cek jika tombol belum disabled
                    if (!importButton.disabled) {
                        importButton.disabled = true;
                        importButton.innerHTML =
                            '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Loading...';
                        // Izinkan form untuk disubmit
                    }
                });
            }
        });
    </script>
</body>

</html>
