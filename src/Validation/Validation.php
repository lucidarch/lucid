<?php

namespace Lucid\Validation;

/**
 * Validation factory.
 */
class Validation
{
    /**
     * Get a new validation instance for the given attributes and rules.
     *
     * @param  array  $data
     * @param  array  $rules
     * @param  array  $messages
     * @param  array  $customAttributes
     * @return \Illuminate\Validation\Validator
     */
    public function make(
        array $data,
        array $rules,
        array $messages = [],
        array $customAttributes = []
    ): \Illuminate\Validation\Validator {
        return $this->getValidationFactory()->make($data, $rules, $messages, $customAttributes);
    }

    /**
     * Get a validation factory instance.
     *
     * @return \Illuminate\Validation\Factory
     */
    public function getValidationFactory(): \Illuminate\Validation\Factory
    {
        return app(\Illuminate\Contracts\Validation\Factory::class);
    }
}
