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

/*
CREATE TABLE `equipamento_fornecedor` (
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  CONSTRAINT pk_equipamento_fornecedor PRIMARY KEY (`id_equipamento`, `id_fornecedor`)
);
*/

CREATE TABLE `equipamento_fornecedor` (
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int NOT NULL,
  `tipo` varchar(50) NOT NULL,
  CONSTRAINT pk_equipamento_fornecedor PRIMARY KEY (`id_equipamento`, `id_fornecedor`),
  CONSTRAINT chk_equipamento_fornecedor_tipo CHECK (`tipo` IN ('fabricante', 'distribuidor', 'assistencia', 'consumiveis'))
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

ALTER TABLE logs DROP CONSTRAINT chk_logs_tipo_evento;

ALTER TABLE logs ADD CONSTRAINT chk_logs_tipo_evento CHECK (tipo_evento IN (
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
    'localizacao_desativada',
    'documento_criado',
    'documento_desativado',
    'garantia_criada',
    'garantia_desativada'
));

CREATE TABLE `documentos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int,
  `tipo_documento` varchar(50) NOT NULL,
  `nome_documento` varchar(150) NOT NULL,
  `data_documento` date NOT NULL,
  `data_validade` date,
  `caminho_ficheiro` varchar(255) NOT NULL,
  `apagado` boolean NOT NULL DEFAULT false,
  `data_apagado` date,
  `id_utilizador_apagou` int,
  CONSTRAINT pk_documentos PRIMARY KEY (`id`),
  CONSTRAINT fk_documentos_equipamento FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT fk_documentos_fornecedor FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT fk_documentos_utilizador FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT chk_documentos_tipo_documento CHECK (`tipo_documento` IN ('manual', 'certificado_calibracao', 'fatura', 'ficha_tecnica', 'certificado_conformidade', 'outro'))
);

CREATE TABLE `garantias_contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `id_equipamento` int NOT NULL,
  `id_fornecedor` int,
  `data_inicio_garantia` date,
  `data_fim_garantia` date,
  `tem_contrato_manutencao` boolean NOT NULL DEFAULT false,
  `tipo_contrato` varchar(50),
  `periodicidade` varchar(50),
  `observacoes` text,
  `apagado` boolean NOT NULL DEFAULT false,
  `data_apagado` date,
  `id_utilizador_apagou` int,
  CONSTRAINT pk_garantias_contratos PRIMARY KEY (`id`),
  CONSTRAINT fk_garantias_contratos_equipamento FOREIGN KEY (`id_equipamento`) REFERENCES `equipamentos` (`id`),
  CONSTRAINT fk_garantias_contratos_fornecedor FOREIGN KEY (`id_fornecedor`) REFERENCES `fornecedores` (`id`),
  CONSTRAINT fk_garantias_contratos_utilizador FOREIGN KEY (`id_utilizador_apagou`) REFERENCES `utilizadores` (`id`),
  CONSTRAINT chk_garantias_contratos_tipo_contrato CHECK (`tipo_contrato` IN ('garantia_fabricante', 'manutencao_preventiva', 'manutencao_corretiva', 'manutencao_completa', 'outro')),
  CONSTRAINT chk_garantias_contratos_periodicidade CHECK (`periodicidade` IN ('mensal', 'trimestral', 'semestral', 'anual', 'nao_aplicavel')),
  CONSTRAINT chk_garantias_contratos_datas CHECK (`data_fim_garantia` >= `data_inicio_garantia`)
);

CREATE TABLE acessorios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_equipamento_pai INT NOT NULL,
  codigo VARCHAR(30) NOT NULL,
  nome VARCHAR(150) NOT NULL,
  marca VARCHAR(100) NULL,
  fabricante VARCHAR(100) NULL,
  modelo VARCHAR(100) NULL,
  numero_serie VARCHAR(100) NULL,
  estado VARCHAR(20) NOT NULL DEFAULT 'ativo',
  observacoes TEXT NULL,
  apagado TINYINT(1) NOT NULL DEFAULT 0,
  data_apagado DATETIME NULL,
  id_utilizador_apagou INT NULL,
  CONSTRAINT fk_acessorio_pai FOREIGN KEY (id_equipamento_pai) REFERENCES equipamentos(id),
  CONSTRAINT fk_acessorio_utilizador FOREIGN KEY (id_utilizador_apagou) REFERENCES utilizadores(id)
);

-- tem um único fornecedor (regra de negócio: 1 fornecedor por consumível)
-- =====================================================
CREATE TABLE consumiveis (
  id INT AUTO_INCREMENT PRIMARY KEY,
  id_equipamento_pai INT NOT NULL,
  codigo VARCHAR(30) NOT NULL,
  nome VARCHAR(150) NOT NULL,
  quantidade INT NOT NULL DEFAULT 0,
  id_fornecedor INT NOT NULL,
  observacoes TEXT NULL,
  apagado TINYINT(1) NOT NULL DEFAULT 0,
  data_apagado DATETIME NULL,
  id_utilizador_apagou INT NULL,
  CONSTRAINT fk_consumivel_pai FOREIGN KEY (id_equipamento_pai) REFERENCES equipamentos(id),
  CONSTRAINT fk_consumivel_fornecedor FOREIGN KEY (id_fornecedor) REFERENCES fornecedores(id)
);