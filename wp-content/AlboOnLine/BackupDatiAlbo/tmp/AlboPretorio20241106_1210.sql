CREATE TABLE IF NOT EXISTS `zfv_albopretorio_allegati` (
  `IdAllegato` int NOT NULL AUTO_INCREMENT,
  `TitoloAllegato` varchar(255) NOT NULL DEFAULT '',
  `Allegato` varchar(255) NOT NULL DEFAULT '',
  `IdAtto` int NOT NULL DEFAULT '0',
  `TipoFile` varchar(6) DEFAULT '',
  `DocIntegrale` tinyint(1) NOT NULL DEFAULT '1',
  `Impronta` char(64) NOT NULL,
  `Natura` char(1) NOT NULL DEFAULT 'A',
  `Note` varchar(255) DEFAULT '',
  PRIMARY KEY (`IdAllegato`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_allegati ;
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_atti` (
  `IdAtto` int NOT NULL AUTO_INCREMENT,
  `Numero` int NOT NULL DEFAULT '0',
  `Anno` int NOT NULL DEFAULT '0',
  `Data` date NOT NULL DEFAULT '0000-00-00',
  `Riferimento` text NOT NULL,
  `Oggetto` text NOT NULL,
  `DataInizio` date NOT NULL DEFAULT '0000-00-00',
  `DataFine` date DEFAULT '0000-00-00',
  `Informazioni` text NOT NULL,
  `IdCategoria` int NOT NULL DEFAULT '0',
  `RespProc` int NOT NULL,
  `DataAnnullamento` date DEFAULT '0000-00-00',
  `MotivoAnnullamento` text,
  `Ente` int NOT NULL DEFAULT '0',
  `DataOblio` date NOT NULL DEFAULT '0000-00-00',
  `Soggetti` varchar(100) NOT NULL,
  `IdUnitaOrganizzativa` int NOT NULL DEFAULT '0',
  `Richiedente` varchar(100) NOT NULL DEFAULT '',
  PRIMARY KEY (`IdAtto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_atti ;
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_categorie` (
  `IdCategoria` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(255) NOT NULL DEFAULT '',
  `Descrizione` varchar(255) NOT NULL DEFAULT '',
  `Genitore` int NOT NULL DEFAULT '0',
  `Giorni` smallint NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_categorie ;
INSERT INTO `zfv_albopretorio_categorie` VALUES (1, 'Bandi e gare', 'Bandi e gare', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (2, 'Contratti - Personale ATA', 'Contratti - Personale ATA', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (3, 'Contratti - Personale Docente', 'Contratti - Personale Docente', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (4, 'Contratti e convenzioni', 'Contratti e convenzioni', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (5, 'Convocazioni', 'Convocazioni', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (6, 'Delibere Consiglio di Istituto', 'Delibere Consiglio di Istituto', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (7, 'Documenti altre P.A.', 'Documenti altre P.A.', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (8, 'Esiti esami', 'Esiti esami', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (9, 'Graduatorie', 'Graduatorie', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (10, 'Organi collegiali', 'Organi collegiali', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (11, 'Organi collegiali - Elezioni', 'Organi collegiali - Elezioni', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (12, 'Privacy', 'Privacy', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (13, 'Programmi annuali e Consuntivi', 'Programmi annuali e Consuntivi', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (14, 'Regolamenti', 'Regolamenti', 0, 15);
INSERT INTO `zfv_albopretorio_categorie` VALUES (15, 'Sicurezza', 'Sicurezza', 0, 15);
SET SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO';
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_enti` (
  `IdEnte` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) NOT NULL,
  `Indirizzo` varchar(150) NOT NULL DEFAULT '',
  `Url` varchar(100) NOT NULL DEFAULT '',
  `Email` varchar(100) NOT NULL DEFAULT '',
  `Pec` varchar(100) NOT NULL DEFAULT '',
  `Telefono` varchar(40) NOT NULL DEFAULT '',
  `Fax` varchar(40) NOT NULL DEFAULT '',
  `Note` text,
  PRIMARY KEY (`IdEnte`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_enti ;
INSERT INTO `zfv_albopretorio_enti` VALUES (0, 'APSP Cesare Bendetti', '', '', '', '', '', '', '');
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_resprocedura` (
  `IdResponsabile` int NOT NULL AUTO_INCREMENT,
  `Cognome` varchar(20) NOT NULL DEFAULT '',
  `Nome` varchar(20) NOT NULL DEFAULT '',
  `Email` varchar(100) NOT NULL DEFAULT '',
  `Telefono` varchar(30) NOT NULL DEFAULT '',
  `Orario` varchar(60) NOT NULL DEFAULT '',
  `Note` text,
  `Funzione` char(8) DEFAULT 'RP',
  PRIMARY KEY (`IdResponsabile`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_resprocedura ;
INSERT INTO `zfv_albopretorio_resprocedura` VALUES (1, 'La Grutta', 'Antonino', 'test@test.test', '', '', '', 'DR');
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_attimeta` (
  `IdAttoMeta` int NOT NULL AUTO_INCREMENT,
  `IdAtto` int NOT NULL,
  `Meta` varchar(100) NOT NULL,
  `Value` text,
  PRIMARY KEY (`IdAttoMeta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_attimeta ;
CREATE TABLE IF NOT EXISTS `zfv_albopretorio_unitaorganizzative` (
  `IdUO` int NOT NULL AUTO_INCREMENT,
  `Nome` varchar(100) NOT NULL,
  `Indirizzo` varchar(150) NOT NULL DEFAULT '',
  `Url` varchar(100) NOT NULL DEFAULT '',
  `Email` varchar(100) NOT NULL DEFAULT '',
  `Pec` varchar(100) NOT NULL DEFAULT '',
  `Telefono` varchar(40) NOT NULL DEFAULT '',
  `Fax` varchar(40) NOT NULL DEFAULT '',
  `Note` text,
  PRIMARY KEY (`IdUO`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci ;
Delete From zfv_albopretorio_unitaorganizzative ;
INSERT INTO `zfv_albopretorio_unitaorganizzative` VALUES (1, 'APSP Cesare Bendetti', '', '', 'test@test.test', '', '', '', '');
CREATE TABLE IF NOT EXISTS `zfv_options` (
  `option_id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `option_name` varchar(191) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT '',
  `option_value` longtext COLLATE utf8mb4_unicode_520_ci NOT NULL,
  `autoload` varchar(20) COLLATE utf8mb4_unicode_520_ci NOT NULL DEFAULT 'yes',
  PRIMARY KEY (`option_id`),
  UNIQUE KEY `option_name` (`option_name`),
  KEY `autoload` (`autoload`)
) ENGINE=InnoDB AUTO_INCREMENT=4711 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci ;
INSERT INTO `zfv_options` VALUES (4677, 'opt_AP_AnnoProgressivo', '2024', 'auto');
INSERT INTO `zfv_options` VALUES (4696, 'opt_AP_AutoShortcode', '0', 'auto');
INSERT INTO `zfv_options` VALUES (4699, 'opt_AP_BootstrapItalia', '0', 'auto');
INSERT INTO `zfv_options` VALUES (4672, 'opt_AP_ColonneFE', '{"Data":0,"Ente":0,"Riferimento":0,"Oggetto":1,"Validita":1,"Categoria":1,"Note":0,"DataOblio":0}', 'auto');
INSERT INTO `zfv_options` VALUES (4684, 'opt_AP_ColoreAnnullati', '#FFCFBD', 'auto');
INSERT INTO `zfv_options` VALUES (4686, 'opt_AP_ColoreDispari', '#FFF', 'auto');
INSERT INTO `zfv_options` VALUES (4685, 'opt_AP_ColorePari', '#ECECEC', 'auto');
INSERT INTO `zfv_options` VALUES (4703, 'opt_AP_DefaultEnte', '0', 'auto');
INSERT INTO `zfv_options` VALUES (4671, 'opt_AP_DefaultSoggetti', '{"AM":"1","RP":"0","RB":"0"}', 'auto');
INSERT INTO `zfv_options` VALUES (4704, 'opt_AP_Ente', 'APSP Cesare Bendetti', 'auto');
INSERT INTO `zfv_options` VALUES (4679, 'opt_AP_FolderUpload', 'AllegatiAttiAlboPretorio', 'auto');
INSERT INTO `zfv_options` VALUES (4675, 'opt_AP_FolderUploadMeseAnno', '', 'auto');
INSERT INTO `zfv_options` VALUES (4689, 'opt_AP_GiorniOblio', '1825', 'auto');
INSERT INTO `zfv_options` VALUES (4706, 'opt_AP_IconaDocumenti', '', 'auto');
INSERT INTO `zfv_options` VALUES (4681, 'opt_AP_LivelloTitoloEnte', 'h2', 'auto');
INSERT INTO `zfv_options` VALUES (4683, 'opt_AP_LivelloTitoloFiltri', 'h4', 'auto');
INSERT INTO `zfv_options` VALUES (4682, 'opt_AP_LivelloTitoloPagina', 'h3', 'auto');
INSERT INTO `zfv_options` VALUES (4688, 'opt_AP_LogAc', 'Si', 'auto');
INSERT INTO `zfv_options` VALUES (4687, 'opt_AP_LogOp', 'Si', 'auto');
INSERT INTO `zfv_options` VALUES (4678, 'opt_AP_NumeroProgressivo', '1', 'auto');
INSERT INTO `zfv_options` VALUES (4697, 'opt_AP_OldInterfaccia', 'Si', 'auto');
INSERT INTO `zfv_options` VALUES (4690, 'opt_AP_PAttiCor', 'https://apspcesarebenedetti.chebellagiornata.it/account/', 'auto');
INSERT INTO `zfv_options` VALUES (4691, 'opt_AP_PAttiSto', 'https://apspcesarebenedetti.chebellagiornata.it/account/', 'auto');
INSERT INTO `zfv_options` VALUES (4692, 'opt_AP_PAtto', 'https://apspcesarebenedetti.chebellagiornata.it/account/', 'auto');
INSERT INTO `zfv_options` VALUES (4673, 'opt_AP_RestApi', '', 'auto');
INSERT INTO `zfv_options` VALUES (4674, 'opt_AP_RestApi_UrlEst', '', 'auto');
INSERT INTO `zfv_options` VALUES (4693, 'opt_AP_RuoliPuls', 'administrator,editor,author,amministratore_albo', 'auto');
INSERT INTO `zfv_options` VALUES (4694, 'opt_AP_RuoliPulsGruppi', 'administrator,editor,author,amministratore_albo', 'auto');
INSERT INTO `zfv_options` VALUES (4695, 'opt_AP_RuoliPulsVisualizzaAtto', 'administrator,editor,author,amministratore_albo', 'auto');
INSERT INTO `zfv_options` VALUES (4700, 'opt_AP_TabResp', '[{"ID":"RP","Funzione":"Responsabile Procedimento","Display":"Si"},{"ID":"OP","Funzione":"Gestore procedura","Display":"Si"},{"ID":"SC","Funzione":"Segretario Comunale","Display":"No"},{"ID":"RB","Funzione":"Responsabile Pubblicazione","Display":"No"},{"ID":"DR","Funzione":"Direttore dei Servizi e Amministrativi","Display":"No"}]', 'auto');
INSERT INTO `zfv_options` VALUES (4705, 'opt_AP_Testi', '{"NoResp":"","CertPub":"Si attesta l\'avvenuta pubblicazione del documento all\'albo pretorio sopra indicato per il quale non sono pervenute osservazioni"}', 'auto');
INSERT INTO `zfv_options` VALUES (4676, 'opt_AP_TipidiFiles', 'a:3:{s:3:"ndf";a:3:{s:11:"Descrizione";s:22:"Tipo file non definito";s:5:"Icona";s:114:"https://apspcesarebenedetti.chebellagiornata.it/wp-content/plugins/albo-pretorio-on-line-master/img/notipofile.png";s:8:"Verifica";s:0:"";}s:3:"pdf";a:3:{s:11:"Descrizione";s:8:"File Pdf";s:5:"Icona";s:107:"https://apspcesarebenedetti.chebellagiornata.it/wp-content/plugins/albo-pretorio-on-line-master/img/Pdf.png";s:8:"Verifica";s:0:"";}s:3:"p7m";a:3:{s:11:"Descrizione";s:25:"File firmato digitalmente";s:5:"Icona";s:111:"https://apspcesarebenedetti.chebellagiornata.it/wp-content/plugins/albo-pretorio-on-line-master/img/firmato.png";s:8:"Verifica";s:193:"&lt;a href=&quot;http://vol.ca.notariato.it/&quot; onclick=&quot;window.open(this.href);return false;&quot;&gt;Verifica firma con servizio fornito da Consiglio Nazionale del Notariato&lt;/a&gt;";}}', 'auto');
INSERT INTO `zfv_options` VALUES (4698, 'opt_AP_UpCSSNewInterface', 'Si', 'auto');
INSERT INTO `zfv_options` VALUES (4670, 'opt_AP_UrlSprite', '', 'auto');
INSERT INTO `zfv_options` VALUES (4669, 'opt_AP_Versione', '4.5.7', 'auto');
INSERT INTO `zfv_options` VALUES (4680, 'opt_AP_VisualizzaEnte', 'Si', 'auto');
