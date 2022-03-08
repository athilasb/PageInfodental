ALTER TABLE `ident_colaboradores` ADD `lat` VARCHAR(50) NOT NULL AFTER `id_cidade`, ADD `lng` VARCHAR(50) NOT NULL AFTER `lat`;
ALTER TABLE `ident_colaboradores` ADD `primeiro_acesso` BOOLEAN NOT NULL AFTER `contratacaoAtiva`;
