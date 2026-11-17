<?php

namespace App\Ops;

use Illuminate\Http\Response;

/**
 * Base for the ops endpoints. Answers are plain text, one fact per line, because that is the cheapest to read.
 */
abstract class OpsController
{
    protected function redactor(): Redactor
    {
        return new Redactor(
            array_values(array_filter([(string) config('ops.token'), (string) config('app.key')])),
            (int) config('ops.line_max')
        );
    }

    protected function reader(): LogReader
    {
        return new LogReader((string) config('ops.log_path'), (int) config('ops.tail_bytes'));
    }

    protected function digest(): LogDigest
    {
        return new LogDigest($this->reader(), base_path().'/');
    }

    /** @param list<string> $lines */
    protected function text(array $lines): Response
    {
        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=utf-8',
            'Cache-Control' => 'no-store',
            'X-Robots-Tag' => 'noindex',
        ]);
    }
}
