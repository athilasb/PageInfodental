
CREATE TABLE `ident_pacientes_evolucoes_assinaturas` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `id_evolucao` int NOT NULL,
  `id_paciente` int NOT NULL,
  `assinatura` longtext NOT NULL,
  `dispositivo` varchar(250) NOT NULL,
  `lat` varchar(50) NOT NULL,
  `lng` varchar(50) NOT NULL,
  `ip` varchar(150) NOT NULL,
  `cpf` varchar(30) NOT NULL,
  `data_nascimento` date NOT NULL,
  `lixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `ident_pacientes_evolucoes_assinaturas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_pacientes_evolucoes_assinaturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;
