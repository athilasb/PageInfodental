ALTER TABLE `ident_parametros_cartoes_operadoras_bandeiras` 
ADD `credito_parcelas_semjuros` INT NOT NULL AFTER `credito_parcelas`, 
ADD `taxas` MEDIUMTEXT NOT NULL AFTER `credito_parcelas_semjuros`;

