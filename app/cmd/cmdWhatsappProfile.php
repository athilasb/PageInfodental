<?php
	require_once("../lib/conf.php");
	require_once("../lib/classes.php");
	echo strtotime(date('Y-m-d H:i:s'));die();
	$sql = new Mysql();
	$usr = (object) array('id'=>1);

	$attr=array('prefixo'=>$_p,'usr'=>$usr);
	$wts = new Whatsapp($attr);

	$attr=array('instance'=>'556282433773',
				//'numero'=>'62982400606',
				'numero'=>'62999181775'
			);
	if($wts->getProfile($attr)) {
		var_dump($wts->response);

	} else {
		echo "Erro: ".$wts->erro;
	}


?>curl https://api.cloudinary.com/v1_1/infodental/image/upload -X POST --data 'file=https://pps.whatsapp.net/v/t61.24694-24/187564315_470837964172350_9212215705648331116_n.jpg?ccb=11-4&oh=01_AVyzgl1H447th0MOuuJ3wD976eB2j3HqmN_wLERmFMX_uQ&oe=62F22CEC&timestamp=1659019733&public_id=sample&api_key=589795168263967&signature=a8a44ac62a6f5dc5397c1d8af1a848c82b7fa617'   

file=https://pps.whatsapp.net/v/t61.24694-24/187564315_470837964172350_9212215705648331116_n.jpg?ccb=11-4&oh=01_AVyzgl1H447th0MOuuJ3wD976eB2j3HqmN_wLERmFMX_uQ&oe=62F22CEC&

public_id=sample&timestamp=1659019733ir9b4eem

api_key=589795168263967

&signature=6225c37f4c388458eaefc3353f9dc91615e66384