<?php

namespace Lucid\Console\Commands;

use Lucid\Finder;
use Lucid\Filesystem;
use Lucid\Console\Command;
use Lucid\Generators\MicroGenerator;
use Symfony\Component\Console\Command\Command as SymfonyCommand;

class InitMicroCommand extends SymfonyCommand
{
    use Finder;
    use Command;
    use Filesystem;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'init:micro';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize Lucid Micro in current project.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Initializing Lucid Micro...\n");

        $generator = new MicroGenerator();
        $paths = $generator->generate();

        $this->comment("Created directories:");
        $this->comment(join("\n", $paths));

        $this->info('');
        $this->info("You're all set to build something awesome that scales!");
        $this->info('');
        $this->info('Here are some examples to get you started:');
        $this->info('');

        $this->info('You may wish to start with a feature');
        $this->comment("lucid make:feature LoginUser");
        $this->info("will generate <fg=cyan>app/Features/LoginUserFeature.php</>");

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
        $this->comment('lucid make:operation ProcessUserLogin');
        $this->info('will generate <fg=cyan>app/Operations/ProcessUserLoginOperation.php</>');

        $this->info('');

        $this->info('For more details, help yourself with the docs at https://docs.lucidarch.dev');
        $this->info('');
        $this->info('Remember to enjoy the journey.');
        $this->info('Cheers!');

        return 0;
    }
}
