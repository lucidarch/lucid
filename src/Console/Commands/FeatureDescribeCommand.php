<?php

 namespace Lucid\Console\Commands;

 use Lucid\Finder;
 use Lucid\Parser;
 use Illuminate\Console\Command;
 use Symfony\Component\Console\Input\InputArgument;

 class FeatureDescribeCommand extends Command
 {
     use Finder;

     protected string $name = 'describe:feature';

     protected string $description = 'List the jobs of the specified feature in sequential order.';

     public function handle(): void
     {
         if ($feature = $this->findFeature($this->argument('feature'))) {
            $parser = new Parser();
            $jobs = $parser->parseFeatureJobs($feature);

            $features = [];
            foreach ($jobs as $index => $job) {
                $features[$feature->title][] = [$index+1, $job->title, $job->domain->name, $job->relativePath];
            }

            foreach ($features as $feature => $jobs) {
                $this->comment("\n$feature\n");
                $this->table(['', 'Job', 'Domain', 'Path'], $jobs);
            }

            return;
        }

        throw new InvalidArgumentException('Feature with name "'.$this->argument('feature').'" not found.');
     }

     protected function getArguments(): array
     {
         return [
             ['feature', InputArgument::REQUIRED, 'The feature name to list the jobs of.'],
         ];
     }
 }
