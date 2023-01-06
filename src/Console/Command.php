<?php

namespace Lucid\Console;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

trait Command
{
    protected InputInterface $input;

    protected OutputInterface $output;

    /**
     * Configure the command options.
     */
    protected function configure(): void
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description);

        foreach ($this->getArguments() as $arguments) {
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
        }
    }

    /**
     * Default implementation to get the arguments of this command.
     */
    protected function getArguments(): array
    {
        return [];
    }

    /**
     * Default implementation to get the options of this command.
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * Execute the command.
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return (int) $this->handle();
    }

    /**
     * Get an argument from the input.
     */
    public function argument(string $key): ?string
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get an option from the input.
     */
    public function option(string $key): ?string
    {
        return $this->input->getOption($key);
    }

    /**
     * Write a string as information output.
     */
    public function info(string $string): void
    {
        $this->output->writeln("<info>$string</info>");
    }

    /**
     * Write a string as comment output.
     */
    public function comment(string $string): void
    {
        $this->output->writeln("<comment>$string</comment>");
    }

    /**
     * Write a string as error output.
     */
    public function error(string $string): void
    {
        $this->output->writeln("<error>$string</error>");
    }

    /**
     * Format input to textual table.
     *
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $rows
     */
    public function table(array $headers, mixed $rows, string $style = 'default'): void
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Ask the user the given question.
     */
    public function ask(string $question, bool $default = false): string
    {
        $question = '<comment>'.$question.'</comment> ';

        $confirmation = new ConfirmationQuestion($question, false);

        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $confirmation);
    }
}
