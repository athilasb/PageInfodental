<?php
  $title="";
  include "includes/header.php";
  include "includes/nav.php";

  if($usr->tipo!="admin" and !in_array("parametros",$_usuariosPermissoes)) {
    $jsc->jAlert("Você não tem permissão para acessar esta área!","erro","document.location.href='dashboard.php'");
    die();
  }

  $values=$adm->get($_GET);

  $_table=$_p."produtos";
  $_page=basename($_SERVER['PHP_SELF']);

  $_pacientes=array();
  $sql->consult($_p."pacientes","*","where lixo=0 order by nome");
  while($x=mysqli_fetch_object($sql->mysqry)) {
    $_pacientes[]=array('id'=>$x->id,'nome'=>utf8_encode($x->nome));
  }

  $_especialidades=array();
  $sql->consult($_p."parametros_especialidades","*","where lixo=0 order by titulo asc");
  while($x=mysqli_fetch_object($sql->mysqry)) $_especialidades[$x->id]=$x;

  $_produtosMarcas=array();
  $sql->consult($_p."produtos_marcas","*","where lixo=0 order by titulo asc");
  while($x=mysqli_fetch_object($sql->mysqry)) $_produtosMarcas[$x->id]=$x;
  
  $_unidadesMedidas=array();
  $sql->consult($_p."produtos_unidadesmedidas","*","where lixo=0 order by titulo asc");
  while($x=mysqli_fetch_object($sql->mysqry)) $_unidadesMedidas[$x->id]=$x;

?>

