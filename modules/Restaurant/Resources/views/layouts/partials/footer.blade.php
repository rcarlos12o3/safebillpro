<div class="footer-middle">
    <div class="container">
        <div class="row">
            <div class="col-lg-4">
                <div class="widget widget-info">
                    <h4 class="widget-title">Contáctanos</h4>
                    <ul class="contact-info">
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 172 172" style="fill:#000000;">
                                <g fill-rule="nonzero" stroke="none" style="mix-blend-mode: normal;">
                                    <path d="M0,172v-172h172v172z" fill="none"></path>
                                    <g fill="#fff"> 
                                        <path d="M121.69,102.5636c-2.32773,-1.36453 -5.18867,-1.33587 -7.50493,0.04013l-11.7304,6.98893c-2.62587,1.5652 -5.90533,1.38173 -8.31333,-0.4988c-4.1624,-3.2508 -10.86467,-8.7204 -16.69547,-14.5512c-5.8308,-5.8308 -11.3004,-12.53307 -14.5512,-16.69547c-1.88053,-2.408 -2.064,-5.68747 -0.4988,-8.31333l6.98893,-11.7304c1.38173,-2.31627 1.3932,-5.20013 0.02867,-7.52787l-17.21147,-29.40053c-1.6684,-2.84373 -4.98227,-4.24267 -8.1872,-3.4572c-3.1132,0.7568 -7.1552,2.60293 -11.39213,6.8456c-13.26693,13.26693 -20.3132,35.64413 29.57827,85.5356c49.89147,49.89147 72.26293,42.85093 85.5356,29.57827c4.2484,-4.2484 6.0888,-8.29613 6.85133,-11.41507c0.774,-3.1992 -0.602,-6.49013 -3.44,-8.1528c-7.0864,-4.1452 -22.37147,-13.09493 -29.45787,-17.24587z"></path>
                                    </g>
                                </g>
                            </svg>
                            <a href="tel:+51944999965" target="blank" style="font-size: 25px;">{{$information->information_contact_phone}}</a>
                        </li>
                        @if($information->information_contact_address)
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30" height="30" viewBox="0 0 172 172" style="fill:#000000;">
                                <g fill-rule="nonzero" stroke="none" style="mix-blend-mode: normal;">
                                    <path d="M0,172v-172h172v172z" fill="none"></path>
                                    <g fill="#fff">
                                        <path d="M121.69,102.5636c-2.32773,-1.36453 -5.18867,-1.33587 -7.50493,0.04013l-11.7304,6.98893c-2.62587,1.5652 -5.90533,1.38173 -8.31333,-0.4988c-4.1624,-3.2508 -10.86467,-8.7204 -16.69547,-14.5512c-5.8308,-5.8308 -11.3004,-12.53307 -14.5512,-16.69547c-1.88053,-2.408 -2.064,-5.68747 -0.4988,-8.31333l6.98893,-11.7304c1.38173,-2.31627 1.3932,-5.20013 0.02867,-7.52787l-17.21147,-29.40053c-1.6684,-2.84373 -4.98227,-4.24267 -8.1872,-3.4572c-3.1132,0.7568 -7.1552,2.60293 -11.39213,6.8456c-13.26693,13.26693 -20.3132,35.64413 29.57827,85.5356c49.89147,49.89147 72.26293,42.85093 85.5356,29.57827c4.2484,-4.2484 6.0888,-8.29613 6.85133,-11.41507c0.774,-3.1992 -0.602,-6.49013 -3.44,-8.1528c-7.0864,-4.1452 -22.37147,-13.09493 -29.45787,-17.24587z"></path>
                                    </g>
                                </g>
                            </svg>
                            <a href="#" target="blank" style="font-size: 14px;">
                                {{$information->information_contact_address}}
                            </a>
                        </li>
                        @endif
                        <!-- correo -->
                        @if($information->information_contact_email)
                        <li>
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="30" height="30"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ffffff" d="M48 64C21.5 64 0 85.5 0 112c0 15.1 7.1 29.3 19.2 38.4L236.8 313.6c11.4 8.5 27 8.5 38.4 0L492.8 150.4c12.1-9.1 19.2-23.3 19.2-38.4c0-26.5-21.5-48-48-48L48 64zM0 176L0 384c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-208L294.4 339.2c-22.8 17.1-54 17.1-76.8 0L0 176z"/></svg>
                            <a href="mailto:{{$information->information_contact_email}}" target="blank" style="font-size: 14px;">{{$information->information_contact_email}}</a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
            <div class="col-md-4">
                <div class="widget">
                    <h4 class="widget-title">Enlaces de interés</h4>
                    <div class="row">
                        <div class="col-sm-6 col-md-5">
                            <ul class="links">
                                <li><a href="{{ route("tenant.restaurant.menu") }}">Inicio</a></li>
                                <li><a href="{{ route('restaurant.detail.cart') }}">Ver Carrito</a></li>
                                @guest
                                <li><a href="{{route('tenant_ecommerce_login')}}" class="login-link">Login</a></li>
                                @else
                                <li><a role="menuitem" href="{{ route('logout') }}" class="login-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Salir
                                </a></li>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                                @endguest
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="widget">
                    <h4 class="widget-title">Redes Sociales</h4>
                    <div class="social-icons">
                        
                        <!-- @if($information->link_facebook)
                            <a href="{{$information->link_facebook}}" class="social-icon" target="_blank"></a>
                        @endif -->

                        <!-- @if($information->link_twitter)
                            <a href="{{$information->link_twitter}}" class="social-icon" target="_blank"><i class="icon-twitter"></i></a>
                        @endif -->
                        
                        <!-- @if($information->link_instagram)
                            <a href="{{$information->link_instagram}}" class="social-icon" target="_blank"><i class="fab fa-youtube"></i></a>
                        @endif -->

                        <a href="{{$information->link_facebook}}" class="social-icon" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ffffff" d="M512 256C512 114.6 397.4 0 256 0S0 114.6 0 256C0 376 82.7 476.8 194.2 504.5V334.2H141.4V256h52.8V222.3c0-87.1 39.4-127.5 125-127.5c16.2 0 44.2 3.2 55.7 6.4V172c-6-.6-16.5-1-29.6-1c-42 0-58.2 15.9-58.2 57.2V256h83.6l-14.4 78.2H287V510.1C413.8 494.8 512 386.9 512 256h0z"/></svg></a>
                        <a href="{{$information->link_twitter}}" class="social-icon" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" width="20" height="20"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ffffff" d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg></a>
                        <a href="{{$information->link_tiktok}}" class="social-icon" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" height="14" width="12.25" viewBox="0 0 448 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path fill="#f9fafb" d="M448 209.9a210.1 210.1 0 0 1 -122.8-39.3V349.4A162.6 162.6 0 1 1 185 188.3V278.2a74.6 74.6 0 1 0 52.2 71.2V0l88 0a121.2 121.2 0 0 0 1.9 22.2h0A122.2 122.2 0 0 0 381 102.4a121.4 121.4 0 0 0 67 20.1z"/></svg></a>
                        <a href="{{$information->link_instagram}}" class="social-icon" target="_blank"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" width="20" height="20"><!--!Font Awesome Free 6.7.1 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.--><path fill="#ffffff" d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z"/></svg></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container container-footer">
    <div class="footer-bottom" style="padding-bottom: 2rem;">
        <!-- <p class="footer-copyright">Facturador Pro 4. &copy; {{ now()->year }}. Todos los Derechos Reservados</p> -->
        <img src="{{ asset('porto-ecommerce/assets/images/payments.png') }}" alt="payment methods"
            class="footer-payments">
    </div>
