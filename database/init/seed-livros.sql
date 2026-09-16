-- Cadastro de Livros - Dados de exemplo
-- Autores, assuntos, livros (com valor) e seus vínculos.

SET NAMES utf8mb4;
USE cadastro_livro;

INSERT INTO Autor (Nome) VALUES
('Machado de Assis'),
('J. R. R. Tolkien'),
('Agatha Christie'),
('George Orwell'),
('Erich Gamma'),
('Richard Helm'),
('Ralph Johnson'),
('John Vlissides');

INSERT INTO Assunto (Descricao) VALUES
('Romance'),
('Ficção'),
('Suspense'),
('Política'),
('Tecnologia');

INSERT INTO Livro (Titulo, Editora, Edicao, AnoPublicacao, valor) VALUES
('Dom Casmurro', 'Garnier', 1, '1899', 34.90),
('O Senhor dos Anéis', 'HarperCollins', 3, '1954', 129.90),
('Assassinato no Expresso do Oriente', 'Companhia das Letras', 2, '1934', 49.50),
('1984', 'Companhia das Letras', 1, '1949', 39.90),
('Design Patterns', 'Addison-Wesley', 1, '1994', 199.90);

-- Livro_Autor (o "Design Patterns" tem 4 autores para demonstrar o N:N)
INSERT INTO Livro_Autor (Livro_Codl, Autor_CodAu) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(5, 6),
(5, 7),
(5, 8);

INSERT INTO Livro_Assunto (Livro_Codl, Assunto_codAs) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5);