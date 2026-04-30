<div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
    <p class="text-gray-600 text-center w-full mb-3">الطلبات حسب القسم</p>
    <canvas id="orders-by-category-chart"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('orders-by-category-chart');

        const data = {
            labels: [
                @foreach ($ordersByCategory as $category)
                    '{{ $category->name }}'@if (!$loop->last),@endif
                @endforeach
            ],
            datasets: [{
                label: 'عدد الطلبات',
                data: [
                    @foreach ($ordersByCategory as $category)
                        {{ $category->total_orders ?? 0 }}@if (!$loop->last),@endif
                    @endforeach
                ],
                backgroundColor: window.chart_light_colors
            }]
        };

        const config = {
            type: 'bar',
            data: data,
            options: {
                plugins: {
                    datalabels: { display: false },
                    legend: { display: true }
                },
                scales: {
                    x: {
                        ticks: { display: true, font: { size: 14 } },
                        grid: { display: true },
                    },
                    y: {
                        ticks: { display: true },
                        grid: { display: true }
                    }
                }
            }
        };

        new Chart(ctx, config);
    });
</script>
