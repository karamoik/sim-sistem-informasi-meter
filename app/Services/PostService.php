<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PostService
{
    public function all(): LengthAwarePaginator
    {
        return Post::query()
            ->with('user:id,name')
            ->latest()
            ->paginate(10);
    }

    public function find(Post $post): Post
    {
        return $post->load('user:id,name');
    }

    public function create(array $data): Post
    {
        return auth('api')->user()->posts()->create($data);
    }

    public function update(Post $post, array $data): Post
    {
        $post->update($data);

        return $post->fresh()->load('user:id,name');
    }

    public function delete(Post $post): void
    {
        $post->delete();
    }
}
