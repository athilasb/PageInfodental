
CREATE TABLE `ident_pacientes_arquivos` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `id_paciente` int NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `extensao` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_pacientes_arquivos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_pacientes_arquivos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
