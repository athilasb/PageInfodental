
/*

foreach($list_pacientes as $linha){
    
    if($linha['nome'] == ""){
        continue;
    }

    //pesquisando por nomes na lista de pacientes do bacno
    $index = strtolowerWLIB(str_replace(" ", "", tirarAcentos(utf8_encode($linha['nome']))));

    echo("<br> =============================================================<br>");
    echo $linha['nome']."::::::::::::::::::<br>";
    print_r($list_arquivos[$index]);
    echo("<br>-------------------------------------------------------------<br>");
    print_r($linha);
    echo("<br> +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++");

   
    $list_arquivos[$index]['id'] = $max_id;

    $vSQL .= "( '". $max_id ."', 
                    now(), '" . 
                    $linha['id'] . "', '" . 
                    $list_arquivos[$index]['tx_tipo_arquivo'] . "', '" . 
                    $list_arquivos[$index]['tx_nome_arquivo'] . "', '" .
                    $list_arquivos[$index]['extensao'] . "', '" . 
                // verificar se esse é o id do profissional    "'101', ".
                    "'',".
                    "'0')";

  /*  
   try {
        $s3->putObject(array(
            'Bucket'=>'infodental',
            'Key' =>  $infoConta->instancia."/".$_dir.$nomeCompleto,
            'SourceFile' => $_FILES['file']['tmp_name'],
            'ACL'    => 'public-read', //for public access
        ));

        // se for foto, gera thumbnails
        if($isImage) {
            $imgThumb = "arqs/tmp/thumb-".$infoConta->instancia.".".$extensao;

            $canvas->carrega($_FILES['file']['tmp_name'])
                    ->redimensiona("100", "100", 'crop')
                    ->hexa( '#FFFFFF' )
                    ->grava($imgThumb);

                $s3->putObject(array(
                                'Bucket'=>'infodental',
                                'Key' =>  $infoConta->instancia."/".$_dir."thumb/".$nomeCompleto,
                                'SourceFile' => $imgThumb,
                                'ACL'    => 'public-read', //for public access
                            ));
        } 

    } catch (S3Exception $e) {
        //code when fails
        $erro='Algum erro ocorreu durante o envio do arquivo. Tente novamente ou entre em contato com nosso suporte!';
    }
    */
    
//}

//$vSQL = str_replace($vSQL, 0, -1);
