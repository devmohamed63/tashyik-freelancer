<div class="rounded-2xl border border-gray-200 bg-white p-5 md:p-6">
    <p class="text-gray-600 text-center w-full mb-3">{{ __('ui.categories_chart_title') }}</p>
    <canvas id="categories-chart"></canvas>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('categories-chart');

        const data = {
            labels: [
                @foreach ($serviceProviderCategories as $categoryLabel)
                    '{{ $categoryLabel->name }}'
                    @if (!$loop->last)
                        ,
                    @endif
                @endforeach
            ],
            datasets: [{
                label: `{{ __('ui.service_providers') }}`,
                data: [
                    @foreach ($serviceProviderCategories as $categoryData)
                        {{ $categoryData->service_providers_count }}
                        @if (!$loop->last)
                            ,
                        @endif
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
                        font: {
                            size: 14,
                        }
                    },
                    legend: {
                        labels: {
                            font: {
                                size: 14,
                            }
                        }
                    }
                },
            }
        };

        new Chart(ctx, config);
    });
</script>
