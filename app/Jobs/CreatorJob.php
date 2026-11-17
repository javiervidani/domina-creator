<?php

namespace App\Jobs;

use App\Tenancy\Tenancy;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Base for every job that touches creator data: it carries the id and switches schema before work() runs.
abstract class CreatorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $creatorId)
    {
    }

    abstract protected function work(): void;

    public function handle(Tenancy $tenancy): void
    {
        $tenancy->run($this->creatorId, fn () => $this->work());
    }
}
