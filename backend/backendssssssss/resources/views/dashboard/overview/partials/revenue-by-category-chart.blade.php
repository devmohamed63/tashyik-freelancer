<div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
    <p class="text-gray-600 text-center w-full mb-3">الإيرادات حسب القسم</p>
    <canvas id="revenue-by-category-chart"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('revenue-by-category-chart');

        const data = {
            labels: [
                @foreach ($revenueByCategory as $category)
                    '{{ $category->name }}'@if (!$loop->last),@endif
                @endforeach
            ],
            datasets: [{
                label: 'الإيرادات',
                data: [
                    @foreach ($revenueByCategory as $category)
                        {{ $category->revenue ?? 0 }}@if (!$loop->last),@endif
                    @endforeach
                ],
                backgroundColor: window.chart_colors
            }]
        };

        const config = {
            type: 'polarArea',
            data: data,
            options: {
                plugins: {
                    datalabels: {
                        formatter: (value, context) => {
                            return context.chart.data.labels[context.dataIndex];
                        },
                        color: 'white',
                        font: { size: 14 }
                    },
                    legend: {
                        labels: { font: { size: 14 } }
                    }
                },
            }
        };

        new Chart(ctx, config);
    });
</script>
