<?php
	

	$dir="../";
	require_once '../lib/conf.php';	
	require_once '../lib/classes.php';	

// Require the Composer autoloader.
	require_once '../vendor/autoload.php';
	use Aws\S3\S3Client;

	$s3 = new S3Client([
	    'version' => 'latest',
	    'endpoint' => $_scalewayS3endpoint,
	    'region'  => $_scalewayS3Region,
	    'credentials' => [
	    	'key' => $_scalewayAccessKey,
	    	'secret' => $_scalewaySecretKey
	    ],
	     'bucket_endpoint' => true
	]);





	if(isset($_FILES['arq'])) {


		try {
		    $s3->putObject(array(
		        'Bucket'=>'infodental',
		        'Key' =>  'teste2.jpg',
		        'SourceFile' => $_FILES['arq']['tmp_name'],
		        'ACL'    => 'public-read', //for public access
		    ));
		} catch (S3Exception $e) {
		    //code when fails
		    var_dump($e);
		}

		die();

	//	echo $_scalewayBucket;die();


		// S3 Scaleway
		$_scalewayBucket='infodental';

		$_scalewayAccessKey='SCWN5R94E7FX32B0WBR1';
		$_scalewaySecretKey='b3b09dd4-29f8-449c-a2d9-1e53ee38510a';

		$_scalewayAccessKey='SCWVHD9GZMJ4HPQANBX3';
		$_scalewaySecretKey='b0375821-d985-4def-9789-7bbb08d10dac';


		$_scalewayAccessKey='SCWDR65BKHXGZEB1ASFE';
		$_scalewaySecretKey='9c4109ee-00ec-425c-91ee-42ea595c124f';

		$_scalewayS3endpoint = "infodentalteste.s3.fr-par.scw.cloud";
		$_scalewayS3Region = "fr-par";
		$scalewayS3 = new S3($_scalewayAccessKey, $_scalewaySecretKey, false, $_scalewayS3endpoint, $_scalewayS3Region);


		var_dump($scalewayS3->listBuckets());die();

		$uploaded=$scalewayS3->putObject(S3::inputFile($_FILES['arq']['tmp_name'],true),"infodentalteste","arqs/".$_FILES['arq']['name'],S3::ACL_PUBLIC_READ);

		var_dump($uploaded);
	}

?>

<form method="post" enctype="multipart/form-data">
	
	<input type="file" name="arq" />
	<button>enviar</button>
</form>