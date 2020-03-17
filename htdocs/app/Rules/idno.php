<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class idno implements Rule
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
	    $id = strtoupper(trim($value)); //將英文字母全部轉成大寫，消除前後空白
	    //檢查第一個字母是否為英文字，第二個字元1 2 A~D 其餘為數字共十碼
	    $ereg_pattern = '/^[A-Z]{1}[12ABCD]{1}\d{8}$/';
        if (!preg_match($ereg_pattern, $id)) return false;
        if (substr($id,1) == '123456789') return false;
	    $wd_str="BAKJHGFEDCNMLVUTSRQPZWYX0000OI";   //關鍵在這行字串
	    $d1 = strpos($wd_str, $id[0])%10;
	    $sum = 0;
	    if($id[1] >= 'A') $id[1] = ord($id[1])-65; //居留證，第2碼轉成數字
	    for($ii=1;$ii<9;$ii++)
    	    $sum += (int)$id[$ii]*(9-$ii);
	    $sum += $d1 + (int)$id[9];
	    if ($sum%10 != 0) return false;
	    return true;
    }

    /** 
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return '身分證字號格式不正確';
    }
}
