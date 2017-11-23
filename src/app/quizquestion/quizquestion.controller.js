(function() {
    'use strict';
    
    angular
        .module('smartskillApp')
        .controller('QuizQuestionController', QuizQuestionController);

    /** @ngInject */
    function QuizQuestionController($log,$stateParams,$http,$rootScope,$location,alertify) {
    	var quizquestion = this;
    	quizquestion.radioModel = 'basic';
    	quizquestion.id = $stateParams.id;
    	$log.info('quiz QuizQuestionController',quizquestion.id);
        alertify.logPosition("top right").maxLogItems(1);
        /*alertify.closeLogOnClick(true).delay(100000).error("Hiding in 100 seconds");
        alertify.closeLogOnClick(true).log("Standard log message");*/
        quizquestion.question={};
        /**
        Match the following
        **/
        var i;
        var j;
        quizquestion.question.options = [];
        quizquestion.question.buckets = [];
        quizquestion.dummyQuestion = {};
        quizquestion.dummyQuestion.optionsa = [];
        quizquestion.dummyQuestion.optionsb = [];
        quizquestion.dummyQuestion.optionsdummy = [];

        var options={};
        quizquestion.showQuizType = function(type){
            if(angular.isDefined(quizquestion.quiztypes)&&quizquestion.quiztypes.length>0){
                for(i=0;i<quizquestion.quiztypes.length;i++)
                  {
                      if(quizquestion.quiztypes[i].id_quiz_type==type){
                        return quizquestion.quiztypes[i].name ;
                      }
                  }
                }
        };
        /** Quiz Types List **/
        quizquestion.getQuizTypes = function(){
            $http({
                    method: "GET",
                    url: $rootScope.SITE_URL+'Api/v1/getQuizTypes',
                    timeout:20000,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).success(function (data,status) {
                if(status==200){
                    quizquestion.quiztypes = data;
                    
                }
            });
        };

        quizquestion.getQuizTypes(); 
    	quizquestion.getQuiz = function(){
    		$http({
                    method: "GET",
                    url: $rootScope.SITE_URL+'Api/v1/getTempQuiz/'+quizquestion.id,
                    timeout:20000,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8','Access-Control-Allow-Origin':"*"}
            }).success(function (data,status) {
            	if(status==200){
            		$log.info(data);
            		quizquestion.quiz = data.quiz;
                    $log.info(quizquestion.quiz);
                    var qjson = quizquestion.quiz.question_json;
                    i=0;
                    j=0;
                    if(quizquestion.quiz.quiz_type==1){
                        if(qjson!=''&&qjson!=null){
                            if(angular.isDefined(qjson.answer_result)){
                                quizquestion.question.answer_result = qjson.answer_result.toString();
                            }else{
                                //quizquestion.question.answer_result = '0';
                            }
                            if(angular.isDefined(qjson.show_explanation)){
                                quizquestion.question.show_explanation = qjson.show_explanation.toString();

                            }else{
                                //quizquestion.question.show_explanation = '0';

                            }

                        }
                    }
                    if(quizquestion.quiz.quiz_type==4){
                        if(qjson!=''&&qjson!=null){
                            if(angular.isDefined(qjson.question_topic)){
                                quizquestion.question.question_topic = qjson.question_topic.toString();
                            }
                        }
                    }
                     if(quizquestion.quiz.quiz_type==5){
                        if(qjson!=''&&qjson!=null){
                            if(angular.isDefined(qjson.intro)){
                                quizquestion.question.intro = qjson.intro.toString();
                            }
                            if(angular.isDefined(qjson.introHeading)){
                                quizquestion.question.introHeading = qjson.introHeading.toString();
                            }
                            if(angular.isDefined(qjson.videoUrl)){
                                quizquestion.question.videoUrl = qjson.videoUrl.toString();
                            }
                        }
                    }
                    if(quizquestion.quiz.quiz_type==7){
                        quizquestion.question.options = [];
                        if(qjson!=''&&qjson!=null){
                            if(angular.isDefined(qjson.show_explanation)){
                                quizquestion.question.show_explanation = qjson.show_explanation.toString();
                            }
                        }
                        for ( i= 1; i <= 5; i++) {
                           options = {'text':"","answer":"","index":i};
                          quizquestion.question.options.push(options);  
                        }
                    }
                    if(quizquestion.quiz.quiz_type==3){
                        quizquestion.question.options=[];
                        for ( i= 1; i <= 4; i++) {
                          options = {'text':"","preadd":"","index":i};
                          quizquestion.question.options.push(options);  
                        }
                    }
                    if(quizquestion.quiz.quiz_type==8){
                        quizquestion.question.options = [];
                        quizquestion.question.buckets = [];
                        if(qjson!=''&&qjson!=null){
                            if(angular.isDefined(qjson.show_explanation)){
                                quizquestion.question.show_explanation = qjson.show_explanation.toString();
                            }
                        }
                        for ( i= 1; i <= 2; i++) {
                           options = {'bucket_text':"Bucket "+i,"index":i};
                           quizquestion.question.buckets.push(options);  
                        }
                        for ( j= 1; j <= 1; j++) {
                           options = {'text':"","index":j,"bucket":"","image_url":"","bucket_1_text":"Bucket 1","bucket_2_text":"Bucket 2"};
                           quizquestion.question.options.push(options);  
                        }
                    }

                    
            	}
            });
    	};
    	quizquestion.getQuiz();


        quizquestion.addNewOption = function(options,quiz_type){
            if(quiz_type==7){
                    if(options.length>=7){
                        alertify.closeLogOnClick(true).delay(5000).error('You are not allowed to add more than 7 options');   

                        return false;
                    }else{
                        var length = quizquestion.question.options.length;
                        var options1 = {'text':"","answer":"","index":length+1};
                        quizquestion.question.options.push(options1);
                    }
            }

            if(quiz_type==3){
                if(options.length>=6){
                        alertify.closeLogOnClick(true).delay(5000).error('You are not allowed to add more than 6 options');   
                        return false;
                    }else{
                        var length1 = quizquestion.question.options.length;
                        var options2 = {'text':"","answer":"","index":length1+1};
                        quizquestion.question.options.push(options2);
                    }
            }

            if(quiz_type==8){
                /* if(options.length>=5){
                        alertify.closeLogOnClick(true).delay(5000).error('You are not allowed to add more than 5 options');   
                        return false;
                    }else{*/
                        var length2 = quizquestion.question.options.length;
                           options = {'text':"","index":length2+1,"bucket":"","image_url":""};
                        quizquestion.question.options.push(options);
                /*    }*/
            }
            
        };

        quizquestion.addNewBucket = function(buckets,quiz_type){
           

            if(quiz_type==8){
                if(buckets.length>=3){
                        alertify.closeLogOnClick(true).delay(5000).error('You are not allowed to add more than 3 buckets');   
                        return false;
                    }else{
                        var length1 = quizquestion.question.buckets.length;
                         var  options2 = {'bucket_text':"Bucket "+(length1+1),"index":length1+1};
                        quizquestion.question.buckets.push(options2);
                    }
            }
            
        };


        quizquestion.removeLastOption = function(index){
            if(index!==''){
                quizquestion.question.options.splice(index);
            }
        };
        quizquestion.removeBucket = function(index){
            if(index!==''){
                quizquestion.question.buckets.splice(index);
            }
        };

    	quizquestion.resetQuestion = function(quiz_type){
    		quizquestion.question={};
            if(quiz_type==6){      
                quizquestion.question.options = [];
                quizquestion.dummyQuestion = {};
                quizquestion.dummyQuestion.optionsa = [];
                quizquestion.dummyQuestion.optionsb = [];
                quizquestion.dummyQuestion.optionsdummy = [];
            }
            var option3 ={};
            if(quiz_type==7){
                quizquestion.question.options=[];
                for (i = 1; i <= 5; i++) {
                      option3 = {'text':"","answer":"","index":i};
                      quizquestion.question.options.push(option3);  
                }
            }
            if(quiz_type==3){
                quizquestion.question.options=[];
                for (i = 1; i <= 4; i++) {
                       option3 = {'text':"","preadd":"","index":i};
                      quizquestion.question.options.push(option3);  
                }
            }

            if(quiz_type==8){
                quizquestion.question.options=[];
                quizquestion.question.buckets=[];
                for ( i= 1; i <= 2; i++) {
                   options = {'bucket_text':"Bucket "+i,"index":i};
                   quizquestion.question.buckets.push(options);  
                }
                for ( i= 1; i <= 3; i++) {
                   options = {'text':"","index":i,"bucket":"","image_url":"","bucket_1_text":"Bucket 1","bucket_2_text":"Bucket 2"};
                   quizquestion.question.options.push(options);  
                }
            }
    	};
        /** Match The Following **/
    	quizquestion.buildOptions = function(cola,colb){
    		
    		if(angular.isDefined(cola)&&cola!==''&&angular.isDefined(colb)&&colb!==''){
    			quizquestion.colabblank = false;
	    		var index = quizquestion.dummyQuestion.optionsa.length;
	    		if(index==0){
	    		}else if(index>1){
	    			index = index-1;
	    		}
	    		quizquestion.dummyQuestion.optionsa.push({'col_a':cola,'col_b':colb,'col_b_index':index});
	    		quizquestion.dummyQuestion.optionsb.push({'col_a':cola,'col_b':colb,'col_b_index':index});
	    		//$log.info(quizquestion.question.optionsa);
	    		quizquestion.cola="";
	    		quizquestion.colb="";
    		}else{
    			quizquestion.colabblank = true;
    			quizquestion.errmessage= 'Column A and Column B cannot be blank';
    		}
    	};

    	quizquestion.sortableOptions ={
    		stop: function() {
    			  quizquestion.dummyQuestion.optionsdummy = [];	
			      // this callback has the changed model
			      quizquestion.dummyQuestion.optionsb.map(function(i,key){
			      	//$log.info(i,key,'ggg');
			      	i.col_b_index=key;
			        quizquestion.dummyQuestion.optionsdummy.push(i);
			      });
			      //$log.info(quizquestion.question.optionsdummy);
		    	}
		};
        /** End of Match The Following **/

		quizquestion.addMatchTheFollowingQuestionToQuiz = function(quiz_type){

            if(quiz_type==6){ // Code For Match The Following

    			$('#questionForm').validate(angular.extend({
                    // Rules for form validation
                    rules: {
                        title: {
                          required: true
                        }
                    },
                    // Messages for form validation
                    messages: {
                        title: {
                            required: "<span style='color:red;font-style:initial;margin-left: 15px;'>Question title can't be blank"
                        },
                    }

                }));

                if($('#questionForm').valid()){
                	quizquestion.question.options = [];
                	if(quizquestion.dummyQuestion.optionsdummy&&quizquestion.dummyQuestion.optionsdummy.length>0){

    	            	angular.forEach(quizquestion.dummyQuestion.optionsa,function(val){
    	            			
    	            		angular.forEach(quizquestion.dummyQuestion.optionsdummy,function(val1){

    	            				if(val.col_a==val1.col_a&&val.col_b==val1.col_b){
    	            					quizquestion.question.options.push({'col_a':val.col_a,'col_b':val.col_b,'col_b_index':val1.col_b_index});
    	            				}
    	            		});
    	            	});	
                	}else{
                		quizquestion.question.options = angular.copy(quizquestion.dummyQuestion.optionsa);
                	}
                	$log.info(quizquestion.question.options,'options');

                	if(angular.isDefined(quizquestion.question.options)&&quizquestion.question.options.length>0){

                		$http({
    		                    method: "POST",
    		                    url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
    		                    timeout:20000,
    		                    data:$.param({'question':quizquestion.question,'id':quizquestion.id,"quiz_type":quizquestion.quiz.quiz_type}),
    		                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
    		            }).success(function (data,status) {
    		            	if(status==200){
                                alertify.closeLogOnClick(true).delay(5000).success(data.message);   

    		            		$("#questionForm")[0].reset();
    		            		quizquestion.resetQuestion(quizquestion.quiz.quiz_type);
                                quizquestion.quiz.json_updated_flag=1;
    		            	}
    		            });

                	}else{
                		quizquestion.colabblank = true;
        				quizquestion.errmessage= 'Question options cannot be empty';
        				return false;
                	}
                }
            }//End of If For Quiz Type 6 => Match The Following
		};

         /** MCQ **/

        quizquestion.resetTheMcqChoices = function(choice_type){
            if(choice_type==1){
                quizquestion.question.option1="";
                quizquestion.question.option2="";
                quizquestion.question.option3="";
                quizquestion.question.option4="";
                $("#option1-error").html('');
                $("#option2-error").html('');
                $("#option3-error").html('');
                $("#option4-error").html('');
            }else if(choice_type==0){
                $("#option1_url").val('');
                $("#option2_url").val('');
                $("#option3_url").val('');
                $("#option4_url").val('');
                 $("#option1_url-error").html('');
                $("#option2_url-error").html('');
                $("#option3_url-error").html('');
                $("#option4_url-error").html('');
                quizquestion.question.option1_image_url="";
                quizquestion.question.option2_image_url="";
                quizquestion.question.option3_image_url="";
                quizquestion.question.option4_image_url="";
            }
        };
        quizquestion.addMcqQuestionToQuiz = function(quiz_type){
            
            $("#question_mcq_form").validate(angular.extend({
                rules: {
                    question: {
                        required: function(){
                            if((($("#question_mcq_form input[name='question_image_url']"))[0].files[0])==undefined){
                                return true;
                            }
                        }
                    },
                    question_image_url:{
                        required: function(){
                            if(quizquestion.question.question==""){
                                return true;
                            }
                        }
                    },
                    answer: {
                        required: true
                    },
                    explanation:{
                        required:true
                    },
                    
                    option1:{
                        required:true
                    },
                    option2:{
                        required:true
                    },
                    option1_url:{
                        required:function(){
                            if(quizquestion.question.choice_type==1&&(angular.isUndefined(quizquestion.question.option1_image_url)||quizquestion.question.option1_image_url=="")){

                                return true;
                            }
                        }
                    },
                    option2_url:{
                        required:function(){
                            if(quizquestion.question.choice_type==1&&(angular.isUndefined(quizquestion.question.option2_image_url)||quizquestion.question.option2_image_url=="")){
                                return true;
                            }
                        }
                    },
                    option3:{
                        required:function(){
                            if(quizquestion.question.option4!==''){
                                return true;
                            }
                        }
                    },
                    option4:{
                        required:function(){
                            if(quizquestion.question.option3!==''){
                                return true;
                            }
                        }
                    },
                    option3_url:{
                        required:function(){
                                if(angular.isDefined((($("#question_mcq_form input[name='option4_url']"))[0].files[0]))||(angular.isDefined(quizquestion.question.option4_image_url)&&quizquestion.question.option4_image_url!=='')){
                                                               
                                    if(angular.isDefined(quizquestion.question.option3_image_url)&&quizquestion.question.option3_image_url!==''){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }else{
                                     return false;
                                }
                            
                        }
                    },
                    option4_url:{
                        required:function(){
                           
                            if(angular.isDefined((($("#question_mcq_form input[name='option3_url']"))[0].files[0]))||(angular.isDefined(quizquestion.question.option3_image_url)&&quizquestion.question.option3_image_url!=='')){
                                if(angular.isDefined(quizquestion.question.option4_image_url)&&quizquestion.question.option4_image_url!==''){
                                    return false;
                                }else{
                                    return true;
                                }
                            }else{
                                return false;
                            }
                            
                        }
                    }
                    
                },
                
                // Specify the validation error messages
            messages: {
                question: {
                    required: " Please provide question"
                },
                question_image_url:{
                        required:  " Choose question image url"
                    },
                answer: {
                    required : "Please select answer"
                },
                explanation:{
                    required : "Please provide explanation"
                },
                option1:{
                    required : "Please provide option 1"
                },
                option2:{
                    required : "Please provide option 2"
                },
                option3:{
                    required : "Please provide option3 "
                },
                option4:{
                    required : "Please provide option4 "
                },
                option1_url:{
                    required : "Choose option1 image"
                },
                option2_url:{
                    required : "Choose option2 image"
                },
                option3_url:{
                    required : "Choose option3 image"
                },
                option4_url:{
                    required : "Choose option4 image"
                }
                
                
            }
            }));
            
            if($("#question_mcq_form").valid()&&quizquestion.question!==''){
                var formData = new FormData();
                if((($("#question_mcq_form input[name='question_image_url']"))[0].files[0])!==undefined){    
                        formData.append("question_image_url", (($("#question_mcq_form input[name='question_image_url']"))[0].files[0]));
                }
                if(quizquestion.question.choice_type==1){
                    if((($("#question_mcq_form input[name='option1_url']"))[0].files[0])!==undefined){
                        formData.append("option1_url", (($("#question_mcq_form input[name='option1_url']"))[0].files[0]));
                    }
                    if((($("#question_mcq_form input[name='option2_url']"))[0].files[0])!==undefined){
                        formData.append("option2_url", (($("#question_mcq_form input[name='option2_url']"))[0].files[0]));
                    }
                    if((($("#question_mcq_form input[name='option3_url']"))[0].files[0])!==undefined){
                        formData.append("option3_url", (($("#question_mcq_form input[name='option3_url']"))[0].files[0]));
                    }
                    if((($("#question_mcq_form input[name='option4_url']"))[0].files[0])!==undefined){
                        formData.append("option4_url", (($("#question_mcq_form input[name='option4_url']"))[0].files[0]));
                    }
                }   
                formData.append("id", quizquestion.id);
                formData.append("quiz_type", quiz_type);
                formData.append("question", JSON.stringify(quizquestion.question));

                $http({
                        method: "POST",
                        url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                        timeout:20000,
                        data:formData,
                        contentType:false,
                        processData: false,
                        headers: {'Content-Type': undefined}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   

                        $("#question_mcq_form")[0].reset();
                        quizquestion.resetQuestion(quizquestion.quiz.quiz_type);
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.question={};
                        quizquestion.question.options = [];
                        quizquestion.question.choice_type ='0';
                    }
                });      
            }
            
        };



        /** End of MCQ **/


        /** Slide up MCQ **/
        
        quizquestion.addSlideupMcqQuestionToQuiz = function (quiz_type){
            $("#question_slideupmcq_form").validate(angular.extend({
                rules: {
                    question: {
                        required: true
                    },
                    answer_line:{
                        required:true
                    },
                    answer: {
                        required: true
                    },
                    explanation:{
                        required:true
                    },
                    
                    option1:{
                        required:true
                    },
                    option2:{
                        required:true
                    },
                    option3:{
                        required:function(){
                            if(quizquestion.question.option4!==''){
                                return true;
                            }
                        }
                    },
                    option4:{
                        required:function(){
                            if(quizquestion.question.option3!==''){
                                return true;
                            }
                        }
                    },
                    
                },
                
                // Specify the validation error messages
            messages: {
                question: {
                    required: "Please provide question"
                },
                answer_line:{
                        required:  "Please provide answer line"
                    },
                answer: {
                    required : "Please select answer"
                },
                explanation:{
                    required : "Please provide explanation"
                },
                option1:{
                    required : "Please provide option 1"
                },
                option2:{
                    required : "Please provide option 2"
                },
                option3:{
                    required : "Please provide option3 "
                },
                option4:{
                    required : "Please provide option4 "
                }
                
                
            }
            }));

            if($("#question_slideupmcq_form").valid()&&quizquestion.question!==""){
                $http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                    timeout:20000,
                    data:$.param({'id':quizquestion.id,'quiz_type':quiz_type,'question':quizquestion.question}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   

                        $("#question_slideupmcq_form")[0].reset();
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.question={};
                        quizquestion.question.options = [];

                    }
                }).error(function(data,status){
                    if(status==403){
                        alertify.closeLogOnClick(true).delay(5000).error(data.message);   

                    }
                });
            }
        };

        /** End Of Slide up MCQ **/


        /** Start Of True False **/

        quizquestion.addTrueFalseQuestionToQuiz = function(){
            $("#question_truefalse_form").validate(angular.extend({
                rules: {
                    question: {
                        required: true
                    },
                    answer: {
                        required: true
                    },
                    explanation:{
                        required:true
                    },
                    image_url:{
                        required: function(){
                            if((angular.isUndefined(quizquestion.question.image_url_text)||quizquestion.question.image_url_text=="")&&(angular.isUndefined(($("#question_truefalse_form input[name='image_url']"))[0].files[0]))) {
                                return true;
                            }
                        }
                    }
                    
                },
                
                // Specify the validation error messages
            messages: {
                question: {
                    required: " Please provide question"
                },
                image_url:{
                        required:  " Choose an image"
                    },
                answer: {
                    required : "Please select answer"
                },
                explanation:{
                    required : "Please provide explanation"
                }
                
            }
            }));

            if($("#question_truefalse_form").valid()&&quizquestion.question!==""){
                var formData = new FormData();
                if((($("#question_truefalse_form input[name='image_url']"))[0].files[0])!==undefined){    
                        formData.append("image_url", (($("#question_truefalse_form input[name='image_url']"))[0].files[0]));
                }
                formData.append("id", quizquestion.id);

                formData.append("quiz_type", quizquestion.quiz.quiz_type);

                formData.append("question", JSON.stringify(quizquestion.question));

                $http({
                        method: "POST",
                        url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                        timeout:20000,
                        data:formData,
                        contentType:false,
                        processData: false,
                        headers: {'Content-Type': undefined}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   

                        $("#question_truefalse_form")[0].reset();
                        quizquestion.resetQuestion(quizquestion.quiz.quiz_type);
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.question={};
                        quizquestion.question.options = [];
                    }
                });  
            }
        };
        /** End Of True False **/

        /** Video Quiz Question **/

        quizquestion.addVideoQuestionToQuiz = function(quiz_type){
            $("#question_videomcq_form").validate(angular.extend({
                rules: {
                    question: {
                        required: true
                    },
                    answer: {
                        required: true
                    },
                    explanation:{
                        required:true
                    },
                    option1:{
                        required:function(){
                            if(quizquestion.question.questionType==1){
                                return true;
                            }
                        }
                    },
                    option2:{
                        required:function(){
                            if(quizquestion.question.questionType==1){
                                return true;
                            }
                        }
                    },
                    option1_url:{
                        required:function(){
                            if(quizquestion.question.questionType==2&&(angular.isUndefined(quizquestion.question.option1_image_url)||quizquestion.question.option1_image_url=="")){

                                return true;
                            }
                        }
                    },
                    option2_url:{
                        required:function(){
                            if(quizquestion.question.questionType==2&&(angular.isUndefined(quizquestion.question.option2_image_url)||quizquestion.question.option2_image_url=="")){
                                return true;
                            }
                        }
                    },
                    option3:{
                        required:function(){
                            if(quizquestion.question.questionType==1&&angular.isDefined(quizquestion.question.option4)&&quizquestion.question.option4!==''){
                                return true;
                            }
                        }
                    },
                    option4:{
                        required:function(){
                            if(quizquestion.question.questionType==1&&angular.isDefined(quizquestion.question.option3)&&quizquestion.question.option3!==''){
                                return true;
                            }
                        }
                    },
                    option3_url:{
                        required:function(){

                                if(angular.isDefined(quizquestion.question.option3_text)&&quizquestion.question.option3_text!==''){
                                    return true;
                                }else{

                                    if(angular.isDefined((($("#question_videomcq_form input[name='option4_url']"))[0].files[0]))||(angular.isDefined(quizquestion.question.option4_image_url)&&quizquestion.question.option4_image_url!=='')){
                                                                   
                                        if(angular.isDefined(quizquestion.question.option3_image_url)&&quizquestion.question.option3_image_url!==''){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }else{
                                         return false;
                                    }
                                }
                            
                        }
                    },
                    option4_url:{
                        required:function(){
                            if(angular.isDefined(quizquestion.question.option4_text)&&quizquestion.question.option4_text!==''){
                                    return true;
                            }else{
                                if(angular.isDefined((($("#question_videomcq_form input[name='option3_url']"))[0].files[0]))||(angular.isDefined(quizquestion.question.option3_image_url)&&quizquestion.question.option3_image_url!=='')){
                                    if(angular.isDefined(quizquestion.question.option4_image_url)&&quizquestion.question.option4_image_url!==''){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }else{
                                    return false;
                                }
                            }
                            
                        }
                    }
                    
                },
                
                // Specify the validation error messages
            messages: {
                question: {
                    required: " Please provide question"
                },
                answer: {
                    required : "Please select answer"
                },
                explanation:{
                    required : "Please provide explanation"
                },
                option1:{
                    required : "Please provide option 1"
                },
                option2:{
                    required : "Please provide option 2"
                },
                option3:{
                    required : "Please provide option3 "
                },
                option4:{
                    required : "Please provide option4 "
                },
                option1_url:{
                    required : "Choose option1 image"
                },
                option2_url:{
                    required : "Choose option2 image"
                },
                option3_url:{
                    required : "Choose option3 image"
                },
                option4_url:{
                    required : "Choose option4 image"
                }
                
                
            }
            }));
            if($("#question_videomcq_form").valid()&&quizquestion.question!==''){
                var formData = new FormData();
               
                if(quizquestion.question.questionType==2){
                    if((($("#question_videomcq_form input[name='option1_url']"))[0].files[0])!==undefined){
                        formData.append("option1_url", (($("#question_videomcq_form input[name='option1_url']"))[0].files[0]));
                    }
                    if((($("#question_videomcq_form input[name='option2_url']"))[0].files[0])!==undefined){
                        formData.append("option2_url", (($("#question_videomcq_form input[name='option2_url']"))[0].files[0]));
                    }
                    if((($("#question_videomcq_form input[name='option3_url']"))[0].files[0])!==undefined){
                        formData.append("option3_url", (($("#question_videomcq_form input[name='option3_url']"))[0].files[0]));
                    }
                    if((($("#question_videomcq_form input[name='option4_url']"))[0].files[0])!==undefined){
                        formData.append("option4_url", (($("#question_videomcq_form input[name='option4_url']"))[0].files[0]));
                    }
                }   
                formData.append("id", quizquestion.id);
                formData.append("quiz_type",quiz_type);
                formData.append("question", JSON.stringify(quizquestion.question));

                $http({
                        method: "POST",
                        url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                        timeout:20000,
                        data:formData,
                        contentType:false,
                        processData: false,
                        headers: {'Content-Type': undefined}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   

                        $("#question_videomcq_form")[0].reset();
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.question={};
                        quizquestion.question.options = [];
                        quizquestion.question.questionType ='1';
                    }
                });  
            }
        };


        quizquestion.addPick5QuestionToQuiz = function(quiz_type){
            /*$('input[class="options"]').each(function() {
                console.log($(this).val(),'ffd')
                $(this).rules('add', {
                    required: true,  // example rule
                    // another rule, etc.
                });
            });*/
            /*angular.forEach(quizquestion.question.options,function(value,key){
                $("#option"+value.index).rules('add', {
                    required: true
                }); 
            })*/
            $('#question_pick5_form').validate({
              rules: {
                question: {
                  required: true
                },
                explanation:{
                    required:true
                }
              },
              messages:{
                question:{
                    required:"<span> Please provide question "
                },
                explanation:{
                    required:"<span> Please provide explanation"
                }
              }
            });
            for (i = 0; i < quizquestion.question.options.length; i++) {
                $("#option"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                $("#answer"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                }); 
            }
            if($("#question_pick5_form").valid()&&quizquestion.question!==''){
                $http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                    timeout:20000,
                    data:$.param({'id':quizquestion.id,'quiz_type':quiz_type,'question':quizquestion.question}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   

                        $log.info(data);
                        $("#question_pick5_form")[0].reset();
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.resetQuestion(quizquestion.quiz.quiz_type);

                    }
                }).error(function(data,status){
                    if(status==403){
                        alertify.closeLogOnClick(true).delay(5000).error(data.message);   
                    }
                });
            }
        };


        /** End of Video Quiz Question **/


        /** Line up quiz question **/

        quizquestion.addLineupQuestionToQuiz = function(quiz_type){
            $('#question_lineupmcq_form').validate({
              rules: {
                question: {
                  required: true
                },
                lower_text:{
                    required:true
                },
                higher_text:{
                    required:true
                },
                time:{
                    required:true,
                    number:true
                }
              },
              messages:{
                question:{
                    required:"<span> Please provide question "
                },
                lower_text:{
                    required:"<span> Please provide lower text "
                },
                higher_text:{
                   required:"<span> Please provide higher text " 
                },
                time:{
                   required:"<span> Please provide time " 

                }
              }
            });
            for ( i = 0; i < quizquestion.question.options.length; i++) {
                $("#option"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                $("#preadd"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                }); 
            }
            if($("#question_lineupmcq_form").valid()&&quizquestion.question!==''){
                $http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                    timeout:20000,
                    data:$.param({'id':quizquestion.id,'quiz_type':quiz_type,'question':quizquestion.question}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                }).success(function (data,status) {
                    if(status==200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);   
                        $("#question_lineupmcq_form")[0].reset();
                        quizquestion.quiz.json_updated_flag=1;
                        quizquestion.resetQuestion(quizquestion.quiz.quiz_type);

                    }
                }).error(function(data,status){
                    if(status==403){
                        alertify.closeLogOnClick(true).delay(5000).error(data.message);   
                    }
                });
            }
        };

        quizquestion.addBucketQuestionToQuiz = function(quiz_type){
            $('#question_bucket_form').validate({
              rules: {
                question: {
                  required: true
                }
              },
              messages:{
                question:{
                    required:"<span> Please provide question "
                }
              }
            });
            for ( i = 0; i < quizquestion.question.buckets.length; i++) {
                /*$("#bucket"+quizquestion.question.buckets[i].index).rules('add', {
                    required: true
                });*/
            }
            for (i = 0; i < quizquestion.question.options.length; i++) {
                $("#bucket_1_text"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                $("#bucket_2_text"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                $("#option"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                $("#select"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                }); 
                $("#explanation"+quizquestion.question.options[i].index).rules('add', {
                    required: true
                });
                if(angular.isUndefined(quizquestion.question.options[i].image_url_text)||quizquestion.question.options[i].image_url_text==''){
                     $("#image_url"+quizquestion.question.options[i].index).rules('add', {
                        required: true
                    });
                }
                if(angular.isDefined(quizquestion.question.options[i].image_url_text)&&quizquestion.question.options[i].image_url_text!=''){
                    $("#image_url"+quizquestion.question.options[i].index).rules('add', {
                        required: false
                    });
                    $("#image_url"+quizquestion.question.options[i].index).removeClass('error');
                    $("#image_url"+quizquestion.question.options[i].index+'-error').html('');
                }
            }
            if($("#question_bucket_form").valid()&&quizquestion.question!=''){
                    var formData = new FormData();
                    for (i = 0; i < quizquestion.question.options.length; i++) {
                        if((($("#image_url"+quizquestion.question.options[i].index))[0].files[0])!==undefined){
                            formData.append("image_url"+quizquestion.question.options[i].index, (($("#image_url"+quizquestion.question.options[i].index))[0].files[0]));
                        }
                    }
                    formData.append("id", quizquestion.id);
                    formData.append("quiz_type", quiz_type);
                    formData.append("question", JSON.stringify(quizquestion.question));

                    $http({
                            method: "POST",
                            url: $rootScope.SITE_URL+'Api/v1/setTempQuizQuestion',
                            timeout:40000,
                            data:formData,
                            contentType:false,
                            processData: false,
                            headers: {'Content-Type': undefined}
                    }).success(function (data,status) {
                        if(status==200){
                            alertify.closeLogOnClick(true).delay(5000).success(data.message);   
                            $("#question_bucket_form")[0].reset();
                            quizquestion.quiz.json_updated_flag=1;
                            quizquestion.resetQuestion(quizquestion.quiz.quiz_type);
                        }
                    });

            }
        };

		quizquestion.generateQuizUrl = function(){
			$http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/generateQuizUrl',
                    timeout:20000,
                    data:$.param({'id':quizquestion.id,'quiz_type':quizquestion.quiz.quiz_type}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).success(function (data,status) {
            	if(status==200){
                    alertify.closeLogOnClick(true).delay(5000).success(data.message);   
                    $location.path('quiz');
            		quizquestion.quiz.json_updated_flag=2;
                    alertify.closeLogOnClick(true).delay(5000).success(data.message);	
            	}
            }).error(function(data,status){
            	if(status==403){
                   alertify.closeLogOnClick(true).delay(5000).error(data.message);   

            	}
            });
		};

        quizquestion.showDivs = function(arg){
            if(arg=='view'){
                quizquestion.getQuiz();
                localStorage.setItem('tab',3);
            }
            if(arg=='basic'){
                localStorage.setItem('tab',1);
            }
            if(arg=='add'){
                localStorage.setItem('tab',2);
            }

        };



        quizquestion.updateQuizBasicJsonInfo = function(quiz_type){
            $log.info(quiz_type);
            var params ={};
            if(quiz_type==1){ //MCQ
               params = {'id':quizquestion.quiz.id,'quiz_type':quizquestion.quiz.quiz_type,'answer_result':quizquestion.question.answer_result,'show_explanation':quizquestion.question.show_explanation};
            }
            if(quiz_type==4){ //TRUE FALSE
               params = {'id':quizquestion.quiz.id,'quiz_type':quizquestion.quiz.quiz_type,'question_topic':quizquestion.question.question_topic};
            }
            if(quiz_type==5){ //Video Mcq
               params = {'id':quizquestion.quiz.id,'quiz_type':quizquestion.quiz.quiz_type,'videoUrl':quizquestion.question.videoUrl,'introHeading':quizquestion.question.introHeading,'intro':quizquestion.question.intro};
            }

            if(quiz_type==7){ //Pick 5
               params = {'id':quizquestion.quiz.id,'quiz_type':quizquestion.quiz.quiz_type,'show_explanation':quizquestion.question.show_explanation};
            }
            if(quiz_type==8){ //Bucket
               params = {'id':quizquestion.quiz.id,'quiz_type':quizquestion.quiz.quiz_type,'show_explanation':quizquestion.question.show_explanation};
            } 
            $http({
                method: "POST",
                url: $rootScope.SITE_URL+'Api/v1/updateQuizBasicJsonInfo',
                timeout:20000,
                data:$.param(params),
                headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).success(function (data,status) {
                if(status==200){
                    alertify.closeLogOnClick(true).delay(5000).success(data.message);
                    quizquestion.quiz.json_updated_flag=1;

                }
            }).error(function(data,status){
                if(status==403){
                    alertify.closeLogOnClick(true).delay(5000).error(data.message);
                }
            });
        };

       
        

        var item = localStorage.getItem('tab');
        if(item){
            if(angular.isDefined(item)&&item!=''){
                if(item==1){
                    quizquestion.radioModel='basic';
                }
                if(item==2){
                    quizquestion.radioModel='add';
                }
                if(item==3){
                    quizquestion.radioModel='view';
                }
            }else{
                quizquestion.radioModel='add';
            }
        }
    }
})();