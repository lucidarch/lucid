#!/bin/sh

set -e

# Check if file or directory exists. Exit if it doesn't.
examine() {
    if [ ! -f $1 ] && [ ! -d $1 ]; then
        echo "\n-- ERROR -- $1 could not be found!\n"
        exit 1
    fi
}

# Lint a PHP file for syntax errors. Exit on error.
lint() {
    # echo "\n -- MISSING -- Lint file $1"
    RESULT=$(php -l $1)
    if [ ! $? -eq 0 ] ; then
        echo "$RESULT" && exit 1
    fi
}

# Detect Laravel major version
LARAVEL_VERSION=$(php artisan --version 2>/dev/null | grep 'Laravel Framework' | grep -o '[0-9]\+' | head -1)

if [ ! -f ".env" ]; then
    cp .env.example .env
fi

if ! grep -q "^APP_KEY=.\+" .env; then
    php artisan key:generate
fi

## --- Checks ---

examine "app/Providers"

# RouteServiceProvider was removed in Laravel 11
if [ "$LARAVEL_VERSION" -lt 11 ]; then
    examine "app/Providers/RouteServiceProvider.php"
fi

examine "resources"

# lang/ moved to root in Laravel 9+; removed from default skeleton in Laravel 10+
# (requires php artisan lang:publish) — warn only, do not fail
if [ "$LARAVEL_VERSION" -lt 9 ]; then
    examine "resources/lang"
elif [ ! -d "lang" ] && [ ! -d "resources/lang" ]; then
    echo "Note: lang directory not present (run php artisan lang:publish if needed)"
fi

examine "resources/views"
examine "resources/views/welcome.blade.php"
lint "resources/views/welcome.blade.php"
examine "routes"

# routes/api.php is not created by default in Laravel 11+
if [ "$LARAVEL_VERSION" -lt 11 ]; then
    examine "routes/api.php"
    lint "routes/api.php"
fi

examine "routes/web.php"
lint "routes/web.php"
examine "tests"

## --- Micro ---

# Controller
./vendor/bin/lucid make:controller trade
examine "app/Http/Controllers/TradeController.php"
lint "app/Http/Controllers/TradeController.php"

# Feature
./vendor/bin/lucid make:feature trade
examine "app/Features/TradeFeature.php"
lint "app/Features/TradeFeature.php"
examine "tests/Feature/TradeFeatureTest.php"
lint "tests/Feature/TradeFeatureTest.php"

## Feature in Subdirectory
./vendor/bin/lucid make:feature finance/wallet/pay
examine "app/Features/Finance/Wallet/PayFeature.php"
lint "app/Features/Finance/Wallet/PayFeature.php"
examine "tests/Feature/Finance/Wallet/PayFeatureTest.php"
lint "tests/Feature/Finance/Wallet/PayFeatureTest.php"

# Job
./vendor/bin/lucid make:job submitTradeRequest shipping
examine "app/Domains/Shipping/Jobs/SubmitTradeRequestJob.php"
lint "app/Domains/Shipping/Jobs/SubmitTradeRequestJob.php"
examine "tests/Unit/Domains/Shipping/Jobs/SubmitTradeRequestJobTest.php"
lint "tests/Unit/Domains/Shipping/Jobs/SubmitTradeRequestJobTest.php"

./vendor/bin/lucid make:job sail boat --queue
examine "app/Domains/Boat/Jobs/SailJob.php"
lint "app/Domains/Boat/Jobs/SailJob.php"
examine "tests/Unit/Domains/Boat/Jobs/SailJobTest.php"
lint "tests/Unit/Domains/Boat/Jobs/SailJobTest.php"

# Model
./vendor/bin/lucid make:model bridge
examine "app/Data/Models/Bridge.php"
lint "app/Data/Models/Bridge.php"

# Operation
./vendor/bin/lucid make:operation spin
examine "app/Operations/SpinOperation.php"
lint "app/Operations/SpinOperation.php"
examine "tests/Unit/Operations/SpinOperationTest.php"
lint "tests/Unit/Operations/SpinOperationTest.php"

./vendor/bin/lucid make:operation twist --queue
examine "app/Operations/TwistOperation.php"
lint "app/Operations/TwistOperation.php"
examine "tests/Unit/Operations/TwistOperationTest.php"
lint "tests/Unit/Operations/TwistOperationTest.php"

# Policy
./vendor/bin/lucid make:policy fly
examine "app/Policies/FlyPolicy.php"
lint "app/Policies/FlyPolicy.php"

# Ensure nothing is breaking
./vendor/bin/lucid list:features
./vendor/bin/lucid list:services

# Run PHPUnit tests
./vendor/bin/phpunit

## --- Regression: dispatch must return actual values, not PendingDispatch (issue #58) ---

mkdir -p app/Domains/Dispatch/Jobs

cat > app/Domains/Dispatch/Jobs/ReturnValueJob.php << 'PHPEOF'
<?php
namespace App\Domains\Dispatch\Jobs;

