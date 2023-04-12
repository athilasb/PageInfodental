ALTER TABLE `ident_financeiro_fluxo` ADD `id_categoria` INT NOT NULL DEFAULT '0' AFTER `descricao`, 
ADD `id_centro_custo` INT NOT NULL DEFAULT '0' AFTER `id_categoria`, 
ADD `dividido` INT NOT NULL DEFAULT '0' AFTER `id_centro_custo`, 
ADD `id_dividido` INT NOT NULL DEFAULT '0' AFTER `id_dividido`;