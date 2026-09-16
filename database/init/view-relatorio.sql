-- Cadastro de Livros - View do relatório
-- Traz os livros agrupados por autor (um livro com vários autores
-- aparece uma vez para cada autor), ordenado por nome do autor e título.

SET NAMES utf8mb4;
USE cadastro_livro;

CREATE OR REPLACE VIEW vw_relatorio_livros AS
SELECT
    a.Nome         AS nome_autor,
    l.Codl         AS cod_livro,
    l.Titulo       AS titulo,
    l.Editora      AS editora,
    l.Edicao       AS edicao,
    l.AnoPublicacao AS ano_publicacao,
    l.valor        AS valor
FROM Livro l
INNER JOIN Livro_Autor la ON la.Livro_Codl = l.Codl
INNER JOIN Autor a       ON a.CodAu = la.Autor_CodAu
ORDER BY a.Nome, l.Titulo;