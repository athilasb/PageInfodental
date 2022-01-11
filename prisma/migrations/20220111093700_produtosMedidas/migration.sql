CREATE TABLE `ident_produtos_unidadesmedidas` (
  `id` int(11) NOT NULL,
  `lixo` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `unidade` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `ident_produtos_unidadesmedidas` (`id`, `lixo`, `titulo`, `unidade`) VALUES
(1, 0, 'Litro', 'lt'),
(2, 0, 'Mililitro', 'ml'),
(3, 0, 'Grama', 'g'),
(4, 0, 'Unidade', 'und'),
(5, 0, 'Miligramas', 'ml');

ALTER TABLE `ident_produtos_unidadesmedidas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_produtos_unidadesmedidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
