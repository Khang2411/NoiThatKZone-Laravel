<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{

    function index()
    {
        $yearNow = Carbon::now()->year;
        $sumTotal = 0;
        $ordersByMonth = Order::with('products', 'city', 'district', 'ward', 'user')->where('updated_at', '>=',  now()->subMonth())->get();

        foreach ($ordersByMonth as $order) {
            $total = 0;
            foreach ($order->products as $product) {
                $total = $total + $product->pivot->price * $product->pivot->quantity;
                $order['subtotal'] = $total;
                if ($order->discount >= 1 && $order->discount <= 100) {
                    $order['total'] =  $total - ($total * ($order->discount / 100));
                } else {
                    $order['total'] =  $total - $order->discount;
                }
            }
            $sumTotal += $order['total'];
        }

        // Revenue By Months Year Now
        $sumRevenue = 0;
        for ($i = 1; $i <= 12; $i++) {
            $orders = Order::whereYear('updated_at', $yearNow)->with('products')
                ->whereMonth('updated_at', $i)->get();
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
                }
                $sumRevenue += $order['total'];
            }

            $revenueByMonths[] = $orders->count() === 0 ? 0 : $sumRevenue;
            $sumRevenue = 0;
        }
        // Count order By Month
        for ($i = 1; $i <= 12; $i++) {
            $orderByMonths[] = Order::whereMonth('updated_at', $i)->count();
        };

        $products = Product::where('updated_at', '>=',  now()->subMonth())->get();

        $users = User::where('updated_at', '>=',  now()->subMonth())->get();

        $reviews = Review::orderBy("id", "DESC")->where('review_id', null)->with('user')->take(4)->get();
        // return $reviews;
        return Inertia::render('Dashboard', [
            'orders' => $ordersByMonth, 'reviews' => $reviews, 'order_number' => count($ordersByMonth),
            'revenueByMonths' => $revenueByMonths,  'orderByMonths' => $orderByMonths, 'sumTotal' => $sumTotal, 'product_number' => count($products), 'user_number' => count($users)
        ]);
    }

    function lineChart($xValues, $yValues)
    {
        $chart = "{
            type: 'line',
            data: {
              labels: $xValues,
              datasets: [{
                fill: false,
                lineTension: 0,
                backgroundColor: 'rgba(0,0,255,1.0)',
                borderColor: 'rgba(0,0,255,0.1)',
                data: [$yValues]
              }]
            },
            options: {
              legend: {display: false},
              scales: {
                yAxes: [
                    {
                        ticks: { 
                        callback: function(value, index, values) {
                        return value.toLocaleString('vi',{style:'currency', currency:'VND'})}}
                    }],
              }
            }
          }";
        return urlencode($chart);
    }

    function barChart($xValues, $yValues)
    {
        $chart = "{
            type: 'bar',
            data: {
              labels: [$xValues],
              datasets: [{
                data: [$yValues]
              }]
            },
            options: {
              legend: {display: false},
              scales: {
                yAxes: [
                    {
                        ticks: {precision: 0}
                    }],
              }
            }
          }";
        return urlencode($chart);
    }

    function pdf()
    {
        // Count orders by month
        $yearNow = Carbon::now()->year;
        $oneYearAgo = Carbon::now()->year - 1;
        $twoYearAgo = Carbon::now()->year - 2;
        $years = [$yearNow, $oneYearAgo, $twoYearAgo];
        $ordersByMonth = [];
        $orderByYears = [];

        foreach ($years as $year) {
            $orderByYears[] = Order::whereYear('updated_at', $year)->count();
        };

        // Revenue By Months Year Now
        $sumRevenue = 0;
        for ($i = 1; $i <= 12; $i++) {
            $orders = Order::whereYear('updated_at', $yearNow)->with('products')
                ->whereMonth('updated_at', $i)->get();
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
                }
                $sumRevenue += $order['total'];
            }

            $revenueByMonths[] = $orders->count() === 0 ? 0 : $sumRevenue;
            $sumRevenue = 0;
        }

        // Revenue by years
        $revenueByYears = [];
        $sumRevenueYear = 0;
        foreach ($years as $year) {
            $orders = Order::whereYear('updated_at', $year)->with('products')->get();
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
                }
                $sumRevenueYear += $order['total'];
            }
            $revenueByYears[] = $orders->count() === 0 ? 0 : $sumRevenueYear;
            $sumRevenueYear = 0;
        }

        // return $orderByYears;
        $lineChart = $this->lineChart('[1,2,3,4,5,6,7,8,9,10,11,12]', implode(',', $revenueByMonths));
        $barChart = $this->barChart(implode(',', $years), implode(',', $orderByYears));

        $pdf = Pdf::loadView('statistic', compact('yearNow', 'ordersByMonth', 'revenueByMonths', 'revenueByYears', 'lineChart', 'barChart'));
        return $pdf->download('statistic.pdf');
    }
}
