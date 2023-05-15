<?php

    # API Geral
        if(isset($_POST['ajaxHeader'])) {
              require_once("lib/conf.php");
              require_once("usuarios/checa.php");

              $rtn = [] ;
              if($_POST['ajaxHeader']=='pacientePeriodicidade') {
                    $paciente = '';
                    if(isset($_POST['id_paciente']) and is_numeric($_POST['id_paciente'])) {
                          $sql->consult($_p."pacientes","*","where id=".$_POST['id_paciente']);
                          if($sql->rows) {
                                $paciente=mysqli_fetch_object($sql->mysqry);
                          }
                    }
                    
                    if(is_object($paciente)) {

                          if(isset($_POST['periodicidade'])) {

                                $vSQL="periodicidade='".$_POST['periodicidade']."'";
                                $vWHERE="where id=$paciente->id";

                                $sql->update($_p."pacientes",$vSQL,$vWHERE);
                                $sql->add($_p."log","data=now(),id_usuario='".$usr->id."',tipo='update',vsql='".addslashes($vSQL)."',vwhere='".addslashes($vWHERE)."',tabela='".$_p."pacientes',id_reg='".$paciente->id."'");

                                $rtn=array('success'=>true,
                                            'periodicidade'=>$_POST['periodicidade'],
                                            'periodicidadeHTML'=>isset($_pacientesPeriodicidade[$_POST['periodicidade']])?$_pacientesPeriodicidade[$_POST['periodicidade']]:'-');
                     

                          } else {
                                $rtn=array('success'=>false,'error'=>'Periodicidade inválida!');
                          }

                    } else {
                          $rtn=array('success'=>false,'error'=>'Paciente não encontrado!');
                    }
              }

              header("Content-type: application/json");
              echo json_encode($rtn);
              die();
        }

    # Autenticação automática quando acessa a página inicial (index)
        if(basename($_SERVER['PHP_SELF'])=="index.php") {

              require_once("lib/conf.php");
              require_once("lib/classes.php");
              $str = new StringW();         
              $sql = new Mysql();

              if(!isset($_GET['erro'])) {

                  if(isset($_COOKIE[$_p.'adm_cpf']) and isset($_COOKIE[$_p.'adm_senha']) and isset($_COOKIE[$_p.'adm_id'])) {
                        $sql->consult($_p."colaboradores","*","where id='".addslashes($_COOKIE[$_p.'adm_id'])."' and 
                                                                        cpf='".addslashes($_COOKIE[$_p.'adm_cpf'])."' and 
                                                                        senha='".addslashes($_COOKIE[$_p.'adm_senha'])."' and 
                                                                        permitir_acesso='1'");
                        if($sql->rows) {
                              header("Location: dashboard.php");
                              echo "<html><head><title>Redirecionando...</title></head><body><font size=4>Redirecionando...</font></body></html>";
                              die();
                        }
                  }
              }

        } 

    # Header das demais páginas
        else {

            require_once("lib/conf.php");
            require_once("usuarios/checa.php");

            $sql=new Mysql();
            $str=new StringW();
            $jsc=new Js();

            $adm = new Adm($_p);
            $url=$adm->url($_GET);


            // Verifica se possui conta

            $sql->consult("infodentalADM.infod_contas","*","where instancia='".$_ENV['NAME']."'");
            if($sql->rows) {
                $infoConta=mysqli_fetch_object($sql->mysqry);

                if(basename($_SERVER['PHP_SELF'])!="pg_configuracoes_assinatura.php") {
             /*       // Verifica se possui assinatura
                    if(empty($infoConta->iugu_subscription_id)) {
                        header("Location: pg_configuracoes_assinatura.php");
                    }*/
                }


            } else {
                header("Location: index.php?erro=7");
            }
        }

    $_page=basename($_SERVER['PHP_SELF']);
    $link_landingpage="https://".$_ENV['NAME'].".infodental.dental/";


    $mobileDetect=new MobileDetect();
    $mobile=(!$mobileDetect->isMobile() and !$mobileDetect->isTablet())?0:1;

?>
<!doctype html>
<html xmlns="http://www.w3.org/1999/xhtml"
      xmlns:og="http://ogp.me/ns#"
      xmlns:fb="http://www.facebook.com/2008/fbml">

<head>
<meta charset="utf-8">

<title><?php echo isset($title)?$title." | Info Dental":"Info Dental"; ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta name="description" content="<?php echo ($description)?$description:"Infodental"; ?>">
<meta name="author" content="WLIB Soluções Web - www.wlib.com.br">


<base href="//<?php echo $_SERVER["HTTP_HOST"];?>/" />


<meta property="og:title" content="<?php echo isset($title)?$title:"Infodental"; ?>" />
<meta property="og:description" content="<?php echo isset($description)?$description:"Sistema inteligente para clínicas"; ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="http://<?php echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];?>" />
<meta property="og:image" content="http://<?php echo $_SERVER['HTTP_HOST'];?>/img/facebook.png" />
<meta property="og:image:width" content="1300" />
<meta property="og:image:height" content="700" />
<meta property="og:site_name" content="Infodental" />
<meta property="fb:admins" content="1066108721" />
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="css/style.css?v1" />
<link rel="stylesheet" type="text/css" href="css/calendario.css" />
<link rel="stylesheet" type="text/css" href="css/apps.css" />
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<?php /*<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />*/ ?>

<?php /*<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>*/ ?>

<script type="text/javascript" src="js/jquery.js?v3.6.4"></script>
<script defer type="text/javascript" src="js/jquery.select2.js"></script>
<?php /*<script defer type="text/javascript" src="js/jquery-ui.js"></script>*/?>
<script defer type="text/javascript" src="js/jquery.slick.js"></script>
<script defer type="text/javascript" src="js/jquery.datetimepicker.js"></script>
<script defer type="text/javascript" src="js/jquery.chosen.js"></script>
<script defer type="text/javascript" src="js/jquery.fancybox.js"></script>
<script defer type="text/javascript" src="js/jquery.chart.js"></script>
<script defer type="text/javascript" src="js/jquery.chart-utils.js"></script>
<script defer type="text/javascript" src="js/jquery.tablesorter.js"></script>
<script defer type="text/javascript" src="js/jquery.inputmask.js"></script>
<script defer type="text/javascript" src="js/jquery.dad.js"></script>
<script defer type="text/javascript" src="js/jquery.money.js"></script>
<script defer type="text/javascript" src="js/jquery.tooltipster.js"></script>
<script defer type="text/javascript" src="js/jquery.autocomplete.js"></script>
<script defer type="text/javascript" src="js/jquery.caret.js"></script>
<script defer type="text/javascript" src="js/jquery.mobilePhoneNumber.js"></script>
<script type="text/javascript" src="js/jquery.sweetalert.js"></script>
<script type="text/javascript" src="js/jquery.validacao.js"></script>
<script type="text/javascript" src="js/jquery.funcoes.js?v3"></script>
<script type="text/javascript" src="ckeditor/ckeditor.js"></script>
<script defer src="https://code.iconify.design/1/1.0.3/iconify.min.js"></script>
<script type="text/javascript" src="js/moment.js"></script>
<script type="text/javascript" src="js/jquery.daterangepicker.js"></script>

<script type="text/javascript">
    var isMobile = <?php echo $mobile;?>;
    var id_paciente = 0;
    var id_agenda = 0;
    var calendar = '';
</script>

</head>

<body>

    <div id="loading" style="display: none;border-radius: 10px;font-size: 0.89em;color:var(--cinza4)">
        <center>
            <span class="iconify" data-icon="eos-icons:loading" data-height="30" style="color:var(--cinza5)"></span><br />
            <span>Carregando...</span>
        </center>
    </div>
<?php
    // verifica situação da conta infodental
    if(basename($_SERVER['PHP_SELF'])!="index.php" and basename($_SERVER['PHP_SELF'])!="pg_configuracoes_assinatura2.php") {
                  
        if($infoConta->status=="bloqueada") {
              if(basename($_SERVER['PHP_SELF'])=="pg_configuracoes_assinatura.php") {

             ?>
             <div style="width:100%;padding:20px;display: flex;background: red;color:#fff;justify-content: center;">
                    <span class="iconify" data-icon="mdi:alert-rhombus" data-height="20" data-inline="true"></span>&nbsp;A sua conta está bloqueada. Para utilizar o Info Dental é preciso que você regularize a sua assinatura!
              </div>
             <?php 
              } else {
                    ?>
                    <script type="text/javascript">document.location.href='pg_configuracoes_assinatura.php';</script>
                    <?php
                    die();
              }
        } else if($infoConta->status=="inadimplente") {
             ?>
             <div style="width:100%;padding:20px;display: flex;background: red;color:#fff;justify-content: center;">
                    <span class="iconify" data-icon="mdi:alert-rhombus" data-height="20" data-inline="true"></span>&nbsp;A sua conta está inadimplente. Para evitar bloqueios&nbsp;<a href="pg_configuracoes_assinatura.php"><b><u>clique aqui</u></b></a>&nbsp;para se regularizar!
              </div>
             <?php 
        } else {
              if($infoConta->iugu_subscription_suspended==1) {
                    // verifica a quanto tempo esta suspensa
                    $dif = strtotime(date('Y-m-d H:i:s'))-strtotime($infoConta->iugu_subscription_suspended_data);
                    $dif /= (60 * 60 * 21);
                    $dif = floor($dif);
                    //echo $dif;die();
                    if($dif>=2) {
                          if(basename($_SERVER['PHP_SELF'])!="pg_configuracoes_assinatura.php") {
                           ?>
                          <script type="text/javascript">document.location.href='pg_configuracoes_assinatura.php';</script>
                          <?php
                          die();
                          }
                    } 
              ?>
             <div style="width:100%;padding:20px;display: flex;background: red;color:#fff;justify-content: center;">
                    <span class="iconify" data-icon="mdi:alert-rhombus" data-height="20" data-inline="true"></span>&nbsp;O seu plano está suspenso.  Favor regularizar &nbsp;<a href="pg_configuracoes_assinatura.php"><b><u>clicando aqui</u></b></a> 
              </div>
             <?php 

                    echo $dif;
              }
        }
        
    }
?>
<section class="wrapper">