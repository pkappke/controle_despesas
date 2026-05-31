CREATE DATABASE IF NOT EXISTS controle_despesas
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE controle_despesas;

CREATE TABLE IF NOT EXISTS categorias (
    id        INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome      VARCHAR(100) NOT NULL,
    tipo      ENUM('receita','despesa','ambos') NOT NULL DEFAULT 'ambos',
    cor       CHAR(7) NOT NULL DEFAULT '#607D8B',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO categorias (nome, tipo, cor) VALUES
('Salário',          'receita', '#4CAF50'),
('Freelance',        'receita', '#8BC34A'),
('Investimentos',    'receita', '#00BCD4'),
('Outros (Receita)', 'receita', '#9E9E9E'),
('Moradia',          'despesa', '#F44336'),
('Alimentação',      'despesa', '#FF9800'),
('Transporte',       'despesa', '#FF5722'),
('Saúde',            'despesa', '#E91E63'),
('Educação',         'despesa', '#9C27B0'),
('Lazer',            'despesa', '#3F51B5'),
('Roupas',           'despesa', '#2196F3'),
('Utilidades',       'despesa', '#009688'),
('Outros (Despesa)', 'despesa', '#757575');

CREATE TABLE IF NOT EXISTS transacoes (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tipo          ENUM('receita','despesa') NOT NULL,
    descricao     VARCHAR(255) NOT NULL,
    valor         DECIMAL(12,2) NOT NULL,
    data          DATE NOT NULL,
    id_categoria  INT UNSIGNED NOT NULL,
    observacao    TEXT DEFAULT NULL,
    criado_em     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_categoria) REFERENCES categorias(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    INDEX idx_data (data),
    INDEX idx_tipo (tipo),
    INDEX idx_categoria (id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
