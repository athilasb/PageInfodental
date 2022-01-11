CREATE TABLE `ident_produtos` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `id_especialidade` int(11) NOT NULL,
  `unidade_medida` int(11) NOT NULL,
  `embalagem` double NOT NULL,
  `id_marca` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


CREATE TABLE `ident_produtos_variacoes` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_produto` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `estoqueMin` double NOT NULL,
  `referencia` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ident_produtos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_produtos_variacoes`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_produtos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ident_produtos_variacoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
