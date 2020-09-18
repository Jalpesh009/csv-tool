<?php 
namespace App\Http\Requests\Backend;
use Illuminate\Foundation\Http\FormRequest;

class AddstoreRequest extends FormRequest{

	public function authorize()
    {
        return true;
    }

	public function rules(){
		$rules = [
				'store_name' => 'required', 
				'store_description' => 'required',  
				'logo' => 'required|max:10000',
				'contact_email' => 'required|email',
				'contact_person' => 'required', 
				'contact_number' => 'required|numeric',
				// 'store_csv'=>'mimes:xlsx',
				// 'edit_store_csv'=>'mimes:xlsx' 
				 ];
		return $rules;
	}
	public function messages()
    { 
        // $messages = [];
        $messages = [
        		'store_name.required' => 'The Store name is required.', 
				'store_description.required' => 'The Store description is required.',
				'logo.required' => 'The Logo is required.', 
				'contact_email.required' => 'The Contact email is required.',
				'contact_person.required' => 'The Contact person is required.', 
				'contact_number.required' => 'The Contact number is required.',
				// 'store_csv.mimes'=>'The Store csv file should be in xlsx format.',
				// 'edit_store_csv.mimes'=>'The Store csv file should be in xlsx format.' 
			];
        return $messages;
    }
}