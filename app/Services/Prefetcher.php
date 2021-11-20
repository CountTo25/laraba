<?php

namespace App\Services;

use App\Models\Board;
use App\Models\Thread;
use Illuminate\Http\Request;

class Prefetcher
{
    public function get(): array
    {
        /** @var Request $request */
        $request = request();

        $board = $request->route('board');
        $thread = $request->route('thread');

        $package = [];
        $package['boards'] = Board::select('short_name')->pluck('short_name')->toArray();

        if (
            $board !== null
            && $boardModel = Board::with(['threads.latestPosts.images', 'threads.firstPost.images'])
                ->firstWhere('short_name', $board)
        ) {
            $package['board'] = $boardModel->toArray();
        }

        if ($thread !== null
            && $threadModel = Thread::with(['posts.images', 'firstPost.images'])
                ->firstWhere('first_post_id', $thread)
        ) {
            $package['thread'] = $threadModel->toArray();
        }

        if (($board !== null && $boardModel === null) || ($thread !== null && $thread === null)) {
            $package['should404'] = true;
        }

        return ['prefetch' => $package];
    }
}
