<?php

namespace App\Http\Requests\Backend\Auth\User;

use Illuminate\Foundation\Http\FormRequest;
use App\Helpers\Auth\SocialiteHelper;
use Illuminate\Validation\Rule;
/**
 * Class UpdateUserRequest.
 */
class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        //return $this->user()->isAdmin();
        return $this->user()->can('view backend');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        if ($this->user()->isAdmin()) {
            return [
                'email' => ['required', 'email', 'max:191'],
                'first_name' => ['required', 'max:191'],
                'last_name' => ['required', 'max:191'],
                'roles' => ['required', 'array'],
            ];
        }else{
            return [
                'email' => ['required', 'email', 'max:191'],
                'first_name' => ['required', 'max:191'],
                'last_name' => ['required', 'max:191'],
                'avatar_type' => ['required', 'max:191', Rule::in(array_merge(['gravatar', 'storage'], (new SocialiteHelper)->getAcceptedProviders()))],
                //'avatar_location' => ['sometimes', 'image', 'max:191'],
               // 'roles' => ['required', 'array'],
            ];
        }die;

    }
}