</div>

<div class="modal fade" id="moda-succes-add-product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div class="alert alert-success" role="alert">
                    <i class="icon-ok"></i> Tu producto se agregó al carrito
                </div>
                <div class="row">
                    <div id="product_added_image" class="col-md-4">
                    </div>
                    <!-- Esta parte es del agregar el producto al carrito -->
                    <div class="col-md-8">
                        <div id="product_added" class="product-single-details-restaurant">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('restaurant.detail.cart') }}" class="btn btn-primary text-white">Ir a Carrito</a>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Seguir Comprando</button>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="modal-already-product" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-body">
                <div style="font-size: 1em;" class="alert alert-warning" role="alert">
                <i class="fas fa-exclamation"></i> Tu Producto ya está agregado al carrito.
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('restaurant.detail.cart') }}" class="btn btn-primary text-white">Ir al Carrito</a>
                <button type="button" class="btn btn-warning" data-dismiss="modal">Seguir Comprando</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="login_register_modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div id="tony" class="modal-body-restaurant">
                    <div class="contenedor-form" id="contenedor-form">
                        <!-- contenedor de login -->
                         <!-- <div class="contenedor-column-form"> -->
                        <div id="first-column" class="first-column">
                        <form action="#" id="form_login" class="iniciar-sesion">
                                <h4 class="title mb-2">Iniciar sesión</h4>
                                <div id="msg_login" class="alert alert-danger" role="alert">
                                    Usuario o Contraseña Incorrectos.
                                </div>
                                <div class="form-group">
                                    <label for="email">Correo Electronico:</label>
                                    <input type="email" required class="form-control" id="email"
                                        placeholder="Enter email" name="email">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Contraseña:</label>
                                    <input type="password" required class="form-control" id="pwd"
                                        placeholder="Enter password" name="password">
                                </div>
                                <button type="submit" class="btn btn-primary">Ingresar</button>
                            </form>
                        </div>
                        <!-- contenedor de registro -->
                        <div id="second-column" class="second-column">
                        <form autocomplete="off" action="#" id="form_register" class="registrarse">
                                <h4 class="title mb-2">Nuevo Registro</h4>
                                <div id="msg_register" class="alert alert-danger" role="alert">
                                    <p id="msg_register_p"></p>
                                </div>
                                <div class="form-group">
                                    <label for="email">Nombres:</label>
                                    <input type="text" required autocomplete="off" class="form-control" id="name_reg"
                                        placeholder="Enter name" name="name">
                                </div>
                                <div class="form-group">
                                    <label for="ruc">RUC/DNI:</label>
                                    <input type="text" required autocomplete="off" maxlength="11" class="form-control" id="ruc_reg"
                                        placeholder="Ingrese ruc/dni" name="ruc">
                                </div>
                                <div class="form-group">
                                    <label for="email">Correo Electronico:</label>
                                    <input type="email" required autocomplete="off" class="form-control" id="email_reg"
                                        placeholder="Enter email" name="email">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Contraseña:</label>
                                    <input type="password" required autocomplete="off" class="form-control" id="pwd_reg"
                                        placeholder="Ingrese contraseña" name="pswd">
                                </div>
                                <div class="form-group">
                                    <label for="pwd">Repita la Contraseña:</label>
                                    <input type="password" required autocomplete="off" class="form-control"
                                        id="pwd_repeat_reg" placeholder="Repita contraseña" name="pswd_rpt">
                                </div>
                                <button type="submit" class="btn btn-primary">Registrarse</button>
                            </form>
                        </div>
                        <!-- </div> -->
                        <!-- contenedor overlay -->
                        <div class="terceary-column">
                            <div class="contenedor-iniciar-sesion">
                                <h3>Hola!</h3>
                                <p>Por favor ingrese sus datos para registrarse</p>
                                <button id="iniciar-sesion" class="btn-iniciar-sesion">Iniciar Sesión</button>
                            </div>
                            <div class="contenedor-registro">
                                <h3>Bienvenido!</h3>
                                <p>Por favor ingrese sus credenciales para iniciar sesión</p>
                                <button id="registrarse" class="btn-registrarse">Registrarse!</button>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
   
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const firstColumn = document.getElementById("contenedor-form");
    // console.log(firstColumn);
    const btnIniciarSesion = document.getElementById("iniciar-sesion");
    // console.log(btnIniciarSesion);
    const btnRegistrarse = document.getElementById("registrarse");
    // console.log(btnRegistrarse);

    btnIniciarSesion.addEventListener("click", () => {
        firstColumn.classList.remove("active");

    });
    btnRegistrarse.addEventListener("click", () => {
        firstColumn.classList.add("active");

    });
});

