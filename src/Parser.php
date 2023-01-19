<?php

namespace Lucid;

 use Lucid\Entities\Domain;
 use Lucid\Entities\Feature;
 use Lucid\Entities\Job;

 class Parser
 {
     use Finder;

     const SYNTAX_STRING = 'string';

     const SYNTAX_KEYWORD = 'keyword';

     const SYNTAX_INSTANTIATION = 'init';

     /**
      * Get the list of jobs for the given feature.
      */
     public function parseFeatureJobs(Feature $feature): array
     {
         $contents = file_get_contents($feature->realPath);

         $body = explode("\n", $this->parseFunctionBody($contents, 'handle'));

         $jobs = [];
         foreach ($body as $line) {
             $job = $this->parseJobInLine($line, $contents);
             if ($job !== null) {
                 $jobs[] = $job;
             }
         }

         return $jobs;
     }

     public function parseFunctionBody($contents, $function): string
     {
         // $pattern = "/function\s$function\([a-zA-Z0-9_\$\s,]+\)?". // match "function handle(...)"
        //     '[\n\s]?[\t\s]*'. // regardless of the indentation preceding the {
        //     '{([^{}]*)}/'; // find everything within braces.

         $pattern = '~^\s*[\w\s]+\(.*\)\s*\K({((?>"[^"]*+"|\'[^\']*+\'|//.*$|/\*[\s\S]*?\*/|#.*$|<<<\s*["\']?(\w+)["\']?[^;]+\3;$|[^{}<\'"/#]++|[^{}]++|(?1))*)})~m';

         // '~^ \s* [\w\s]+ \( .* \) \s* \K'.       # how it matches a function definition
        //      '('.                                  # (1 start)
        //           '{'.                             # opening brace
        //           '('.                             # (2 start)
         /*                '(?>'.*/                      // atomic grouping (for its non-capturing purpose only)
        //                     '" [^"]*+ "'.          # double quoted strings
        //                  '|  \' [^\']*+ \''.       # single quoted strings
        //                  '|  // .* $'.             # a comment block starting with //
        //                  '|  /\* [\s\S]*? \*/'.    # a multi line comment block /*...*/
        //                  '|  \# .* $'.             # a single line comment block starting with #...
        //                  '|  <<< \s* ["\']?'.      # heredocs and nowdocs
        //                     '( \w+ )'.             # (3) ^
        //                     '["\']? [^;]+ \3 ; $'. # ^
        //                  '|  [^{}<\'"/#]++'.       # force engine to backtack if it encounters special characters [<'"/#] (possessive)
        //                  '|  [^{}]++'.             # default matching bahaviour (possessive)
        //                  '|  (?1)'.                # recurse 1st capturing group
        //                ')*'.                       # zero to many times of atomic group
        //           ')'.                             # (2 end)
        //           '}'.                             # closing brace
        //      ')~';                                  # (1 end)

         preg_match($pattern, $contents, $match);

         return $match[1];
     }

     /**
      * Parses the job class out of the given line of code.
      *
      * @throws \Exception
      */
     public function parseJobInLine(string $line, string $contents): ?Job
     {
         $line = trim($line);
         // match the line that potentially has the job,
         // they're usually called by "$this->run(Job...)"
         preg_match('/->run\(([^,]*),?.*\)?/i', $line, $match);

         // we won't do anything if no job has been matched.
         if (empty($match)) {
             return null;
         }

         $match = $match[1];
         // prepare for parsing
         $match = $this->filterJobMatch($match);

         /*
         * determine syntax style and afterwards detect how the job
         * class name was put into the "run" method as a parameter.
         *
         * Following are the different ways this might occur:
         *
         * 	- ValidateArticleInputJob::class
         * 		The class name has been imported with a 'use' statement
         * 		and uses the ::class keyword.
         * 	- \Fully\Qualified\Namespace::class
         * 		Using the ::class keyword with a FQDN.
         * 	- 'Fully\Qualified\Namespace'
         * 		Using a string as a class name with FQDN.
         * 	- new \Full\Class\Namespace
         * 		Instantiation with FQDN
         * 	- new ImportedClass($input)
         * 		Instantiation with an imported class using a `use` statement
         * 		passing parameters to the construction of the instance.
         * 	- new ImportedClass
         * 		Instantiation without parameters nor parentheses.
         */
         switch ($this->jobSyntaxStyle($match)) {
             case self::SYNTAX_STRING:
                 [$name, $namespace] = $this->parseStringJobSyntax($match, $contents);
                 break;

             case self::SYNTAX_KEYWORD:
                 [$name, $namespace] = $this->parseKeywordJobSyntax($match, $contents);
                 break;

             case self::SYNTAX_INSTANTIATION:
                 [$name, $namespace] = $this->parseInitJobSyntax($match, $contents);
                 break;
         }

         $domainName = $this->domainForJob($namespace);

         $domain = new Domain(
             $domainName,
             $this->findDomainNamespace($domainName),
             $domainPath = $this->findDomainPath($domainName),
             $this->relativeFromReal($domainPath)
         );

         $path = $this->findJobPath($domainName, $name);

         return new Job(
             $name,
             $namespace,
             basename($path),
             $path,
             $this->relativeFromReal($path),
             $domain
         );
     }

     /**
      * Parse the given job class written in the string syntax: 'Some\Domain\Job'
      */
     private function parseStringJobSyntax(string $match, string $contents): array
     {
         $slash = strrpos($match, '\\');
         if ($slash !== false) {
             $name = str_replace('\\', '', Str::substr($match, $slash));
             $namespace = '\\'.preg_replace('/^\\\/', '', $match);

             return [$name, $namespace];
         }

         return ['', ''];
     }

     /**
      * Parse the given job class written in the ::class keyword syntax:	SomeJob::class
      */
     private function parseKeywordJobSyntax(string $match, string $contents): array
     {
         // is it of the form \Full\Name\Space::class?
         // (using full namespace in-line)
         // to figure that out we look for
         // the last occurrence of a \
         $slash = strrpos($match, '\\');
         if ($slash !== false) {
             $namespace = str_replace('::class', '', $match);
             // remove the ::class and the \ prefix
             $name = str_replace(['\\', '::class'], '', Str::substr($namespace, $slash));
         } else {
             // nope it's just Space::class, we will figure
             // out the namespace from a "use" statement.
             $name = str_replace(['::class', ');'], '', $match);
             preg_match("/use\s(.*$name)/", $contents, $namespace);
             // it is necessary to have a \ at the beginning.
             $namespace = '\\'.preg_replace('/^\\\/', '', $namespace[1]);
         }

         return [$name, $namespace];
     }

     /**
      * Parse the given job class written in the ini syntax:	new SomeJob()
      */
     private function parseInitJobSyntax(string $match, string $contents): array
     {
         // remove the 'new ' from the beginning.
         $match = str_replace('new ', '', $match);

         // match the job's class name
         preg_match('/(.*Job).*[\);]?/', $match, $name);
         $name = $name[1];

         // Determine Namespace
         $slash = strrpos($name, '\\');
         // when there's a slash when matching the reverse of the namespace,
         // it is considered to be the full namespace we have.
         if ($slash !== false) {
             $namespace = $name;
             // prefix with a \ if not found.
             $name = str_replace('\\', '', Str::substr($namespace, $slash));
         } else {
             // we don't have the full namespace, so we will figure it out
             // from the 'use' statements that we have in the file.
             preg_match("/use\s(.*$name)/", $contents, $namespace);
             $namespace = '\\'.preg_replace('/^\\\/', '', $namespace[1]);
         }

         return [$name, $namespace];
     }

     /**
      * Get the domain for the given job's namespace.
      */
     private function domainForJob(string $namespace): string
     {
         preg_match('/Domains\\\([^\\\]*)\\\Jobs/', $namespace, $domain);

         return (! empty($domain)) ? $domain[1] : '';
     }

     /**
      * Filter the matched line in preparation for parsing.
      */
     private function filterJobMatch(string $match): string
     {
         // we don't want any quotes
         return str_replace(['"', "'"], '', $match);
     }

     /**
      * Determine the syntax style of the class name.
      * There are three styles possible:
      *
      * 	- Using the 'TheJob::class' keyword
      * 	- Using instantiation: new TheJob(...)
      * 	- Using a string with the full namespace: '\Domain\TheJob'
      */
     private function jobSyntaxStyle(string $match): string
     {
         if (str_contains($match, '::class')) {
             $style = self::SYNTAX_KEYWORD;
         } elseif (str_contains($match, 'new ')) {
             $style = self::SYNTAX_INSTANTIATION;
         } else {
             $style = self::SYNTAX_STRING;
         }

         return $style;
     }
 }
