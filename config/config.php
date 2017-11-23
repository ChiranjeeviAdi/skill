<?php
return [

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_KEY'),
            'secret' => env('AWS_SECRET'),
            'region' => env('AWS_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'acl_permission'=>env('AWS_ACL_PERMISSION'),
        ],
        's3_client_factory' => [
        	'version' => 'latest',
        	'region'  => env('AWS_REGION'),
        	'credentials' => ['key'=>env('AWS_KEY'),'secret'=>env('AWS_SECRET')],
        ],
        'QUIZ_IMAGES_S3_PREFIX_PATH'=>'quiz/images/'
];