<!DOCTYPE html>
<html>
<head>
    <title>Data Karyawan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h3 class="mb-4">Data Karyawan</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form action="{{ route('employees.import') }}" method="POST" enctype="multipart/form-data" class="mb-3">
        @csrf
        <div class="row g-2">
            <div class="col-md-6">
                <input type="file" name="file" accept=".csv,.xlsx,.xls" class="form-control" required>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary">Import data</button>
            </div>
        </div>
    </form>

    <form method="GET" action="{{ route('employees.index') }}" class="mb-3">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Cari nama..." value="{{ $search }}">
            <button class="btn btn-secondary" type="submit">Cari</button>
        </div>
    </form>

    <table class="table table-bordered">
        <thead class="table-dark">
            <tr>
                <th>NIK</th>
                <th>Nama</th>
                <th>Unit</th>
                <th>Lama Band Posisi</th>
                <th>Nilai Kinerja</th>
                <th>Nilai Kompetensi</th>
                <th>Nilai Behavior</th>
                <th>Talent Charter</th>
                <th>Eligible</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employees as $employee)
                <tr>
                    <td>{{ $employee->nik }}</td>
                    <td>{{ $employee->nama }}</td>
                    <td>{{ $employee->nama_unit }}</td>
                    <td>{{ $employee->lama_band_posisi }}</td>
                    <td>{{ $employee->nilai_kinerja }}</td>
                    <td>{{ $employee->nilai_kompetensi }}</td>
                    <td>{{ $employee->nilai_behavior }}</td>
                    <td>{{ $employee->tc }}</td>
                    <td>{{ $employee->status}}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">Tidak ada data</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $employees->links() }}
</div>
</body>
</html>
