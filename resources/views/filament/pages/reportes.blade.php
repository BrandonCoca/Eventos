<x-filament::page>
    <div class="space-y-6">
        <h1 class="text-2xl font-bold">Reportes de Eventos por Año</h1>

        @foreach ($reportData as $anio => $eventos)
            <div class="space-y-4">
                <h2 class="text-xl font-semibold text-gray-800 border-b pb-2">Año {{ $anio }}</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach ($eventos as $evento)
                        @php
                            $chartId = 'chart-' . $evento['id'] . '-' . $anio;
                            $porcentajeAsistencia = $evento['total_inscritos'] > 0 
                                ? round(($evento['total_asistentes'] / $evento['total_inscritos']) * 100, 2) 
                                : 0;
                            $ausentes = max(0, $evento['total_inscritos'] - $evento['total_asistentes']);
                        @endphp

                        <div class="bg-white rounded-lg shadow-md p-6 border">
                            <div class="mb-3">
                                <h3 class="text-lg font-semibold text-gray-900" style="color: #111827 !important;">{{ $evento['nombre'] }}</h3>
                                @if(isset($evento['tipo']) && $evento['tipo'])
                                <span class="inline-block px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full mt-1" style="color: #1e40af !important; background-color: #dbeafe !important;">
                                        {{ ucfirst($evento['tipo']) }}
                                    </span>
                                @endif
                            </div>
                            
                            @if(isset($evento['fechainicio']) && isset($evento['fechafin']))
                                <div class="mb-3 text-sm text-gray-500">
                                    <span class="font-medium">Fechas:</span><br>
                                    {{ \Carbon\Carbon::parse($evento['fechainicio'])->format('d/m/Y') }}
                                    @if($evento['fechainicio'] !== $evento['fechafin'])
                                        - {{ \Carbon\Carbon::parse($evento['fechafin'])->format('d/m/Y') }}
                                    @endif
                                </div>
                            @endif
                            
                            <div class="mb-4 text-sm text-gray-600 space-y-1">
                                <div class="flex justify-between">
                                    <span>Total Inscritos:</span>
                                    <span class="font-medium">{{ $evento['total_inscritos'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Asistieron:</span>
                                    <span class="font-medium text-green-600">{{ $evento['total_asistentes'] }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>No Asistieron:</span>
                                    <span class="font-medium text-red-600">{{ $ausentes }}</span>
                                </div>
                                <div class="flex justify-between border-t pt-1">
                                    <span>% Asistencia:</span>
                                    <span class="font-bold text-blue-600">{{ $porcentajeAsistencia }}%</span>
                                </div>
                            </div>

                            @if($evento['total_inscritos'] > 0)
                                <div class="relative" style="height: 200px;">
                                    <canvas id="{{ $chartId }}"></canvas>
                                </div>
                            @else
                                <div class="flex items-center justify-center h-48 bg-gray-50 rounded">
                                    <p class="text-gray-500">Sin inscripciones</p>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if(empty($reportData) || $reportData->isEmpty())
            <div class="text-center py-12">
                <p class="text-gray-500 text-lg">No hay eventos registrados</p>
            </div>
        @endif
    </div>

    {{-- Carga Chart.js primero --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // Esperamos a que Chart.js esté completamente cargado
        function initializeCharts() {
            if (typeof Chart === 'undefined') {
                setTimeout(initializeCharts, 100);
                return;
            }

            @foreach ($reportData as $anio => $eventos)
                @foreach ($eventos as $evento)
                    @php
                        $chartId = 'chart-' . $evento['id'] . '-' . $anio;
                        $porcentajeAsistencia = $evento['total_inscritos'] > 0 
                            ? round(($evento['total_asistentes'] / $evento['total_inscritos']) * 100, 2) 
                            : 0;
                        $ausentes = max(0, $evento['total_inscritos'] - $evento['total_asistentes']);
                    @endphp

                    @if($evento['total_inscritos'] > 0)
                        (() => {
                            const ctx = document.getElementById('{{ $chartId }}');
                            if (ctx) {
                                new Chart(ctx, {
                                    type: 'pie',
                                    data: {
                                        labels: ['Asistieron', 'No Asistieron'],
                                        datasets: [{
                                            data: [{{ $evento['total_asistentes'] }}, {{ $ausentes }}],
                                            backgroundColor: [
                                                '#10b981', // Verde para asistentes
                                                '#ef4444'  // Rojo para ausentes
                                            ],
                                            borderWidth: 2,
                                            borderColor: '#ffffff'
                                        }]
                                    },
                                    options: {
                                        responsive: true,
                                        maintainAspectRatio: false,
                                        plugins: {
                                            legend: {
                                                position: 'bottom',
                                                labels: {
                                                    padding: 15,
                                                    font: {
                                                        size: 12
                                                    }
                                                }
                                            },
                                            tooltip: {
                                                callbacks: {
                                                    label: function(context) {
                                                        const total = {{ $evento['total_inscritos'] }};
                                                        const value = context.parsed;
                                                        const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                                                        return context.label + ': ' + value + ' (' + percentage + '%)';
                                                    }
                                                }
                                            }
                                        },
                                        animation: {
                                            animateScale: true,
                                            duration: 1000
                                        }
                                    }
                                });
                            }
                        })();
                    @endif
                @endforeach
            @endforeach
        }

        // Inicializar cuando el DOM esté listo
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeCharts);
        } else {
            initializeCharts();
        }

        // También inicializar en el evento de Livewire si estás usando componentes dinámicos
        document.addEventListener('livewire:navigated', initializeCharts);
    </script>

    {{-- Estilos adicionales --}}
    <style>
        .chart-container {
            position: relative;
            height: 200px;
            width: 100%;
        }
        
        /* Mejoras visuales para las tarjetas */
        .bg-white {
            transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }
        
        .bg-white:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
</x-filament::page>