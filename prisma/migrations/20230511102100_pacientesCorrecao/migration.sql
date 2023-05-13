CREATE TABLE `ident_pacientes_correcoes` (
  `id` int NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `data` datetime NOT NULL,
  `id_paciente` int NOT NULL,
  `id_usuario` int NOT NULL,
  `obs` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_correcoes` ADD PRIMARY KEY (`id`);
ALTER TABLE `ident_pacientes_correcoes` MODIFY `id` int NOT NULL AUTO_INCREMENT;