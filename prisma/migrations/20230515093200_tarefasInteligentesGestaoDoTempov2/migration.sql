ALTER TABLE `ident_pacientes_proximasconsultas` ADD `excluido_data` DATETIME NOT NULL AFTER `id_agenda_origem`, ADD `excluido_id_colaborador` INT NOT NULL AFTER `excluido_data`;
ALTER TABLE `ident_pacientes_proximasconsultas` ADD `id_agenda` INT NOT NULL COMMENT 'agendamento feito atraves do lembrete' AFTER `excluido_id_colaborador`;
