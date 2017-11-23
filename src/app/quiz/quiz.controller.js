(function() {
    'use strict';
    
    angular
        .module('smartskillApp')
        .controller('QuizController', QuizController);

    /** @ngInject */
    function QuizController($log,$http,$rootScope,$timeout,alertify) {
    	$log.info('quiz controller');
        alertify.logPosition("top right").maxLogItems(1);
    	var quiz = this;
    	quiz.single={};
    	quiz.startIndex = 0;
    	quiz.limitIndex = 10;
    	quiz.noOfPages	= 0;
    	quiz.parseInt = parseInt;
    	quiz.inPopup = false;
    	quiz.searchString = "";
        quiz.deleteButton=0;
        quiz.showLoadingBarGif = true;
    	quiz.resetSorting=function(tableId)
        {
            $('#'+tableId+' th.enableSort').removeClass('sorting_asc');
            $('#'+tableId+' th.enableSort').removeClass('sorting_desc');
            $('#'+tableId+' th.enableSort').addClass('sorting');
        };

        quiz.getAllQuiz = function(){
            quiz.showLoadingBarGif = true;
    		var startIndex=quiz.limitIndex*quiz.startIndex;
        	var limitIndex=quiz.limitIndex;
        	var sortColumn='';
	        var sortOrder='asc';
	        var searchColumns = [];
          	if(quiz.searchString&&quiz.searchString!=='')
          	{
              $('#quizTable th').each(function()
              {
                  if($(this).attr('search-index')){
                      searchColumns.push($(this).attr('search-index'));
                  }
              });
          	}
	        if($('#quizTable th.sorting_asc').length>0)
	        {
	            sortColumn=$('#quizTable th.sorting_asc').first().attr('sort-index');                      
	        }
	        else
	        {
	            if($('#quizTable th.sorting_desc').length>0)
	              {
	                  sortColumn=$('#quizTable th.sorting_desc').first().attr('sort-index');                      
	                  sortOrder='desc';
	              }
	        } 


        	var params = {'startIndex':startIndex,'limitIndex':limitIndex,'sortOrder':sortOrder,'sortColumn':sortColumn,'searchColumns':searchColumns,'searchString':quiz.searchString};

			$http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/getAllQuizs',
                    timeout:20000,
                    data:$.param(params),//posting data from login form
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).success(function (data,status) {

            	if(status===200){
                    quiz.deleteButton=0;
                    quiz.showLoadingBarGif = false;
            		quiz.quizs = data.quiz.quizs;
            		
            		if(!quiz.sortingAdded)
                    {
                        quiz.sortingAdded=true;
            		 	$timeout(function(){
                      	$('#quizTable th.enableSort').on('click', function () {
                          	var currentClass=$(this).prop('class').split(/\s+/);
                          	//console.log(currentClass);
                          	quiz.resetSorting('quizTable');
                          	if($.inArray('sorting_asc',currentClass)>=0)
                          	{
                                $(this).addClass('sorting_desc');
                                $(this).removeClass('sorting_asc');
                                quiz.getAllQuiz();    
                          	}
                          	else
                            {
                                $(this).addClass('sorting_asc');
                                $(this).removeClass('sorting_desc');                                              
                                quiz.getAllQuiz();    
                            }          
                        });
                      

                    	},10);
            		}
            		quiz.quiz_count = data.quiz.quiz_count;
            		quiz.noOfPages=Math.ceil(quiz.quiz_count/quiz.limitIndex);

            	}

          	}).error(function(){

          	});
    	};

        quiz.searchQuiz = function(){
        	quiz.startIndex = 0;
        	quiz.limitIndex = 10;
        	quiz.getAllQuiz();
        };
    	


    	/** Pagination stuffs starts**/
    	

    	quiz.changeLimitIndex = function(){
    		quiz.startIndex = 0;
    		quiz.getAllQuiz();
    	};

    	quiz.itemsPaginated = function () {
	      if(quiz.noOfPages!==''&&quiz.noOfPages>0)
	      {
	      	  var noOfPages;
	          if(quiz.noOfPages>10)
	          {
	          var currentPageIndex = quiz.startIndex+1;
	          if(currentPageIndex>=5){
	              currentPageIndex=currentPageIndex-5;
	          }
	          else{
	              currentPageIndex=0;
	          }
	           noOfPages = new Array(quiz.noOfPages).join().split(',')
	                              .map(function(item, index){ return ++index;});
	          return noOfPages.slice(
	              currentPageIndex, 
	              currentPageIndex + 10);
	          }
	          else
	          {
	              noOfPages = new Array(quiz.noOfPages).join().split(',')
	                              .map(function(item, index){ return ++index;});
	              return noOfPages;
	          }
	      }
	  	};

	  	quiz.setPage = function(pageNo) {
		    quiz.startIndex=pageNo;
		    quiz.getAllQuiz();
		          
		};

		quiz.getAllQuiz();

	  	/** Pagination Stuffs ends **/

	  	/** Quiz Types List **/
	  	quiz.getQuizTypes = function(){
	  		$http({
                    method: "GET",
                    url: $rootScope.SITE_URL+'Api/v1/getQuizTypes',
                    timeout:20000,
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
            }).success(function (data,status) {
            	if(status===200){
            		quiz.quiztypes = data;
            	}
            });
	  	};

	  	quiz.getQuizTypes(); 


	  	quiz.setQuiz = function(quizObj){

	  		$('#quizForm').validate(angular.extend({
                // Rules for form validation
                rules: {
                    quiz_name: {
                      required: true
                    },
                    quiz_type: {
                      required: true,
                    }
                },
                // Messages for form validation
                messages: {
                    quiz_name: {
                        required: "<span style='color:red;font-style:initial;margin-left: 15px;'>Name can't be blank</span>"
                    },
                    quiz_type: {
                      required: "<span style='color:red;font-style:initial;margin-left: 15px;'>Choose quiz type</span>"
                    }
                }

            }, $rootScope.validateOptions));
	  		if($('#quizForm').valid()&&quizObj&&quizObj!==''){

	  			$http({
                    method: "POST",
                    url: $rootScope.SITE_URL+'Api/v1/setQuiz',
                    timeout: 20000,
                    data: $.param(quizObj),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
	            }).success(function (data,status) {
	            	if(status===200){
                        alertify.closeLogOnClick(true).delay(5000).success(data.message);
	            		$("#myModal").modal('toggle');
	            		quiz.startIndex=0;
	            		quiz.limitIndex=10;
	            		quiz.getAllQuiz();
	            	}
	            }).error(function(data,status){
	            	if(status===403){
                        alertify.closeLogOnClick(true).delay(5000).error(data.message);
	            	}
	            });
	  		
	  		}
	  		
	  	};


        

        quiz.checkUncheckAll =function(){
            for (var i = 0; i < quiz.quizs.length; i++) {
                quiz.quizs[i].selected = quiz.isAllChecked;
                if(quiz.isAllChecked==true){
                    quiz.deleteButton = 1;
                }else{
                    quiz.deleteButton = 0;
                }
            }
        };
        var i;
        quiz.checkUncheckHeader = function () {

                quiz.isAllChecked = true;
                for (i = 0; i < quiz.quizs.length; i++) {
                    if (!quiz.quizs[i].selected) {
                        quiz.isAllChecked = false;
                        quiz.deleteButton = 0;
                        break;
                    }
                }
                for ( i= 0; i < quiz.quizs.length; i++) {
                    if (quiz.quizs[i].selected) {
                        quiz.deleteButton = 1;
                        break;
                    }
                }
        };

        quiz.deleteQuizs = function(){
            quiz.selectedIds=[];
            if(quiz.quizs.length>0){
                for (var i = 0; i < quiz.quizs.length; i++) {
                    if (quiz.quizs[i].selected) {
                        quiz.selectedIds.push(quiz.quizs[i].id);
                    }
                }
            }

            alertify.confirm("Are you sure want to delete the selected "+quiz.selectedIds.length+" quiz(s)?", function () {
                // user clicked "ok"
                 $http({
                    method: "DELETE",
                    url: $rootScope.SITE_URL+'Api/v1/deleteQuiz',
                    timeout: 20000,
                    data: $.param({'ids':quiz.selectedIds}),
                    headers: {'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8'}
                }).success(function (data,status) {
                        if(status===200){
                            alertify.closeLogOnClick(true).delay(5000).success(data.message);
                            quiz.startIndex=0;
                            quiz.limitIndex=10;
                            quiz.getAllQuiz();
                        }
                }).error(function(data,status){
                        if(status==403){
                            alertify.closeLogOnClick(true).delay(5000).error(data.message);
                        }
                });
            }, function() {
                // user clicked "cancel"
            });
             
           
        };

        quiz.setTabIndex = function(){
            localStorage.setItem('tab',1);
        };
        
    }
})();