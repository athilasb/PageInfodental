
ALTER TABLE `ident_pacientes_evolucoes_receitas`
  DROP `pdf`,
  DROP `pdf_assinado`,
  DROP `pdf_assinado_data`;

  ALTER TABLE `ident_pacientes_evolucoes` ADD `receita_assinada` DATETIME NOT NULL AFTER `pconsulta_profissionais`;

  ALTER TABLE `ident_landingpage_banner` CHANGE `foto` `foto` VARCHAR(250) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL; 

ALTER TABLE `ident_landingpage_aclinica` CHANGE `foto1` `foto1` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL; 

ALTER TABLE `ident_landingpage_aclinica` CHANGE `foto2` `foto2` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;

ALTER TABLE `ident_landingpage_aclinica` CHANGE `foto3` `foto3` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL; 

DROP TABLE IF EXISTS `ident_landingpages_antesedepois`;