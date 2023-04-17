CREATE TABLE `ident_financeiro_fluxo_splits_vencimentos` (
    `id` INT NOT NULL AUTO_INCREMENT , 
    `id_split` INT NOT NULL , 
    `id_fluxo` INT NOT NULL , 
    `vencimento` DATE NOT NULL , 
    `efetivacao` DATE NOT NULL , 
    `centrodecusto` INT NOT NULL , 
    `valor` DOUBLE NOT NULL , 
    PRIMARY KEY (`id`)
    ) ENGINE = InnoDB;