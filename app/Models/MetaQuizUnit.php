<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetaQuizUnit extends Model
{
    // Table Basic Info

    protected $table="meta_quiz_unit";

	protected $primaryKey ="id";

	protected $fillable = array('id','name','quiz_type','url');

	/**
	*@author chiranjeevi
	*Description :list of all quiz
	*Params :startIndex,limitIndex,sortColumn,sortOrder,searchString
	*/

	public function getAllQuizs($startIndex,$limitIndex,$sortOrder,$sortColumn,$count=FALSE,$searchColumns=array(),$searchString=''){
		$finalArray = array();
		$where = "";
		$searchApplied=0;
		if($searchString!='')
      	{
		if($searchColumns&&count($searchColumns)>0) {
         	if(count($searchColumns)>0)
	          {
	              foreach($searchColumns as $searchColumn)
	              {
	                  if($searchApplied==0)
	                      $where.=" ((meta_quiz_unit.".$searchColumn." LIKE '%$searchString%')";
	                  else
	                  $where.=" OR (meta_quiz_unit.".$searchColumn." LIKE '%$searchString%')";
	                  $searchApplied++;
	              }
	              if($searchApplied>0)
	                  $where.=")";
	          }
      	}
      	}
      	if($where==''){
      		$where = '1';
      	}

      	$quiz_query = MetaQuizUnit::whereRaw($where);

      	if($count){
      		$quiz_count = $quiz_query->count();
      		$finalArray['quiz_count'] = $quiz_count;
      	}


      	$quiz_query->selectRaw('meta_quiz_unit.*');

      	if($sortColumn!=''){
      		$quiz_query->orderBy($sortColumn,$sortOrder);
      	}else{
      		$quiz_query->orderBy('created_at','DESC');

      	}
      	$quiz_query->limit($limitIndex)->offset($startIndex);  
		
		$finalArray['quizs'] = $quiz_query->get();

		return $finalArray;

	}

	/**
	*@author chiranjeevi
	*Description :checks quiz exists with the name
	*Params :name
	*/

	public function checkQuizExists($name){
		$res = MetaQuizUnit::where(['name'=>$name])
				->first();
		return $res;
	}


	/**
	*@author chiranjeevi
	*Description :sets the quiz with the name
	*Params :name,quiz_type
	*/

	public function setQuiz($name,$type){
		$res = MetaQuizUnit::insertGetId(['name'=>$name,'quiz_type'=>$type]);
		if($res){
			$quizTempModel = new \App\Models\MetaQuizUnitTemp();
			$res1 = $quizTempModel->setQuizTemp($res,$name,$type);
		}
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description :update the quiz 
 	*Params : id,updatearray
	*/
	public function updateQuiz($id,$updateArray,$updateTempTable=FALSE){
		if($updateTempTable){
			$tempObj = new \App\Models\MetaQuizUnitTemp();
			$resTemp = $tempObj->updateQuizTemp($id,$updateArray);
		}
		if(isset($updateArray['json_updated_flag'])){
			unset($updateArray['json_updated_flag']);
		}
		if(isset($updateArray['question_json'])){
			unset($updateArray['question_json']);
		}
		$res = MetaQuizUnit::where(['id'=>$id])->update($updateArray);
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description :delete the quiz
 	*Params : ids array
	*/
	public function deleteQuiz($ids,$deleteTempTable){
		$res = MetaQuizUnit::destroy($ids);
		if($res&&$deleteTempTable){
			$quizTempModel = new \App\Models\MetaQuizUnitTemp();
			$res1 = $quizTempModel->deleteQuiz($ids);
		}
		return $res;
	}

	/**
	*@author chiranjeevi
	*Description: Return all the quizs
	**/

	public function getQuizs(){
		$allQuiz = MetaQuizUnit::all();
		return $allQuiz;
	}
}
