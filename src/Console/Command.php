<?php

namespace Lucid\Console;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
            call_user_func_array([$this, 'addArgument'], $arguments);
        }

        foreach ($this->getOptions() as $options) {
            call_user_func_array([$this, 'addOption'], $options);
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
    public function execute(InputInterface $input, OutputInterface $output)
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
     *
     * @return string
     */
    public function ask($question, $default = false)
    {
        $question = '<comment>'.$question.'</comment> ';

        $confirmation = new ConfirmationQuestion($question, false);

        return $this->getHelperSet()->get('question')->ask($this->input, $this->output, $confirmation);
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
        $question = '<comment>'.$question.'</comment> ';

        return $this->getHelperSet()->get('dialog')->askHiddenResponse($this->output, $question, false);
    }
}
