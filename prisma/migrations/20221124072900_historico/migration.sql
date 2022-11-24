ALTER TABLE `ident_pacientes_tratamentos_procedimentos_evolucao_historico` ADD `tipo_alterouStatus` BOOLEAN NOT NULL COMMENT 'se o historico é referente a alteração de status' AFTER `usuario`;
