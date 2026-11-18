<?php

namespace App\Collections;

use App\Models\Collection;

// One plain array that the page, the JSON and the RSS all render from, so they can never disagree.
class FeedPayload
{
    public static function build(Collection $collection): array
    {
        $collection->load(['embed', 'items.content.copies']);
        $embed = $collection->embed;
        $showMedia = $embed ? $embed->show_media : true;

        $items = [];
        foreach ($collection->items as $item) {
            $content = $item->content;
            if (! $content) { // removed from the Library
                continue;
            }
            $origin = $content->copies->firstWhere('is_origin', true);
            $media = $showMedia && $origin && ($origin->thumbnail_url || $origin->media_url)
                ? ['type' => $origin->media_type, 'thumbnail_url' => $origin->thumbnail_url ?: $origin->media_url] : null;

            $items[] = [
                'id' => $content->platformId(),
                'title' => $content->title,
                'text' => $content->master_text,
                'published_at' => ($origin?->remote_published_at ?? $content->created_at)->toIso8601String(),
                'url' => $origin?->permalink, // the original post; the media address itself can expire
                'media' => $media,
            ];
        }

        return [
            'collection' => ['name' => $collection->name, 'description' => $collection->description, 'cover_url' => $collection->cover_url],
            'layout' => $embed->layout ?? 'list',
            'accent' => preg_match('/^#[0-9a-f]{6}$/i', $embed->accent ?? '') ? $embed->accent : '#0b57d0',
            'domains' => AllowedDomains::parse($embed?->allowed_domains),
            'items' => $items,
        ];
    }
}
