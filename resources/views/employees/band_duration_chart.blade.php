<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Data durasi band</title>
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
    {{-- resources/views/employees/band_duration_chart.blade.php --}}

    @extends('layouts.app')

    @section('content')
        <div class="container">
            <h2>Distribusi Karyawan berdasarkan Durasi Band Posisi per Unit</h2>
            <div class="row">
                <div class="col-md-12">
                    <canvas id="bandDurationChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Ambil data dari route API
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
                        ];

                        const datasets = durationGroups.map((group, index) => ({
                            label: group,
                            data: units.map(unit => aggregatedData[unit][group]),
                            backgroundColor: colors[index % colors.length],
                            borderColor: colors[index % colors.length].replace('0.8', '1'),
                            borderWidth: 1
                        }));

                        const ctx = document.getElementById('bandDurationChart').getContext('2d');
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
                                                    return value; // hanya tampilkan angka bulat
                                                }
                                            }
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
                                    }
                                }
                            }
                        });
                    });
            });
        </script>
    @endsection
</body>

</html>
