<?php

namespace Lucid\Units;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * An abstract Job that can be managed with a queue
 * when extended the job will be queued by default.
 */
class QueueableJob extends Job implements ShouldQueue
{
    use SerializesModels;
    use InteractsWithQueue;
    use Queueable;
}
