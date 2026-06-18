CREATE TABLE `utilizadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(100) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `perfil` varchar(20) NOT NULL COMMENT 'administrador ou tecnico',
  CONSTRAINT pk_utilizadores PRIMARY KEY (`id`),
  CONSTRAINT chk_utilizadores_perfil CHECK (`perfil` IN ('administrador', 'tecnico'))
);

CREATE TABLE `localizacoes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `edificio` varchar(50) NOT NULL,
  `piso` varchar(20) NOT NULL,
  `servico` varchar(100) NOT NULL,
  `sala_internamento_gabinete` varchar(100) NOT NULL,
  `apagado` boolean NOT NULL DEFAULT false,
  `data_apagado` date,
  `id_utilizador_apagou` int,
  CONSTRAINT pk_localizacoes PRIMARY KEY (`id`)
);

CREATE TABLE `fornecedores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome_empresa` varchar(150) NOT NULL,
  `nif` varchar(9) UNIQUE NOT NULL,
  `morada` varchar(255),
  `telefone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `website` varchar(150),
  `tipo` varchar(50) NOT NULL,
  `pessoa_contacto` varchar(100),
  `telefone_pessoa_contacto` varchar(20),
  `observacoes` text,
  `apagado` boolean NOT NULL DEFAULT false,
  `data_apagado` date,
  `id_utilizador_apagou` int,
  CONSTRAINT pk_fornecedores PRIMARY KEY (`id`),
  CONSTRAINT chk_fornecedores_tipo CHECK (`tipo` IN ('fabricante', 'distribuidor', 'assistencia', 'consumiveis'))
);

CREATE TABLE `equipamentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `codigo_interno` varchar(50) UNIQUE NOT NULL,
  `designacao` varchar(150) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `numero_serie` varchar(100) NOT NULL,
  `fabricante` varchar(100) NOT NULL,
  `data_aquisicao` date NOT NULL,
  `ano_fabrico` int,
  `custo_aquisicao` decimal(10,2),
  `tipo_entrada` varchar(20) NOT NULL,
  `estado` varchar(20) NOT NULL,
  `criticidade` varchar(20) NOT NULL,
  `observacoes` text,
  `id_localizacao` int NOT NULL,
  `apagado` boolean NOT NULL DEFAULT false,
  `data_apagado` date,
  `id_utilizador_apagou` int,
  CONSTRAINT pk_equipamentos PRIMARY KEY (`id`),
  CONSTRAINT chk_equipamentos_categoria CHECK (`categoria` IN ('monitorizacao', 'suporte_vida', 'terapia', 'diagnostico', 'laboratorio', 'esterilizacao', 'reabilitacao')),
  CONSTRAINT chk_equipamentos_tipo_entrada CHECK (`tipo_entrada` IN ('compra', 'doacao', 'aluguer', 'emprestimo')),
  CONSTRAINT chk_equipamentos_estado CHECK (`estado` IN ('ativo', 'manutencao', 'inativo', 'calibracao', 'quarentena', 'abatido')),
  CONSTRAINT chk_equipamentos_criticidade CHECK (`criticidade` IN ('baixa', 'media', 'alta', 'suporte_vida')),
  CONSTRAINT chk_equipamentos_custo CHECK (`custo_aquisicao` > 0),
  CONSTRAINT chk_equipamentos_data_apagado_aquisicao CHECK (`data_apagado` >= `data_aquisicao`)
);

CREATE TABLE `equipamento_fornecedor` (
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  CONSTRAINT pk_equipamento_fornecedor PRIMARY KEY (`id_equipamento`, `id_fornecedor`)
);

CREATE UNIQUE INDEX `utilizadores_index_0` ON `utilizadores` (`email`);

ALTER TABLE `equipamentos` ADD CONSTRAINT `fk_equipamentos_localizacao` FOREIGN KEY (`id_localizacao`) REFERENCES `localizacoes` (`id`);

ALTER TABLE `equipamentos` ADD CONSTRAINT `fk_equipamentos_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`);

ALTER TABLE `localizacoes` ADD CONSTRAINT `fk_localizacoes_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`);

ALTER TABLE `fornecedores` ADD CONSTRAINT `fk_fornecedores_utilizador` FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`);

ALTER TABLE `equipamento_fornecedor` ADD CONSTRAINT `fk_equipamento_fornecedor_equipamento` FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`);

ALTER TABLE `equipamento_fornecedor` ADD CONSTRAINT `fk_equipamento_fornecedor_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`);

CREATE TABLE logs (
  id int NOT NULL AUTO_INCREMENT,
  id_utilizador int,
  tipo_evento varchar(50) NOT NULL,
  descricao text,
  data_hora datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  CONSTRAINT fk_logs_utilizador FOREIGN KEY (id_utilizador) REFERENCES utilizadores(id),
  CONSTRAINT chk_logs_tipo_evento CHECK (tipo_evento IN (
    'login_sucesso', 
    'login_falhado', 
    'erro_bd', 
    'equipamento_criado',
    'equipamento_editado',
    'equipamento_desativado', 
    'fornecedor_criado',
    'fornecedor_editado',
    'fornecedor_desativado', 
    'localizacao_criada',
    'localizacao_editada',
    'localizacao_desativada'
  ))
);