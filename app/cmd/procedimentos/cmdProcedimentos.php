<?php
	
	require_once("../../lib/conf.php");
	require_once("../../lib/classes.php");

	$sql = new Mysql();
	$usr = (object) array('id'=>1);


	$arq = file("procedimentos.csv");


	$_categorias=[];

	$sql->consult($_p."parametros_especialidades","*","");
	while($x=mysqli_fetch_object($sql->mysqry)) {
		$_categorias[utf8_encode($x->titulo)]=$x->id;
	}
	$id=1000;
	foreach($arq as $v) {
		list($procedimento,$categoria,$regiao,$quantativo,$plano,$valor,$obs,$desenho)=explode(";",$v);

		if($categoria=="Divisão") continue;	
		$quantativo=trim($quantativo);

		if(isset($_categorias[$categoria])) {
			$id_categoria=$_categorias[$categoria];

			$id_regiao = 0;
			if($regiao=="Geral") $id_regiao=1;
			else if($regiao=="Por Dente") $id_regiao=4;
			else if($regiao=="Por Quadrante") $id_regiao=3;
			else if($regiao=="Por Arcada") $id_regiao=2;
			else if($regiao=="HOF") $id_regiao=5;
			//echo $quantativo."<BR>";
			$quantativosql=$facesql=0;
			if($quantativo=="Face") { 
				$quantativosql=0;
				$facesql=1;
			} else if($quantativo=="Quantitativo") { 
				$quantativosql=1;
				$facesql=0;
			}

			$vSQL="id=$id,
					data=now(),
					titulo='".utf8_decode($procedimento)."',
					id_especialidade=$id_categoria,
					id_regiao=$id_regiao,
					face=$facesql,
					quantitativo=$quantativosql";
			//$sql->add($_p."parametros_procedimentos",$vSQL);

			$id++;

		}

	}


	


?>