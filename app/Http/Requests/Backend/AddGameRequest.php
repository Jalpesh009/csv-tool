<?php 
namespace App\Http\Requests\Backend; 
use Illuminate\Foundation\Http\FormRequest;

use App\Models\Fields;
class AddGameRequest extends FormRequest{

	public function authorize()
    {
        return true;
    }

	public function rules(){
	 	$fields = Fields::where('master_field_required', 'yes')->get()->toArray(); 
	 	$rules = array(); 
	 	foreach (  $fields as $key => $value) {
		 	// $rules['input']['game'][ $value['id']]  = 'required'; 
		 	
	 		if($value['master_field_type'] == 'image'){ 
	  			$rules['photos_'. $value['id']] = 'required'; 
	  		}else{ 
	  			$rules['input.game.'. $value['id']]  =  'required' ;
	 		}  
	 	} 
	 	
	 // 	echo '<pre>Rules : '; print_r( $rules );
		// die;
		return $rules;
	}

	public function messages()
    { 
    	$fields123 = Fields::where('master_field_required', 'yes')->get()->toArray(); 
        $messages = [];
   
	  	foreach (  $fields123 as $key123 => $value123) { 
	  		if($value123['master_field_type'] == 'image'){ 
	  			$messages['photos_'. $value123['id'].'.'.'required'] = 'The '.$value123['master_field_name'].' must be required.'; 
	  		}else{ 
	  			$messages['input.game.'. $value123['id'].'.'.'required'] = 'The '.$value123['master_field_name'].' must be required.'; 
	 		}
	 	} 
	 //    echo '<pre>'; print_r( $messages );
		// die;
        return $messages;
    }
}