INSERT INTO `ident_pacientes_evolucoes_tipos` (`id`, `tipo`, `titulo`, `tituloSingular`, `icone`, `pagina`) VALUES
(10, 'documentos', 'Documentos', 'Documento', 'fluent:document-add-28-regular', '');
COMMIT;

CREATE TABLE `ident_pacientes_evolucoes_documentos` (`id` INT NOT NULL AUTO_INCREMENT , `id_evolucao` INT NOT NULL , `lixo` BOOLEAN NOT NULL , `data` DATETIME NOT NULL , `id_documento` INT NOT NULL , `texto` LONGTEXT NOT NULL , PRIMARY KEY (`id`)) ENGINE = InnoDB;
