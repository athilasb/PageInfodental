ALTER TABLE `ident_financeiro_bancosecontas` ADD `id_banco` INT NOT NULL AFTER `conta`, ADD `pix_tipo` VARCHAR(50) NOT NULL AFTER `id_banco`, ADD `pix_chave` VARCHAR(150) NOT NULL AFTER `pix_tipo`, ADD `pix_beneficiario` VARCHAR(150) NOT NULL AFTER `pix_chave`;