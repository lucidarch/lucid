<?php

namespace Lucid\Units;

use Lucid\Units\Operation;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

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
