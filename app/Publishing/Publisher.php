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
        if (str_starts_with($copy->external_id, 'pending:')) {
            $externalId = $account->external_id.':'.$connector->send($text);
        } else {
            $connector->edit(substr($copy->external_id, strrpos($copy->external_id, ':') + 1), $text);
            $externalId = $copy->external_id;
        }

        $copy->update(['external_id' => $externalId, 'source_text' => $text, 'status' => 'published', 'last_error' => null,
            'published_hash' => CaptionText::changeHash($text), 'published_at' => now()]);
    }

    // True when the text now differs from what was last sent, which the editor shows next to Re-publish.
    public static function differs(ContentCopy $copy, string $currentText): bool
    {
        return $copy->status === 'published' && $copy->published_hash !== CaptionText::changeHash($currentText);
    }
}
