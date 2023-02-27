ALTER TABLE `ident_parametros_cartoes_operadoras` ADD `pixOperadora` INT NOT NULL DEFAULT '0' AFTER `id_banco`;

ALTER TABLE `ident_financeiro_fluxo_recebimentos` ADD `id_colaborador` INT NOT NULL AFTER `id_pagante`;


ALTER TABLE `ident_financeiro_fluxo` ADD `valor_multa` DOUBLE NOT NULL DEFAULT '0' AFTER `valor`, ADD `valor_juros` DOUBLE NOT NULL DEFAULT '0' AFTER `valor_multa`, ADD `valor_taxa` DOUBLE NULL DEFAULT '0' AFTER `valor_multa`, ADD `valor_desconto` DOUBLE NOT NULL DEFAULT '0' AFTER `valor_taxa`;


ALTER TABLE `ident_financeiro_fluxo` ADD `id_operadora` INT NOT NULL DEFAULT '0' AFTER `id_formapagamento`, ADD `id_bandeira` INT NOT NULL DEFAULT '0' AFTER `id_operadora`, ADD `taxa_cartao` DOUBLE NOT NULL DEFAULT '0' AFTER `id_bandeira`;

ALTER TABLE `ident_financeiro_fluxo` ADD `lixo_data` INT NULL DEFAULT '0' AFTER `id_banco`, ADD `lixo_id_colaborador` INT NOT NULL DEFAULT '0' AFTER `lixo_data`;

ALTER TABLE `ident_financeiro_fluxo` ADD `desconto` INT NOT NULL DEFAULT '0' AFTER `lixo_id_colaborador`;