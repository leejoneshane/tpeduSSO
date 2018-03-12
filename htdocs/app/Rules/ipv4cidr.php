<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ipv4cidr implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
	$ereg_pattern = '/^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])(\/([0-9]|[1-2][0-9]|3[0-2]))$/';
	if (!preg_match($ereg_pattern, $value)) return false;
	return true;
    }

    /** 
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '網路地址與遮罩格式不正確。';
    }
}
