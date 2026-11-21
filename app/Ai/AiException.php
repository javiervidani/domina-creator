<?php

namespace App\Ai;

use RuntimeException;

// The message is written for the creator: what went wrong and what to do. It never holds the key or the post text.
class AiException extends RuntimeException
{
}
