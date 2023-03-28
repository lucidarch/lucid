<?php

namespace Lucid\Units;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * An abstract Operation that can be managed with a queue
 * when extended the operation will be queued by default.
 */
class QueueableOperation extends Operation implements ShouldQueue
{
    use SerializesModels;
    use InteractsWithQueue;
    use Queueable;
}
