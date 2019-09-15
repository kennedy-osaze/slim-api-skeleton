<?php

namespace App\Libraries\Validation;

use Respect\Validation\Exceptions\NestedValidationException;

class Validator
{
    protected $errors = [];

    /**
     * @param array $data The data to validate
     * @param array $rules The rules to validate the data against. To get full details about the rules check https://respect-validation.readthedocs.io/en/1.1/list-of-rules/
     * @param array $message The custom error messages that should replace the default messages. Each key is a rule identifier and the value is the custom message string. Use "{{name}}" to specify the data field validated against
     * @param  array customAttributes The key-value of the field name and a custom name for that field
     *
     * @return static
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = [])
    {
        foreach ($rules as $field => $rule) {
            try {
                $value = isset($data[$field]) ? $data[$field] : null;
                $attribute = !empty($customAttributes[$field]) ? $customAttributes[$field] : $field;

                $rule->setName($attribute)->assert($value);
            } catch (NestedValidationException $e) {
                $this->addError($e, $field, $messages);
            }
        }

        return $this;
    }

    /**
     * Check if there is any validation error
     *
     * @return bool
     */
    public function fails()
    {
        return !empty($this->errors);
    }

    /**
     * Return all validations errors if any
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    public function addError(NestedValidationException $exception, string $field, array $messages = [])
    {
        if (empty($messages)) {
            $this->errors[$field] = $exception->getMessages();

            return;
        }

        $this->errors[$field] = array_values(array_filter($exception->findMessages($messages)));
    }
}
