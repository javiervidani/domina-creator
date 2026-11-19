<?php

namespace App\Platforms\Telegram;

use App\Platforms\BaseConnector;
use App\Platforms\PlatformException;
use Illuminate\Support\Facades\Http;

// Telegram Bot API. The access token is the bot token; the chat is a channel or group the bot may post in.
class TelegramConnector extends BaseConnector
{
    public const LIMIT = 4096;
    public const CAPTION_LIMIT = 1024; // a picture's text is shorter than a plain message

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

    /** A picture sent by link (Telegram downloads it, we never store it), with the text as its caption. Returns the message id. */
    public function sendPhoto(string $url, string $caption = ''): string
    {
        $payload = ['chat_id' => $this->chatId, 'photo' => $url] + ($caption !== '' ? ['caption' => $caption] : []);

        return (string) $this->call('sendPhoto', $payload)['message_id'];
    }

    /** Re-publish of a message that was sent as a picture: only its caption can change. */
    public function editCaption(string $messageId, string $caption): void
    {
        if (mb_strlen($caption) > self::CAPTION_LIMIT) {
            throw new PlatformException('This message was sent with a picture, so Telegram allows '.self::CAPTION_LIMIT.' characters in its text; yours is '.mb_strlen($caption).'. Shorten the Telegram text.');
        }
        $this->tolerant(fn () => $this->call('editMessageCaption', ['chat_id' => $this->chatId, 'message_id' => (int) $messageId, 'caption' => $caption]));
    }

    /** Re-publish: the message is edited in place, never posted a second time. */
    public function edit(string $messageId, string $text): void
    {
        $this->guardLength($text);

        $this->tolerant(fn () => $this->call('editMessageText', ['chat_id' => $this->chatId, 'message_id' => (int) $messageId, 'text' => $text, 'disable_web_page_preview' => true]));
    }

    // The text on Telegram is already exactly this, which is the result we wanted.
    private function tolerant(callable $edit): void
    {
        try {
            $edit();
        } catch (PlatformException $e) {
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
            // 404 is what Telegram answers for a token it does not know.
            throw new PlatformException(TelegramError::explain($code, (string) ($body['description'] ?? '')),
                $code === 401 || $code === 404, $code === 429 || $code >= 500);
        }

        return $body['result'];
    }
}
