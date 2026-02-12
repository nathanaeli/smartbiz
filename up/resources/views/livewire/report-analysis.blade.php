<div>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Report Analysis</h4>
                        <p class="card-subtitle">Graphical presentation of duka performance data</p>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Sales by Duka</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="salesChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card">
                                    <div class="card-header">
                                        <h5>Stock Levels by Duka</h5>
                                    </div>
                                    <div class="card-body">
                                        <canvas id="stockChart" width="400" height="300"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('livewire:loaded', function () {
            const salesCtx = document.getElementById('salesChart').getContext('2d');
            const stockCtx = document.getElementById('stockChart').getContext('2d');

            const salesData = @json($salesData);
            const stockData = @json($stockData);
            const dukaNames = @json($dukaNames);

            new Chart(salesCtx, {
                type: 'bar',
                data: {
                    labels: dukaNames,
                    datasets: [{
                        label: 'Total Sales',
                        data: salesData,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            new Chart(stockCtx, {
                type: 'pie',
                data: {
                    labels: dukaNames,
                    datasets: [{
                        label: 'Stock Quantity',
                        data: stockData,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.5)',
                            'rgba(54, 162, 235, 0.5)',
                            'rgba(255, 205, 86, 0.5)',
                            'rgba(75, 192, 192, 0.5)',
                            'rgba(153, 102, 255, 0.5)'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 205, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });
        });
    </script>
</div>
