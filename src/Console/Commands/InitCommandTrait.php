<?php

namespace Lucid\Console\Commands;

use Lucid\Str;

trait InitCommandTrait
{
    private function welcome($service = null)
    {
        $service = Str::service($service);

        $this->info('');
        $this->info("You're all set to build something awesome that scales!");
        $this->info('');
        $this->info('Here are some examples to get you started:');
        $this->info('');

        $this->info('You may wish to start with a feature');
        $this->comment("lucid make:feature LoginUser $service");
        if ($service) {
            $this->info("will generate <fg=cyan>app/Services/$service/Features/LoginUserFeature.php</>");
        } else {
            $this->info("will generate <fg=cyan>app/Features/LoginUserFeature.php</>");
        }

        $this->info('');

        $this->info('Or a job to do a single thing');
        $this->comment('lucid make:job GetUserByEmail User');
        $this->info('will generate <fg=cyan>app/Domains/User/Jobs/GetUserByEmailJob.php</>');
        $this->info('');
        $this->info('For more Job examples check out Lucid\'s built-in jobs:');
        $this->comment('- Lucid\Domains\Http\Jobs\RespondWithJsonJob');
        $this->info('for consistent JSON structure responses.');
        $this->info('');
        $this->comment('- Lucid\Domains\Http\Jobs\RespondWithJsonErrorJob');
        $this->info('for consistent JSON error responses.');
        $this->info('');
        $this->comment('- Lucid\Domains\Http\Jobs\RespondWithViewJob');
        $this->info('basic view and data response functionality.');

        $this->info('');

        $this->info('Finally you can group multiple jobs in an operation');
        $this->comment("lucid make:operation ProcessUserLogin $service");

        if ($service) {
            $this->info("will generate <fg=cyan>app/Services/$service/Operations/ProcessUserLoginOperation.php</>");
        } else {
            $this->info('will generate <fg=cyan>app/Operations/ProcessUserLoginOperation.php</>');
        }

        $this->info('');

        $this->info('For more details, help yourself with the docs at https://docs.lucidarch.dev');
        $this->info('');
        $this->info('Remember to enjoy the journey.');
        $this->info('Cheers!');
    }
}
