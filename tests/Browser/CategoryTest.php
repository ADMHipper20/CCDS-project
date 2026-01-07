<?php

namespace Tests\Browser;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class CategoryTest extends DuskTestCase
{
    /** @test */
    public function admin_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/login')
                ->type('#data\.email', 'admin@impal.com')
                ->type('#data\.password', 'admin123')
                ->press('#form > div.fi-form-actions > div > button')
                ->waitForLocation('/admin', 30)
                ->assertPathIs('/admin');
        });
    }

    /** @test */
    public function admin_category_list(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/categories')
                ->assertSee('Categories');
        });
    }

    /** @test */
    public function admin_category_create(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/categories/create')
                ->typeSlowly('#data\.title', 'New Category')
                ->press('#key-bindings-1')
                ->waitUntil("window.location.href != '/admin/categories/create'")
                ->waitForText('Created', 20)
                ->assertSee('Created');
        });
    }

    /** @test */
    public function admin_category_update(): void
    {
        $this->browse(function (Browser $browser) {
            $lastItem = Category::latest()->first()->id;
            $browser->visit("/admin/categories/{$lastItem}/edit")
                ->typeSlowly('#data\.title', 'Updated Category')
                ->press('#key-bindings-2')
                ->waitForText('Saved', 40)
                ->assertSee('Saved');
        });
    }

    /** @test */
    public function admin_category_delete(): void
    {
        $this->browse(function (Browser $browser) {
            $lastItem = Category::latest()->first()->id;
            $browser->visit("/admin/categories/{$lastItem}/edit")
                ->press('#key-bindings-1')
                ->waitFor('.fi-modal-window button[x-data*="isProcessing"]')
                ->press('.fi-modal-window button[x-data*="isProcessing"]')
                ->waitForLocation('/admin/categories')
                ->assertSee('Deleted');
        });
    }

    /** @test */
    public function admin_required_empty_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/categories/create')
                ->pause(3000)
                ->press('#key-bindings-1')
                ->assertFocused('#data\.title');
        });
    }

    /** @test */
    public function admin_required_empty_force_save_error(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/admin/categories/create')
                ->pause(3000)
                ->press('#key-bindings-2')
                ->waitForLocation('/admin/categories/create')
                ->waitForText('field is required', 20)
                ->assertSee('field is required');
        });
    }
}
