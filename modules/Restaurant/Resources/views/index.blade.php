@extends('restaurant::layouts.master')

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12">
            @php
                $tagid = Request::segment(3);
            @endphp
            @if(!$tagid)
                @include('restaurant::layouts.partials.banner')
            @endif
            <div class="row py-4">
            @include('restaurant::layouts.partials.categories')
            </div>
            <div class="row py-4">
                <div class="container">
                <h1 class="title-category">Explora nuestro Menú</h1>
                </div>
            </div>
            <div class="row row-sm mt-0">
                @include('restaurant::layouts.partials.list_products')
            </div>
            <div class="row page-pagination">
              <div class="col-md-12 col-lg-12 d-flex justify-content-end mb-4">
                {{ $dataPaginate->links() }}
              </div>
            </div>
            <div class="row">
                @include('restaurant::layouts.partials.offers')
            </div>
        </div>
    </div>
</div>
@endsection