</script>
@push('scripts')
<!-- <script type="text/javascript" src="{{ asset('porto-ecommerce/assets/js/cart.js') }}"></script> -->
<script type="text/javascript">
    


    matchPassword();
    submitLogin();
    submitRegister();


    function matchPassword() {
        var password = document.getElementById("pwd_reg"),
            confirm_password = document.getElementById("pwd_repeat_reg");

        function validatePassword() {
            if (password.value != confirm_password.value) {
                confirm_password.setCustomValidity("El Password no coincide.");
            } else {
                confirm_password.setCustomValidity('');
            }
        }

        password.onchange = validatePassword;
        confirm_password.onkeyup = validatePassword;
    }

    function submitLogin() {
        $('#msg_login').hide();

        $('#form_login').submit(function (e) {
            e.preventDefault()
            $.ajax({
                type: "POST",
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('tenant_ecommerce_login')}}",
                data: $(this).serialize(),
                success: function (data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        $('#msg_login').show();
                    }
                },
                error: function (error_data) {
                    console.log(error_data)
                }
            });
        })

    }

    function submitRegister() {
        $('#msg_register').hide();

        $('#form_register').submit(function (e) {
            e.preventDefault()
            $.ajax({
                type: "POST",
                dataType: 'JSON',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: "{{route('tenant_ecommerce_store_user')}}",
                data: $(this).serialize(),
                success: function (data) {
                    if (data.success) {
                        location.reload();
                    } else {
                        $('#msg_register').show();
                        $('#msg_register_p').text(data.message)
                    }
                },
                error: function (error_data) {
                    console.log(error_data)
                }
            });
        })
    }

</script>
@endpush
