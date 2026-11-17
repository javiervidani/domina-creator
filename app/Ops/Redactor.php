<?php

namespace App\Ops;

/**
 * Masks anything that looks like a secret before text leaves the server.
 * Plain PHP, no framework calls, so it can be tested on its own.
 */
class Redactor
{
    /** Order matters: URLs with passwords go before the e-mail pattern. */
    private const PATTERNS = [
        '#\b([a-z][a-z0-9+.-]*://)([^/\s:@]+):([^@\s/]+)@#i' => '$1[user]:[secret]@',
        '/\b(authorization:?\s*)(bearer|basic)\s+[A-Za-z0-9._~+\/=-]+/i' => '$1$2 [secret]',
        '/\bbearer\s+[A-Za-z0-9._~+\/=-]{16,}/i' => 'bearer [secret]',
        '/([?&])k=[^&\s"\']+/' => '$1k=[secret]',
        '/(?<![A-Za-z0-9_])((?:password|passwd|pwd|secret|token|api[_-]?key|access[_-]?key|client[_-]?secret)["\']?\s*[=:]\s*)("[^"]*"|\'[^\']*\'|[^\s&,;)}\]]+)/i' => '$1[secret]',
        '/base64:[A-Za-z0-9+\/=]{20,}/' => 'base64:[secret]',
        '/\b(?:sk|pk|rk)-[A-Za-z0-9_-]{16,}/' => '[secret]',
        '/\bgh[pousr]_[A-Za-z0-9]{20,}/' => '[secret]',
        '/\bxox[abprs]-[A-Za-z0-9-]{10,}/' => '[secret]',
        '/\bAIza[0-9A-Za-z_-]{20,}/' => '[secret]',
        '/\b(?:EAA|IGAA|IGQ)[A-Za-z0-9_-]{30,}/' => '[secret]',
        '/\b[A-Za-z0-9_-]{40,}\b/' => '[long-string]',
        '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}/' => '[email]',
    ];

    /** @param list<string> $secrets exact values to hide, for example the ops token and the app key */
    public function __construct(private array $secrets = [], private int $lineMax = 300)
    {
    }

    public function clean(string $text): string
    {
        foreach ($this->secrets as $secret) {
            if (strlen($secret) >= 8) {
                $text = str_replace($secret, '[secret]', $text);
            }
        }

        foreach (self::PATTERNS as $pattern => $replacement) {
            $text = preg_replace($pattern, $replacement, $text) ?? $text;
        }

        return $this->cut($text);
    }

    /** Long lines cost tokens and rarely help, so each one is cut. */
    public function cut(string $text): string
    {
        $text = trim($text);

        return mb_strlen($text) > $this->lineMax ? mb_substr($text, 0, $this->lineMax).'…' : $text;
    }
}
