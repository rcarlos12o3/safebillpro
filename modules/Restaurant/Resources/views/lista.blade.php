@extends('restaurant::layouts.list.lista_master')

@section('lista')
<div class="container" style="background-color:#eef5f5; border-radius:6px;">
    <div class="row">
        <div class="col-lg-12 mt-5 mb-5 text-center">
            <div class="content-logo">
            <a href="{{ route("tenant.restaurant.menu") }}" class="logo" style="max-width: 180px">
                    @if($information->logo)
                        <img src="{{ asset('storage/uploads/logos/'.$information->logo) }}" alt="Logo" />
                    @else
                        <img src="{{asset('logo/tulogo.png')}}" alt="Logo" />
                    @endif
                 </a>
            
            </div>
            <h1 class="display-4 font-weight-bold title">La Carta</h1>
            <p class="text-uppercase sub-title">¡Disfruta de nuestra variedad de platos!</p>
        </div>
        <div class="col-lg-12 mb-3">
            <div class="category-grid">
                @foreach ($categoriesWithProducts as $index => $categoryData)
                    <div class="category-section">
                        <!-- Título de la categoría -->
                        <h2 class="category-title-list text-center">{{ $categoryData['category']->name }}</h2>
                        <div class="menu-list">
                            <!-- Lista de productos -->
                            @foreach ($categoryData['products'] as $product)
                                <div class="item">
                                    <span class="relleno">
                                    <span class="nombre">{{ $product->description }}</span>
                                    </span>
                                    <span class="precio">{{ $product->currency_type['symbol'] }} {{ number_format($product->sale_unit_price, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    body {
        background-color: #000;
        color: #fff;
        font-family: Arial, sans-serif;
    }

    h1 {
        font-size: 2.5rem;
        margin-bottom: 1rem;
    }

    p {
        font-size: 1rem;
        color: #bbb;
    }
    .content-logo {
        display: flex;
        justify-content: center;
        align-items: center;
        margin-bottom: 1rem;
    }

    .category-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr); 
        gap: 2rem;
    }

    .category-section {
        background-color: #fff;
        padding: 1.5rem;
        border-radius: 8px;
    }

    .category-title-list {
        font-size: 1.8rem;
        margin-bottom: 1rem;
        text-transform: uppercase;
        font-weight: bold;
        text-align: center;
        color: #15616D;
        position: relative;
        font-family: 'Poppins', sans-serif;
    }


    .menu-list {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: relative;
    }

    .nombre {
        flex-grow: 1;
        position: relative;
        z-index: 1;
        padding-right: 5px;
        background-color: #fff;
        color: #001524;
    }

    .relleno::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 1px;
        background-image: linear-gradient(to right, #000 0%, #ddd 50%, transparent 50%);
        background-size: 4px 1px;
        background-repeat: repeat-x;
        transform: translateY(-50%); 
        z-index: 0;
    }

    .precio {
        white-space: nowrap;
        z-index: 1;
        padding-left: 5px;
        background-color: #fff;
        color:#001524;
    }


    .title {
        display: inline-grid;
        color: #15616D;
    }

    .title::before,
    .title::after {
        content: '';
        height: 2px;
        background-color: white;
        margin: 10px;
    }

    .sub-title {
        font-size: 1.5rem;
        color: #15616D;
    }

    /* Versión responsive para móviles */
    @media (max-width: 768px) {
        .category-grid {
            grid-template-columns: 1fr; /* Una sola columna en pantallas pequeñas */
        }

        .menu-item .product-name,
        .menu-item .price {
            font-size: 1rem; /* Reducir tamaño de texto en móviles */
        }

        .category-title-list {
            font-size: 1.5rem; /* Reducir tamaño del título de categoría */
        }
    }
</style>
