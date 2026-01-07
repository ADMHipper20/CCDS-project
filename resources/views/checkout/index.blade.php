@extends('layouts.base')

@section('title', 'Checkout')

@section('content')
  @include('includes.breadcrumb', [
    'breadcrumb' => ['Checkout']
  ])

  <div class="container">
    <form method="POST" action="#" class="ps-checkout" id="checkout-form">
      <div class="ps-checkout__left">
        <div class="ps-form--checkout">
          <h4>Data Pelanggan</h4>
          <div class="form-group">
            <label>Nama <i class="text-danger">*</i></label>
            <input name="customer_name" type="text" required class="form-control border rounded-pill" placeholder="Argus">
          </div>
          <div class="form-group">
            <label>Nomor Whatsapp <i class="text-danger">*</i></label>
            <input name="customer_whatsapp" type="text" required class="form-control border rounded-pill" placeholder="628xxxxxxxxx">
          </div>
          <div class="row">
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 ">
              <div class="form-group">
                <label>Kota/Kecamatan <i class="text-danger">*</i></label>
                <input name="customer_city" type="text" required class="form-control border rounded-pill" placeholder="Surabaya">
              </div>
            </div>
            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12 col-12 ">
              <div class="form-group">
                <label>Kode Pos <i class="text-danger">*</i></label>
                <input name="customer_postcode" type="text" required class="form-control border rounded-pill" placeholder="60999">
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Alamat Lengkap <i class="text-danger">*</i></label>
            <textarea name="customer_address" rows="4" required class="form-control border rounded-pill" placeholder="Jalan Meraih Mimpi 6 No. 2"></textarea>
          </div>
        </div>
      </div>

      <div class="ps-checkout__right">
        <div class="ps-block--your-order">
          <div class="ps-block__header">
            <h3>Pesanan Anda</h3>
          </div>
          <div class="ps-block__divider"></div>
          <div class="ps-block__detail">
            <div class="order-preview"></div>
            <div class="ps-block__divider"></div>
            <p class="total">
              Total <strong class="cart-total">Rp. 0</strong>
            </p>
          </div>
          <div class="ps-block__footer mt-5">
            <button type="submit" class="ps-btn ps-btn--fullwidth">Place Order</button>
          </div>
        </div>
      </div>
    </form>
  </div>
@endsection

@section('customScripts')
  <script>
    window.addEventListener('storage', function(event) {
      if (event.key === 'cart') {
        console.log('Cart changed in another tab. Reloading...');
        window.location.reload();
      }
    });
    
    document.addEventListener('DOMContentLoaded', function () {
      const checkoutForm = document.getElementById('checkout-form');
      const submitButton = checkoutForm.querySelector('button[type="submit"]');

      checkoutForm.addEventListener('submit', async function (event) {
          event.preventDefault();

          submitButton.disabled = true;
          submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processing...';

          const cart = getCart();
          if (cart.length === 0) {
            notify(`Cart masih kosong!`, 'error');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Place Order';
            return;
          }

          const formData = new FormData(checkoutForm);
          const data = Object.fromEntries(formData.entries());
          data.cart = cart;

          try {
            const response = await fetch('{{ route("checkout-payment") }}', { 
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/pdf',
                'X-CSRF-TOKEN': '{{ csrf_token() }}' // <--- REQUIRED: Security token for web routes
              },
              body: JSON.stringify(data),
            });

            if (!response.ok) {
              const err = await response.json().catch();
              notify(err.message, 'error');
              return;
            }

            const blob = await response.blob();
            const disposition = response.headers.get('Content-Disposition');
            let filename = "receipt.pdf";

            if (disposition && disposition.includes("filename=")) {
              filename = disposition.split("filename=")[1];
            }

            const url = window.URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;
            link.click();

            clearCart();
            notify(`Pesanan berhasil dibuat`, 'success');
          } catch (error) {
            notify(`Terjadi masalah dalam pemesanan!`, 'error');
          } finally {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Place Order';
          }
      });
    });
  </script>
@endsection