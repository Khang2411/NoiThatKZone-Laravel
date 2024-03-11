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
use Cloudinary\Api\Upload\UploadApi;

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

    function edit($id)
    {
        $collections = Collection::where('collection_id', '!=', null)->get();
        $product = Product::find($id);
        $product->detailImages;
        return Inertia::render('Product/ProductEdit', ['collections' => $collections, 'product' => $product]);
    }

    function store()
    {
        $input = request()->all();
        $input['price'] = Str::replace(['.', ','], '', $input['price']);
        $input['promotion_price'] = Str::replace(['.', ','], '', $input['promotion_price']);

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
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/products',
                'format' => 'jpg',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            $input['public_id_thumbnail'] = $thumbnail['public_id'];
        }

        $input['slug'] = Str::slug($input['name']);
        if ($input['promotion_price']) {
            $input['price_before_discount'] = $input['price'];
            $input['price'] = $input['promotion_price'];
        }
        $product = Product::create($input);

        // ADD Product Images
        if ($product && request()->hasFile('detail_images')) {
            foreach ($_FILES['detail_images']['tmp_name'] as $image) {
                $detailImage = (new UploadApi())->upload($image, [
                    'folder' => 'noithatkzone/detail_images',
                    'format' => 'jpg',
                    'quality' => '80',
                ]);
                $data['image'] =  $detailImage['secure_url'];
                $data['product_id'] = $product->id;
                $data['public_id_image'] = $detailImage['public_id'];
                DetailImage::create($data);
            }
        }
        return to_route('admin.product.list');
    }

    function update()
    {
        $input = request()->all();
        $input['price'] = Str::replace(['.', ','], '', $input['price']);

        if ($input['promotion_price']) {
            $input['promotion_price'] = Str::replace(['.', ','], '', $input['promotion_price']);
            $input['price_before_discount'] = $input['price'];
            $input['price'] = $input['promotion_price'];
        }
        if (request()->thumbnail == "undefined") {
            $input = request()->except("thumbnail");
        }

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

        $removeImages = request()->removeDetailImage;
        if ($removeImages) {
            foreach ($removeImages as $item) {
                if ($item['public_id_image'] !== null) {
                    (new UploadApi())->destroy($item['public_id_image']);
                }
                DetailImage::destroy($item['id']);
            }
        }

        if (request()->hasFile('detail_images')) { // Có chi tiết ảnh
            foreach ($_FILES['detail_images']['tmp_name'] as $image) {
                $detailImage = (new UploadApi())->upload($image, [
                    'folder' => 'noithatkzone/detail_images',
                    'format' => 'jpg',
                    'quality' => '80',
                ]);
                $data['image'] =  $detailImage['secure_url'];
                $data['product_id'] = $input['id'];
                $data['public_id_image'] = $detailImage['public_id'];
                DetailImage::create($data);
            }
        }

        if (request()->hasFile('thumbnail')) { // Có ảnh 
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/products',
                'format' => 'jpg',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            $input['public_id_thumbnail'] = $thumbnail['public_id'];
        }

        $id = request()->id;

        $product = Product::find($id);
        $product->fill($input)->save();

        $page = request()->back_to;
        return redirect('/admin/product/list?page=' . $page);
    }

    function delete($id)
    {
        $product = Product::withTrashed()->find($id);

        if ($product->deleted_at) {
            if ($product->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($product->public_id_thumbnail);
            }
            $product->forceDelete();
        } else {
            $product->delete();
        }
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
                    $products = Product::withTrashed()->whereIn('id', $list_check)->with('detailImages');
                    foreach ($products->get() as $product) {
                        if ($product->public_id_thumbnail !== null) {
                            (new UploadApi())->destroy($product->public_id_thumbnail);
                        }

                        foreach ($product->detailImages as $detailImage) {
                            if ($detailImage->public_id_image !== null) {
                                (new UploadApi())->destroy($detailImage->public_id_image);
                            }
                            DetailImage::destroy($detailImage->id);
                        }
                    }
                    $products->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
