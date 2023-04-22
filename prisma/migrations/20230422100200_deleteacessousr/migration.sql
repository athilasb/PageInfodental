ALTER TABLE `ident_colaboradores` DROP `acesso_tipo`;
ALTER TABLE `ident_colaboradores` CHANGE `tipo` `tipo` ENUM('admin','moderador') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL;
