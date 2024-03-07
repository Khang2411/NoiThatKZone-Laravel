<?php

use App\Http\Controllers\AdminCheckOutController;
use App\Models\Address;
use App\Models\City;
use App\Models\Collection;
use App\Models\Coupon;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use App\Models\Ward;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Auth\Events\Registered;
use App\Models\Banner;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Post;
use App\Models\Slider;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('v1')->group(function () {
    Route::post('/login', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails())
            return response()->json(['code' => 400, 'message' => $validator->errors()], 200);
        // Attempt to authenticate
        if (Auth::attempt(
            [
                'email' => request('email'),
                'password' => request('password'),
            ]
        )) {
            $user = Auth::user();
            $data['token'] = $user->createToken('accessToken')->plainTextToken;
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    })->name('api.login');

    Route::get('/profile', function (Request $request) {
        $user = auth('sanctum')->user();
        if ($user) {
            $user->address;
            if (isset($user->address)) {
                $city = Address::find($user->address['id'])->city;
                $district = Address::find($user->address['id'])->district;
                $ward = Address::find($user->address['id'])->ward;
                $user->address['apartment_number'] =  $user->address['apartment_number'] . ", " . $ward->name . ", " .  $district->name . ", " . $city->name;
            }
        } else {
            return response()->json(['data' => null], 200);
        }
        return response()->json(['data' => $user], 200);
    });

    Route::post('/logout', function () {
        $user = auth('sanctum')->user();
        $user->tokens()->delete();
        $data['message'] = 'Logout Success';
        return response()->json(['data' => $data], 200);
    });

    Route::post('/register', function (Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:users,email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'role_id' => '3',
            'password' => Hash::make($request->password),
        ]);
        // event(new Registered($user));
        $address = Address::create([
            'user_id' => $user->id,
            'city_id' => $request->city_id,
            'district_id' => $request->district_id,
            'ward_id' => $request->ward_id,
            'apartment_number' => $request->apartment_number,
        ]);
        return response()->json(['message' => 'Success'], 200);
    })->name('api.register');

    Route::get('/profile/address/{id}', function ($id) {
        $address = User::find($id)->address;
        return response()->json($address);
    })->name('api.address');

    Route::get('/home/collections', function () {
        $collections = Collection::where('collection_id', '=', null)->with('collections')->get();
        return response()->json(['data' => $collections]);
    })->name('api-home.collections');

    Route::get('/home/best-seller', function () {
        $products['list_best_seller'] = Product::where('is_hot', '1')->get();
        $products['list_featured_office'] = Product::where('is_featured', '1')->get();
        return response()->json(['data' => $products]);
    })->name('api-home.getBestSeller');

    Route::get('/collections/{id}', function ($id) {
        $collection = Collection::find($id);
        $collection->rootCollection;

        if ($collection->collection_id === null) {
            $collection->collections;
        }
        if ($collection->collection_id) {
            $collection['collections'] =  $collection::find($collection->collection_id)->collections;
        }
        return response()->json($collection);
    })->name('api-collection.byId');

    Route::get('/listings', function () {
        $temp = array();
        $collections = Collection::find(request()->collection_id)->collections;
        foreach ($collections as $collection) {
            array_push($temp, $collection->id);
        }
        switch (request()->sort) {
            case ('default'):
                $products = Product::where("collection_id", request()->collection_id)
                    ->orWhereIn("collection_id", $temp)
                    ->orderBy('id', 'desc')
                    ->paginate(request()->limit);
                break;
            case ('top-seller'):
                $products = Product::where("collection_id", request()->collection_id)
                    ->orWhereIn("collection_id", $temp)
                    ->orderBy('is_hot', 'desc')
                    ->paginate(request()->limit);
                break;
            case ('price-asc'):
                $products = Product::where("collection_id", request()->collection_id)
                    ->orWhereIn("collection_id", $temp)
                    ->orderBy('price', 'asc')
                    ->paginate(request()->limit);
                break;
            case ('price-desc'):
                $products = Product::where("collection_id", request()->collection_id)
                    ->orWhereIn("collection_id", $temp)
                    ->orderBy('price', 'desc')
                    ->paginate(request()->limit);
                break;
            default:
                $products = Product::where("collection_id", request()->collection_id)
                    ->orWhereIn("collection_id", $temp)
                    ->orderBy('id', 'desc')
                    ->paginate(request()->limit);
        }
        return response()->json($products);
    })->name('api-products.byCollection');

    Route::get('/listings/{id}', function ($id) {
        $product = Product::find($id);
        $product->collection->rootCollection;
        return response()->json($product);
    })->name('api-product.byID');

    Route::get('listings/{id}/similar', function ($id) {
        $collection_id = Product::find($id)->collection_id;
        $products = Product::where('collection_id', $collection_id)->inRandomOrder()->limit(8)->get();
        return response()->json(['data' => $products]);
    })->name('api-product.similar');

    Route::get('/cities', function () {
        $cities = City::all();
        return response()->json(['data' => $cities]);
    })->name('api-cities');

    Route::get('/districts/{cityId}', function ($cityId) {
        $districts = City::find($cityId)->districts;
        return response()->json(['data' => $districts]);
    })->name('api-districts.byCityID');

    Route::get('/wards/{districtId}', function ($districtId) {
        $wards = District::find($districtId)->wards;
        return response()->json(['data' => $wards]);
    })->name('api-wards.byDistrictID');

    Route::get('/reviews', function () {
        $reviews = Review::where('product_id', request()->product_id)
            ->where('status', 'confirmed')->where('review_id', '=', null)
            ->with('user', 'replies')->paginate(10);
        foreach ($reviews as $review) {
            foreach ($review['replies'] as $reply) {
                $reply['user_name'] = User::find($reply['user_id'])->name;
            }
        }
        return response()->json($reviews);
    })->name('api-reviews.byProductId');

    Route::post('/reviews/add', function () {
        //return request()->product_id;
        Review::create([
            'product_id' => request()->product_id,
            'content' => request()->review,
            'user_id' => request()->user_id,
            'rating' => request()->rating,
            'status' => 'pending'
        ]);
    })->name('api-reviews.add');

    Route::post('/reviews/reply', function () {
        Review::create([
            'product_id' => request()->product_id,
            'user_id' => request()->user_id,
            'review_id' => request()->review_id,
            'content' => request()->reply,
            'rating' => request()->rating,
            'status' => 'pending'
        ]);
    })->name('api-reviews.add');

    Route::get('/search/{search}', function ($search) {
        $products = Product::where('name', 'LIKE', '%' . $search . '%')->get();
        return response()->json(['data' => $products]);
    })->name('api-search');

    Route::post('account/email/update', function () {
        $user = auth('sanctum')->user();
        $user = User::find($user->id);
        $user->email = request()->email;
        if ($user->role_id != 1) {
            $user->save();
        }
    })->name('api.account.email.update');

    Route::post('account/phone/update', function () {
        $user = auth('sanctum')->user();
        $user = User::find($user->id);
        $user->phone = request()->phone;
        if ($user->role_id != 1) {
            $user->save();
        }
    })->name('api.account.phone.update');

    Route::post('account/password/update', function () {
        $user = auth('sanctum')->user();
        $user = User::find($user->id);
        $user->password = Hash::make(request()->password);
        if ($user->role_id != 1) {
            $user->save();
        }
    })->name('api.account.password.update');

    Route::post('account/address/update', function () {
        $user = auth('sanctum')->user();
        $user = User::find($user->id);
        $user->phone = request()->phone;

        $address = $user->address;

        if (!isset($address->id)) {
            Address::create([
                'user_id' => $user->id,
                'apartment_number' => request()->apartment_number,
                'city_id' => request()->city_id,
                'district_id' => request()->district_id,
                'ward_id' => request()->ward_id
            ]);
        } else {
            Address::find($address->id);
            $address->apartment_number = request()->apartment_number;
            $address->city_id = request()->city_id;
            $address->district_id = request()->district_id;
            $address->ward_id = request()->ward_id;
            $address->save();
        }
        $user->save();
    })->name('api.account.address.update');

    Route::post('/cart', function () {
        $productId = [];
        $total = 0;
        foreach (request()->product as $product) {
            array_push($productId, $product['id']);
        }
        $cart = Product::whereIn('id',  $productId)->get();

        foreach ($cart as $cartItem) {
            foreach (request()->product as $product) {
                if ($cartItem['id'] == $product['id']) {
                    $cartItem['quantity'] = $product['quantity'];
                }
            }
            $total = $total + $cartItem['quantity'] * $cartItem['price'];
        }
        return response()->json(['data' =>  $cart, "total" => $total], 200);
    })->name('api-cart-list');

    Route::post('/cart/stock', function () {
        $product = Product::find(request()->product_id);
        if ($product->stock < request()->quantity) {
            return response()->json(['error' => "Số lượng còn lại của sản phẩm này là {$product->stock}"], 400);
        } else {
            return response()->json(['data' => "success"], 200);
        }
    })->name('api-cart-stock');

    Route::get('/user/cart', function () {
        $user = auth('sanctum')->user();
        $cart = Cart::where('user_id', $user->id)->with('products:id')->first();
        $productId = [];
        $total = 0;
        foreach ($cart->products as $product) {
            array_push($productId, $product['id']);
        }

        $products = Product::whereIn('id',  $productId)->get();

        foreach ($products as $cartItem) {
            foreach ($cart->products as $product) {
                if ($cartItem['id'] == $product['id']) {
                    $cartItem['quantity'] = $product['pivot']['quantity'];
                }
            }
            $total = $total + $cartItem['quantity'] * $cartItem['price'];
        }
        return response()->json(['data' =>  $products, "total" => $total], 200);
    })->name('api-cart-list');

    Route::post('/user/cart/quantity', function () {
        $user = auth('sanctum')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        $cart->products()->syncWithoutDetaching(
            [
                request()->product_id =>
                ['quantity' => request()->quantity]
            ]
        );
        return request();
    })->name('api-cart-list');

    Route::post('/user/cart/remove', function () {
        $user = auth('sanctum')->user();
        $cart = Cart::where('user_id', $user->id)->first();
        $cart->products()->detach([request()->product_id]);
        return $cart;
    })->name('api-cart-list');

    Route::post('/cart/add', function () {
        $user = auth('sanctum')->user();
        $cart = Cart::updateOrCreate(
            ['user_id' => $user->id],
            ['user_id' => $user->id]
        );

        $cart = Cart::where('user_id', $user->id)->first();
        $test = CartDetail::where('cart_id', $cart->id)->where('product_id', request()->product_id)->first();
        if (isset($test->quantity)) {
            $test->quantity;
            CartDetail::where('cart_id', $cart->id)->where('product_id', request()->product_id)->update(['quantity' => $test->quantity + 1]);
        } else {
            $cart->products()->syncWithoutDetaching(
                [
                    request()->product_id =>
                    ['quantity' => request()->quantity]
                ]
            );
        }
    })->name('api-cart-stock');


    Route::post('/cart/coupon', function () {
        $total = request()->total;
        $coupon = Coupon::where('code', request()->coupon)->first();
        if ($coupon) {
            if ($coupon->minimum_spend <= $total) {
                if ($coupon->type === 'percent') {
                    $discount =  $total * ($coupon->amount / 100);
                    $total = $total - $discount;
                } else {
                    $discount =  $coupon->amount;
                    $total = $total - $discount;
                }
                return response()->json(["data" => $coupon, "total" => $total, "discount" => $discount], 200);
            } else {
                return response()->json(["error" => "Không đủ điều kiện"], 400);;
            }
        } else {
            return response()->json(["error" => "Mã khuyến mãi không hợp lệ"], 400);;
        }
    })->name('api-cart-coupon');

    Route::post('/checkout/momo', [AdminCheckOutController::class, 'momoCheckout'])->name('api.checkout.momo');
    Route::get('/checkout/momo/return', [AdminCheckOutController::class, 'momoCheckoutReturn'])->name('api.checkout.momo.return');
    Route::post('/checkout', function () {
        $order = Order::create([
            'user_id' => request()->order['user_id'],
            'phone' => request()->order['phone'],
            'ship_address' => request()->order['ship_address'],
            'method' => request()->order['method'],
            'status' => request()->order['status'],
            'city_id' => request()->order['city_id'],
            'district_id' => request()->order['district_id'],
            'ward_id' => request()->order['ward_id'],
            'discount' => request()->order['discount'],
        ]);
        $order = Order::find($order->id);
        foreach (request()->product as $product) {
            $order->products()->attach($product['id'], ['quantity' => $product['quantity'], 'price' => $product['price']]);
        }
        return response()->json($order);
    })->name('checkout');
    Route::post('/checkout/vnpay', [AdminCheckOutController::class, 'vnpayCheckout'])->name('api.checkout.vnpay');
    Route::get('/checkout/vnpay/return', [AdminCheckOutController::class, 'vnpayCheckoutReturn'])->name('api.checkout.vnpay.return');

    Route::get('order/list', function () {
        $user = auth('sanctum')->user();
        if ($user) {
            switch (request()->status) {
                case 'all':
                    $orders = Order::where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
                case 'pending':
                    $orders = Order::where('status', 'pending')->where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
                case 'processing':
                    $orders = Order::where('status', 'processing')->where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
                case 'confirmed':
                    $orders = Order::where('status', 'confirmed')->where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
                case 'completed':
                    $orders = Order::where('status', 'completed')->where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
                default:
                    $orders = Order::where('status', 'cancelled')->where('user_id', $user->id)->orderBy('id', 'desc')->with('products')->get();
                    return response()->json(['data' => $orders]);
                    break;
            }
        }
    })->name('api.order.list');

    Route::get('order/{id}', function ($id) {
        $subTotal = 0;
        $total = 0;
        $user = auth('sanctum')->user();
        if ($user) {
            $order = Order::find($id);
            $products = $order->products;
            foreach ($products as $product) {
                $subTotal = $subTotal + $product['pivot']['quantity'] * $product['pivot']['price'];
            }
            foreach ($products as $product) {
                if ($order->discount >= 1  && $order->discount <= 100) {
                    $sumDiscount =  $subTotal * ($order->discount / 100);
                    $total = $subTotal - $sumDiscount;
                } else {
                    $sumDiscount =  $order->discount;
                    $total = $subTotal - $sumDiscount;
                }
            }
            return response()->json(['data' =>  $order, "subTotal" => $subTotal, "total" => $total, 'sumDiscount' => $sumDiscount], 200);
        }
    });

    Route::get('/home/posts', function () {
        $post = Post::take(request()->limit)->orderBy('id', 'DESC')->get();
        return response()->json(['data' => $post], 200);
    })->name('api-home-post');

    Route::get('/posts', function () {
        $post = Post::find(request()->id);
        $post->user;
        $similar = Post::take(3)->orderBy('id', 'DESC')->get();
        return response()->json(['data' => $post, 'similar' => $similar], 200);
    })->name('api-post-details');

    Route::get('/home/banner', function () {
        $sliders = Slider::all();
        $banners = Banner::all();
        return response()->json(['data' => ['sliders' => $sliders, 'banners' => $banners]], 200);
    })->name('api-post-details');

    Route::post('/social/login', function () {
        $user = User::where('provider_id', request()->provider_id)->orWhere('email', request()->email)->first();

        if (!isset($user)) {
            $user = User::create([
                'name' => request()->name,
                'email' => request()->email ? request()->email : "",
                'password' =>  Hash::make(request()->provider_id),
                'phone' => '',
                'provider_id' => request()->provider_id,
                'provider_type' => request()->provider
            ]);
        }

        if (isset($user) && $user->provider_id === null) {
            $user = User::find($user->id);
            $user->provider_id = request()->provider_id;
            $user->provider_type = request()->provider;
            $user->save();
        }

        if ($user) {
            $data['token'] = $user->createToken('accessToken')->plainTextToken;
            return response()->json(['data' => $data], 200);
        } else {
            return response()->json(['error' => 'Unauthorised'], 401);
        }
    })->name('api-social-login');
});
