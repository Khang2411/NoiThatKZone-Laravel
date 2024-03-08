<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Coupon;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\Ward;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Collection;

class AdminOrderController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        if (request()->status === 'trash') {
            $orders = Order::onlyTrashed()->orderBy("id", "DESC")->with('products', 'city', 'district', 'ward', 'user')
                ->when(request()->search, function ($q) {
                    return $q->where('ship_name', 'LIKE', '%' . request()->search . '%');
                })->paginate(10);
            $orders->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
            $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
        } else {
            $orders = Order::orderBy("id", "DESC")->with('products', 'city', 'district', 'ward', 'user')
                ->when(request()->search, function ($q) {
                    return $q->where('ship_name', 'LIKE', '%' . request()->search . '%');
                })->paginate(10);
            $orders->appends(['status' => 'active', 'search' => request()->search]);
        }
        $count_order_active = Order::count();
        $count_order_trash = Order::onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];

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
        }
        //return $orders;
        return Inertia::render('Order/OrderList', ['orders' => $orders, 'list_action' => $list_action, 'count' => $count]);
    }

    function add()
    {
        $cities = City::all();
        $coupons = Coupon::all();
        $products = Product::when(request()->search, function ($q) {
            return $q->where('products.name', 'LIKE', '%' . request()->search . '%');
        })->orderBy("id", "DESC")->paginate(10);
        $products->appends(['order' => request()->order, 'search' => request()->search]);
        //return $products;
        if (request()->wantsJson()) {
            return collect($products);
        }
        return Inertia::render('Order/OrderAdd', ['cities' => $cities, 'coupons' => $coupons, 'products' => $products]);
    }
    function store()
    {
        Validator::make(
            request()->all(),
            [
                'phone' => 'required',
                'email' => 'required',
                'status' => 'required',
                'ship_address' => 'required',
                'city_id' => 'required',
                'district_id' => 'required',
                'ward_id' => 'required',
                'products' => 'required'
            ],
            [
                'email.required' => 'Email là bắt buộc',
                'phone.required' => 'Số điện thoại là bắt buộc',
                'status.required' => 'Trạng thái là bắt buộc',
                'ship_address.required' => 'Số nhà là bắt buộc',
                'city_id.required' => 'Tỉnh/thành là bặt buộc',
                'district_id.required' => 'Quận/huyện là bắt buộc',
                'ward_id.required' => 'Phường/xã là bắt buộc',
                'products.required' => 'Sản phẩm là bắt buộc'
            ]
        )->validate();

        $order = Order::create([
            'email' => request()->email,
            'phone' => request()->phone,
            'status' => request()->status,
            'ship_address' => request()->ship_address,
            'city_id' => request()->city_id,
            'district_id' => request()->district_id,
            'ward_id' => request()->ward_id,
            'coupon_code' => request()->coupon_code,
            'discount' => request()->discount,
            'method' => 'cod'
        ]);

        foreach (request()->products as $product) {
            $order->products()->attach(
                $product['id'],
                ['quantity' => $product['pivot']['quantity'], 'price' => $product['pivot']['price']]
            );
        }
        return redirect()->route('admin.order.list');
    }

    function action()
    {
        $list_check = request()->list_check;

        if (!$list_check) {
            return redirect()->back()->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Order::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Order::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    Order::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }

    function edit()
    {
        $total = 0;

        $order = Order::find(request()->order);
        $order['products'] = $order->products;
        $order['city'] = $order->city;
        $order['district'] = $order->district;
        $order['ward'] = $order->ward;
        $order['user'] = $order->user;
        $order['subtotal'] = 0;
        $order['total'] = 0;
        foreach ($order->products as $product) {
            $total = $total + $product->pivot->price * $product->pivot->quantity;
            $order['subtotal'] = $total;
            if ($order->discount >= 1 && $order->discount <= 100) {
                $order['total'] =  $total - ($total * ($order->discount / 100));
            } else {
                $order['total'] =  $total - $order->discount;
            }
        }
        $cities = City::all();
        $districts = District::where('city_id', $order->city_id)->get();
        $wards =  Ward::where('district_id', $order->district_id)->get();
        $coupons = Coupon::all();
        $products = Product::when(request()->search, function ($q) {
            return $q->where('products.name', 'LIKE', '%' . request()->search . '%');
        })->orderBy("id", "DESC")->paginate(10);
        $products->appends(['order' => request()->order, 'search' => request()->search]);
        //return $products;
        if (request()->wantsJson()) {
            return collect($products);
        }
        return Inertia::render('Order/OrderEdit', ['order' => $order, 'cities' => $cities, 'districts' => $districts, 'wards' => $wards, 'coupons' => $coupons, 'products' => $products]);
    }

    function update()
    {
        Validator::make(
            request()->all(),
            [
                'phone' => 'required',
                //'email' => 'required',
                'status' => 'required',
                'ship_address' => 'required',
                'city_id' => 'required',
                'district_id' => 'required',
                'ward_id' => 'required',

            ],
            [
                'phone.required' => 'Số điện thoại là bắt buộc',
                'status.required' => 'Trạng thái là bắt buộc',
                'ship_address.required' => 'Số nhà là bắt buộc',
                'city_id.required' => 'Tỉnh/thành là bặt buộc',
                'district_id.required' => 'Quận/huyện là bắt buộc',
                'ward_id.required' => 'Phường/xã là bắt buộc'
            ]
        )->validate();

        $order = Order::find(request()->id);
        foreach (request()->products as $product) {
            //return $product['pivot'];
            $order->products()->syncWithoutDetaching([
                $product['id'] => ['quantity' => $product['pivot']['quantity'], 'price' => $product['pivot']['price']]
            ]);
        }

        $order->products()->detach(request()->deleteProductId);
        $order->phone = request()->phone;
        $order->status = request()->status;
        $order->ship_address = request()->ship_address;
        $order->city_id = request()->city_id;
        $order->district_id = request()->district_id;
        $order->ward_id = request()->ward_id;
        $order->coupon_code = request()->coupon_code;
        $order->discount = request()->discount;
        $order->save();
        return redirect()->route('admin.order.list');
    }

    function delete($id)
    {
        $order = Order::withTrashed()->find($id);

        if ($order->deleted_at) {
            $order->forceDelete();
        } else {
            $order->delete();
        }
    }
}
