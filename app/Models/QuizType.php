<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizType extends Model
{
	// Table Basic Info

    protected $table="quiz_type";

	protected $primaryKey ="id_quiz_type";

	protected $fillable = array('id_quiz_type','name');


	/**
	*@author chiranjeevi
	*Description :list of all quiz types
	*Params :
	*/

	public function getQuizTypes(){
		$result = QuizType::all();
		return $result;
	}
}