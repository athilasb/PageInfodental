ALTER TABLE `ident_pacientes_evolucoes` ADD `lixo_data` DATETIME NOT NULL AFTER `tipo_receita`, ADD `lixo_id_colaborador` INT NOT NULL AFTER `lixo_data`, ADD `pconsulta_data` DATE NOT NULL AFTER `lixo_id_colaborador`, ADD `pconsulta_tempo` INT(11) NOT NULL AFTER `pconsulta_data`, ADD `pconsulta_profissionais` VARCHAR(250) NOT NULL AFTER `pconsulta_tempo`;

ALTER TABLE `ident_pacientes_evolucoes_receitas` ADD `controleespecial` BOOLEAN NOT NULL AFTER `posologia`, ADD `id_medicamento` INT(11) NOT NULL AFTER `controleespecial`;
