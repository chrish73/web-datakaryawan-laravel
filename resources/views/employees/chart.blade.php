<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body>
    @extends('layouts.app') {{-- Sesuaikan dengan layout Anda --}}

    @section('content')
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
            <h2>Jumlah Karyawan berdasarkan Unit dan Band Posisi</h2>
            <div class="row">
                <div class="col-md-12">
                    <canvas id="bandPosisiChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
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
                                            stepSize: 1, // Langkah naik 1
                                            callback: function(value) {
                                                if (Number.isInteger(value)) {
                                                    return value; // Hanya tampilkan bilangan bulat
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
            });
        </script>
    @endsection

</body>

</html>
