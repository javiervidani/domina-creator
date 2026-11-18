<?php

namespace App\Collections;

class Rss
{
    public static function render(array $payload, string $link): string
    {
        $x = fn ($s) => htmlspecialchars((string) $s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        $items = '';
        foreach ($payload['items'] as $i) {
            $items .= '<item><title>'.$x($i['title']).'</title><link>'.$x($i['url'] ?: $link).'</link><guid isPermaLink="false">'.$x($i['id']).'</guid>'
                .'<pubDate>'.gmdate(DATE_RSS, strtotime($i['published_at'])).'</pubDate><description>'.$x($i['text']).'</description></item>';
        }
        $c = $payload['collection'];

        return '<?xml version="1.0" encoding="UTF-8"?><rss version="2.0"><channel><title>'.$x($c['name']).'</title><link>'.$x($link).'</link>'
            .'<description>'.$x($c['description'] ?: $c['name']).'</description>'.$items.'</channel></rss>';
    }
}
