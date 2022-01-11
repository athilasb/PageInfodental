

CREATE TABLE `ident_parametros_cadeiras_horarios` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `lixo_data` datetime NOT NULL,
  `id_cadeira` int(11) NOT NULL,
  `dia` int(11) NOT NULL,
  `inicio` time NOT NULL,
  `fim` time NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `data` datetime NOT NULL,
  `id_alteracao` int(11) NOT NULL,
  `alteracao_data` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ident_parametros_cadeiras_horarios`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_parametros_cadeiras_horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
  