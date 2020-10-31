<?php

namespace Lucid\Units;

use Lucid\Bus\MarshalTrait;
use Lucid\Bus\UnitDispatcherTrait;
use Illuminate\Foundation\Bus\DispatchesJobs;

abstract class Feature
{
    use MarshalTrait;
    use DispatchesJobs;
    use UnitDispatcherTrait;
}
