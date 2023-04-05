ALTER TABLE `ident_financeiro_fluxo_recebimentos` 
ADD `valor_multa` DOUBLE NOT NULL DEFAULT '0' AFTER `valor`, 
ADD `valor_taxa` DOUBLE NOT NULL DEFAULT '0' AFTER `valor_multa`, 
ADD `valor_desconto` DOUBLE NOT NULL DEFAULT '0' AFTER `valor_taxa`, 
ADD `taxa_cartao` DOUBLE NOT NULL DEFAULT '0' AFTER `valor_desconto`;