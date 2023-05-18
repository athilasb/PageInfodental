ALTER TABLE `ident_produtos_marcas` ADD `fixo` BOOLEAN NOT NULL AFTER `titulo`;

CREATE TABLE `ident_colaboradores_assinaturas` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `id_colaborador` int NOT NULL,
  `assinatura` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `lat` varchar(50) NOT NULL,
  `lng` varchar(50) NOT NULL,
  `dispositivo` varchar(250) NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_colaboradores_assinaturas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_colaboradores_assinaturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
