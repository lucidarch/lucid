<?php

namespace Lucid\Domains\Http\Jobs;

use Illuminate\Routing\ResponseFactory;
use Lucid\Units\Job;

class RespondWithViewJob extends Job
{
    protected array|string $template;

    protected array $data;

    protected int $status;

    protected array $headers;

    public function __construct($template, $data = [], $status = 200, array $headers = [])
    {
        $this->template = $template;
        $this->data = $data;
        $this->status = $status;
        $this->headers = $headers;
    }

    public function handle(ResponseFactory $factory)
    {
        return $factory->view($this->template, $this->data, $this->status, $this->headers);
    }
}
