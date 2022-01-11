
CREATE TABLE `ident_financeiro_categorias` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `receita` tinyint(1) NOT NULL,
  `fixo` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `ident_financeiro_categorias`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_financeiro_categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
