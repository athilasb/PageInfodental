ALTER TABLE `ident_pacientes_evolucoes_procedimentos` ADD `id_procedimento` INT(11) NOT NULL AFTER `id_opcao`, ADD `id_procedimento_aevoluir` INT(11) NOT NULL AFTER `id_procedimento`, ADD `numero` INT(11) NOT NULL AFTER `id_procedimento_aevoluir`, ADD `numeroTotal` INT(11) NOT NULL AFTER `numero`;
ALTER TABLE `ident_pacientes_evolucoes_procedimentos` CHANGE `id_tratamento_procedimento` `id_tratamento_procedimento` INT(11) NOT NULL COMMENT 'pacientes_tratamentos_procedimentos ';
