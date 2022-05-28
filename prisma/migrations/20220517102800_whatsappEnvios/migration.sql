
CREATE TABLE `ident_whatsapp_respostasdeconfirmacao` (
  `id` int NOT NULL,
  `pubSim` tinyint(1) NOT NULL,
  `pubNao` tinyint(1) NOT NULL,
  `pubNaoIdentificado` tinyint(1) NOT NULL,
  `msgSim` text NOT NULL,
  `msgNao` text NOT NULL,
  `msgNaoIdentificado` text NOT NULL,
  `pubInteligenciaSim` tinyint(1) NOT NULL,
  `pubInteligenciaNao` tinyint(1) NOT NULL,
  `pubInteligenciaNaoIdentificado` tinyint(1) NOT NULL,
  `msgInteligenciaSim` text NOT NULL,
  `msgInteligenciaNao` text NOT NULL,
  `msgInteligenciaNaoIdentificado` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `ident_whatsapp_respostasdeconfirmacao`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_whatsapp_respostasdeconfirmacao`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;
