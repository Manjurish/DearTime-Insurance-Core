<?php     

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class UniqueInModel implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    var $model;
    var $column;

    public function __construct($model,$column = null)
    {
        $this->model = $model;
        $this->column = $column;
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
        $model = $this->model;
        $query = $model::where($this->column ?? $attribute,$value);
        return $query->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('validation.unique');
    }
}
