<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\DetailImage;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class AdminProductController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $products = Product::onlyTrashed()
                    ->join('collections', 'collections.id', '=', 'products.collection_id')
                    ->select('products.*')->when(request()->search, function ($q) {
                        return $q->where('products.name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);
                $products->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $products = Product::join('collections', 'collections.id', '=', 'products.collection_id')
                    ->select('products.*', 'collections.name as cate_name')->with('detailImages')->when(request()->search, function ($q) {
                        return $q->where('products.name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);
                $products->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }
        $count_order_active = Product::count();
        $count_order_trash = Product::onlyTrashed()->count();

        $count = [$count_order_active, $count_order_trash];

        $collections = Collection::where('collection_id', '!=', null)->get();
        return Inertia::render('Product/ProductList', ['products' => $products, 'list_action' => $list_action, 'count' => $count, 'collections' => $collections]);
    }

    function add()
    {
        $collections = Collection::where('collection_id', '!=', null)->get();
        return Inertia::render('Product/ProductAdd', ['collections' => $collections]);
    }

    function store()
    {
        $input = request()->all();
        $input['price'] = Str::replace('.', '', $input['price']);
        $input['promotion_price'] = Str::replace('.', '', $input['promotion_price']);
        Validator::make(
            $input,
            [
                'name' => 'required',
                'collection_id' => 'required',
                'stock' => 'nullable',
                'price' => 'required|integer',
                'promotion_price' => 'nullable|integer',
                'thumbnail' => 'required',
                'describe' => 'required'
            ],
            [
                'name.required' => 'Tên sản phẩm là bắt buộc',
                'collection_id.required' => 'Thể loại  là bắt buộc',
                'price.required' => 'Giá sản phẩm là bắt buộc',
                'price.integer' => 'Giá sản phẩm không hợp lệ',
                'promotion_price.integer' => 'Giá khuyến mãi không hợp lệ',
                'thumbnail.required' => 'Ảnh sản phẩm là bắt buộc',
                'describe.required' => 'Nội dung sản phẩm là bắt buộc'
            ]
        )->validate();

        if (request()->hasFile('thumbnail')) {
            $name = request()->file('thumbnail')->getClientOriginalName();
            request()->file('thumbnail')->storeAs('public/products', $name);
            $urlImg = '/storage/products/' . $name;
        }
        $input['thumbnail'] = url($urlImg);
        $input['slug'] = Str::slug($input['name']); // vẫn thêm slug vô dc mảng input dù view kh có name 
        if ($input['promotion_price']) {
            $input['price_before_discount'] = $input['price'];
            $input['price'] = $input['promotion_price'];
        }
        $product = Product::create($input);

        // ADD Product Images
        if ($product && request()->hasFile('detail_images')) {
            foreach (request()->file('detail_images') as $image) {
                $nameFile = $image->getClientOriginalName();
                $image->storeAs('public/detail_images/', $nameFile); // cho ảnh vô thư mục
                $urlMultifile = 'storage/detail_images/' . $nameFile; // lấy url
                $data['image'] = url($urlMultifile); // lấy form name multifile để add vô database không lấy $input vì đang gán nhiều trưởng $input = $request->all();
                $data['product_id'] = $product->id;
                DetailImage::create($data);
            }
        }
        return to_route('admin.product.list');
    }

    function update()
    {
        // if (request()->hasFile('detail_images')) { // Có chi tiết ảnh
        //     foreach (request()->file("detail_images") as  $image) {
        //         echo 1;
        //         // $extension = $image->extension();
        //         // $imageName = microtime() . md5(Str::random(10)) . '.' . $extension;
        //         // $imageName = Str::replace(" ", "", $imageName);
        //         // $image->storeAs('public/detail_images/', $imageName); // cho ảnh vô thư mục
        //         // $urlMultifile = 'storage/detail_images/' . $imageName; // lấy url
        //         // $data['image'] = url($urlMultifile); // lấy form name multifile để add vô database không lấy $input vì đang gán nhiều trưởng $input = $request->all();
        //         // $data['product_id'] = $input['id'];
        //         // DetailImage::create($data);
        //     }
        // }
        // return request()->all();
        $input = request()->all();
        $input['price'] = Str::replace('.', '', $input['price']);
        $input['promotion_price'] = Str::replace('.', '', $input['promotion_price']);
        if ($input['promotion_price']) {
            $input['price_before_discount'] = $input['price'];
            $input['price'] = $input['promotion_price'];
        }
        if (request()->thumbnail == "undefined") {
            $input = request()->except("thumbnail");
        }

        if (request()->hasFile('detail_images')) { // Có chi tiết ảnh
            foreach (request()->file("detail_images") as  $image) {
                $extension = $image->extension();
                $imageName = microtime() . md5(Str::random(10)) . '.' . $extension;
                $imageName = Str::replace(" ", "", $imageName);
                $image->storeAs('public/detail_images/', $imageName); // cho ảnh vô thư mục
                $urlMultifile = 'storage/detail_images/' . $imageName; // lấy url
                $data['image'] = url($urlMultifile); // lấy form name multifile để add vô database không lấy $input vì đang gán nhiều trưởng $input = $request->all();
                $data['product_id'] = $input['id'];
                DetailImage::create($data);
            }
        }

        if (request()->hasFile('thumbnail')) { // Có ảnh 
            $extension = request()->file('thumbnail')->extension();
            // $imageName = microtime() . md5(Str::random(10)) . '.' . $extension;
            // $imageName = Str::replace(" ", "", $imageName);
            // response(Storage::disk("products")->put($imageName, file_get_contents(request()->file('thumbnail'))));
            // $input["thumbnail"] = url($imageName);
            // if (request()->default_thumbnail) {
            //     unlink(storage_path('app/public/products/' . request()->default_thumbnail));
            // }
            $name = request()->file('thumbnail')->getClientOriginalName();
            request()->file('thumbnail')->storeAs('public/products', $name);
            $urlImg = '/storage/products/' . $name;
            $input["thumbnail"] = url($urlImg);
        }
        $id = request()->id;
        $product = Product::find($id);
        $product->fill($input)->save();
        $product->collection;
        // return response()->json($product);
    }

    function action()
    {
        $list_check = request()->list_check; // $list_check là 1 mảng có mảng thì phải duyệt
        if (!$list_check) {
            return to_route('admin.product.list')->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Product::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Product::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    Product::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
