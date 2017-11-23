<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaQuizUnitTemp extends Model
{

	// Table Basic Info

    protected $table="meta_quiz_unit_temp";

	protected $primaryKey ="id";

	protected $fillable = array('id','name','quiz_type','url','question_json','json_updated_flag');


	/**
	*@author chiranjeevi
	*Description :set the quiz temp table
	*Params : name,quiz_type 
	*/
	public function setQuizTemp($id,$name,$quiz_type){
		$res = MetaQuizUnitTemp::insert(['id'=>$id,'name'=>$name,'quiz_type'=>$quiz_type]);
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description :get the quiz temp table
	*Params : id 
	*/
	public function getTempQuiz($id){
		$res = MetaQuizUnitTemp::where(['id'=>$id])->first();
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description :update the quiz in temp table
 	*Params : id,question
	*/
	public function updateQuizTemp($id,$updateArray){
		$res = MetaQuizUnitTemp::where(['id'=>$id])->update($updateArray);
		return $res;
	}


	/**
	*@author chiranjeevi
	*Description :delete the quiz in temp table
 	*Params : ids array
	*/
	public function deleteQuiz($ids){
		$res = MetaQuizUnitTemp::destroy($ids);
		return $res;
	}


	public function updateorcreateQuizTemp($quiz_array){
		$res = MetaQuizUnitTemp::updateOrCreate(['id' => $quiz_array['id']],['id'=>$quiz_array['id'],'name'=>$quiz_array['name'],'quiz_type'=>$quiz_array['quiz_type'],'url'=>$quiz_array['url'],'question_json'=>$quiz_array['question_json'],'json_updated_flag'=>$quiz_array['json_updated_flag'],'created_at'=>$quiz_array['created_at'],'updated_at'=>$quiz_array['updated_at']]);
		return $res;
	}
}