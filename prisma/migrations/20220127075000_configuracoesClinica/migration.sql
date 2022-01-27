CREATE TABLE `ident_clinica` (
  `id` int NOT NULL,
  `np` varchar(150) NOT NULL,
  `instagram` varchar(100) NOT NULL,
  `site` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `whatsapp` varchar(20) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `endereco` varchar(250) NOT NULL,
  `complemento` varchar(150) NOT NULL,
  `lat` varchar(20) NOT NULL,
  `lng` varchar(20) NOT NULL,
  `tipo` enum('PF','PJ') NOT NULL,
  `razao_social` varchar(150) NOT NULL,
  `cnpj` varchar(20) NOT NULL,
  `inscricao_estadual` varchar(40) NOT NULL,
  `cpf` varchar(30) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `pg_colaboradores_dadospessoais` varchar(150) NOT NULL,
  `responsavel_cro_uf` varchar(10) NOT NULL,
  `responsavel_cro_tipo` varchar(30) NOT NULL,
  `cn_logo` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


INSERT INTO `ident_clinica` (`id`, `clinica_nome`, `instagram`, `site`, `email`, `whatsapp`, `telefone`, `endereco`, `complemento`, `lat`, `lng`, `tipo`, `razao_social`, `cnpj`, `inscricao_estadual`, `cpf`, `nome`, `responsavel_cro`, `responsavel_cro_uf`, `responsavel_cro_tipo`, `cn_logo`) VALUES
(1, ' ', '', '', '', '', '', '', '', '', '', 'PJ', '', '', '', '', '', '', '', '', '');

ALTER TABLE `ident_clinica`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_clinica`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;