<section class="content">

  <?php
  require_once("includes/abaConfiguracao.php");
  ?>

  
  <?php
  if(isset($_GET['form'])) {
    $cnt='';
    $campos=explode(",","titulo,id_especialidade,unidade_medida,embalagem,id_marca");
    
    foreach($campos as $v) $values[$v]='';

    
    $variacoesJSON=array();
    if(isset($_GET['edita']) and is_numeric($_GET['edita'])) {
      $sql->consult($_table,"*","where id='".$_GET['edita']."'");
      if($sql->rows) {
        $cnt=mysqli_fetch_object($sql->mysqry);
        
        $values=$adm->values($campos,$cnt);

        $sql->consult($_p."produtos_variacoes","*","where id_produto=$cnt->id and lixo=0");
        while($x=mysqli_fetch_object($sql->mysqry)) {
          $variacoesJSON[]=array('id_variacao'=>$x->id,
                      'titulo'=>utf8_encode($x->titulo),
                      'estoqueMin'=>$x->estoqueMin,
                      'referencia'=>$x->referencia);
        }
      } else {
        $jsc->jAlert("Informação não encontrada!","erro","document.location.href='".$_page."'");
        die();
      }
    }



    if(isset($_POST['acao']) and $_POST['acao']=="wlib") {
      var_dump($_POST);die();
      $vSQL=$adm->vSQL($campos,$_POST);
      $values=$adm->values;
      if(is_object($cnt)) {
        $vSQL=substr($vSQL,0,strlen($vSQL)-1);
        $vWHERE="where id='".$cnt->id."'";
        $sql->update($_table,$vSQL,$vWHERE);
        $sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$cnt->id."'");
        $id_reg=$cnt->id;
      } else {
        $vSQL=substr($vSQL,0,strlen($vSQL)-1);
        //echo $vSQL;die();
        $sql->add($_table,$vSQL);
        $id_reg=$sql->ulid;
        $sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='insert',vsql='".addslashes($vSQL)."',tabela='".$_table."',id_reg='".$sql->ulid."'");


        $id_procedimento=$id_reg;
        
      }

      $msgErro='';
      if(!empty($msgErro)) {
        $jsc->jAlert($msgErro,"erro","");
      } else {


        if(isset($_POST['variacoes'])) {
          $variacoes=json_decode($_POST['variacoes']);
          foreach($variacoes as $v) {
            $vSQL="id_produto=$id_reg,
                titulo='".utf8_decode($v->titulo)."',
                estoqueMin='".$v->estoqueMin."',
                referencia='".$v->referencia."'";

            $variacao='';
            if(isset($v->id_variacao) and is_numeric($v->id_variacao)) { 
              $sql->consult($_p."produtos_variacoes","*","where id='".$v->id_variacao."'");

              if($sql->rows) $variacao=mysqli_fetch_object($sql->mysqry);
            }

            if(is_object($variacao)) {
              $sql->update($_p."produtos_variacoes",$vSQL,"where id=$variacao->id");
            } else {
              $sql->add($_p."produtos_variacoes",$vSQL);
            }
          }
        }

        $jsc->jAlert("Informações salvas com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
        die();
      }
      
    }
  ?>

  <section class="grid">
    <div class="box">
      <form method="post" class="form formulario-validacao"  autocomplete="off" enctype="multipart/form-data">
        <input type="hidden" name="acao" value="wlib" />

        <div class="filter">
          <div class="filter-group">
            <div class="filter-button">
              <a href="<?php echo $_page;?>"><i class="iconify" data-icon="bx-bx-left-arrow-alt"></i></a>
            </div>
          </div>
          <div class="filter-group filter-group_right">
            <div class="filter-button">
              <?php if(is_object($cnt)){?><a href="<?php echo $_page;?>?deleta=<?php echo $cnt->id."&".$url;?>" class="js-deletar"><i class="iconify" data-icon="bx-bx-trash"></i></a><?php }?>
              <a href="javascript:window.print();"><i class="iconify" data-icon="bx-bx-printer"></i></a>
              <a href="javascript:;" class="azul btn-submit"><i class="iconify" data-icon="bx-bx-check"></i><span>salvar</span></a>
            </div>
          </div>
        </div>

        <fieldset>
          <legend><span class="badge">1</span> Dados do Produto</legend>
          <style>
            .easy-autocomplete {
              width: 500px;
            }
          </style>
          <input type="hidden" name="id_paciente" value="0" />
          <div class="colunas4">
            <dl class="dl2">
              <dt>Nome do Produto</dt>
              <dd><input type="text" name="titulo" value="<?php echo $values['titulo'];?>" class="obg js-input-paciente" /></dd>
            </dl>
          </div>
        
        </fieldset>

        <script type="text/javascript">
          var variacoes = JSON.parse(`<?php echo json_encode($variacoesJSON);?>`);

          const variacoesLista = () => {

            $('.js-variacoes-lista .js-item').remove();
            let nomeProduto = $('input[name=titulo]').val();
            let marcaProduto = $('select[name=id_marca] option:selected').text();
            variacoes.forEach(x=> {
              $('.js-variacoes-lista').append(`<a href="javascript:;" class="reg-group js-item">
                                <div class="reg-color" style=""></div
                                >
                              
                                <div class="reg-data" style="">
                                  <h1>
                                    ${nomeProduto}
                                  </h1>
                                  <p>${marcaProduto}</p>
                                </div>

                                <div class="reg-data" style="">
                                  <h1>
                                    ${x.titulo}
                                  </h1>
                                  <p>
                                  Estoque Mínimo: ${x.estoqueMin}
                                  </p>
                                </div>
                                <div class="reg-data" style="flex:0 1 150px;">
                                  <p>Ref. ${x.referencia}</p>
                                </div>
                              </a>`);
            });
              $('textarea[name=variacoes]').val(JSON.stringify(variacoes))
          }

          $(function(){
            var options = {
                data: <?php echo json_encode($_pacientes);?>,
                getValue: "nome",
                list: {
                  match: {enabled: true},
                  onChooseEvent: function (){
                    val = $(".js-input-paciente").getSelectedItemData().id;
                    $("input[name=id_paciente]").val(val);
                    console.log(val);
                  }
                },
                  template: {
                    type: "custom",
                    method: function(value, item) {
                      return item.nome
                    }
                  }
            };
            $('.js-input-paciente').easyAutocomplete(options);

            $('select[name=id_marca],input[name=titulo]').change(function(){
              variacoesLista();
            });

            $('.js-btn-addVariacao').click(function(){

              let titulo = $('input.js-titulo').val();
              let estoqueMin = $('input.js-estoqueMin').val();
              let referencia = $('input.js-referencia').val();
              let index = $('input.js-index').val();
              let id_variacao = $('input.js-id_variacao').val();

              let item = { titulo, estoqueMin, referencia, id_variacao };

              if($.isNumeric(eval(index))) {
                variacoes[eval(index)]=item;
              } else {
                variacoes.push(item);
              }
              
              $('.js-titulo,.js-estoqueMin,.js-referencia,.js-index,.js-id_variacao').val('');


              variacoesLista();
            }); 

            $('.js-variacoes-lista').on('click','.js-item',function(){
              let index = $('.js-variacoes-lista .js-item').index(this);
              

              let info = variacoes[index];

              $('.js-titulo').val(info.titulo);
              $('.js-estoqueMin').val(info.estoqueMin);
              $('.js-referencia').val(info.referencia);
              $('.js-id_variacao').val(info.id_variacao?info.id_variacao:'');
              $('.js-index').val(index);
            });

            variacoesLista();
          });
        </script>

      </form>
    </div>
  </section>

  <?php
  } else {
    if(isset($_GET['deleta']) and is_numeric($_GET['deleta']) and $usr->tipo=="admin") {
      $vSQL="lixo='1'";
      $vWHERE="where id='".$_GET['deleta']."'";
      $sql->update($_table,$vSQL,$vWHERE);
      $sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='delete',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_table."',id_reg='".$_GET['deleta']."'");
      $jsc->jAlert("Registro excluído com sucesso!","sucesso","document.location.href='".$_page."?".$url."'");
      die();
    }
    
    $where="WHERE lixo='0'";

    if(isset($values['id_especialidade']) and is_numeric($values['id_especialidade'])) $where.=" and id_especialidade='".$values['id_especialidade']."'";
    if(isset($values['id_subcategoria']) and is_numeric($values['id_subcategoria'])) $where.=" and id_subcategoria='".$values['id_subcategoria']."'";
    
    $sql->consult($_table,"*",$where." order by id");
    
  ?>

  <section class="grid">
    <div class="box">

      <div class="filter">
        <div class="filter-button">
          <a href="<?php echo $_page."?form=1&$url";?>" class="verde"><i class="iconify" data-icon="bx-bx-plus"></i><span> Novo Produto</span></a>
        </div>
      </div>

      <div class="reg">
        <?php
        while($x=mysqli_fetch_object($sql->mysqry)) {
        ?>
        <a href="?form=1&edita=<?php echo $x->id;?>" class="reg-group">
          <div class="reg-color" style="background-color:green;"></div>
          <div class="reg-data" style="flex:0 1 50%;">
            <h1>
              <?php
                echo strtoupperWLIB(utf8_encode($x->titulo));
              ?>  
            </h1>
            <p>
            <?php 
              echo $_unidadesMedidas[$x->unidade_medida]->titulo
            ?>  
            </p>
          </div>
          <div class="reg-data" style="flex:0 1 150px;">
            <p><?php echo utf8_encode($x->embalagem).$_unidadesMedidas[$x->unidade_medida]->unidade;?></p>
          </div>
        </a>
        <?php
          }
        ?>
      </div>

    </div>
  </section>
        
  <?php
  }
  ?>

</section>

<?php
  include "includes/footer.php";
?>