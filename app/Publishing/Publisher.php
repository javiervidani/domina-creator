<?php

namespace App\Publishing;

use App\Import\CaptionText;
use App\Models\ConnectedAccount;
use App\Models\ContentCopy;
use App\Platforms\Telegram\TelegramConnector;

// Sends one target copy. Only Telegram so far: YouTube needs a video, and phase 1 is text only.
class Publisher
{
    public const SUPPORTED = ['telegram'];

    public static function supports(string $platform): bool
    {
        return in_array($platform, self::SUPPORTED, true);
    }

    /** @throws \App\Platforms\PlatformException */
    public function publish(ContentCopy $copy, ConnectedAccount $account, string $text): void
    {
        $connector = new TelegramConnector($account->token->access_token, $account->external_id);

        // "chat:message". Still "pending:..." means it was never sent, so this is a first publish.
        $mode = $copy->image_mode;
        if (str_starts_with($copy->external_id, 'pending:')) {
            [$message, $mode] = $this->first($connector, $copy, $text);
            $externalId = $account->external_id.':'.$message;
        } else {
            $id = substr($copy->external_id, strrpos($copy->external_id, ':') + 1);
            $mode === 'caption' ? $connector->editCaption($id, $text) : $connector->edit($id, $text);
            $externalId = $copy->external_id;
        }

        $copy->update(['external_id' => $externalId, 'image_mode' => $mode, 'source_text' => $text, 'status' => 'published', 'last_error' => null,
            'published_hash' => CaptionText::changeHash($text), 'published_at' => now()]);
    }

    /** First send. Telegram only lets a caption hold 1024 characters, so a longer text goes as its own message after the picture. */
    private function first(TelegramConnector $connector, ContentCopy $copy, string $text): array
    {
        $image = $copy->send_image ? self::imageUrl($copy->content->copies()->where('is_origin', true)->first()) : null;
        if (! $image) {
            return [$connector->send($text), null];
        }
        if (mb_strlen($text) <= TelegramConnector::CAPTION_LIMIT) {
            return [$connector->sendPhoto($image, $text), 'caption'];
        }
        $connector->sendPhoto($image);

        return [$connector->send($text), 'separate'];
    }

    /** The picture to send: the post's image, or a video's thumbnail. It is a link, nothing is copied. */
    public static function imageUrl(?ContentCopy $origin): ?string
    {
        if (! $origin) {
            return null;
        }

        return $origin->media_type === 'VIDEO' ? $origin->thumbnail_url : ($origin->media_url ?: $origin->thumbnail_url);
    }

    // True when the text now differs from what was last sent, which the editor shows next to Re-publish.
    public static function differs(ContentCopy $copy, string $currentText): bool
    {
        return $copy->status === 'published' && $copy->published_hash !== CaptionText::changeHash($currentText);
    }
}
