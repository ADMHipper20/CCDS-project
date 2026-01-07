<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Collection;

// 1. IMPORT YOUR API CONTROLLERS
use App\Http\Controllers\Api\SingleController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\OrderController;

// Import Models for the 'show' functions
use App\Models\Product;
use App\Models\Single;

class GeneralController extends Controller
{

    private function getData($response) {
        return $response->getData(True);
    }

    public function home() {
        // Direct calls to API logic
        $singles    = $this->getData((new SingleController)->index());
        $categories = $this->getData((new CategoryController)->index());
        $products   = $this->getData((new ProductController)->index());
        $reviews    = $this->getData((new ReviewController)->index());

        return view('index', [
            'singles' => $singles,
            'categories' => $categories,
            'products' => $products,
            'reviews' => $reviews,
        ]);
    }

    public function products(Request $request)
    {
        $categories = $this->getData((new CategoryController)->index());
        $products   = $this->getData((new ProductController)->index());

        $search = $request->get('search');
        $category = $request->get('category');
        $sort = $request->get('sort'); // 1=Nama A-Z, 2=Nama Z-A, 3=Harga Terendah, 4=Harga Tertinggi
        $page = $request->get('page', 1);
        $perPage = 8;

        if ($category) {
            $products = array_filter($products, function ($p) use ($category) {
                return collect($p['categories'])->pluck('id')->contains(intval($category));
            });
        }

        if ($search) {
            $products = array_filter($products, function ($p) use ($search) {
                return stripos($p['title'], $search) !== false;
            });
        }

        $total = count($products);

        if ($sort) {
            switch ($sort) {
                case '1':
                    usort($products, fn($a, $b) => strcmp($a['title'], $b['title']));
                    break;
                case '2':
                    usort($products, fn($a, $b) => strcmp($b['title'], $a['title']));
                    break;
                case '3':
                    usort($products, fn($a, $b) => $a['price'] <=> $b['price']);
                    break;
                case '4':
                    usort($products, fn($a, $b) => $b['price'] <=> $a['price']);
                    break;
            }
        }

        $products = array_slice($products, ($page - 1) * $perPage, $perPage);
        
        return view('products.index', [
            'categories' => $categories,
            'products' => $products,
            'total' => $total,
            'perPage' => $perPage,
            'currentPage' => $page,
            'selectedCategory' => $category,
            'selectedSort' => $sort,
        ]);
    }

    public function product($id) {
        $productModel = Product::findOrFail($id);

        $product = $this->getData((new ProductController)->show($productModel));
        $reviews = $this->getData((new ReviewController)->index());

        $filteredReviews = array_filter($reviews, function ($review) use ($id) {
            return $review['product_id'] == $id;
        });

        $productRating = count($filteredReviews) > 0 
            ? array_sum(array_column($filteredReviews, 'rating')) / count($filteredReviews) 
            : 0;

        return view('products._detail', [
            'product' => $product,
            'reviews' => $filteredReviews,
            'productRating' => $productRating,
        ]);
    }

    public function productImage($filename)
    {
        $apiHost = env('API_HOST'); // http://nginx-api
        $token = env('BEARER_TOKEN');

        // 1. Fetch the image from the API (Server-to-Server)
        $response = Http::withToken($token)
            ->get("$apiHost/storage/$filename");

        // 2. If image doesn't exist, return 404
        if ($response->failed()) {
            abort(404);
        }

        // 3. Return the image data with the correct content type (e.g., image/jpeg)
        return response($response->body())
            ->header('Content-Type', $response->header('Content-Type'));
    }
    
    public function orderCheck(Request $request) {
        $invoice = $request->get('invoice', '0');
        
        // Handling the "show" logic for Order manually since it might return 404
        try {
            $orderModel = \App\Models\Order::where('invoice', $invoice)->firstOrFail();
            $order = $this->getData((new OrderController)->show($orderModel));
        } catch (\Exception $e) {
            $order = [];
        }

        $whatsapp = $order['customer_whatsapp'] ?? '';
        $whatsapp = substr($whatsapp, -5);

        return view('order-check', [
            'order' => $order,
            'whatsapp' => $whatsapp
        ]);
    }
    
    public function checkout() {
        return view('checkout.index', []);
    }

    public function checkoutPayment(Request $request)
    {
        $apiHost = env('API_HOST');
        $token = env('BEARER_TOKEN');

        // 1. Forward the data to the API securely
        // We act as a "Proxy": The browser talks to us, we talk to the API
        $response = Http::withToken($token)
            ->accept('application/pdf') // We tell the API we want the PDF receipt back
            ->post("$apiHost/api/orders/checkout", $request->all());

        // 2. If the API fails (e.g., validation error), forward the error to JS
        if ($response->failed()) {
            return response()->json($response->json(), $response->status());
        }

        // 3. If success, pass the PDF back to the browser
        // We get the raw PDF content from the API and stream it to the user
        return response($response->body())
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', $response->header('Content-Disposition'));
    }
    
    public function single($slug) {
        $singleModel = Single::where('slug', $slug)->firstOrFail();
            
        $single = $this->getData((new SingleController)->show($singleModel));
            
        return view('single', [
            'pageTitle' => $single['title'] ?? '404',
            'body' => $single['body'] ?? '',
            'accordions' => $single['accordions'] ?? [],
        ]);
    }

    public function submitReview(Request $request)
    {
        $apiHost = env('API_HOST'); // http://nginx-api

        // Prepare the data
        $data = $request->all();
        // Crucial: Tell the API to redirect back to THIS page (localhost:8000/products/1)
        // If we don't set this, the API might try to redirect to an internal Docker URL.
        $data['redirect_url'] = url()->previous(); 

        // Send POST to API
        // We use 'allow_redirects' => false because the API returns a 302 Redirect.
        // We want to capture that 302, not follow it inside the controller.
        $response = Http::withOptions(['allow_redirects' => false])
                        ->post("$apiHost/api/reviews/submit", $data);

        // If the API says "Redirect" (Success or Validation Error), we obey.
        if ($response->status() == 302) {
            return redirect($response->header('Location'));
        }

        // Fallback for system errors (500, 404)
        return back()->with('error', 'Gagal menghubungi server review.');
    }
}
