
<style>
#header_bar .header-menu {
    max-height: 300px !important;
    overflow:auto;
    overflow-y: auto;
}

#header_bar .header-menu::-webkit-scrollbar-track {
    -webkit-box-shadow: inset 0 0 6px rgba(0,0,0,0.1);
    background-color: #fdfdfd;
}

#header_bar .header-menu::-webkit-scrollbar {
    width: 6px;
    background-color: #fdfdfd;
}

#header_bar .header-menu::-webkit-scrollbar-thumb {
    background-color: #0187cc;
}

.header-dropdown a img {
    border-radius: 8px;
    padding: 4px;
}

@media (max-width: 768px) {
    .header-dropdown {
        min-width: 100px !important;
    }
}

.header-menu ul a {
    padding: 3px 6px;
}

.header-menu {
    box-shadow: 0 0 2px rgba(0,0,0,0.1);
    padding: 0 !important;
    border: none;
}

.header-menu a:hover, .header-menu a:focus {
    color: #0187cc;
    background-color: #f4f4f4;
}

.header-menu ul a {
    text-transform: capitalize !important;
}

.search_input {
    margin-bottom: 0.1rem;
    border-radius: 20px !important;
}

.search_input:focus {
    background-color: #fff;
    border-color: #fff;
    box-shadow: none;
}

.header-contact span {
    font-weight: normal;
}

div.cart-dropdown {
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: transparent;
}

.header .dropdown-toggle {
    color: red;
    font-size: 10px;
    background-color: #1f1f39;
    width: 90px;
    height: 35px;
    display: flex;
    justify-content: center;
    align-items: center;
    border-radius: 20px;
}

.dropdown-toggle .cart-count {
    background-color: transparent !important;
    color: white !important;
    margin-top: 12px;
    margin-right: 27px;
}

.search_input:focus {
    border: 1px solid #00b3a5 !important;
    background-color: transparent !important;
}

.search_input {
    width: 100%;
    height: 38px !important;
    border-radius: 20px !important;
    background-color: #eff0f6 !important;
}

.header-dropdown-inside {
    position: relative; 
}

.header-dropdown-inside .search-icon {
    position: absolute;
    left: 10px; 
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.header-dropdown-inside .search_input {
    padding-left: 40px; 
    padding-right: 40px; 
    width: 100%;
}

.header-dropdown-inside .clear-icon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    width: 18px;
    height: 18px;
    cursor: pointer;
    display: none;
}
.header-dropdown-inside input:focus + .clear-icon,
.header-dropdown-inside input:not(:placeholder-shown) + .clear-icon {
    display: inline-block; /* Muestra el ícono */
}

 </style>

 <header class="header">

     <div class="header-middle">
         <div   class="container">
             <div class="header-left">
                 <a href="{{ route("tenant.restaurant.menu") }}" class="logo" style="max-width: 180px">
                    @if(isset($information->logo))
                        <img src="{{ asset('storage/uploads/logos/'.$information->logo) }}" alt="Logo" />
                    @else
                        <img src="{{asset('logo/tulogo.png')}}" alt="Logo" />
                    @endif
                 </a>
             </div><!-- End .header-left -->
             
             
             <div id="header_bar" class="header-center header-dropdowns">

                 <div class="header-dropdown header-dropdown-inside" style="min-width:400px;">
                    <img src="{{ asset('images/search.svg') }}" alt="search" class="search-icon" style="width: 18px; height: 18px;">
                    <input placeholder="Buscar..." type="text" class="search_input form-control form-control-lg" v-model="value" v-on:keyup="autoComplete" @focus="isFocused = true" @blur="isFocused = false"/>
                    <img src="{{ asset('images/circle-xmark.svg') }}" alt="Clear" class="clear-icon" @click="clearInput">
                     <div class="header-menu">
                         <ul v-if="results.length > 0">
                            <li v-for="result in results">
                                <a :href="'/ecommerce/item/' + result.id" class="d-flex">
                                    <div class="flex-grow-1"><img style="max-width: 80px" :src="result.image_url_small" alt="England flag">
                                    <span class="search_title" style="font-size: 1.0em;"> @{{ result.description }} </span>
                                    </div>
                                    <span class="search_price">@{{result.sale_unit_price}}</span>
                                    {{-- <div class="search_btn btn btn-default">@{{result.sale_unit_price}}</div> --}}
                                </a>
                            </li>
                         </ul>
                     </div><!-- End .header-menu -->
                 </div><!-- End .header-dropown -->


             </div><!-- End .headeer-center -->

             <div class="header-right">
                 <button class="mobile-menu-toggler" type="button">
                     <i class="icon-menu"></i>
                 </button>
                 <div class="header-contact">
                     <span> Atención al</span>
                     <i class="fab fa-whatsapp"></i> <a href="#"><strong>{{$information->information_contact_phone}}</strong></a>
                 </div><!-- End .header-contact -->

                @include('restaurant::layouts.partials.cart_dropdown')
                @include('ecommerce::partials.headers.session')

             </div><!-- End .header-right -->
         </div><!-- End .container -->
     </div><!-- End .header-middle -->

     <div class="header-bottom sticky-header">
        <div class="container d-flex">
            <nav class="main-nav flex-grow-1">

             </nav>
         </div><!-- End .header-bottom -->
     </div><!-- End .header-bottom -->
 </header><!-- End .header -->

 @push('scripts')
 <script type="text/javascript">
    var app = new Vue({
        el: '#header_bar',
        data: {
            value: '', 
            isFocused: false, 
            suggestions: [],
            resource: 'ecommerce',
            results: [],
        },
        created() {
            this.getItems();
        },
        methods: {
            // Método para limpiar el campo de texto
            clearInput() {
                this.value = ''; 
                this.results = []; 
            },

            // Método para manejar la autocompletación
            autoComplete() {
                if (this.value) {
                    let val = this.value.toUpperCase();
                    this.results = this.suggestions.filter((obj) => {
                        let desc = obj.description.toUpperCase();
                        let internal_id = obj.internal_id ? obj.internal_id.toUpperCase() : '';
                        return desc.includes(val) || internal_id.includes(val);
                    });
                } else {
                    this.results = [];
                }
            },

            // Método para obtener las sugerencias desde el backend
            getItems() {
                let contex = this;
                fetch(`/${this.resource}/items_bar`)
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (myJson) {
                        contex.suggestions = myJson.data;
                    });
            },

            // Método para manejar el clic en una sugerencia
            suggestionClick(item) {
                this.results = [];
                this.value = item.description;
            }
        }
    });
</script>
 @endpush