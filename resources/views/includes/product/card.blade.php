<div class="col-xl-3 col-lg-4 col-md-4 col-sm-6 col-6 ">
  <div class="ps-product border rounded overflow-hidden" style="background: #fff4e9;">
    <div class="ps-product__thumbnail">
      <img src="{{ route('product.image', ['filename' => $item['image']]) }}" alt="" class="h-100 object-fit-cover" />
      <a class="ps-product__overlay" href="{{ url("products/${item['id']}") }}"></a>
    </div>
    <div class="ps-product__content p-4">
      <div class="ps-product__desc h-100 d-flex flex-column align-items-center justify-content-center">
        <div class="d-flex flex-wrap justify-content-center">
          @foreach ($item['categories'] as $cat)
            <span class="badge badge-pill text-white m-1" style="background: #7f462c; font-size: 90%">
              {{ $cat['title'] }}
            </span>                              
          @endforeach
        </div>
        <a class="ps-product__title" href="javascript:;">{{ $item['title'] }}</a>
        <span class="ps-product__price">Rp. {{ number_format($item['price'], 0, ',', '.') }}</span>
      </div>
      <div class="ps-product__shopping h-100 d-flex align-items-center justify-content-center">
        <button class="ps-btn ps-product__add-to-cart cart-add" data-id="{{ $item['id'] }}" data-name="{{ $item['title'] }}" 
          data-image="{{ route('product.image', ['filename' => $item['image']]) }}" data-price="{{ $item['price'] }}">
          Add to Cart
        </button>
      </div>
    </div>
  </div>
</div>