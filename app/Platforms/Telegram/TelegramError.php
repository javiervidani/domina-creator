<?php

namespace App\Platforms\Telegram;

// Turns Telegram's short error text into a sentence that says what to do. The original wording is kept in
// brackets so a log or a support message still shows what Telegram said. Never contains the bot token.
class TelegramError
{
    public static function explain(int $code, string $description): string
    {
        $d = strtolower($description);
        $hint = match (true) {
            $code === 401, $code === 404 => 'The bot token is not valid. Copy the whole token again from @BotFather (about 46 characters, with a colon in the middle) and make sure it is the token of the bot you added to the chat.',
            str_contains($d, 'chat not found') => 'Telegram cannot find that chat for this bot. Check the channel name or id, and that this exact bot was added to the channel as an administrator. For a private chat, open the bot and press Start first. A private channel id starts with -100.',
            str_contains($d, 'bot can\'t send messages to the bot') => 'The destination is the bot itself. Use your channel, or your own numeric user id for a private chat.',
            str_contains($d, 'bot was kicked'), str_contains($d, 'bot is not a member') => 'The bot is no longer in this chat. Add it again as an administrator.',
            str_contains($d, 'not enough rights'), str_contains($d, 'have no rights'), str_contains($d, 'administrator rights') => 'The bot is not allowed to post here. In the channel, open Administrators, open the bot and allow Post messages and Edit messages.',
            str_contains($d, 'bot was blocked by the user') => 'You blocked the bot. Unblock it in Telegram and press Start.',
            str_contains($d, 'user not found'), str_contains($d, 'user_is_bot') => 'That user id is not valid for a private chat. Use your own numeric id from @userinfobot, after you pressed Start on the bot.',
            str_contains($d, 'message to edit not found'), str_contains($d, 'message can\'t be edited') => 'The message on Telegram was deleted or is too old to edit. Remove this copy and create a new draft.',
            str_contains($d, 'message is too long') => 'The text is longer than Telegram allows (4096 characters). Shorten it in the Telegram tab.',
            str_contains($d, 'failed to get http url content'), str_contains($d, 'wrong file identifier'), str_contains($d, 'wrong type of the web page content'), str_contains($d, 'wrong remote file') => 'Telegram could not download the picture, usually because Instagram\'s picture address expired. Press Sync now on the Instagram account, then Retry, or untick "Send with the picture".',
            str_contains($d, 'caption is too long') => 'The text is too long for a message with a picture (1024 characters). Shorten it, or untick "Send with the picture".',
            str_contains($d, 'message text is empty') => 'The text is empty. Add text in the Telegram tab or in the master text.',
            str_contains($d, 'group chat was upgraded to a supergroup') => 'Telegram changed this group into a supergroup. Connect it again with its new -100 id.',
            $code === 429 => 'Telegram asks to slow down. It will be tried again shortly.',
            $code >= 500 => 'Telegram had a problem on its side. It will be tried again shortly.',
            default => 'Telegram did not accept the request.',
        };

        return $hint.' (Telegram said: '.($description !== '' ? $description : 'error '.$code).')';
    }
}
