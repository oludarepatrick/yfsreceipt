@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Overview</h1>
                    <h4 class="h3">Welcome, {{ Auth::guard('admin')->user()->name }}</h4>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body">
                                <h6 class="card-title text-primary">Active Students</h6>
                                <h2 class="mt-3 mb-0">{{ number_format($activeStudents) }}</h2>
                                <p class="text-success mb-0"><i class="bi bi-arrow-up"></i> Paid: {{ $collectionPercentage }}%</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-warning">
                            <div class="card-body">
                                <h6 class="card-title text-success">Active Staff</h6>
                                <h2 class="mt-3 mb-0">{{ number_format($activeStaff) }}</h2>
                                <h6 class="card-title text-warning">Total Salary</h6>
                                <h6 class="mt-3 mb-0">#{{ number_format($totalSalary, 2) }}</h6>
                                <p class="text-danger mb-0"><i class="bi bi-arrow-down"></i> </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-success">
                            <div class="card-body">
                                <h6 class="card-title text-success">Miscellaneous Income</h6>
                                <h6 class="mt-3 mb-0">Total: ₦{{ number_format($uniform_stationeries, 2) }}</h6>
                                <h6 class="mt-3 mb-0">Uniform: ₦{{ number_format($uniform_only, 2) }}</h6>
                                <h6 class="mt-3 mb-0">Stationeries: ₦{{ number_format($stationeries_only, 2) }}</h6>
                                <p class="text-success mb-0"><i class="bi bi-arrow-up"></i> </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card h-100 border-info">
                            <div class="card-body">
                                <h6 class="card-title text-info">Total Revenue</h6>
                                <h6 class="mt-3 mb-0">Expected Fees: ₦{{ number_format($totalExpected, 2) }}</h6>
                                <h6 class="mt-3 mb-0">TotalPaid: #{{ number_format($totalRevenue, 2) }}</h6>
                                <h6 class="mt-3 mb-0">Outstanding: ₦{{ number_format($outstandingBalance, 2) }}</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-8">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Fee Collection Trend</h5>
                                <div class="row">
                                    <div class="col-md-8 custom-pie">
                                        <canvas id="revenueChart" height="300"></canvas>
                                    </div>
                                    <div class="col-md-8">
                                        <canvas id="termPieChart" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card h-100">
                            <div class="card-body">
                                <h5 class="card-title">Recent Payments</h5>
                                <div class="list-group list-group-flush">
                                    @foreach($recentInvoices as $invoice)
                                        <div class="list-group-item">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">{{ $invoice->student->firstname ?? 'Unknown' }}</h6>
                                                <small>{{ $invoice->created_at->diffForHumans() }}</small>
                                            </div>
                                            <p class="mb-1">Term {{ ucfirst($invoice->term) }} Fees</p>
                                            <small class="text-success">#{{ number_format($invoice->amount_paid, 2) }}</small>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const pieCtx = document.getElementById('termPieChart').getContext('2d');
    new Chart(pieCtx, {
        type: 'pie',
        data: {
            labels: {!! json_encode(array_keys($chartData)) !!},
            datasets: [{
                data: {!! json_encode(array_values($chartData)) !!},
                backgroundColor: ['#0d6efd', '#ffc107', '#198754']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
</script>

</body>

<style>
    .custom-pie {
        margin-left: 30px !important;
        margin-top: -500px !important;
    }
</style>
</html>
