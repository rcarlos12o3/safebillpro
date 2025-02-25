<div class="dropdown cart-dropdown" style="margin-left: 9px;">

    @guest('ecommerce')
        <div class="header-contact mr-0">
            <img src="{{ asset('images/circle-user.svg') }}" alt="User" style="width: 18px; height: 18px;">
            <a class="login-link" href="{{ route('tenant_ecommerce_login') }}"><strong style="font-size: 15px;">Log In</strong></a>
        </div>
    @elseauth('ecommerce')
        <a href="#" class="dropdown-toggle" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
            data-display="static">
            <i class="icon-user fa-2x text-white"></i>
        </a>
        <div class="dropdown-menu">
            <div class="dropdownmenu-wrapper">
                <div class="dropdown-cart-total d-flex justify-content-center">
                    <span>{{ Auth::guard('ecommerce')->user()->email }}</span>
                    <a href="#" role="menuitem" class="btn-logout" data-toggle="tooltip" data-placement="bottom"
                    title="Cerrar Sesión" onclick="event.preventDefault(); logout();">
                        <i class="fas fa-power-off"></i>
                    </a>
                </div>
            </div>
        </div>
    @endauth

</div>
