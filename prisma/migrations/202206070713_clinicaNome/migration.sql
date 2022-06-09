ALTER TABLE `ident_clinica` ADD `clinica_nome` VARCHAR(150) NOT NULL AFTER `id`;
ALTER TABLE `ident_colaboradores` CHANGE `foto` `foto` VARCHAR(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;

