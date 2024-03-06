<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Cloudinary\Api\Upload\UploadApi;

class AdminPostController extends Controller
{
    function list()
    {
        $list_action = ['delete' => 'Xóa tạm thời'];
        switch (request()->status) {
            case ('trash'):
                $posts = Post::onlyTrashed()->with('user')->when(request()->search, function ($q) {
                    return $q->where('title', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);
                $posts->appends(['status' => 'trash', 'search' => request()->search]); // khi nhấn qua page 2 vẫn ở status=trash
                $list_action = ['restore' => 'Khôi phục', 'force_delete' => 'Xóa vĩnh viễn'];
                break;
            default:
                $posts = Post::with('user')->when(request()->search, function ($q) {
                    return $q->where('title', 'LIKE', '%' . request()->search . '%');
                })->orderBy("id", "DESC")->paginate(10);
                $posts->appends(['status' => 'active', 'search' => request()->search]);
                break;
        }
        $count_order_active = Post::count();
        $count_order_trash = Post::onlyTrashed()->count();
        $count = [$count_order_active, $count_order_trash];

        foreach ($posts as $post) {
            $post['content'] = Str::limit($post['content'], 150, '...');
        };

        //return $posts;
        return Inertia::render('Post/PostList', ['posts' => $posts, 'list_action' => $list_action, 'count' => $count]);
    }

    function add()
    {
        return Inertia::render('Post/PostAdd');
    }

    function store()
    {
        $user = auth()->user();
        $input = request()->all();
        Validator::make(
            $input,
            [
                'title' => 'required',
                'thumbnail' => 'required',
                'content' => 'required'
            ],
            [
                'title.required' => 'Tiêu đề là bắt buộc',
                'thumbnail.required' => 'Ảnh đại diện là bắt buộc',
                'content.required' => 'Nội dung là bắt buộc'
            ]
        )->validate();

        if (request()->hasFile('thumbnail')) {
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/posts',
                'format' => 'jpg',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            $input['public_id_thumbnail'] = $thumbnail['public_id'];
        }

        $input['slug'] = Str::slug($input['title']); // vẫn thêm slug vô dc mảng input dù view kh có name 
        $input['content'] = request()->content;
        $input['user_id'] =   $user->id;
        $post = Post::create($input);

        return to_route('admin.post.list');
    }

    function edit($id)
    {
        $post = Post::find($id);
        return Inertia::render('Post/PostEdit', ['post' => $post]);
    }

    function update()
    {
        Validator::make(
            request()->all(),
            [
                'title' => 'required',
                'thumbnail' => 'required',
                'content' => 'required'
            ],
            [
                'title.required' => 'Tiêu đề là bắt buộc',
                'thumbnail.required' => 'Ảnh đại diện là bắt buộc',
                'content.required' => 'Nội dung là bắt buộc'
            ]
        )->validate();

        $post = Post::find(request()->id);
        $post->title = request()->title;
        if (request()->hasFile('thumbnail')) {
            $thumbnail = (new UploadApi())->upload($_FILES['thumbnail']['tmp_name'], [
                'folder' => 'noithatkzone/posts',
                'quality' => '80',
            ]);
            $input['thumbnail'] = $thumbnail['secure_url'];
            if ($post->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($post->public_id_thumbnail);
            }
            $post->thumbnail =  $input['thumbnail'];
            $post->public_id_thumbnail = $thumbnail['public_id'];
        }
        $post->content = request()->content;
        $post->save();
        return redirect()->route('admin.post.list');
    }

    function delete($id)
    {
        $post = Post::withTrashed()->find($id);

        if ($post->deleted_at) {
            if ($post->public_id_thumbnail !== null) {
                (new UploadApi())->destroy($post->public_id_thumbnail);
            }
            $post->forceDelete();
        } else {
            $post->delete();
        }
    }

    function action()
    {
        $list_check = request()->list_check;
        if (!$list_check) {
            return redirect()->back()->with(['status' => 'Bạn cần chọn phần tử để thực hiện']);
        } else {
            switch (request()->action) {
                case 'delete':
                    Post::destroy($list_check);
                    return redirect()->back()->with(['status' => 'Xóa thành công']);
                    break;
                case 'restore':
                    Post::whereIn('id', $list_check)->restore();
                    return redirect()->back()->with(['status' => 'Khôi phục thành công']);
                    break;
                case 'force_delete':
                    $posts = Post::withTrashed()->whereIn('id', $list_check);
                    foreach ($posts->get() as $post) {
                        if ($post->public_id_thumbnail !== null) {
                            (new UploadApi())->destroy($post->public_id_thumbnail);
                        }
                    }
                    $posts->forceDelete();
                    return redirect()->back()->with(['status' => 'Xóa vĩnh viễn thành công']);
                    break;
                default:
                    return redirect()->back()->with(['status' => 'Chưa chọn tác vụ nào']);
            }
        }
    }
}