use Lucid\Units\Job;

class ReturnValueJob extends Job
{
    public function handle(): string
    {
        return 'job_executed';
    }
}
PHPEOF

cat > app/Features/DispatchReturnFeature.php << 'PHPEOF'
<?php
namespace App\Features;

use Lucid\Units\Feature;
use App\Domains\Dispatch\Jobs\ReturnValueJob;

class DispatchReturnFeature extends Feature
{
    public function handle(): string
    {
        return $this->run(new ReturnValueJob());
    }
}
PHPEOF

cat > tests/Feature/DispatchRegressionTest.php << 'PHPEOF'
<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Features\DispatchReturnFeature;
use Illuminate\Foundation\Bus\PendingDispatch;
use Lucid\Bus\ServesFeatures;

/**
 * Regression test for https://github.com/lucidarch/lucid/issues/58
 *
 * In Laravel 12, dispatch() returns PendingDispatch for all jobs. serve() and run()
 * must return the actual job/feature result, not a PendingDispatch instance.
 */
class DispatchRegressionTest extends TestCase
{
    use ServesFeatures;

    public function test_serve_returns_feature_result_not_pending_dispatch(): void
    {
        $result = $this->serve(DispatchReturnFeature::class);

        $this->assertNotInstanceOf(PendingDispatch::class, $result);
        $this->assertEquals('job_executed', $result);
    }

    public function test_run_inside_feature_returns_job_result_not_pending_dispatch(): void
    {
        $feature = new DispatchReturnFeature();
        $result = $feature->handle();

        $this->assertNotInstanceOf(PendingDispatch::class, $result);
        $this->assertEquals('job_executed', $result);
    }
}
PHPEOF

lint "app/Domains/Dispatch/Jobs/ReturnValueJob.php"
lint "app/Features/DispatchReturnFeature.php"
lint "tests/Feature/DispatchRegressionTest.php"

./vendor/bin/phpunit tests/Feature/DispatchRegressionTest.php

rm -rf app/Domains/Dispatch
rm app/Features/DispatchReturnFeature.php
rm tests/Feature/DispatchRegressionTest.php

echo "\nMicro tests PASSED!\n"

## --- Monolith ---

# Controller
./vendor/bin/lucid make:controller trade harbour
examine "app/Services/Harbour/Http/Controllers/TradeController.php"
lint "app/Services/Harbour/Http/Controllers/TradeController.php"

# Feature
./vendor/bin/lucid make:feature trade harbour
examine "app/Services/Harbour/Features/TradeFeature.php"
lint "app/Services/Harbour/Features/TradeFeature.php"
examine "tests/Feature/Services/Harbour/TradeFeatureTest.php"
lint "tests/Feature/Services/Harbour/TradeFeatureTest.php"

## Feature in Subdirectory
./vendor/bin/lucid make:feature port/yacht/park harbour
examine "app/Services/Harbour/Features/Port/Yacht/ParkFeature.php"
lint "app/Services/Harbour/Features/Port/Yacht/ParkFeature.php"
examine "tests/Feature/Services/Harbour/Port/Yacht/ParkFeatureTest.php"
lint "tests/Feature/Services/Harbour/Port/Yacht/ParkFeatureTest.php"

## Operation
./vendor/bin/lucid make:operation spin harbour
examine "app/Services/Harbour/Operations/SpinOperation.php"
lint "app/Services/Harbour/Operations/SpinOperation.php"
examine "tests/Unit/Services/Harbour/Operations/SpinOperationTest.php"
lint "tests/Unit/Services/Harbour/Operations/SpinOperationTest.php"

./vendor/bin/lucid make:operation twist harbour --queue
examine "app/Services/Harbour/Operations/TwistOperation.php"
lint "app/Services/Harbour/Operations/TwistOperation.php"
examine "tests/Unit/Services/Harbour/Operations/TwistOperationTest.php"
lint "tests/Unit/Services/Harbour/Operations/TwistOperationTest.php"

# Ensure nothing is breaking
./vendor/bin/lucid list:features
./vendor/bin/lucid list:services

./vendor/bin/phpunit

## --- TEARDOWN ---

./vendor/bin/lucid delete:feature trade
./vendor/bin/lucid delete:feature finance/wallet/pay
./vendor/bin/lucid delete:job submitTradeRequest shipping
./vendor/bin/lucid delete:job sail boat
./vendor/bin/lucid delete:model bridge
./vendor/bin/lucid delete:operation spin
./vendor/bin/lucid delete:operation twist
./vendor/bin/lucid delete:policy fly
rm app/Http/Controllers/TradeController.php

./vendor/bin/lucid delete:feature trade harbour
./vendor/bin/lucid delete:feature port/yacht/park harbour
./vendor/bin/lucid delete:operation spin harbour
./vendor/bin/lucid delete:operation twist harbour
rm app/Services/Harbour/Http/Controllers/TradeController.php

echo "\nPASSED!\n"

exit 0
