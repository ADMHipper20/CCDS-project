<?php

namespace Tests\Browser;

use App\Models\Order;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CartTest extends DuskTestCase
{
    /** @test */
    public function cart_add(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/products')
                ->pause(1000);
            $browser->script([
                'document.querySelector(".ps-product .cart-add").click();'
            ]);
            $browser->pause(1000)
                ->assertSee('berhasil ditambahkan ke cart')
                ->pause(1000)
                ->within('.toastify', function (Browser $toast) {
                    $toast->click('.toast-close');
                });
        });
    }

    /** @test */
    public function cart_remove(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->click('.ps-cart--mini')
                ->pause(1000)
                ->click('.ps-cart--mini .ps-cart__items .ps-btn--close')
                ->pause(1000)
                ->assertSee('telah dihapus dari cart')
                ->pause(1000)
                ->within('.toastify', function (Browser $toast) {
                    $toast->click('.toast-close');
                });
        });
    }

    /** @test */
    public function cart_clear(): void
    {
        $this->cart_add();
        $this->browse(function (Browser $browser) {
            $browser->click('.ps-cart--mini')
                ->pause(1000)
                ->click('.cart-clear')
                ->pause(1000)
                ->assertSee('Cart telah dikosongkan')
                ->pause(1000)
                ->within('.toastify', function (Browser $toast) {
                    $toast->click('.toast-close');
                });
        });
    }

    /** @test */
    public function cart_checkout(): void
    {
        $this->cart_add();
        $this->browse(function (Browser $browser) {
            $browser->visit('/checkout')
                ->scrollTo('#checkout-form textarea[name=customer_address]')
                ->typeSlowly('#checkout-form input[name=customer_name]', 'John')
                ->typeSlowly('#checkout-form input[name=customer_whatsapp]', '089123456789')
                ->typeSlowly('#checkout-form input[name=customer_city]', 'Surabaya')
                ->typeSlowly('#checkout-form input[name=customer_postcode]', '60124')
                ->typeSlowly('#checkout-form textarea[name=customer_address]', 'Jl. Example No. 123')
                ->click('#checkout-form button[type=submit]')
                ->waitForText('Pesanan berhasil dibuat', 30);
        });
        Order::latest()->first()->delete();
    }
}
