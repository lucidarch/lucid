<?php

namespace Lucid\Units;

use Lucid\Testing\MockMe;
use Lucid\Bus\UnitDispatcher;

abstract class Feature
{
    use MockMe;
    use UnitDispatcher;
}
