<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Providers\LdapServiceProvider;

class idnoAvail implements Rule
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
        $openldap = new LdapServiceProvider();
        $id = strtoupper(trim($value));
        return ! $openldap->checkIdno($id);
    }

    /** 
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '身分證字號已經被使用';
    }
}
