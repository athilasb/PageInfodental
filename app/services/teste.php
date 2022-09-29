<?php 
var_dump($_FILES);

var_dump($_POST);


if(copy($_FILES['document']['tmp_name'],"arqs/teste.pdf")) echo "ok";
else echo "erro";

?>