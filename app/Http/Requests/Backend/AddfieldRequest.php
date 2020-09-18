<?php 
namespace App\Http\Requests\Backend;
use Illuminate\Foundation\Http\FormRequest; 
class AddfieldRequest extends FormRequest{

	public function authorize()
    {
        return true;
    }

	public function rules(){
		   
		$rules = [
			'f_name' => 'required', 
			'f_type' => 'required',
			'f_unicname' => 'required_if:f_fieldunic,==,yes',
		];	 		 
		return $rules;
	}
	public function messages()
    {
        $messages = [
        		'f_name.required' => 'Field name is required', 
				'f_type.required' => 'Field type is required',
				'f_unicname.required_if' => 'Field Unic Name is required'];
        
        return $messages;
    }
}