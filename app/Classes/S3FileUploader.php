<?php


use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
class S3FileUploader{
	public function __construct() {
		    	$this->clientArrayForDriver = \Config::get('config.s3_client_factory');

				$this->s3BucketDetails = \Config::get('config.s3');
    }

    public function __destruct() {
    }


    public function generateRandomString($length = 10) {
        $characters = 'abcdefghijklmnopqrstuvwxyz';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return $randomString;
    }


    public function is_url_exist($url)
    {
        /*$ch = curl_init($url);    
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($code == 200){
           $status = true;
        }else{
          $status = false;
        }
        curl_close($ch);*/
        $headers=get_headers($url);
        return stripos($headers[0],"200 OK")?true:false;
    }
    /**
    *@chiranjeevi
    *Function to upload new file to some path with contents;
    */
    public function uploadSourceFileWithContents($filename,$subPath,$contentType,$contents){
    	$result = array();
    	$uploadResult['url'] = '';
    	$uploadResult['error'] = '';
    	$s3 = S3Client::factory(array(
                'version' => $this->clientArrayForDriver['version'],
                'region' => $this->clientArrayForDriver['region'],
                'credentials' => array(
                    'key' => $this->clientArrayForDriver['credentials']['key'],
                        'secret' => $this->clientArrayForDriver['credentials']['secret']
        )));
    		try {
            $result = $s3->putObject(array(
            'Bucket' => $this->s3BucketDetails['bucket'],
            'Key'    => $subPath.$filename,
            'ContentType' => $contentType,
            'Body' => $contents,
            'ACL'    => $this->s3BucketDetails['acl_permission']
                ));
            $uploadResult['url']= $result['ObjectURL'];
        } catch (S3Exception $e) {
            $uploadResult['error'] =$e->getMessage();
        } finally {
            return $uploadResult;
        }
       
    }

    /**
    *
    *Uploading Image Files
    */
    public function uploadFile($file,$subPath) {
        $uploadResult['url'] = '';
        $uploadResult['error'] = '';
        if ($file['name'] !== '' && isset($file['tmp_name']) && $file['error'] === UPLOAD_ERR_OK) {
            $extensionArray = ['jpg', 'jpeg', 'png'];
            $contentTypeArray = ['image/jpg', 'image/jpeg', 'image/png'];
            $randomStr = $this->generateRandomString(5);
            $fileName = $randomStr.$file['name'];
            $fileName = str_replace(" ", "_", $fileName);
            $extension = substr($fileName, strpos($fileName, '.') + 1);

            if (in_array(strtolower($extension), $extensionArray)) {
                $contentType = $contentTypeArray[array_search(strtolower($extension), $extensionArray)];
                $tmpPath = $file['tmp_name'];
                $fileContent = file_get_contents($tmpPath);
                $s3 = S3Client::factory(array(
                'version' => $this->clientArrayForDriver['version'],
                'region' => $this->clientArrayForDriver['region'],
                'credentials' => array(
                    'key' => $this->clientArrayForDriver['credentials']['key'],
                        'secret' => $this->clientArrayForDriver['credentials']['secret']
                )));
                try {
                    $result = $s3->putObject(array(
                        'Bucket' => $this->s3BucketDetails['bucket'],
                        'Key'    => $subPath.$fileName,
                        'ContentType' => $contentType,
                        'Body' => $fileContent,
                        'ACL'    => $this->s3BucketDetails['acl_permission']
                    ));
                    $uploadResult['url'] = $result['ObjectURL'];
                } catch (S3Exception $e) {
                    $uploadResult['error'] = $e->getMessage();
                }
            } else {
                $uploadResult['error'] = 'Only jpeg, jpg and png files are allowed';
            }
        } else {
            $uploadResult['error'] = 'File not found';
        }
        return $uploadResult;
    }



    /**
    *
    *Uploading Image Files
    */
    public function generateAndUploadFile($url,$fileName,$extension) {

        $uploadResult['url'] = '';
        $uploadResult['error'] = '';
        if ($fileName!=''&&$url!='') {
            $extensionArray = ['jpg', 'jpeg', 'png'];
            $contentTypeArray = ['image/jpg', 'image/jpeg', 'image/png'];
            $randomStr = $this->generateRandomString(5);
            $fileName = $randomStr.$fileName;

            if (in_array(strtolower($extension), $extensionArray)) {
                $contentType = $contentTypeArray[array_search(strtolower($extension), $extensionArray)];
                $fileContent = file_get_contents($url);

                $s3 = S3Client::factory(array(
                'version' => $this->clientArrayForDriver['version'],
                'region' => $this->clientArrayForDriver['region'],
                'credentials' => array(
                    'key' => $this->clientArrayForDriver['credentials']['key'],
                        'secret' => $this->clientArrayForDriver['credentials']['secret']
                )));
                try {
                    $result = $s3->putObject(array(
                        'Bucket' => $this->s3BucketDetails['bucket'],
                        'Key'    => $subPath.$fileName,
                        'ContentType' => $contentType,
                        'Body' => $fileContent,
                        'ACL'    => $this->s3BucketDetails['acl_permission']
                    ));
                    $uploadResult['url'] = $result['ObjectURL'];
                } catch (S3Exception $e) {
                    $uploadResult['error'] = $e->getMessage();
                }
            } else {
                $uploadResult['error'] = 'Only jpeg, jpg and png files are allowed';
            }
        } else {
            $uploadResult['error'] = 'File not found';
        }
        return $uploadResult;
    }
}
