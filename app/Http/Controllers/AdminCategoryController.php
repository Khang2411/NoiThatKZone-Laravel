<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Cloudinary\Api\Upload\UploadApi;

class AdminCategoryController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $categories = Collection::where('collection_id', '!=', null)->onlyTrashed()->with('rootCollection')
                    ->when(request()->search, function ($q) {
                        return $q->where('name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $categories->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $categories = Collection::where('collection_id', '!=', null)
                    ->with('rootCollection')->when(request()->search, function ($q) {
                        return $q->where('name', 'LIKE', '%' . request()->search . '%');
                    })->orderBy("id", "DESC")->paginate(10);

                $categories->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }
        $count_order_active = Collection::where('collection_id', '!=', null)->count();
        $count_order_trash = Collection::where('collection_id', '!=', null)->onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];

        $collections = Collection::where('collection_id', null)->get();
        return Inertia::render('Category/CategoryList', ['categories' => $categories, 'collections' => $collections, 'list_action' => $list_action, 'count' => $count]);
    }

    function add()
    {
        $collections = Collection::where('collection_id', null)->get();
        return Inertia::render('Category/CategoryAdd', ['collections' => $collections]);
    }

    function store()
    {
        $input = request()->all();
        Validator::make(
            $input,
            [
                'name' => 'required',
                'collection_id' => 'required',
                'thumbnail' => 'required',
                'banner' => 'required'
            ],
            [
                'name.required' => 'Tên thể loại là bắt buộc',
                'collection_id.required' => 'Bộ sưu tập là bắt buộc',
                'thumbnail.required' => 'Ảnh thể loại là bắt buộc',
                'banner.required' => 'Banner thể loại là bắt buộc'
            ]
        )->validate();

        if (request()->hasFile('thumbnail')) {
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/collections',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            $input['public_id_thumbnail'] = $thumbnail['public_id'];
        }
        if (request()->hasFile('banner')) {
            $banner = (new UploadApi())->upload($_FILES['banner']['tmp_name'], [
                'folder' => 'noithatkzone/banners',
                'format' => 'jpg',
                'quality' => '80',
            ]);
            $input['banner'] = $banner['secure_url'];
            $input['public_id_banner'] = $banner['public_id'];
        }
        $input['slug'] = Str::slug($input['name']); // vẫn thêm slug vô dc mảng input dù view kh có name 
        Collection::create($input);

        return to_route('admin.category.list');
    }

    function update()
    {
        Validator::make(
            request()->all(),
            [
                'name' => 'required',
                'collection_id' => 'required',
                'thumbnail' => 'required',
                'banner' => 'required'
            ],
            [
                'name.required' => 'Tên thể loại là bắt buộc',
                'collection_id.required' => 'Bộ sưu tập là bắt buộc',
                'thumbnail.required' => 'Ảnh thể loại là bắt buộc',
                'banner.required' => 'Banner thể loại là bắt buộc'
            ]
        )->validate();

        $collection = Collection::find(request()->id);
        $collection->name = request()->name;
        $collection->collection_id = request()->collection_id;

        if (request()->hasFile('thumbnail')) {
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/collections',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            if ($collection->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($collection->public_id_thumbnail);
            }
            $collection->thumbnail =  $input['thumbnail'];
            $collection->public_id_thumbnail = $thumbnail['public_id'];
        }
        if (request()->hasFile('banner')) {
            $banner = (new UploadApi())->upload($_FILES['banner']['tmp_name'], [
                'folder' => 'noithatkzone/banners',
                'format' => 'jpg',
                'quality' => '80',
            ]);
            $input['banner'] = $banner['secure_url'];
            $collection->banner =  $input['banner'];
            if ($collection->public_id_banner !== null) {
                (new UploadApi())->destroy($collection->public_id_banner);
            }
            $collection->public_id_banner = $banner['public_id'];
        }
        $collection->save();
    }

    function delete($id)
    {
        $collection = Collection::withTrashed()->find($id);

        if ($collection->deleted_at) {
            if ($collection->public_id_banner !== null && $collection->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($collection->public_id_banner);
                (new UploadApi())->destroy($collection->public_id_thumbnail);
            }
            $collection->forceDelete();
        } else {
            $collection->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check;
        if (!$list_check) {
            return to_route('admin.category.list')->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Collection::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Collection::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    $categories = Collection::withTrashed()->whereIn('id', $list_check);
                    foreach ($categories->get() as $category) {
                        if ($category->public_id_banner !== null && $category->public_id_thumbnail !== null) {
                            (new UploadApi())->destroy($category->public_id_banner);
                            (new UploadApi())->destroy($category->public_id_thumbnail);
                        }
                    }
                    $categories->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
