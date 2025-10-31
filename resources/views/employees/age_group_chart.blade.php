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
    @extends('layouts.app')

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
            <h2>Distribusi Karyawan berdasarkan Kelompok Usia per Unit</h2>
            <div class="row">
                <div class="col-md-12">
                    <canvas id="ageGroupChart"></canvas>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Ambil data dari route API
                fetch('{{ route('employees.age_group_chart_data') }}')
                    .then(response => response.json())
                    .then(data => {
                        const ageGroups = data.age_groups;
                        const units = data.units;
                        const aggregatedData = data.data;

                        // Warna untuk setiap kelompok usia
                        const colors = [
                            'rgba(54, 162, 235, 0.8)', // < 30
                            'rgba(75, 192, 192, 0.8)', // 30-40
                            'rgba(255, 206, 86, 0.8)', // 41-50
                            'rgba(255, 99, 132, 0.8)', // 51-55
                        ];

                        const datasets = ageGroups.map((group, index) => ({
                            label: group,
                            data: units.map(unit => aggregatedData[unit][group]),
                            backgroundColor: colors[index % colors.length],
                            borderColor: colors[index % colors.length].replace('0.8', '1'),
                            borderWidth: 1
                        }));

                        const ctx = document.getElementById('ageGroupChart').getContext('2d');
                        new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: units, // Unit pada sumbu X
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
                                                    return value; // hanya tampilkan bilangan bulat
                                                }
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    title: {
                                        display: true,
                                        text: 'Distribusi Kelompok Usia per Unit'
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
