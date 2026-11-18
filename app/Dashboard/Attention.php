<?php

namespace App\Dashboard;

use App\Accounts\AccountHealth;
use App\Models\ConnectedAccount;
use App\Models\Content;
use App\Models\ContentCopy;

// What needs the creator's eyes, most urgent first. Each item: kind, text, url, tone.
class Attention
{
    private const LIMIT = 5;

    /** @return array<int, array{kind: string, text: string, url: string, tone: string}> */
    public static function items(): array
    {
        $items = [];

        foreach (ConnectedAccount::with('token')->get() as $a) {
            $health = AccountHealth::forAccount($a);
            if ($health !== 'ok') {
                $items[] = ['kind' => 'account', 'tone' => $health === 'reconnect' ? 'bad' : 'warn', 'url' => route('accounts'),
                    'text' => ucfirst($a->platform).' @'.$a->handle.($health === 'reconnect' ? ': reconnect needed' : ': token expiring soon')];
            }
        }

        foreach (ContentCopy::with('content')->where('link_mode', 'target')->where('status', 'failed')->latest('id')->limit(self::LIMIT)->get() as $c) {
            $items[] = ['kind' => 'failed', 'tone' => 'bad', 'url' => route('content.show', $c->content_id),
                'text' => 'Failed to publish to '.ucfirst($c->platform).': '.$c->content?->title.' ('.str($c->last_error)->limit(60).')'];
        }

        foreach (ContentCopy::with('content')->where('link_mode', 'target')->where('status', 'draft')->latest('id')->limit(self::LIMIT)->get() as $c) {
            $items[] = ['kind' => 'draft', 'tone' => 'warn', 'url' => route('content.show', $c->content_id),
                'text' => ucfirst($c->platform).' draft waiting for approval: '.$c->content?->title];
        }

        foreach (Content::where('source_changed', true)->latest('id')->limit(self::LIMIT)->get() as $c) {
            $items[] = ['kind' => 'changed', 'tone' => 'warn', 'url' => route('content.show', $c),
                'text' => 'The original changed: '.$c->title];
        }

        return $items;
    }
}
