ALTER TABLE `ident_pacientes_proximasconsultas` ADD `id_agenda_origem` INT NOT NULL COMMENT 'agendamento de onde partiu o registro' AFTER `situacao`;
ALTER TABLE `ident_pacientes_historico` ADD `id_agenda_origem` INT NOT NULL COMMENT 'agendamento de onde partiu o registro' AFTER `relacionamento_momento`;

ALTER TABLE `ident_pacientes` ADD `id_agenda_origem` INT NOT NULL COMMENT 'agendamento de onde partiu a periodicidade' AFTER `periodicidade`;

