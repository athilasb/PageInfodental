ALTER TABLE `ident_financeiro_fluxo_splits` ADD `id_origem` INT NULL AFTER `id_centro_custo`, ADD `id_categoria` INT NULL AFTER `id_origem`, ADD `calcular_como` VARCHAR(250) NULL AFTER `id_categoria`;

ALTER TABLE `ident_financeiro_fluxo_splits` CHANGE `id_fluxo` `id_registro` INT NOT NULL;

ALTER TABLE `ident_financeiro_fluxo_splits` CHANGE `id_origem` `id_origem` INT NULL DEFAULT NULL AFTER `id`, CHANGE `id_registro` `id_registro` INT NOT NULL AFTER `id_origem`, CHANGE `valor` `valor` DOUBLE NOT NULL DEFAULT '0' AFTER `id_registro`, CHANGE `id_centro_custo` `id_centro_custo` INT NOT NULL AFTER `valor`