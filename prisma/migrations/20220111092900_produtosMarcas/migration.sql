CREATE TABLE `ident_produtos_marcas` (
  `id` int(11) NOT NULL,
  `lixo` tinyint(1) NOT NULL,
  `titulo` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `ident_produtos_marcas`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ident_produtos_marcas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


INSERT INTO `ident_produtos_marcas` (`lixo`, `titulo`) VALUES
(0, '3M'),
(0, 'Ivoclar'),
(0, 'DFL'),
(0, 'BIOPHARMA'),
( 0, 'EQUIPLEX '),
( 0, 'BD PLASTIPAK'),
( 0, 'HALEXISTAR '),
( 0, 'EUROFARMA'),
( 0, 'PROTDESC'),
( 0, 'ULTRACOTTON'),
( 0, 'HNDESC '),
( 0, 'TALGE'),
( 0, 'SUPERMAX '),
( 0, 'DESCARPACK '),
( 0, 'ULTRAPAK'),
( 0, 'FLAG HEALTH '),
( 0, 'SEM MARCA'),
( 0, 'SOFT PLUS'),
( 0, 'SSPLUS'),
( 0, 'KERR'),
( 0, 'INDUSBELLO'),
( 0, 'KURARAY'),
( 0, 'ALL PRIME'),
( 0, 'ZHERMACK '),
( 0, 'BIODINAMICA '),
( 0, 'FGM'),
( 0, 'POWER DENT '),
( 0, 'ORAL B'),
( 0, 'XYLESTESIN'),
( 0, 'RIOQUIMICA '),
( 0, 'MAQUIRA '),
( 0, 'VICTA'),
( 0, 'GALDERMA'),
( 0, 'SEPTODONT '),
( 0, 'BEGE'),
( 0, 'KINESIOSPORT'),
( 0, 'PREVEN '),
( 0, 'MICRODONT'),
( 0, 'SUNSIRE'),
( 0, 'XPROF '),
( 0, 'KDENT '),
( 0, 'PROCARE'),
( 0, 'KG'),
( 0, 'YVOIRE'),
( 0, 'TTAB INSTRUMENTOS CIRURGICO LTDA'),
( 0, 'BIOLINE '),
( 0, 'LG CHEM'),
( 0, 'MESOESTETIC'),
( 0, 'PRO VITAE'),
( 0, 'ODORYLAN'),
( 0, 'FILL-MED');
