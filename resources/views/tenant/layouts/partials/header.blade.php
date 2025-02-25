<header class="header">
    <div class="logo-container">
        <div class="sidebar-toggle" data-toggle-class="sidebar-left-collapsed" data-target="html"
            data-fire-event="sidebar-left-toggle">
            <i class="fas fa-angle-left" aria-label="Toggle sidebar"></i>
            <i class="fas fa-angle-right" aria-label="Toggle sidebar"></i>
        </div>
        <div class="d-md-none toggle-sidebar-left" data-toggle-class="sidebar-left-opened" data-target="html"
            data-fire-event="sidebar-left-opened">
            <div style="width: 24px; height: 24px; display: flex; align-items: center;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                    class="icon icon-tabler icons-tabler-outline icon-tabler-grid-dots">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M5 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M12 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M19 5m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M5 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M12 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M19 12m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M5 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M12 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                    <path d="M19 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                </svg>
            </div>
            <h2>Modulos</h2>
        </div>
        <tenant-dialog-header-menu></tenant-dialog-header-menu>

        @if ($tenant_show_ads && $url_tenant_image_ads)
            <div class="ml-3 mr-3">
                <img src="{{$url_tenant_image_ads}}" style="max-height: 50px; max-width: 500px;">
            </div>
        @endif

        <!-- @if(config('configuration.multi_user_enabled'))
            <tenant-multi-users-change-client></tenant-multi-users-change-client>
        @endif -->

        <div class="logo-container-mobile">
            <a href="{{route('tenant.dashboard.index')}}" class="logo pt-2 pt-md-0">
                @if($vc_company->logo)
                    <img src="{{ asset('storage/uploads/logos/' . $vc_company->logo) }}" alt="Logo" />
                @else
                    <img src="{{asset('logo/tulogo.png')}}" alt="Logo" />
                @endif
            </a>
        </div>

        <div class="options-user-mobile">
            <i class="fas fa-bars"></i>
        </div>
    </div>
    <div class="header-right">
        <div class="dropdown-menu-mobile" style="display: none;">
            <div class="user-content">
                <div class="close-container-user">
                    <i class="fas fa-times"></i>
                </div>
                <div>

                </div>
                <a href="#" class="user-profile-content">
                    <div class="profile-info" data-lock-name="{{ $vc_user->email }}"
                        data-lock-email="{{ $vc_user->email }}">
                        <span class="name">{{ $vc_user->name }}</span>
                        <span class="role">{{ $vc_user->email }}</span>
                    </div>
                    <figure class="profile-picture">
                        {{-- <img src="{{asset('img/%21logged-user.jpg')}}" alt="Profile" class="rounded-circle"
                            data-lock-picture="img/%21logged-user.jpg" /> --}}
                        <div class="border rounded-circle text-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="30" height="30" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-user-square-rounded">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" />
                                <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
                                <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05" />
                            </svg>
                        </div>
                    </figure>
                    {{-- <i class="fa custom-caret"></i> --}}
                </a>
            </div>
            <ul class="pendingWork-container">
                <li class="li-title-mobile">
                    <h4>Pendientes</h4>
                </li>
                @if($vc_document > 0)
                    <li>
                        <a href="{{route('tenant.documents.not_sent')}}"
                            class="notification-icon text-secondary navigation-options" data-toggle="tooltip"
                            data-placement="bottom" title="Comprobantes no enviados/por enviar">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                    fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path
                                        d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                                    <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                                </svg>
                                <span class="ml-2">Comprobantes no enviados</span>
                                <span
                                    class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document }}</span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </a>
                    </li>
                @endif
                <li>
                    <a href="{{ route('tenant_orders_index') }}"
                        class="notification-icon text-secondary navigation-options" data-toggle="tooltip"
                        data-placement="bottom" title="Pedidos pendientes">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                                <path d="M17 17h-11v-14h-2" />
                                <path d="M6 5l14 1l-1 7h-13" />
                            </svg>
                            <span class="ml-2">Pedidos pendientes</span>
                            <span class="badge badge-pill badge-info badge-up cart-item-count">{{ $vc_orders }}</span>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </a>
                </li>
                @if(in_array('cuenta', $vc_modules))
                    @if(in_array('account_users_list', $vc_module_levels))
                        <li>
                            <a role="menuitem" href="{{route('tenant.payment.index')}}"
                                class="notification-icon text-secondary navigation-options">
                                <span>
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-dollar mr-2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                        <path
                                            d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" />
                                        <path d="M12 6v10" />
                                    </svg>
                                    <span>Mis Pagos</span>
                                </span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M9 6l6 6l-6 6" />
                                </svg>
                            </a>
                        </li>
                    @endif
                @endif
            </ul>

            <ul class="pendingWork-container">
                <li class="li-title-mobile">
                    <h4>Sistema</h4>
                </li>
                @php
                    $is_pse = $vc_company->send_document_to_pse;
                    $environment = 'SUNAT';
                    $is_ose = ($vc_company->soap_send_id === '02') ? true : false;
                    if ($is_pse) {
                        $environment = 'PSE';
                    }
                    if ($is_ose) {
                        $environment = 'OSE';
                    }
                    if ($is_ose && $is_pse) {
                        $environment = 'OSE-PSE';
                    }
                @endphp
                @if($vc_company->soap_type_id == "01")
                    <li>
                        <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                            class="notification-icon text-secondary navigation-options" data-toggle="tooltip"
                            data-placement="bottom"
                            title="{{$environment}}: ENTORNO DE DEMOSTRACIÓN, pulse para ir a configuración"
                            style="background-color: transparent !important;">
                            <span class="options-sunat">
                                <i class="fas fa-2x fa-toggle-off mr-2" style="font-size: 20px;"></i>
                                <span class="ml-2" style="display: flex; flex-direction: column;">
                                    <span>DEMO</span>
                                    <span>SUNAT Entorno de Demostración</span>
                                </span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </a>
                    </li>
                @elseif($vc_company->soap_type_id == "02")
                    <li>
                        <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                            class="notification-icon text-secondary navigation-options" data-toggle="tooltip"
                            data-placement="bottom"
                            title="{{$environment}}: ENTORNO DE PRODUCCIÓN, pulse para ir a configuración">
                            <span class="options-sunat">
                                <i class="text-success fas fa-2x fa-toggle-on mr-2"
                                    style="font-size: 20px; color: #28a745 !important"></i>
                                <span class="ml-2" style="display: flex; flex-direction: column;">
                                    <span>PROD</span>
                                    <span>SUNAT Entorno de Demostración</span>
                                </span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </a>
                    </li>
                @else
                    <li>
                        <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                            class="notification-icon text-secondary navigation-options" data-toggle="tooltip"
                            data-placement="bottom" title="INTERNO: ENTORNO DE PRODUCCIÓN, pulse para ir a configuración">
                            <span class="options-sunat">
                                <i class="text-info fas fa-2x fa-toggle-on mr-2"
                                    style="font-size: 20px; color: #398bf7!important;"></i>
                                <span class="ml-2" style="display: flex; flex-direction: column;">
                                    <span>INT</span>
                                    <span>SUNAT Entorno de Demostración</span>
                                </span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M9 6l6 6l-6 6" />
                            </svg>
                        </a>
                    </li>
                @endif
                <li>
                    <a class="style-switcher-open notification-icon text-secondary navigation-options" href="#">
                        <span>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-paint mr-2">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M5 3m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" />
                                <path d="M19 6h1a2 2 0 0 1 2 2a5 5 0 0 1 -5 5l-5 0v2" />
                                <path
                                    d="M10 15m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                            </svg>
                            Estilos y temas
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-chevron-right">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M9 6l6 6l-6 6" />
                        </svg>
                    </a>
                </li>
            </ul>

            <ul class="log-out-container">
                <li role="menuitem" href="{{ route('logout') }}"
                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    {{--<a role="menuitem" href="#"><i class="fas fa-user"></i> Perfil</a>--}}
                    <a>
                        <svg xmlns="http://www.w3.org/2000/svg" width="26" height="26" viewBox="0 0 24 24" fill="none"
                            stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-logout mr-2">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M14 8v-2a2 2 0 0 0 -2 -2h-7a2 2 0 0 0 -2 2v12a2 2 0 0 0 2 2h7a2 2 0 0 0 2 -2v-2" />
                            <path d="M9 12h12l-3 -3" />
                            <path d="M18 15l3 -3" />
                        </svg>
                        Cerrar Sesión
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
        <ul class="notifications mx-2">
            @php
                $is_pse = $vc_company->send_document_to_pse;
                $environment = 'SUNAT';
                $is_ose = ($vc_company->soap_send_id === '02') ? true : false;
                if ($is_pse) {
                    $environment = 'PSE';
                }
                if ($is_ose) {
                    $environment = 'OSE';
                }
                if ($is_ose && $is_pse) {
                    $environment = 'OSE-PSE';
                }

                $productionClass = ($vc_company->soap_type_id == "02" && $is_ose) ? 'btn-success' : 'btn-primary';
            @endphp
            @if($vc_company->soap_type_id == "1")
                <li>
                    <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                        class="btn-sunat btn-danger" data-toggle="tooltip" data-placement="bottom"
                        title="Clic para ver o cambiar la configuración del entorno y el tipo de conexión">
                        <span class="btn-title">Modo: DEMO</span>
                        <span style="font-size: 12px;">Conectado a {{ $environment }}</span>
                    </a>
                </li>
            @elseif($vc_company->soap_type_id == "02")
                <li>
                    <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                        class="btn-sunat {{ $productionClass }}" data-toggle="tooltip" data-placement="bottom"
                        title="Clic para ver o cambiar la configuración del entorno y el tipo de conexión">
                        <span class="btn-title">PRODUCCIÓN</span>
                        <span style="font-size: 12px;">Conectado a {{ $environment }}</span>
                    </a>
                </li>
            @else
                <li>
                    <a href="@if(in_array('configuration', $vc_modules)){{route('tenant.companies.create')}}@else # @endif"
                        class="btn-sunat btn-info" data-toggle="tooltip" data-placement="bottom"
                        title="Clic para ver o cambiar la configuración del entorno y el tipo de conexión">
                        <span class="btn-title">Modo: INTERNO</span>
                        <span style="font-size: 12px;">Conectado a SERVIDOR</span>
                    </a>
                </li>
            @endif
        </ul>

        <span class="separator"></span>
        <ul class="notifications">
            <li>
                <a href="{{ route('tenant_orders_index') }}" class="notification-icon text-secondary"
                    data-toggle="tooltip" data-placement="bottom" title="Pedidos pendientes">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="icon icon-tabler icons-tabler-outline icon-tabler-shopping-cart">
                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                        <path d="M6 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M17 19m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" />
                        <path d="M17 17h-11v-14h-2" />
                        <path d="M6 5l14 1l-1 7h-13" />
                    </svg>
                    <span class="badge badge-pill badge-info badge-up cart-item-count">{{ $vc_orders }}</span>
                </a>
            </li>
        </ul>

        @if($vc_document > 0)
            <span class="separator"></span>
            <ul class="notifications">
                <li>
                    <a href="{{route('tenant.documents.not_sent')}}" class="notification-icon text-secondary"
                        data-toggle="tooltip" data-placement="bottom" title="Comprobantes no enviados/por enviar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-bell">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path
                                d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
                            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
                        </svg>
                        <span class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document }}</span>
                    </a>
                </li>
            </ul>
        @endif

        @if($vc_document_regularize_shipping > 0)
            <span class="separator"></span>
            <ul class="notifications">
                <li>
                    <a href="{{route('tenant.documents.regularize_shipping')}}" class="notification-icon text-secondary"
                        data-toggle="tooltip" data-placement="bottom" title="Comprobantes pendientes de rectificación">
                        <i class="fas fa-exclamation-triangle text-secondary"></i>
                        <span
                            class="badge badge-pill badge-danger badge-up cart-item-count">{{ $vc_document_regularize_shipping }}</span>
                    </a>
                </li>
            </ul>
        @endif

        @if(in_array('reports', $vc_modules) && $vc_finished_downloads > 0)
            <span class="separator"></span>
            <ul class="notifications">
                <li>

                    <a href="{{route('tenant.reports.download-tray.index')}}" class="notification-icon text-secondary"
                        data-toggle="tooltip" data-placement="bottom" title="Bandeja de descargas (Reportes procesados)">
                        <i class="fas fa-file-download text-secondary"></i>
                        <span
                            class="badge badge-pill badge-info badge-up cart-item-count">{{ $vc_finished_downloads }}</span>
                    </a>
                </li>
            </ul>
        @endif
        <span class="separator"></span>
        <div id="userbox" class="userbox">
            <a href="#" class="user-profile-content check-double" style="cursor: pointer;">
                <div class="profile-info" data-lock-name="{{ $vc_user->email }}"
                    data-lock-email="{{ $vc_user->email }}">
                    <span class="name">{{ $vc_user->name }}</span>
                    <span class="role">{{ $vc_user->email }}</span>
                </div>
                <figure class="profile-picture">
                    {{-- <img src="{{asset('img/%21logged-user.jpg')}}" alt="Profile" class="rounded-circle"
                        data-lock-picture="img/%21logged-user.jpg" /> --}}
                    <div class="border rounded-circle text-center" style="width: 25px;">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                            class="icon icon-tabler icons-tabler-outline icon-tabler-user-square-rounded">
                            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                            <path d="M12 13a3 3 0 1 0 0 -6a3 3 0 0 0 0 6z" />
                            <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" />
                            <path d="M6 20.05v-.05a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v.05" />
                        </svg>
                    </div>
                </figure>
                {{-- <i class="fa custom-caret"></i> --}}
            </a>
            <div class="dropdown-menu-desktop">
                <ul class="list-unstyled mb-0">
                    @if(in_array('cuenta', $vc_modules))
                        @if(in_array('account_users_list', $vc_module_levels))
                            <li>
                                <a role="menuitem" href="{{route('tenant.payment.index')}}">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-receipt-dollar mr-2">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path
                                            d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16l-3 -2l-2 2l-2 -2l-2 2l-2 -2l-3 2" />
                                        <path
                                            d="M14.8 8a2 2 0 0 0 -1.8 -1h-2a2 2 0 1 0 0 4h2a2 2 0 1 1 0 4h-2a2 2 0 0 1 -1.8 -1" />
                                        <path d="M12 6v10" />
                                    </svg>
                                    <span>Mis Pagos</span>
                                </a>
                            </li>
                        @endif
                    @endif
                    <li>
                        <a class="style-switcher-open" href="#">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-paint mr-2">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path
                                    d="M5 3m0 2a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v2a2 2 0 0 1 -2 2h-10a2 2 0 0 1 -2 -2z" />
                                <path d="M19 6h1a2 2 0 0 1 2 2a5 5 0 0 1 -5 5l-5 0v2" />
                                <path
                                    d="M10 15m0 1a1 1 0 0 1 1 -1h2a1 1 0 0 1 1 1v4a1 1 0 0 1 -1 1h-2a1 1 0 0 1 -1 -1z" />
                            </svg>
                            Estilos y temas</a>
                    </li>
                    <li class="multi-user-content pt-1 pl-4 pr-4">
                        @if(config('configuration.multi_user_enabled'))
                            <tenant-multi-users-change-client></tenant-multi-users-change-client>
                        @endif

                        <!-- <div id="reception-component-container" style="width: 100%;">
                            <reception-component
                                :user-type="'admin'"
                                :establishment-id="{{ auth()->user()->establishment_id }}"
                                :establishments="{{ isset($establishments) ? json_encode($establishments) : json_encode([]) }}"
                            ></reception-component>
                        </div> -->

                    </li>
                    {{-- <li class="dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"
                            @click.stop>
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-download mr-2">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M4 14v4a1 1 0 0 0 1 1h14a1 1 0 0 0 1 -1v-4" />
                                <path d="M7 10l5 5l5 -5" />
                                <path d="M12 4l0 11" />
                            </svg>
                            Establecimientos <span class="caret"></span>
                        </a>
                        <ul class="dropdown-menu" role="menu">
                            <li>
                                <a class="text-1" href="#" @click.prevent="clickExport()">Reporte recepción</a>
                            </li>
                        </ul>
                    </li> --}}
                    <li class="divider"></li>
                    <li>
                        {{--<a role="menuitem" href="#"><i class="fas fa-user"></i> Perfil</a>--}}
                        <a role="menuitem" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round"
                                class="icon icon-tabler icons-tabler-outline icon-tabler-door-exit mr-2">
                                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                <path d="M13 12v.01" />
                                <path d="M3 21h18" />
                                <path d="M5 21v-16a2 2 0 0 1 2 -2h7.5m2.5 10.5v7.5" />
                                <path d="M14 7h7m-3 -3l3 3l-3 3" />
                            </svg>
                            Cerrar Sesión
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const optionsUserMobile = document.querySelector('.options-user-mobile');
        const headerRight = document.querySelector('.header-right');
        const closeContainerUser = document.querySelector('.close-container-user');
        const body = document.body;

        // Mostrar/ocultar header-right al hacer clic en options-user-mobile
        optionsUserMobile.addEventListener('click', function () {
            headerRight.classList.toggle('active');
            document.documentElement.classList.add('options-user-mobile-opened');
        });

        // Ocultar header-right al hacer clic en close-container-user
        closeContainerUser.addEventListener('click', function () {
            headerRight.classList.remove('active');
            document.documentElement.classList.remove('options-user-mobile-opened');
        });

        // Ocultar header-right al hacer clic fuera de él
        document.addEventListener('click', function (event) {
            if (!headerRight.contains(event.target) && !optionsUserMobile.contains(event.target)) {
                headerRight.classList.remove('active');
                document.documentElement.classList.remove('options-user-mobile-opened');
            }
        });
    });
    // script para panejo de dropdown-menu-desktop
    document.addEventListener('DOMContentLoaded', function () {
        const userProfile = document.querySelector('.check-double');
        const dropdownMenu = document.querySelector('.dropdown-menu-desktop');

        userProfile.addEventListener('click', function (event) {
            event.stopPropagation();
            dropdownMenu.classList.toggle('active');
        });

        document.addEventListener('click', function (event) {
            if (!userProfile.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('active');
            }
        });
    });
