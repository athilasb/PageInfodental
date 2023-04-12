CREATE TABLE `ident_financeiro_fluxo_splits` (
    `id` INT NOT NULL AUTO_INCREMENT ,
    `id_fluxo` INT NOT NULL , 
    `valor` DOUBLE NOT NULL DEFAULT '0' , 
    `id_centro_custo` INT NOT NULL , PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;