
CREATE TABLE `ident_whatsapp_disparos` (
  `id` int NOT NULL,
  `data` datetime NOT NULL,
  `ativo` tinyint(1) NOT NULL,
  `json` text NOT NULL,
  `erro` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE `ident_whatsapp_disparos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `data` (`data`,`ativo`);


ALTER TABLE `ident_whatsapp_disparos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;
COMMIT;