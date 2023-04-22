ALTER TABLE `ident_configuracoes_parametros` ADD `check_agendaTamanhoMinimoAltura` BOOLEAN NOT NULL AFTER `check_agendaDesativarRegrasStatus`;
ALTER TABLE `ident_colaboradores` ADD `acesso_tipo` ENUM('admin','moderador') NOT NULL AFTER `whatsapp_notificacoes`, ADD `acesso_permissoes` VARCHAR(250) NOT NULL AFTER `acesso_tipo`;
