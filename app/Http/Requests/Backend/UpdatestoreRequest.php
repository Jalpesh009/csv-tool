<?php 
namespace App\Http\Requests\Backend;
use Illuminate\Foundation\Http\FormRequest;

class UpdatestoreRequest extends FormRequest{

	public function authorize()
    {
        return true;
    }

	public function rules(){
		$rules = [
				'store_name' => 'required',  
				'logo' => 'required|max:10000',
				'contact_email' => 'required|email',
				'contact_person' => 'required', 
				'contact_number' => 'required|numeric' ];
		return $rules;
	}
	public function messages()
    { 
        $messages = [];
        return $messages;
    }
}