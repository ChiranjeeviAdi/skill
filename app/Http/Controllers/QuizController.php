<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use S3FileUploader;
class QuizController extends Controller
{
    public function __construct(Request $request) {
        $this->middleware(function ($request, $next) {
           $adminDet                                =   $request->adminDet;

            if(($adminDet))
            {
                $this->id_admin                      =   $adminDet['id'];
            }

            $this->curr_date                        =   date('y-m-d H:i:s'); 
            return $next($request);
        });       
    }

    /**
	*@author chiranjeevi
	*Description :list of all quiz
	*Params :startIndex,limitIndex,sortOrder,sortColumn
	*/

	public function getAllQuizs(Request $request){
		
		$startIndex = $request->input('startIndex');
		$limitIndex = $request->input('limitIndex');
		$sortOrder = $request->input('sortOrder');
		$sortColumn = $request->input('sortColumn');
		$searchColumns = $request->input('searchColumns');
		$searchString = $request->input('searchString');

		$quizModel = new \App\Models\MetaQuizUnit();
		$count = TRUE;
		$quizs = $quizModel->getAllQuizs($startIndex,$limitIndex,$sortOrder,$sortColumn,$count,$searchColumns,$searchString);
		if($quizs&&count($quizs)>0){
			return \Response::json(['quiz'=>$quizs,'message'=>'Quiz Found'],200);
		}else{
			return \Response::json(['quiz'=>array(),'message'=>'No Quiz Found'],403);
		}

	}

	/**
	*@author chiranjeevi
	*Description :list of all quiz types
	*Params :
	*/

	public function getQuizTypes(Request $request){
		$quizTypeObj = new \App\Models\QuizType();
		$allQTypes = $quizTypeObj->getQuizTypes();
		if($allQTypes&&count($allQTypes)>0){
			return \Response::json($allQTypes,200);
		}else{
			return \Response::json(array(),403);
		}
	}


	/**
	*@author chiranjeevi
	*Description :set the quiz
	*Params : name,quiz_type 
	*/

	public function setQuiz(Request $request){
		$quizObj = new \App\Models\MetaQuizUnit();
		$name = $request->input('quiz_name');
		$quiz_type = $request->input('quiz_type');

		$exists = $quizObj->checkQuizExists($name);
		if($exists&&count($exists)>0){
			return \Response::json(array('message'=>'Quiz with name "'.$name.'" already exists'),403);
		}else{
			$resQ = $quizObj->setQuiz($name,$quiz_type);
			if($resQ){
				return \Response::json(array('message'=>'Quiz created successfully'),200);
			}else{
				return \Response::json(array('message'=>'Something went wrong'),403);
			}
		}
		
	}

	/**
	*@author chiranjeevi
	*Description :get the quiz
	*Params : id
	*/
	public function getTempQuiz($id,Request $request){
		$quizTempModel = new \App\Models\MetaQuizUnitTemp();
		$data = $quizTempModel->getTempQuiz($id);
		if($data&&count($data)>0){
			if($data&&$data->question_json!=''){
				$data->question_json = json_decode($data->question_json); 
			}
			return \Response::json(array('message'=>'Quiz found','quiz'=>$data),200);
		}else{
			return \Response::json(array('message'=>'Something went wrong'),403);
		}
	}


