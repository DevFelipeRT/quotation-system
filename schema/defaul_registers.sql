-- Query para limpar as tabelas mantendo a integridade das chaves primárias
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE Item;
TRUNCATE TABLE Categoria;
SET FOREIGN_KEY_CHECKS = 1;

-- Inserir categorias 'Serviço', 'Produto' e 'Indefinido' (caso ainda não tenham sido inseridas)
INSERT INTO Categoria (nome, descricao)
VALUES
    ('Indefinido', 'Categoria para itens sem uma definição específica'),
    ('Serviço', 'Categoria para itens relacionados a serviços prestados'),
    ('Produto', 'Categoria para itens relacionados a produtos físicos')
    
ON DUPLICATE KEY UPDATE id=id; -- Evita erro caso já existam

-- Inserir itens na categoria 'Serviço'
INSERT INTO Item (nome, descricao, preco, id_categoria)
VALUES
    ('Consultoria', 'Serviço de consultoria em tecnologia', 500.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Instalação', 'Serviço de instalação de sistemas', 300.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Manutenção', 'Serviço de manutenção preventiva e corretiva', 150.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Treinamento', 'Treinamento especializado em sistemas e software', 600.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Desenvolvimento Personalizado', 'Desenvolvimento de soluções sob medida', 1200.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Suporte Técnico', 'Suporte técnico remoto e presencial', 100.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Auditoria de Sistema', 'Auditoria e análise de segurança de sistemas', 800.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Design Gráfico', 'Serviço de criação de identidade visual e material gráfico', 450.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Análise de Dados', 'Serviço de análise e visualização de dados empresariais', 700.00, (SELECT id FROM Categoria WHERE nome = 'Serviço')),
    ('Implementação de Software', 'Implantação e configuração de software corporativo', 1500.00, (SELECT id FROM Categoria WHERE nome = 'Serviço'));

-- Inserir itens na categoria 'Produto'
INSERT INTO Item (nome, descricao, preco, id_categoria)
VALUES
    ('Notebook', 'Notebook com especificações avançadas', 2000.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Impressora', 'Impressora multifuncional', 700.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Monitor', 'Monitor 27" com resolução 4K', 1500.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Teclado Mecânico', 'Teclado mecânico com switches personalizados', 300.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Mouse Óptico', 'Mouse óptico ergonômico', 120.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Fone de Ouvido Bluetooth', 'Fone de ouvido com cancelamento de ruído', 400.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Câmera Digital', 'Câmera fotográfica profissional com lentes intercambiáveis', 2500.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Smartphone', 'Smartphone com tela de 6.5" e 128GB de armazenamento', 1500.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Tablet', 'Tablet com tela de 10" e 64GB de armazenamento', 800.00, (SELECT id FROM Categoria WHERE nome = 'Produto')),
    ('Fones de Ouvido Sem Fio', 'Fones de ouvido sem fio com bateria de longa duração', 350.00, (SELECT id FROM Categoria WHERE nome = 'Produto'));

-- Inserir itens na categoria 'Indefinido' (alguns sem descrição)
INSERT INTO Item (nome, descricao, preco, id_categoria)
VALUES
    ('Item Desconhecido', NULL, 0.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Serviço X', 'Serviço ainda não especificado', 0.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Produto Y', NULL, 999.99, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Item Experimental', 'Item em fase de testes', 250.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Pacote Especial', 'Pacote com múltiplos itens', 0.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Objeto Não Identificado', NULL, 0.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Serviço Secreto', 'Serviço exclusivo e confidencial', 5000.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Ferramenta Misteriosa', NULL, 120.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Acessório Aleatório', 'Acessório de uso geral', 0.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido')),
    ('Oferta Relâmpago', 'Oferta de tempo limitado', 50.00, (SELECT id FROM Categoria WHERE nome = 'Indefinido'));