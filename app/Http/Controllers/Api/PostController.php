<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\PostRequest;
use App\Models\Post;
use App\Services\PostService;

class PostController extends BaseApiController
{
    public function __construct(
        protected PostService $postService
    ) {}

    public function index()
    {
        return $this->success(
            $this->postService->all(),
            'Posts retrieved'
        );
    }

    public function store(PostRequest $request)
    {
        return $this->success(
            $this->postService->create($request->validated()),
            'Post created',
            201
        );
    }

    public function show(Post $post)
    {
        return $this->success(
            $this->postService->find($post),
            'Post retrieved'
        );
    }

    public function update(PostRequest $request, Post $post)
    {
        return $this->success(
            $this->postService->update($post, $request->validated()),
            'Post updated'
        );
    }

    public function destroy(Post $post)
    {
        $this->postService->delete($post);

        return $this->success(null, 'Post deleted');
    }
}