</script>
{{--
<div class="container d-none d-sm-block">
    <div id="switcher-top" class="d-flex justify-content-center switcher-hover">
        <span class="text-white py-0 px-5 text-center"><i class="fas fa-plus fa-fw"></i>Acceso Rápido</span>
    </div>
</div>
<div class="container d-none d-sm-block">
    <div id="switcher-list" class="d-flex justify-content-center switcher-hover">
        <div class="row">
            <div class="px-3"><a class="py-3" href="{{ route('tenant.documents.create') }}"><i
                        class="fas fa-fw fa-file-invoice" aria-hidden="true"></i> Nuevo Comprobante</a></div>
            <div class="px-3"><a class="py-3"
                    href="{{ in_array('pos', $vc_modules) ? route('tenant.pos.index') : '#' }}"><i
                        class="fas fa-fw fa-cash-register" aria-hidden="true"></i> POS</a></div>
            <div style="min-width: 220px;"></div>
            <div class="px-3"><a class="py-3"
                    href="{{ in_array('configuration', $vc_modules) ? route('tenant.companies.create') : '#' }}"><i
                        class="fas fa-fw fa-industry" aria-hidden="true"></i> Empresa</a></div>
            <div class="px-3"><a class="py-3"
                    href="{{ in_array('establishments', $vc_modules) ? route('tenant.establishments.index') : '#' }}"><i
                        class="fas fa-fw fa-warehouse" aria-hidden="true"></i> Establecimientos</a></div>
        </div>
    </div>
</div> --}}