	/**
	*@author chiranjeevi
	*Description :set the quiz in temp table
 	*Params : id,question
	*/
	public function setTempQuizQuestion(Request $request){
		$id = $request->input('id');
		$quiz_type = $request->input('quiz_type');

		$quizTempModel = new \App\Models\MetaQuizUnitTemp();
		$quizData = $quizTempModel->getTempQuiz($id);
		$questionJson = new \stdclass();

		//Global Vars
		$errorInUploading = false;
		$errorMessageArray =array();
		$s3Obj = new S3FileUploader();//Class object

		$quizPath = \Config::get('config.QUIZ_IMAGES_S3_PREFIX_PATH');
		if($quiz_type==1){ //MCQ Question

			$question = $request->input('question');
			$question = json_decode($question);
			$singleQuestion = array();

			$singleQuestion['choice_type'] = intval($question->choice_type);
			if(isset($question->question))
			$singleQuestion['question'] = $question->question;
			if(isset($question->answer))
			$singleQuestion['answer'] = $question->answer;
			if(isset($question->answer_line))
			$singleQuestion['answer_line'] = $question->answer_line;
			if(isset($question->explanation))
			$singleQuestion['explanation'] = $question->explanation;
			$options = array();

			if($question->choice_type==0){
				if(property_exists($question, 'option1')){
					$optionarray = array('option'=>$question->option1,'index'=>1);
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option2')){
					$optionarray = array('option'=>$question->option2,'index'=>2);
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option3')){
					$optionarray = array('option'=>$question->option3,'index'=>3);
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option4')){
					$optionarray = array('option'=>$question->option4,'index'=>4);
					array_push($options, $optionarray);
				}
				$singleQuestion['choices'] = $options;
			}

        	
			//Question Image
			if(isset($_FILES['question_image_url'])&&$_FILES['question_image_url']!=''){
				$resQIUpload = $s3Obj->uploadFile($_FILES['question_image_url'],$quizPath);
				if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
                    $singleQuestion['question_image_url'] = $resQIUpload['url'];
              	} else {
              		$errorInUploading = true;
                	array_push($errorMessageArray, "Question Image");
              	}
			}else{
				if(property_exists($question, 'question_image_url_text')){
					if(isset($question->question_image_url_text)&&$question->question_image_url_text!=''){
						if($s3Obj->is_url_exist($question->question_image_url_text)){

							$singleQuestion['question_image_url'] = $question->question_image_url_text;
						}else{
							$errorInUploading = true;
                			array_push($errorMessageArray, "Question Image");
						}
					}
				}
			}

			if($question->choice_type==1){

					//Option 1 Image
					if(isset($_FILES['option1_url'])&&$_FILES['option1_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option1_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray1 = array('option'=>$resQIUpload['url'],'index'=>1);
								array_push($options, $optionarray1);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option1 Image");
	                  	}
					}else{
						if(property_exists($question, 'option1_image_url')){
							if(isset($question->option1_image_url)&&$question->option1_image_url!=''){
								if($s3Obj->is_url_exist($question->option1_image_url)){

									$optionarray1 = array('option'=>$question->option1_image_url,'index'=>1);
									array_push($options, $optionarray1);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option1 Image");
								}
							}
						}
					}

					//Option 2 Image
					if(isset($_FILES['option2_url'])&&$_FILES['option2_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option2_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray2 = array('option'=>$resQIUpload['url'],'index'=>2);
								array_push($options, $optionarray2);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option2 Image");
	                  	}
					}else{
						if(property_exists($question, 'option2_image_url')){
							if(isset($question->option2_image_url)&&$question->option2_image_url!=''){
								if($s3Obj->is_url_exist($question->option2_image_url)){

									$optionarray2 = array('option'=>$question->option2_image_url,'index'=>2);
									array_push($options, $optionarray2);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option2 Image");
								}
							}
						}
					}

					//Option 3 Image
					if(isset($_FILES['option3_url'])&&$_FILES['option3_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option3_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray3 = array('option'=>$resQIUpload['url'],'index'=>3);
								array_push($options, $optionarray3);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option3 Image");
	                  	}
					}else{
						if(property_exists($question, 'option3_image_url')){
							if(isset($question->option3_image_url)&&$question->option3_image_url!=''){
								if($s3Obj->is_url_exist($question->option3_image_url)){

									$optionarray3 = array('option'=>$question->option3_image_url,'index'=>3);
									array_push($options, $optionarray3);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option3 Image");
								}
							}
						}
					}

					//Option 4 Image
					if(isset($_FILES['option4_url'])&&$_FILES['option4_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option4_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray4 = array('option'=>$resQIUpload['url'],'index'=>4);
								array_push($options, $optionarray4);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option4 Image");
	                  	}
					}else{
						if(property_exists($question, 'option4_image_url')){
							if(isset($question->option4_image_url)&&$question->option4_image_url!=''){
								if($s3Obj->is_url_exist($question->option4_image_url)){

									$optionarray4 = array('option'=>$question->option4_image_url,'index'=>4);
									array_push($options, $optionarray4);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option4 Image");
								}
							}
						}
					}	

					if($errorInUploading&&count($errorMessageArray)>0){
						$errors = implode(',',$errorMessageArray);
						$message =" unable to upload these ".$errors." images";
						return \Response::json(array('message'=>$message),403);
					}
					$singleQuestion['choices'] = $options;

			}



			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'items')){

					$questions = $questionJson->items;
				}else{
					$questions = array();
				}
				if($questionJson!=''){
					if(property_exists($questionJson,'items')){

					}else{
						$questionJson->items = array();
					}
				}else{
					$questionJson = new \stdclass();
					$questionJson->items = array();
				}
				if($singleQuestion&&count($singleQuestion)>0){
					array_push($questions, $singleQuestion);
				}
				$questionJson->items = $questions;
			}


		}
		if($quiz_type==6){ //Match the following
			$question = $request->input('question');
			if($question&&$question['options']!=''){
				foreach ($question['options'] as $key => $value) {
					$value['col_b_index'] = intval($value['col_b_index']);
				}
			}

			
			$questions = array();
			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''){
					$questions = $questionJson->questions;
					if($question['title']!=''&&$question['options']!=''){
						if($questions!=''){
							array_push($questions, $question);
							$questionJson->questions = $questions;
						}else{
							$questions =array();
							array_push($questions, $question);
							$questionJson->questions = array();
							$questionJson->questions=$questions;
						}

					}
				}else{
					$questionJson = new \stdclass();
					$questions =array();
					array_push($questions, $question);
					$questionJson->questions = array();
					$questionJson->questions=$questions;
				}
				
			}
		}//End of Match the following

		if($quiz_type==2){ // Slideup MCQ
			$question = $request->input('question');
			$question = (object) $question;
			$singleQuestion = array();

			if(isset($question->question))
			$singleQuestion['question'] = $question->question;
			if(isset($question->answer))
			$singleQuestion['answer'] = $question->answer;
			if(isset($question->answer_line))
			$singleQuestion['answer_line'] = $question->answer_line;
			if(isset($question->explanation))
			$singleQuestion['explanation'] = $question->explanation;
			$options = array();

			if(property_exists($question, 'option1')){
				$optionarray = array('option'=>$question->option1,'index'=>1);
				array_push($options, $optionarray);
			}
			if(property_exists($question, 'option2')){
				$optionarray = array('option'=>$question->option2,'index'=>2);
				array_push($options, $optionarray);
			}
			if(property_exists($question, 'option3')){
				$optionarray = array('option'=>$question->option3,'index'=>3);
				array_push($options, $optionarray);
			}
			if(property_exists($question, 'option4')){
				$optionarray = array('option'=>$question->option4,'index'=>4);
				array_push($options, $optionarray);
			}
			$singleQuestion['choices'] = $options;
			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'items')){

					$questions = $questionJson->items;

					if($questions&&count($questions)==4){
						return \Response::json(array('message'=>"Slide up MCQ contains only 4 questions. You are not allowed to add more than 4 Questions"),403);
					}

				}else{
					$questions = array();
				}
				if($questionJson!=''){
					if(property_exists($questionJson,'items')){

					}else{
						$questionJson->items = array();
					}
				}else{
					$questionJson = new \stdclass();
					$questionJson->items = array();
				}
				if($singleQuestion&&count($singleQuestion)>0){
					array_push($questions, $singleQuestion);
				}
				$questionJson->items = $questions;
			}
		}//End of Slide up MCQ

		if($quiz_type!=''&&$quiz_type==4){ //True False Quiz
        	$question = $request->input('question');
			$question = json_decode($question);
			$singleQuestion = array();

			if(isset($question->question))
			$singleQuestion['question'] = $question->question;
			if(property_exists($question, 'answer')){	
			 $singleQuestion['answer'] = intval($question->answer);
			}
			
			if(isset($question->explanation))
			$singleQuestion['explanation'] = $question->explanation;
			$options = array();

			if(isset($_FILES['image_url'])&&$_FILES['image_url']!=''){
				$resQIUpload = $s3Obj->uploadFile($_FILES['image_url'],$quizPath);
				if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
                    $singleQuestion['image_url'] = $resQIUpload['url'];
              	} else {
              		$errorInUploading = true;
                	array_push($errorMessageArray, "Question");
              	}
			}else{
				if(property_exists($question, 'image_url_text')){
					if(isset($question->image_url_text)&&$question->image_url_text!=''){
						if($s3Obj->is_url_exist($question->image_url_text)){
							$singleQuestion['image_url'] = $question->image_url_text;
							/*$fileObject = new \SplFileInfo($question->image_url_text);
							$fileExtension = $fileObject->getExtension();
							$fileName = $fileObject->getBasename();

							$resQIUpload = $s3Obj->generateAndUploadFile($question->image_url_text,$fileName,$fileExtension);
							if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
			                    $singleQuestion['image_url'] = $resQIUpload['url'];
			              	} else {
			              		$errorInUploading = true;
			                	array_push($errorMessageArray, "Question");
			              	}*/
						}else{
							$errorInUploading = true;
                			array_push($errorMessageArray, "Question");
						}
					}
				}
			}
			if($errorInUploading&&count($errorMessageArray)>0){
				$errors = implode(',',$errorMessageArray);
				$message =" unable to upload this ".$errors." images";
				return \Response::json(array('message'=>$message),403);
			}
			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'items')){

					$questions = $questionJson->items;
				}else{
					$questions = array();
				}
				if($questionJson!=''){
					if(property_exists($questionJson,'items')){

					}else{
						$questionJson->items = array();
					}
				}else{
					$questionJson = new \stdclass();
					$questionJson->items = array();
				}
				if($singleQuestion&&count($singleQuestion)>0){
					array_push($questions, $singleQuestion);
				}
				$questionJson->items = $questions;
			}
        }

        if($quiz_type!=''&&$quiz_type==5){  //Vide MCQ
        	$question = $request->input('question');
			$question = json_decode($question);
			$singleQuestion = array();

			$singleQuestion['questionType'] = intval($question->questionType);
			if(isset($question->question))
			$singleQuestion['questionText'] = $question->question;
			if(isset($question->answer))
			$singleQuestion['correctAns'] = $question->answer;
			if(isset($question->explanation))
			$singleQuestion['explanation'] = $question->explanation;
			$options = array();

			if($question->questionType==1){
				if(property_exists($question, 'option1')){
					$optionarray = array('option'=>$question->option1,'optionNo'=>1,"imageLink"=>"");
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option2')){
					$optionarray = array('option'=>$question->option2,'optionNo'=>2,"imageLink"=>"");
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option3')){
					$optionarray = array('option'=>$question->option3,'optionNo'=>3,"imageLink"=>"");
					array_push($options, $optionarray);
				}
				if(property_exists($question, 'option4')){
					$optionarray = array('option'=>$question->option4,'optionNo'=>4,"imageLink"=>"");
					array_push($options, $optionarray);
				}
				$singleQuestion['optionList'] = $options;
			}
			

			if($question->questionType==2){
					if(property_exists($question, 'option1_text')){
						$option1Text=$question->option1_text;
					}else{
						$option1Text="";
					}

					if(property_exists($question, 'option2_text')){
						$option2Text=$question->option2_text;
					}else{
						$option2Text="";
					}

					if(property_exists($question, 'option3_text')){
						$option3Text=$question->option3_text;
					}else{
						$option3Text="";
					}

					if(property_exists($question, 'option4_text')){
						$option4Text=$question->option4_text;
					}else{
						$option4Text="";
					}


					//Option 1 Image
					if(isset($_FILES['option1_url'])&&$_FILES['option1_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option1_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
								
		                    	$optionarray1 = array('option'=>$option1Text,'optionNo'=>1,'imageLink'=>$resQIUpload['url']);
								array_push($options, $optionarray1);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option1 Image");
	                  	}
					}else{
						if(property_exists($question, 'option1_image_url')){
							if(isset($question->option1_image_url)&&$question->option1_image_url!=''){
								if($s3Obj->is_url_exist($question->option1_image_url)){

									$optionarray1 = array('option'=>$option1Text,'optionNo'=>1,'imageLink'=>$question->option1_image_url);
									array_push($options, $optionarray1);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option1 Image");
								}
							}
						}
					}

					//Option 2 Image
					if(isset($_FILES['option2_url'])&&$_FILES['option2_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option2_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray2 = array('option'=>$option2Text,'optionNo'=>2,'imageLink'=>$resQIUpload['url']);
								array_push($options, $optionarray2);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option2 Image");
	                  	}
					}else{
						if(property_exists($question, 'option2_image_url')){
							if(isset($question->option2_image_url)&&$question->option2_image_url!=''){
								if($s3Obj->is_url_exist($question->option2_image_url)){
									$optionarray2 = array('option'=>$option2Text,'optionNo'=>2,'imageLink'=>$question->option2_image_url);
									array_push($options, $optionarray2);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option2 Image");
								}
							}
						}
					}

					//Option 3 Image
					if(isset($_FILES['option3_url'])&&$_FILES['option3_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option3_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray3 = array('option'=>$option2Text,'optionNo'=>3,'imageLink'=>$resQIUpload['url']);
								array_push($options, $optionarray3);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option3 Image");
	                  	}
					}else{
						if(property_exists($question, 'option3_image_url')){
							if(isset($question->option3_image_url)&&$question->option3_image_url!=''){
								if($s3Obj->is_url_exist($question->option3_image_url)){

									$optionarray3 = array('option'=>$question->option3_image_url,'optionNo'=>3,'imageLink'=>$resQIUpload['url']);
									array_push($options, $optionarray3);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option3 Image");
								}
							}
						}
					}

					//Option 4 Image
					if(isset($_FILES['option4_url'])&&$_FILES['option4_url']!=''){
						$resQIUpload = $s3Obj->uploadFile($_FILES['option4_url'],$quizPath);
						if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
		                    	$optionarray4 = array('option'=>$option4Text,'optionNo'=>4,'imageLink'=>$resQIUpload['url']);
								array_push($options, $optionarray4);
	                  	} else {
	                    	$errorInUploading = true;
	                    	array_push($errorMessageArray, "Option4 Image");
	                  	}
					}else{
						if(property_exists($question, 'option4_image_url')){
							if(isset($question->option4_image_url)&&$question->option4_image_url!=''){
								if($s3Obj->is_url_exist($question->option4_image_url)){

									$optionarray4 = array('option'=>$option4Text,'optionNo'=>4,'imageLink'=>$question->option4_image_url);
									array_push($options, $optionarray4);
								}else{
									$errorInUploading = true;
	                    			array_push($errorMessageArray, "Option4 Image");
								}
							}
						}
					}	

					if($errorInUploading&&count($errorMessageArray)>0){
						$errors = implode(',',$errorMessageArray);
						$message =" unable to upload these ".$errors." images";
						return \Response::json(array('message'=>$message),403);
					}
					$singleQuestion['optionList'] = $options;

			}



			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'questionList')){

					$questions = $questionJson->questionList;
				}else{
					$questions = array();
				}
				if($questionJson!=''){
					if(property_exists($questionJson,'questionList')){

					}else{
						$questionJson->questionList = array();
					}
				}else{
					$questionJson = new \stdclass();
					$questionJson->questionList = array();
				}

				$singleQuestion['questionNo'] = count($questions)+1;
				if($singleQuestion&&count($singleQuestion)>0){
					array_push($questions, $singleQuestion);
				}
				$questionJson->questionList = $questions;
			}

        }

        if($quiz_type&&$quiz_type==7){ //Pick 5
        	$question = $request->input('question');
        	$options = $request->input('options');
        	$explanation = $request->input('explanation');
			$singleQuestion = array();
			if(isset($question['question'])){
				$singleQuestion['question'] = $question['question'];
			}
       	 	
       	 	$singleQuestion['select_count'] = 0;
       	 	if(isset($question['explanation'])){
       	 		$singleQuestion['explanation'] = $question['explanation'];
       	 	}
       	 	$count= 0;
       	 	if(isset($question['options'])){
       	 		foreach ($question['options'] as $key => $value) {
       	 			if(isset($value)&&isset($value['$$hashKey'])){

	       	 			unset($value['$$hashKey']);
       	 			}
       	 			if($value['answer']==1){
       	 				$count++;
       	 			}
       	 			$question['options'][$key] = $value;

	       	 	}
	       	 	$singleQuestion['select_count'] = $count;
				$singleQuestion['items'] = $question['options'];
       	 		
       	 	}
       	 	if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'questions')){

					$questions = $questionJson->questions;
				}else{
					$questions = array();
				}
				if($questionJson!=''){
					if(property_exists($questionJson,'questions')){

					}else{
						$questionJson->questions = array();
					}
				}else{
					$questionJson = new \stdclass();
					$questionJson->questions = array();
				}
				if($singleQuestion&&count($singleQuestion)>0){
					array_push($questions, $singleQuestion);
				}
				$questionJson->questions = $questions;
			}
       	}

       	if($quiz_type!=''&&$quiz_type==3) { //Line up
       		if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''&&property_exists($questionJson, 'question')){
					return \Response::json(array('message'=>'You are not allowed to add multiple questions for line up quiz'),403);
					
				}else{
					$singleQuestion = new \stdclass();
					$question = $request->input('question');
					if(isset($question['lower_text'])){
						$singleQuestion->lower_text = $question['lower_text'];
					}
					if(isset($question['higher_text'])){
						$singleQuestion->higher_text = $question['higher_text'];
					}
					if(isset($question['time'])){
						$singleQuestion->time = $question['time'];
					}
					if(isset($question['question'])){
						$singleQuestion->question = $question['question'];
					}
					if(isset($question['options'])){
						foreach ($question['options'] as $key => $value) {
							if(isset($value)&&isset($value['$$hashKey'])){

			       	 			unset($value['$$hashKey']);
		       	 			}
		       	 			if($value['preadd']&&$value['preadd']=="true"){
		       	 				$value['preadd'] = true;
		       	 			}else{
		       	 				$value['preadd'] = false;
		       	 			}
		       	 			$question['options'][$key] = $value;

						}
						$singleQuestion->options = $question['options'];
					}
					$questionJson = $singleQuestion;
				}
				
			}
       	}

       	if($quiz_type!=''&&$quiz_type==8){ //Bucket

       		$question = $request->input('question');
			$question = json_decode($question);
			$singleQuestion = array();

			/*if(isset($question->question))
			$singleQuestion['question'] = $question->question;
*/
			/*if(isset($question->buckets)){
				if(count($question->buckets)>0){
					foreach ($question->buckets as $key1 => $value1) {
						$sampleKeyText = 'bucket_'.$value1->index."_text";
						$singleQuestion[$sampleKeyText] = $value1->bucket_text;
					}
				}
			}*/
			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''){
					if(property_exists($questionJson, 'items')){
						return \Response::json(array('message'=>'You are not allowed to add multiple questions for this quiz'),403);
					}
				}
			}
			$items = array();
			if(isset($question->options)){
				if(count($question->options)>0){
					foreach ($question->options as $key2 => $value2) {
						$sampleKey = 'image_url'.$value2->index;
						$itemarray = array();

						if(isset($_FILES[$sampleKey])&&$_FILES[$sampleKey]!=''){
							$resQIUpload = $s3Obj->uploadFile($_FILES[$sampleKey],$quizPath);
							if ($resQIUpload['url'] !== null && $resQIUpload['url'] !== '') {
			                    $itemarray['image_url'] = $resQIUpload['url'];
			              	} else {
			              		$errorInUploading = true;
			                	array_push($errorMessageArray, "Image url".$value2->index);
			              	}
						}else{
							if(property_exists($value2, 'image_url_text')){
								if(isset($value2->image_url_text)&&$value2->image_url_text!=''){
									if($s3Obj->is_url_exist($value2->image_url_text)){
										$itemarray['image_url'] = $value2->image_url_text;
										
									}else{
										$errorInUploading = true;
			                			array_push($errorMessageArray, "Image url".$value2->index);
									}
								}
							}

						}//end of else

						if(isset($value2->text)){
							$itemarray['text'] = $value2->text;
						}

						if(isset($value2->bucket)){
							$itemarray['bucket'] = $value2->bucket;
						}
						if(isset($value2->bucket_1_text)){
							$itemarray['bucket_1_text'] = $value2->bucket_1_text;
						}
						if(isset($value2->bucket_2_text)){
							$itemarray['bucket_2_text'] = $value2->bucket_2_text;
						}
						if(isset($value2->explanation)){
							$itemarray['explanation'] = $value2->explanation;
						}

						array_push($items, $itemarray);
					}
					$singleQuestion = $items;
				}
			}
			$questions = array();
			if($quizData){
				$questionJson = json_decode($quizData->question_json);
				if($questionJson&&$questionJson!=''){
					if(property_exists($questionJson, 'items')){
						return \Response::json(array('message'=>'You are not allowed to add multiple questions for this quiz'),403);
						/*$items = $questionJson->items;
						if($items!=''){
							//array_push($questions, $singleQuestion);
							$questionJson->items = $items;
						}else{
							$items =array();
							//array_push($questions, $singleQuestion);
							$questionJson->items = array();
							$questionJson->items=$singleQuestion;
						}*/
					}else{
						$items =array();
						$questionJson->items = array();
						$questionJson->items=$singleQuestion;
					}
				}else{
					$questionJson = new \stdclass();
					//array_push($questions, $singleQuestion);
					//$questionJson->questions = array();
					$questionJson->items=$singleQuestion;
				}
				
			}

			
       	}
       	//print_r($questionJson);
		$updateArray = array('question_json'=>json_encode($questionJson,JSON_NUMERIC_CHECK),'json_updated_flag'=>1);
		$resupdate = $quizTempModel->updateQuizTemp($id,$updateArray);
		if($resupdate){
			return \Response::json(array('message'=>'Question added successfully'),200);
		}else{
			return \Response::json(array('message'=>'Something went wrong'),403);
		}

	}

	/**
	*@author chiranjeevi
	*Description :update the quiz 
 	*Params : id,updatearray
	*/
	public function generateQuizUrl(Request $request){
		$id = $request->input('id');
		$quiz_type = $request->input('quiz_type');
        $quizTempModel = new \App\Models\MetaQuizUnitTemp();
        $quizModel = new \App\Models\MetaQuizUnit();
		$quizData = $quizTempModel->getTempQuiz($id);
        $s3Obj = new S3FileUploader();//Class object

		$subPath = 'quiz/jsons/';
		$contentType = 'application/json';
		
        $newFileName =$quizData->name."_".$quizData->id.".json";

        if($newFileName!=''){
        	$newFileName = str_replace(" ", "_", $newFileName);
        }
        $contents= $quizData->question_json;
        

        $error = false;
        $errorMessageArray = array();
        if($quiz_type!=''&&$quiz_type==6){
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'questions')&&$dummycontents->questions!=''&&count($dummycontents->questions)>0){
        			
        	}else{
        		return \Response::json(array('message'=>'There must be atleast 1 question required to generate the json'),403);
        	}
        }elseif($quiz_type!=''&&$quiz_type==1){
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	
        	if($dummycontents!=''){

	        	if(property_exists($dummycontents, 'answer_result')&&($dummycontents->answer_result==0||$dummycontents->answer_result==1)) {

	        	}else{
	        		$error = true;
	        		array_push($errorMessageArray, "Answer result");
	        	}

	        	if(property_exists($dummycontents, 'show_explanation')&&($dummycontents->show_explanation==0||$dummycontents->show_explanation==1)) {

	        	}else{
	        		$error = true;
	        		array_push($errorMessageArray, "Explanation");
	        	}


	        	if(property_exists($dummycontents, 'items')&&($dummycontents->items&&count($dummycontents->items>0))) {

	        	}else{
	        		$error = true;
	        		array_push($errorMessageArray, "Questions");
	        	}
	        	if($error&&count($errorMessageArray)>0){
	        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
	        		return \Response::json(array('message'=>$message),403);
	        	}
        	}else{
        		return \Response::json(array('message'=>'There must be atleast 1 question with some required fields data to generate the json'),403);
        	}
        }elseif($quiz_type!=''&&$quiz_type==2){//Slide up MCQ Validation
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'items')&&($dummycontents->items&&count($dummycontents->items>0))) {

        	}else{
        		$error = true;
        		$errorMessageArray = array('Questions');
        	}
        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}
        }elseif($quiz_type!=''&&$quiz_type==4){ //True False Quiz
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'question_topic')&&($dummycontents->question_topic!='')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Question topic");

        	}

        	if($dummycontents!=''&&property_exists($dummycontents, 'items')&&($dummycontents->items&&count($dummycontents->items>0))) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Questions");
        	}
        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}
        }elseif($quiz_type!=''&&$quiz_type==5){ //Video Quiz
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'videoUrl')&&($dummycontents->videoUrl!='')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "VideoUrl");
        	}

        	if($dummycontents!=''&&property_exists($dummycontents, 'intro')&&($dummycontents->intro!='')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "intro");
        	}


        	if($dummycontents!=''&&property_exists($dummycontents, 'introHeading')&&($dummycontents->introHeading!='')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "introHeading");
        	}

        	if($dummycontents!=''&&property_exists($dummycontents, 'questionList')&&($dummycontents->questionList&&count($dummycontents->questionList>0))) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Questions");
        	}

        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}

        }elseif($quiz_type&&$quiz_type==7){
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'show_explanation')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Show Explanation");
        	}

        	if($dummycontents!=''&&property_exists($dummycontents, 'questions')&&($dummycontents->questions&&count($dummycontents->questions>0))) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Questions");
        	}
        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}
        }elseif($quiz_type&&$quiz_type==3){
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'lower_text')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Lower Text");
        	}
        	if($dummycontents!=''&&property_exists($dummycontents, 'higher_text')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Higher Text");
        	}
        	if($dummycontents!=''&&property_exists($dummycontents, 'time')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Time");
        	}

        	if($dummycontents!=''&&property_exists($dummycontents, 'question')) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Question");
        	}
        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}
        }elseif($quiz_type&&$quiz_type==8){
        	$dummycontents = $contents;
        	$dummycontents = json_decode($dummycontents);
        	if($dummycontents!=''&&property_exists($dummycontents, 'items')&&$dummycontents->items!=''&&count($dummycontents->items)>0){
        			
        	}else{
        		return \Response::json(array('message'=>'There must be atleast 1 question required to generate the json'),403);
        	}
        	if(property_exists($dummycontents, 'show_explanation')&&($dummycontents->show_explanation==0||$dummycontents->show_explanation==1)) {

        	}else{
        		$error = true;
        		array_push($errorMessageArray, "Explanation");
        	}
        	if($error&&count($errorMessageArray)>0){
        		$message = "Field(s) ".implode(',',$errorMessageArray)." cannot be empty or null";
        		return \Response::json(array('message'=>$message),403);
        	}
        }


        $resFileupload = $s3Obj->uploadSourceFileWithContents($newFileName,$subPath,$contentType,$contents);
        if($resFileupload&&$resFileupload['error']!=''){
        	return \Response::json(array('message'=>$resFileupload['error']),403);
        }elseif($resFileupload&&$resFileupload['url']!=''){
        	$updateTempTable = TRUE;
        	$updateArray = array('id'=>$id,'url'=>$resFileupload['url'],'json_updated_flag'=>2);
        	$resUrl = $quizModel->updateQuiz($id,$updateArray,$updateTempTable);
        	if($resUrl){
        		return \Response::json(array('message'=>'Quiz json updated successfully'),200);
        	}else{	
        		return \Response::json(array('message'=>'Something went wrong while updating quiz'),403);
        	}
        }else{
        	
        		return \Response::json(array('message'=>'Something went wrong while generating quiz json file'),403);
        }

	}


	/**
	*@author: chiranjeevi
	*Function to update basic infor about the quiz into jsin
	*/

	public function updateQuizBasicJsonInfo(Request $request){
		$quiz_type = $request->input('quiz_type');
		$id = $request->input('id');
		$clicked = $request->input('clicked');

		$quizTempModel = new \App\Models\MetaQuizUnitTemp();

		$quizData = $quizTempModel->getTempQuiz($id);
		$question_json = new \stdclass();

		if($quiz_type==1){ // MCQ 
			$answer_result = $request->input('answer_result');
			$show_explanation = $request->input('show_explanation');

			if($quizData->question_json&&$quizData->question_json!=''){
				$question_json = json_decode($quizData->question_json);

				if(property_exists($question_json,'answer_result')){
					$question_json->answer_result = $answer_result;
				}else{
					$question_json->answer_result = $answer_result;
				}
				if(property_exists($question_json,'show_explanation')){
					$question_json->show_explanation = $show_explanation;
				}else{
					$question_json->show_explanation = $show_explanation;
				}
			}else{
				if($answer_result!='')
				$question_json->answer_result = $answer_result;
				if($show_explanation!='')
				$question_json->show_explanation = $show_explanation;
			}
		}elseif($quiz_type==4){ //TRue false
			$question_topic = $request->input('question_topic');
			if($quizData->question_json&&$quizData->question_json!=''){
				$question_json = json_decode($quizData->question_json);
				if(property_exists($question_json,'question_topic')){
					$question_json->question_topic = $question_topic;
				}else{
					$question_json->question_topic = $question_topic;
				}
			}else{
				if($question_topic!=''){
					$question_json->question_topic = $question_topic;
				}else{
					return \Response::json(array('message'=>'Question topic cannot be blank'),403);
				}
			}
		}elseif($quiz_type==5){ //Video
			$introHeading = $request->input('introHeading');
			$intro = $request->input('intro');
			$videoUrl = $request->input('videoUrl');

			if($quizData->question_json&&$quizData->question_json!=''){
				$question_json = json_decode($quizData->question_json);

				if(property_exists($question_json,'introHeading')){
					$question_json->introHeading = $introHeading;
				}else{
					$question_json->introHeading = $introHeading;
				}
				
				if(property_exists($question_json,'intro')){
					$question_json->intro = $intro;
				}else{
					$question_json->intro = $intro;
				}

				if(property_exists($question_json,'videoUrl')){
					$question_json->videoUrl = $videoUrl;
				}else{
					$question_json->videoUrl = $videoUrl;
				}
			}else{
				if($videoUrl!='')
				$question_json->videoUrl = $videoUrl;
				if($intro!='')
				$question_json->intro = $intro;
				if($introHeading!='')
				$question_json->introHeading = $introHeading;
			}
		}elseif($quiz_type&&$quiz_type==7){
			$show_explanation = $request->input('show_explanation');

			if($quizData->question_json&&$quizData->question_json!=''){
				$question_json = json_decode($quizData->question_json);

				
				if(property_exists($question_json,'show_explanation')){
					$question_json->show_explanation = $show_explanation;
				}else{
					$question_json->show_explanation = $show_explanation;
				}
			}else{
				
				if($show_explanation!='')
				$question_json->show_explanation = $show_explanation;
			}
		}elseif($quiz_type&&$quiz_type==8){
			$show_explanation = $request->input('show_explanation');

			if($quizData->question_json&&$quizData->question_json!=''){
				$question_json = json_decode($quizData->question_json);

				
				if(property_exists($question_json,'show_explanation')){
					$question_json->show_explanation = $show_explanation;
				}else{
					$question_json->show_explanation = $show_explanation;
				}
			}else{
				
				if($show_explanation!='')
				$question_json->show_explanation = $show_explanation;
			}
		}


		$updateArray = array('question_json'=>json_encode($question_json,JSON_NUMERIC_CHECK),'json_updated_flag'=>1);
		$resUpdate = $quizTempModel->updateQuizTemp($id,$updateArray);
		if($resUpdate){
			return \Response::json(array('message'=>'Quiz basic info updated successfully'),200);
		}else{
			return \Response::json(array('message'=>'Quiz basic info updation failed'),403);
		}
	}

	/**
	*@author: chiranjeevi
	*Function to delete quiz from quiz_table
	*/
	public function deleteQuiz(Request $request){
		$ids = $request->input('ids');
		$deleteFromTemp = true;
		$quizModel = new \App\Models\MetaQuizUnit();
		$deleteResult = $quizModel->deleteQuiz($ids,$deleteFromTemp);
		if($deleteResult){
			if(count($ids)>1){
				$message=count($ids)." quizs deleted successfully";
			}
			else{
				$message=count($ids)." quiz deleted successfully";
			}
			return \Response::json(array('message'=>$message),200);
		}else{
			return \Response::json(array('message'=>'Quiz deletion failed'),403);

		}
	}


	/**
	*Function to insert the element or update the elements as per as quiz data
	*@author chiranjeevi
	*/

	public function setTempQuiz(Request $request){
		$quizModel = new \App\Models\MetaQuizUnit();
		$quizTempModel = new \App\Models\MetaQuizUnitTemp();
		$allQuiz = $quizModel->getQuizs();

		$count=0;
		if($allQuiz&&count($allQuiz)>0){
			foreach ($allQuiz as $key => $value) {
			 	
			 	$quiz_array = array();
			 	$quiz_array['id'] = $value->id;
			 	$quiz_array['name'] = $value->name;
			 	$quiz_array['quiz_type'] = $value->quiz_type;
			 	$quiz_array['url'] = $value->url;
			 	$quiz_array['created_at'] = $value->created_at;
			 	$quiz_array['updated_at'] = $value->updated_at;
			 	if($value->url!=NULL&&$value->url!=''){
			 		$file_contents = file_get_contents($value->url);
			 		$quiz_array['question_json'] = $file_contents;
			 		$quiz_array['json_updated_flag'] = 2;
			 	}else{
			 		$quiz_array['question_json'] = NULL;
			 		$quiz_array['json_updated_flag'] = 1;
			 	}

			 	$res  = $quizTempModel->updateorcreateQuizTemp($quiz_array);
			 	
			 	if($res){
			 		$count++;
			 	}
			}
		}
		echo $count." Quiz updated successfully"; 
	}
}
