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
        $total = 0;
        $sumByMonth = 0;

        $ordersByMonth = Order::with('products', 'city', 'district', 'ward', 'user')
            ->where('updated_at', '>=',  now()->subMonth())->get();

        $orders = Order::with('products', 'city', 'district', 'ward', 'user')->orderBy("updated_at", "DESC")->get();

        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $total = $total + $product->pivot->price * $product->pivot->quantity;
                $order['subtotal'] = $total;
                if ($order->discount >= 1 && $order->discount <= 100) {
                    $order['total'] =  $total - ($total * ($order->discount / 100));
                } else {
                    $order['total'] =  $total - $order->discount;
                }
            }
        }

        foreach ($ordersByMonth as $order) {
            foreach ($order->products as $product) {
                $total = $total + $product->pivot->price * $product->pivot->quantity;
                $order['subtotal'] = $total;
                if ($order->discount >= 1 && $order->discount <= 100) {
                    $order['total'] =  $total - ($total * ($order->discount / 100));
                } else {
                    $order['total'] =  $total - $order->discount;
                }
                $sumByMonth = $total + $order['total'];
            }
        }

        $products = Product::where('updated_at', '>=',  now()->subMonth())->get();
    
        $users = User::where('updated_at', '>=',  now()->subMonth())->get();

        $reviews = Review::orderBy("id", "DESC")->where('review_id', null)->with('user')->take(4)->get();
        // return $reviews;
        return Inertia::render('Dashboard', [
            'orders' => $orders, 'reviews' => $reviews, 'order_number' => count($orders),
            'sumTotal' => $sumByMonth, 'product_number' => count($products), 'user_number' => count($users)
        ]);
    }

    function pdf()
    {
        $productsNot = Product::whereMonth('updated_at', '<=',  now()->subMonth())->get();

        $pdf = Pdf::loadView('invoice');
        $pdf->download('invoice.pdf');
    }
}
