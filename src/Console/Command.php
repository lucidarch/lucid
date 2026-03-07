<?php

namespace Lucid\Console;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

trait Command
{
    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName($this->name)
            ->setDescription($this->description);

        foreach ($this->getArguments() as $arguments) {
            $this->addArgument(...$arguments);
        }

        foreach ($this->getOptions() as $options) {
            $this->addOption(...$options);
        }
    }

    /**
     * Default implementation to get the arguments of this command.
     *
     * @return array
     */
    public function getArguments()
    {
        return [];
    }

    /**
     * Default implementation to get the options of this command.
     *
     * @return array
     */
    public function getOptions()
    {
        return [];
    }

    /**
     * Execute the command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->input = $input;
        $this->output = $output;

        return (int) $this->handle();
    }

    /**
     * Get an argument from the input.
     *
     * @param string $key
     *
     * @return string
     */
    public function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get an option from the input.
     *
     * @param string $key
     *
     * @return string
     */
    public function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     */
    public function info($string)
    {
        $this->output->writeln("<info>$string</info>");
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @return void
     */
    public function comment($string)
    {
        $this->output->writeln("<comment>$string</comment>");
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @return void
     */
    public function error($string)
    {
        $this->output->writeln("<error>$string</error>");
    }

    /**
     * Format input to textual table.
     *
     * @param  array   $headers
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $rows
     * @param  string  $style
     * @return void
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Ask the user the given question.
     *
     * @param string $question
     * @param mixed  $default
     *
     * @return string
     */
    public function ask($question, $default = null)
    {
        $helper = $this->getHelperSet()->get('question');
        $q = new Question('<comment>' . $question . '</comment> ', $default);

        return $helper->ask($this->input, $this->output, $q);
    }

    /**
     * Ask the user the given secret question.
     *
     * @param string $question
     *
     * @return string
     */
    public function secret($question)
    {
        $helper = $this->getHelperSet()->get('question');
        $q = new Question('<comment>' . $question . '</comment> ');
        $q->setHidden(true)->setHiddenFallback(false);

        return $helper->ask($this->input, $this->output, $q);
    }
}
