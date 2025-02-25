@extends('ecommerce::layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            @php
                $tagid = Request::segment(3);
            @endphp

            @if(!$tagid)
                @include('ecommerce::layouts.partials_ecommerce.home_slider')
            @endif
            <div class="row py-4">
                @include('ecommerce::layouts.partials_ecommerce.categories')
            </div>
            <div class="row py-4">
            <div class="container">
                <h1 class="title-category">Explora nuestros productos</h1>
                </div>
            </div>
                {{-- @include('ecommerce::layouts.partials_ecommerce.featured_products') --}}
            <div class="row row-sm">
                @include('ecommerce::layouts.partials_ecommerce.list_products')
            </div>
            <div class="row row-sm mt-0">
            </div>
            <div class="row page-pagination">
              <div class="col-md-12 col-lg-12 d-flex justify-content-end mb-4">
                {{ $dataPaginate->links() }}
              </div>
            </div>
            <div class="row">
                @include('ecommerce::layouts.partials_ecommerce.offers')
            </div>
        </div>
    </div>
</div>
@endsection