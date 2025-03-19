<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\User;

class UsersUnique implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        
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
        if(request()->get('register_type') == 'corporate' || request()->get('type') == 'corporate')
            return User::where([$attribute => $value, 'type' => 'corporate'])->count() == 0;
        else
            return User::where([$attribute => $value, 'type' => 'individual'])->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The email has already been taken.';
    }
}
