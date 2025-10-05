<?php

namespace App\Http\Controllers\Api\V1\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\User\ForumPostResource;
use App\Mail\CustomMail;
use App\Models\Forum\ForumPost;
use App\Notifications\Forum\NewPostNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ForumPostController extends Controller
{
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = $request->user()->building_units()->find($request->unit);
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'unit_id' => __("واحد مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $posts = $unit->building->forumPosts()->orderBy('created_at', 'desc')->paginate(10);
        return response()->paginate($posts, ForumPostResource::class);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|exists:building_units,id',
            'content' => 'required|string',
            'image' => 'nullable|image',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = $request->user()->building_units()->find($request->unit);
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'unit_id' => __("واحد مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        $post = $unit->building->forumPosts()->create([
            'user_id' => $request->user()->id,
            'content' => $request->content,
        ]);

        if ($request->hasFile('image')) {
            $post->image = Storage::url($request->file('image')->store('public/forum_posts'));
            $post->save();
        }

        Mail::to(['arcenciel.ir@gmail.com', 'saman.moayeri@gmail.com', 'poulpal33@poulpal.com'])->send(
            new CustomMail(
                'CHARGEPAL - انتشار پست جدید ' . $post->id,
                "متن : " . $request->content . "<br>
                تصویر : " . ($post->image ? asset($post->image) : __("بدون تصویر")) . "<br>
                کاربر: " . $request->user()->full_name . "<br>
                ساختمان: " . $post->building->name . "<br>"
            )
        );

        foreach ($unit->building->units as $u) {
            Notification::send($u->residents, new NewPostNotification($post));
        }
        Notification::send($unit->building->buildingManagers, new NewPostNotification($post));

        return response()->json([
            'success' => true,
            'message' => __("پست با موفقیت ایجاد شد"),
            'data' => [
                'post' => new ForumPostResource($post),
            ]
        ]);
    }

    public function toggleLike(Request $request, ForumPost $forumPost)
    {
        $validator = Validator::make($request->all(), [
            'unit' => 'required|exists:building_units,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $unit = $request->user()->building_units()->find($request->unit);
        if (!$unit) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'unit_id' => __("واحد مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        if ($forumPost->building_id != $unit->building_id) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'forum_post_id' => __("پست مورد نظر یافت نشد"),
                ],
            ], 404);
        }

        if ($forumPost->isLikedBy($request->user())) {
            $forumPost->forumLikes()->where('user_id', $request->user()->id)->delete();
            $forumPost->likes--;
        } else {
            $forumPost->forumLikes()->create([
                'user_id' => auth()->user()->id,
            ]);
            $forumPost->likes++;
        }
        $forumPost->save();

        return response()->json([
            'success' => true,
            'message' => __("عملیات با موفقیت انجام شد"),
            'data' => [
                'post' => new ForumPostResource($forumPost),
            ]
        ]);
    }
}
