@extends('layouts.base')

@section('title', $product['title'])

@section('customStyles')
  <style>
    .ps-product__shopping input::-webkit-outer-spin-button,
    .ps-product__shopping input::-webkit-inner-spin-button {
      -webkit-appearance: none;
      margin: 0;
    }

    .ps-product__shopping input[type=number] {
      -moz-appearance:textfield;
    }
  </style>
@endsection

@section('content')
  @include('includes.breadcrumb', [
    'breadcrumb' => ['Produk', $product['title']]
  ])

  <div class="ps-page--shop">
    <div class="container">
      <div class="ps-shopping ps-shopping--fullwidth">
        <div class="ps-product--detail">
          <div class="ps-product__header">
            <div class="ps-product__thumbnail">
              <figure class="d-flex justify-content-center justify-content-lg-end mb-0">
                <img src="{{ route('product.image', ['filename' => $product['image']]) }}" height="340" alt="Foto produk">
              </figure>
            </div>
            <div class="ps-product__info">
              <h1>{{ $product['title'] }}</h1>

              <div class="ps-product__meta">
                <div class="ps-product__rating">
                  <select class="ps-rating" data-read-only="true">
                    @for ($i = 0; $i <= 5; $i++)
                      <option value="{{ $i }}" {{ $i <= $productRating ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                  </select>
                </div>
              </div>

              <h4 class="ps-product__price sale">
                Rp. {{ number_format($product['price'], 0, ',', '.') }}
              </h4>

              <div class="ps-product__desc">
                {{ $product['body'] }}
              </div>

              <div class="ps-product__specification p-0 mb-4">
                <p>STOCK: {{ $product['stock'] }}</p>
                <p>
                  CATEGORIES: 
                  {!! implode(', ', array_map(function ($category) {
                    return '<a href="'.url('/products?category='.$category['id']).'">'.$category['title'].'</a>';
                  }, $product['categories'])) !!}
                </p>
              </div>
              
              <div class="ps-product__shopping">
                <div class="form-group--number">
                  <button class="up"></button>
                  <button class="down"></button>
                  <input class="form-control" type="number" placeholder="1">
                </div>
                <a class="ps-btn" href="javascript:;">ADD TO CART</a>
              </div>
            </div>
          </div>

          <div class="ps-product__content">
            <form method="POST" action="{{ route('reviews.submit') }}" class="ps-form--review pt-0">
              @csrf
              <input type="hidden" name="product_id" value="{{ $product['id'] }}">
              <input type="hidden" name="redirect_url" value="{{ url()->current() }}">
              <div class="ps-form__header">
                <h4>Tambah/Edit Ulasan Produk</h4>
              </div>
              <div class="row">
                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12  ">
                  <div class="form-group">
                    <input class="form-control" type="text" name="order_receipt" required placeholder="Invoice">
                  </div>
                </div>
                <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12  ">
                  <div class="form-group">
                    <input class="form-control" type="number" name="customer_whatsapp" required placeholder="5 digit terakhir nomor Whatsapp">
                  </div>
                </div>
              </div>
              <div class="form-group form-group--inline">
                <label>Rating produk:</label>
                <div class="form-group__content">
                  <select class="ps-rating" name="rating" required data-read-only="false">
                    <option value="0">0</option>
                    <option value="1">1</option>
                    <option value="2">2</option>
                    <option value="3">3</option>
                    <option value="4">4</option>
                    <option value="5">5</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <textarea class="form-control" rows="3" name="body" required placeholder="Tulis ulasan anda disini"></textarea>
              </div>
              <div class="form-group text-center submit">
                <button class="ps-btn">Submit Ulasan</button>
              </div>
            </form>
            
            <div class="ps-reviews pt-40">
              @foreach ($reviews as $review)
                @include('includes/product/review-block', [
                  'review' => $review
                ])
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@section('customScripts')
  <script>
    const quantityInput = document.querySelector('.ps-product__shopping .form-control');
    const incrementButton = document.querySelector('.ps-product__shopping .up');
    const decrementButton = document.querySelector('.ps-product__shopping .down');
    const addToCartButton = document.querySelector('.ps-product__shopping .ps-btn');

    incrementButton.addEventListener('click', () => {
      quantityInput.value = parseInt(quantityInput.value || 1) + 1;
    });

    decrementButton.addEventListener('click', () => {
      const currentValue = parseInt(quantityInput.value || 1);
      quantityInput.value = currentValue > 1 ? currentValue - 1 : 1;
    });

    addToCartButton.addEventListener('click', () => {
      const product = {
        id: {{ $product['id'] }},
        name: '{{ $product['title'] }}',
        image: '{{ route('product.image', ['filename' => $product['image']]) }}',
        price: {{ $product['price'] }},
      };
      const quantity = parseInt(quantityInput.value || 1);
      for (let i = 0; i < quantity; i++) {
        addToCart(product);
      }
    });

    if (window.location.search.includes('success=')) {
      const urlParams = new URLSearchParams(window.location.search);
      const successMessage = urlParams.get('success');
      notify(decodeURIComponent(successMessage), 'success');
      urlParams.delete('success');
      window.history.replaceState({}, document.title, window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
    }

    if (window.location.search.includes('error=')) {
      const urlParams = new URLSearchParams(window.location.search);
      const errorMessage = urlParams.get('error');
      notify(decodeURIComponent(errorMessage), 'error', true);
      urlParams.delete('error');
      window.history.replaceState({}, document.title, window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : ''));
    }
  </script>
@endsection
