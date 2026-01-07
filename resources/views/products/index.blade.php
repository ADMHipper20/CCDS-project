@extends('layouts.base')

@section('title', 'Produk')

@php
  $start = ($currentPage - 1) * $perPage + 1;
  $end = min($currentPage * $perPage, $total);
  $lastPage = ceil($total / $perPage);   
@endphp

@section('content')
  @include('includes.breadcrumb', [
    'breadcrumb' => ['Produk']
  ])

  <div class="ps-page--shop">
    <div class="container-fluid">
      <div class="ps-shopping ps-shopping--fullwidth">
        <div class="ps-shopping__left">
          <div class="sticky-lg-top">
            <aside class="widget widget_shop widget_categories">
              <h3 class="widget-title">Kategori</h3>
              <ul>
                <li>
                  <a href="{{ url()->current().'?'.http_build_query(request()->except('category', 'page')) }}"
                    @class(['active' => !$selectedCategory])>
                    Semua Kategori
                  </a>
                </li>

                @foreach ($categories as $item)
                  <li>
                    {{-- Ensure $item is actually an array before accessing keys --}}
                    @if(is_array($item)) 
                      <a href="{{ url()->current().'?'.http_build_query(array_merge(request()->except('page'), ['category' => $item['id']])) }}"
                        @class(['active' => $selectedCategory == $item['id']])>
                        {{ $item['title'] }}  {{-- <--- FIXED: Use brackets ['title'] --}}
                      </a>
                    @endif
                  </li>
                @endforeach
              </ul>
            </aside>
          </div>
        </div>
        
        <div class="ps-shopping__right">
          <form method="GET" action="{{ url()->current() }}" class="mb-3">
            @foreach (request()->except(['search', 'page']) as $key => $value)
              <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach

            <div class="form-group position-relative">
              <input class="border form-control rounded-pill ps-4 pe-5" type="text" name="search" placeholder="Cari produk..."
                value="{{ request('search') }}">
              <i class="fa fa-search position-absolute" style="right: 3rem; top: 50%; transform: translate(0, -50%);"></i>
            </div>
          </form>
          
          <div class="ps-shopping__top">
            <p>Menampilkan {{ $start }}-{{ $end }} dari {{ $total }} hasil</p>
            <figure>
              <select class="ps-select mr-0" title="Sort" onchange="location = this.value;">
                <option value="{{ url()->current().'?'.http_build_query(array_merge(request()->except('sort'), ['sort' => 1])) }}" 
                  @if($selectedSort == 1) selected @endif>
                  Nama A-Z
                </option>
                <option value="{{ url()->current().'?'.http_build_query(array_merge(request()->except('sort'), ['sort' => 2])) }}" 
                  @if($selectedSort == 2) selected @endif>
                  Nama Z-A
                </option>
                <option value="{{ url()->current().'?'.http_build_query(array_merge(request()->except('sort'), ['sort' => 3])) }}" 
                  @if($selectedSort == 3) selected @endif>
                  Harga Terendah
                </option>
                <option value="{{ url()->current().'?'.http_build_query(array_merge(request()->except('sort'), ['sort' => 4])) }}" 
                  @if($selectedSort == 4) selected @endif>
                  Harga Tertinggi
                </option>
              </select>
            </figure>
          </div>
          
          <div class="ps-product-box">
            <div class="row">
              @foreach ($products as $item)
                @include('includes.product.card', [
                  'item' => $item
                ])
              @endforeach
            </div>
          </div>

          <div class="ps-pagination">
            @include('includes.product.pagination', [
              'currentPage' => $currentPage,
              'lastPage' => $lastPage
            ])
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('customScripts')
  <script>
    $(function() {
      $('.ps-product .ps-product__thumbnail').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
      });
      $('.ps-product .ps-product__content').matchHeight({
        byRow: true,
        property: 'height',
        target: null,
        remove: false
      });
    });
  </script>
@endsection