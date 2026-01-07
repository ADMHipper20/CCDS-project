<?php

namespace App\Http\Controllers\Api;

use App\Models\Review;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $reviews = Review::with('order:id,receipt,customer_name')->get()->map(function ($review) {
            return [
                'id' => $review->id,
                'customer_name' => $review->order ? $this->maskName($review->order->customer_name) : '******',
                'body' => $review->body,
                'rating' => $review->rating,
                'order_receipt' => $review->order ? $review->order->receipt : null,
                'product_id' => $review->product_id,
                'created_at' => $review->created_at->format('d/m/Y'),
            ];
        });

        return response()->json($reviews);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $review = Review::create($request->all());
        return $this->formatResponse($review);
    }

    /**
     * Display the specified resource.
     */
    public function show(Review $review)
    {
        $review->load('order:id,receipt,customer_name');

        return response()->json([
            'id' => $review->id,
            'customer_name' => $review->order ? $review->order->customer_name : '******',
            'body' => $review->body,
            'rating' => $review->rating,
            'order_receipt' => $review->order ? $review->order->receipt : null,
            'product_id' => $review->product_id,
            'created_at' => $review->created_at->format('d/m/Y'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Review $review)
    {
        $review->update($request->all());
        return $this->formatResponse($review);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Review $review)
    {
        $review->delete();
        return response()->json(null, 204);
    }

    /**
     * Add or update a review based on order_receipt.
     */
    public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'order_receipt' => 'required|string',
            'customer_whatsapp' => 'required|digits:5',
            'body' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'product_id' => 'required|integer',
            'redirect_url' => 'nullable|string'
        ]);
        $redirect = $request->input('redirect_url', url()->previous());
        if ($validator->fails()) {
            return redirect($redirect . '?error=' . urlencode($validator->errors()->first()));
        }
        $validated = $validator->validated();
        
        $order = Order::where('receipt', $validated['order_receipt'])
            ->where('customer_whatsapp', 'like', "%{$validated['customer_whatsapp']}")
            ->first();
        if (!$order) {
            return redirect($redirect . '?error=' . urlencode('Invoice atau nomor WhatsApp salah.'));
        }

        $orderProducts = OrderProduct::where('order_id', $order->id)
            ->where('product_id', $validated['product_id'])
            ->first();
        if (!$orderProducts) {
            return redirect($redirect . '?error=' . urlencode('Order tidak sesuai dengan produk yang diulas.'));
        }

        $review = Review::where('order_id', $order->id)->first();

        if ($review) {
            $review->update([
                'body' => $validated['body'],
                'rating' => $validated['rating'],
            ]);
        } else {
            Review::create([
                'order_id' => $order->id,
                'product_id' => $validated['product_id'],
                'title' => "Ulasan untuk {$orderProducts->product->title}",
                'body' => $validated['body'],
                'rating' => $validated['rating'],
            ]);
        }

        return redirect($redirect . '?success=' . urlencode('Review berhasil disimpan.'));
    }

    private function formatResponse($review)
    {
        $review->load('order:id,receipt');
        return response()->json([
            'id' => $review->id,
            'title' => $review->title,
            'body' => $review->body,
            'rating' => $review->rating,
            'order_receipt' => $review->order ? $review->order->receipt : null,
            'product_id' => $review->product_id,
        ]);
    }

    private function maskName($name) {
        $parts = explode(' ', trim($name));
        $masked = [];

        foreach ($parts as $p) {
            $first = substr($p, 0, 1);
            $stars = str_repeat('*', max(strlen($p) - 1, 0));
            $masked[] = $first . $stars;
        }

        return implode(' ', $masked);
    }
}
