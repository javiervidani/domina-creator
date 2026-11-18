<?php

namespace App\Platforms\Telegram;

use App\Platforms\BaseConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Http;

// Telegram Bot API. The access token is the bot token; the chat is a channel or group the bot may post in.
class TelegramConnector extends BaseConnector
{
    public const LIMIT = 4096;

    public function __construct(string $botToken, private string $chatId = '')
    {
        parent::__construct($botToken);
    }

    public static function key(): string
    {
        return 'telegram';
    }

    /** @return array{title: string, id: string} the chat, which also proves the bot can see it */
    public function chat(): array
    {
        $chat = $this->call('getChat', ['chat_id' => $this->chatId]);

        return ['id' => (string) $chat['id'], 'title' => (string) ($chat['title'] ?? $chat['username'] ?? $chat['first_name'] ?? $this->chatId)];
    }

    /** Sends a new message and returns its id. */
    public function send(string $text): string
    {
        $this->guardLength($text);
        $message = $this->call('sendMessage', ['chat_id' => $this->chatId, 'text' => $text, 'disable_web_page_preview' => true]);

        return (string) $message['message_id'];
    }

    /** Re-publish: the message is edited in place, never posted a second time. */
    public function edit(string $messageId, string $text): void
    {
        $this->guardLength($text);

        try {
            $this->call('editMessageText', ['chat_id' => $this->chatId, 'message_id' => (int) $messageId, 'text' => $text, 'disable_web_page_preview' => true]);
        } catch (PlatformException $e) {
            // The text on Telegram is already exactly this, which is the result we wanted.
            if (! str_contains($e->getMessage(), 'message is not modified')) {
                throw $e;
            }
        }
    }

    private function guardLength(string $text): void
    {
        if (mb_strlen($text) > self::LIMIT) {
            throw new PlatformException('The text is '.mb_strlen($text).' characters; Telegram allows '.self::LIMIT.'. Shorten it in the Telegram tab.');
        }
    }

    private function call(string $method, array $payload): array
    {
        // The bot token is in the URL, so errors only ever carry Telegram's own description.
        $response = Http::timeout(30)->asJson()->post('https://api.telegram.org/bot'.$this->accessToken.'/'.$method, $payload);
        $body = $response->json() ?? [];

        if (! $response->successful() || empty($body['ok'])) {
            $code = (int) ($body['error_code'] ?? $response->status());
            throw new PlatformException((string) ($body['description'] ?? 'Telegram request failed.'), $code === 401,
                $code === 429 || $code >= 500);
        }

        return $body['result'];
    }
}
