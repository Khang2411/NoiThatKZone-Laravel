<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminDashboardController extends Controller
{

    function index()
    {
        $orders = Order::with('products', 'city', 'district', 'ward', 'user')
            ->whereMonth('updated_at', '<=',  now()->subMonth())->orderBy("updated_at", "DESC")->get();

        foreach ($orders as $order) {
            $total = 0;
            foreach ($order->products as $product) {
                $total = $total + $product->pivot->price * $product->pivot->quantity;
                $order['subtotal'] = $total;
                if ($order->discount >= 1 && $order->discount <= 100) {
                    $order['total'] =  $total - ($total * ($order->discount / 100));
                } else {
                    $order['total'] =  $total - $order->discount;
                }
                $sum = $total + $order['total'];
            }
        }
        $products = Product::whereMonth('updated_at', '<=',  now()->subMonth())->get();
        $users = User::whereMonth('updated_at', '<=',  now()->subMonth())->get();

        // return $sum;
        $reviews = Review::orderBy("id", "DESC")->where('review_id', null)->with('user')->take(4)->get();
        // return $reviews;
        return Inertia::render('Dashboard', [
            'orders' => $orders, 'reviews' => $reviews, 'order_number' => count($orders),
            'sumTotal' => $sum, 'product_number' => count($products), 'user_number' => count($users)
        ]);
    }

    function pdf()
    {
        $productsNot = Product::whereMonth('updated_at', '<=',  now()->subMonth())->get();

        $pdf = Pdf::loadView('invoice');
        $pdf->download('invoice.pdf');
    }
}
