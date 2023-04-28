
//pegando profissionais; Profissionais já existem no sistema
//existem 3 profissionais no sistema, Igor, Agelica, Matheus, Priscilla e José. Esses três ultimos não estão no banco de dados da clinica, então são colaboradores lixo
//deixar todos como lixo e mudar o atributo manualmente no banco de dados.
/*
    foreach ($prof as $linha) {
        list(
            $i_profissional,
            $celular,
            $cidade,
            $cpf,
            $cro,
            $email,
            $endereco,
            $nome,
            $rg,
            $sexo,
            $telefone,
            $uf
        ) = explode(",", $linha);

        if (strlen($nome) < 3)
            continue;

        $index = $i_profissional;
        $_profissionais[$index] = array(
            'id' => $i_profissional,
            'data' => 'now()',
            'lixo' => 1,
            'lixo_data' => '',
            'nome' => trim($nome),
            'sexo' => $sexo,
            'cpf' => $cpf,
            'rg' => $rg,
            'rg_uf' => $uf,
            'telefone1' => $celular,
            'telefone2' => $telefone,
            'email' => $email,
            'endereco' => $endereco,
            'cidade' => $cidade,
            'cro' => $cro,
        );
    }
*/



/*
//pegando cadeiras do sistema
$cadeirasSistema = array();
$sql->consult($_p . "parametros_cadeiras", "*", "where lixo <> 1");
$x = $sql->rows;
while ($x > 0) {
    $cadeirasSistema[$x] = mysqli_fetch_object($sql->mysqry);
    $x--;
}*/