<?php

namespace Lucid\Units;

use Lucid\Units\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

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
