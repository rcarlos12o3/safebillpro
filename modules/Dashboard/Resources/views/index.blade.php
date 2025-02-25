@extends('tenant.layouts.app')

@section('content')
@php
$a = $vc_modules;
@endphp
    <div class="card welcome-component" style="display: none;">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="row justify-content-center align-items-center h-100">
                        <div>
                            <div class="col-12 text-center mb-2">
                                <span class="h1 font-weight-bold">Hola</span>
                            </div>
                            <div class="col-12 text-center">
                                <span class="h5">¿Qué deseas hacer hoy?</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-9">
                    @if(in_array('documents', $vc_modules))
                        <div class="card shadow-sm border-top my-2">
                            <div class="card-header py-1 font-weight-bold">
                                Facturación
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    @if(auth()->user()->type != 'integrator' && $vc_company->soap_type_id != '03')
                                        @if(in_array('documents', $vc_modules))
                                            @if(in_array('new_document', $vc_module_levels))
                                                <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                                    <a href="{{route('tenant.documents.create')}}" class="w-100 h-100 d-inline-block border bg-danger text-light rounded p-1">
                                                        <div class="h-100 d-flex justify-content-center align-items-center">
                                                            <div>
                                                                <div class="d-block mb-2">Nuevo CPE</div>
                                                                <i class="fas fa-file-invoice" style="font-size: 20px;"></i>
                                                            </div>
                                                        </div>
                                                    </a>
                                                </div>
                                            @endif
                                        @endif
                                    @endif

                                    @if(in_array('sale_notes', $vc_module_levels))
                                        <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                            <a href="{{route('tenant.sale_notes.create')}}" class="w-100 h-100 d-inline-block border bg-danger text-light rounded p-1">
                                                <div class="h-100 d-flex justify-content-center align-items-center">
                                                    <div>
                                                        <div class="d-block mb-2">Nueva nota de venta</div>
                                                        <i class="fas fa-receipt" style="font-size: 20px;"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                    
                                    @if(in_array('quotations', $vc_module_levels))
                                        <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                            <a href="{{route('tenant.quotations.create')}}" class="w-100 h-100 d-inline-block border bg-danger text-light rounded p-1">
                                                <div class="h-100 d-flex justify-content-center align-items-center">
                                                    <div>
                                                        <div class="d-block mb-2">Nueva cotización</div>
                                                        <i class="fas fa-file" style="font-size: 20px;"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif

                                    @if(in_array('documents', $vc_modules) && $vc_company->soap_type_id != '03')
                                        @if(in_array('list_document', $vc_module_levels))
                                            <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                                <a href="{{route('tenant.documents.index')}}" class="w-100 h-100 d-inline-block border bg-danger text-light rounded p-1">
                                                    <div class="h-100 d-flex justify-content-center align-items-center">
                                                        <div>
                                                            <div class="d-block mb-2">Búsqueda de documentos</div>
                                                            <i class="fas fa-search" style="font-size: 20px;"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(in_array('items', $vc_modules))
                        <div class="card shadow-sm border-top my-2">
                            <div class="card-header py-1 font-weight-bold">
                                Productos y servicios
                            </div>
                            <div class="card-body py-2">
                                <div class="row">
                                    @if(in_array('items', $vc_module_levels))
                                        <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                            <a href="{{route('tenant.items.index')}}" class="w-100 h-100 d-inline-block border bg-warning text-light rounded p-1">
                                                <div class="h-100 d-flex justify-content-center align-items-center">
                                                    <div>
                                                        <div class="d-block mb-2">Ver lista de productos</div>
                                                        <i class="fas fa-box" style="font-size: 20px;"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                    
                                    @if(in_array('items_services', $vc_module_levels))
                                        <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                            <a href="{{route('tenant.services')}}" class="w-100 h-100 d-inline-block border bg-warning text-light rounded p-1">
                                                <div class="h-100 d-flex justify-content-center align-items-center">
                                                    <div>
                                                        <div class="d-block mb-2">Ver lista de servicios</div>
                                                        <i class="fas fa-list" style="font-size: 20px;"></i>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @endif
                                    
                                    @if(in_array('inventory', $vc_modules))
                                        @if(in_array('inventory', $vc_module_levels))
                                            <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                                <a href="{{route('inventory.index')}}" class="w-100 h-100 d-inline-block border bg-warning text-light rounded p-1">
                                                    <div class="h-100 d-flex justify-content-center align-items-center">
                                                        <div>
                                                            <div class="d-block mb-2">Ver inventarios</div>
                                                            <i class="fas fa-warehouse" style="font-size: 20px;"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->type != 'integrator')
                        @if(in_array('pos', $vc_modules))
                            <div class="card shadow-sm border-top my-2">
                                <div class="card-header py-1 font-weight-bold">
                                    Punto de venta
                                </div>
                                <div class="card-body py-2">
                                    <div class="row">
                                        @if(in_array('pos', $vc_module_levels))
                                            <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                                <a href="{{route('tenant.pos.index')}}" class="w-100 h-100 d-inline-block border bg-primary text-light rounded p-1">
                                                    <div class="h-100 d-flex justify-content-center align-items-center">
                                                        <div>
                                                            <div class="d-block mb-2">Ir a POS</div>
                                                            <i class="fas fa-cash-register" style="font-size: 20px;"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif

                                        @if(in_array('cash', $vc_module_levels))
                                            <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                                <a href="{{route('tenant.cash.index')}}" class="w-100 h-100 d-inline-block border bg-primary text-light rounded p-1">
                                                    <div class="h-100 d-flex justify-content-center align-items-center">
                                                        <div>
                                                            <div class="d-block mb-2">Ver listado de cajas</div>
                                                            <i class="fas fa-cart-plus" style="font-size: 20px;"></i>
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endif

                    @if(in_array('accounting', $vc_modules) || in_array('reports', $vc_modules))
                    <div class="card shadow-sm border-top my-2">
                        <div class="card-header py-1 font-weight-bold">
                            Reportes
                        </div>
                        <div class="card-body py-2">
                            <div class="row">
                                @if(in_array('account_report', $vc_module_levels))
                                    <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                        <a href="{{route('tenant.account_format.index')}}" class="w-100 h-100 d-inline-block border bg-success text-light rounded p-1">
                                            <div class="h-100 d-flex justify-content-center align-items-center">
                                                <div>
                                                    <div class="d-block mb-2">Reporte contable</div>
                                                    <i class="fas fa-chart-pie" style="font-size: 20px;"></i>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endif

                                <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                    <a href="{{route('tenant.reports.sales.index')}}" class="w-100 h-100 d-inline-block border bg-success text-light rounded p-1">
                                        <div class="h-100 d-flex justify-content-center align-items-center">
                                            <div>
                                                <div class="d-block mb-2">Reporte de ventas</div>
                                                <i class="fas fa-dollar-sign" style="font-size: 20px;"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>

                                <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                    <a href="{{route('tenant.reports.purchases.index')}}" class="w-100 h-100 d-inline-block border bg-success text-light rounded p-1">
                                        <div class="h-100 d-flex justify-content-center align-items-center">
                                            <div>
                                                <div class="d-block mb-2">Reporte de compras</div>
                                                <i class="fas fa-shopping-cart" style="font-size: 20px;"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                
                                <div class="col px-1 text-center" style="height: 100px; max-width: 25%">
                                    <a href="{{route('tenant.reports.general_items.index')}}" class="w-100 h-100 d-inline-block border bg-success text-light rounded p-1">
                                        <div class="h-100 d-flex justify-content-center align-items-center">
                                            <div>
                                                <div class="d-block mb-2">Reporte de productos</div>
                                                <i class="fas fa-boxes" style="font-size: 20px;"></i>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
<script>
    new Vue({
        el: '#app',
        data() {
            return {
                // Obtén el valor desde localStorage
                showWelcome: localStorage.getItem('show_welcome_panel') === 'true'
            };
        },
        watch: {
            // Reaccionamos a cambios en `showWelcome` y actualizamos `localStorage`
            showWelcome(newValue) {
                localStorage.setItem('show_welcome_panel', newValue);
            }
        }
    });
</script>

    <tenant-dashboard-index
    	:type-user="{{ json_encode(auth()->user()->type) }}"
    	:soap-company="{{ json_encode($soap_company) }}"
        :configuration="{{ json_encode($configuration) }}">
    </tenant-dashboard-index>

@endsection
