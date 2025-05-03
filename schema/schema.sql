-- Criação do banco de dados
CREATE DATABASE IF NOT EXISTS sistema_orcamento;
USE sistema_orcamento;

-- Criação da tabela Cliente
CREATE TABLE Cliente (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20) NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação da tabela Orçamento
CREATE TABLE Orcamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    cliente_id INT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES Cliente(id) ON DELETE SET NULL
);

-- Criação da tabela Categoria
CREATE TABLE Categoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação da tabela Item
CREATE TABLE Item (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    preco DECIMAL(10,2) NOT NULL CHECK (preco >= 0),
    id_categoria INT NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES Categoria(id) ON DELETE CASCADE
);

-- Criação da tabela Tipos
CREATE TABLE Tipos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Criação da tabela Item_Orcamento
CREATE TABLE Item_Orcamento (
    id INT PRIMARY KEY AUTO_INCREMENT,
    id_orcamento INT NOT NULL,
    id_item INT NOT NULL,
    id_tipo INT NULL,
    quantidade INT NOT NULL CHECK (quantidade > 0),
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orcamento) REFERENCES Orcamento(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item) REFERENCES Item(id) ON DELETE CASCADE,
    FOREIGN KEY (id_tipo) REFERENCES Tipos(id) ON DELETE SET NULL
);

-- Criação da tabela Modificador_Financeiro (antiga Ajustes_Financeiros)
CREATE TABLE Modificador_Financeiro (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_orcamento INT NULL,
    id_item_orcamento INT NULL,
    tipo ENUM('taxa', 'desconto') NOT NULL,
    valor DECIMAL(10,2) NOT NULL CHECK (valor >= 0),
    tipo_calculo ENUM('fixed', 'percentage') NOT NULL,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_modificacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_orcamento) REFERENCES Orcamento(id) ON DELETE CASCADE,
    FOREIGN KEY (id_item_orcamento) REFERENCES Item_Orcamento(id) ON DELETE CASCADE
);

-- Criação de índice para melhorar buscas por telefone
CREATE INDEX idx_telefone ON Cliente(telefone);
