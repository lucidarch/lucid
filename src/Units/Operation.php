<?php

namespace Lucid\Units;

use Lucid\Bus\MarshalTrait;
use Lucid\Bus\UnitDispatcherTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Operation
{
    use MarshalTrait;
    use DispatchesJobs;
    use UnitDispatcherTrait;
}
