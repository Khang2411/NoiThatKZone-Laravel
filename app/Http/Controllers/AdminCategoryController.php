<?php

namespace App\Http\Controllers;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
            $extension = request()->file('thumbnail')->extension();
            $name = microtime() . md5(Str::random(10)) . '.' . $extension;
            $name = Str::replace(" ", "", $name);
            request()->file('thumbnail')->storeAs('public/collections', $name);
            $urlThumbnail = '/storage/collections/' . $name;
            $input['thumbnail'] = url($urlThumbnail);
        }
        if (request()->hasFile('banner')) {
            $extension = request()->file('banner')->extension();
            $name = microtime() . md5(Str::random(10)) . '.' . $extension;
            $name = Str::replace(" ", "", $name);
            request()->file('banner')->storeAs('public/banner', $name);
            $urlBanner = '/storage/banner/' . $name;
            $input['banner'] = url($urlBanner);
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
            $extension = request()->file('thumbnail')->extension();
            $name = microtime() . md5(Str::random(10)) . '.' . $extension;
            $name = Str::replace(" ", "", $name);
            request()->file('thumbnail')->storeAs('public/collections', $name);
            $urlThumbnail = '/storage/collections/' . $name;
            $input['thumbnail'] = url($urlThumbnail);
            $collection->thumbnail =  $input['thumbnail'];
        }
        if (request()->hasFile('banner')) {
            $extension = request()->file('banner')->extension();
            $name = microtime() . md5(Str::random(10)) . '.' . $extension;
            $name = Str::replace(" ", "", $name);
            request()->file('banner')->storeAs('public/banner', $name);
            $urlBanner = '/storage/banner/' . $name;
            $input['banner'] = url($urlBanner);
            $collection->banner =  $input['banner'];
        }
        $collection->save();
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
                    Collection::withTrashed()->whereIn('id', $list_check)->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
