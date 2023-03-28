<?php

namespace Lucid\Units;

use Lucid\Bus\UnitDispatcher;
use Lucid\Testing\MockMe;

abstract class Feature
{
    use MockMe;
    use UnitDispatcher;
}
