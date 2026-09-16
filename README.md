# Cadastro de Livros (cad-livro)

Sistema de cadastro de livros com CRUD completo (Livro, Autor, Assunto) e
relatório de livros agrupado por autor, construído com **Symfony 8.1**, **PHP
8.4**, **MySQL 8.4**, **Twig** e **Bootstrap 5**, prontos para rodar em
**Docker**.

## Funcionalidades

- CRUD de **Livros** (inclui valor em R$, autores e assuntos);
- CRUD de **Autores**;
- CRUD de **Assuntos**;
- **Relatório** de livros por autor (consulta feita em uma **view** do banco,
  `vw_relatorio_livros`);
- Tela inicial com menu, interface Bootstrap 5 e mensagens ao usuário.

## Como rodar

Pré-requisito: Docker (com Compose).

```bash
docker compose up -d --build
```

Acesse **http://localhost:8080**.

O banco é criado e populado automaticamente na primeira subida
(`database/init/schema-livros.sql`, `seed-livros.sql` e `view-relatorio.sql`).
O MySQL também fica acessível em `127.0.0.1:3307` (usuário `app`, senha
`app123`, banco `cadastro_livro`).

## Testes

```bash
php bin/phpunit
```

## Documentação

Veja [DOCUMENTACAO.md](DOCUMENTACAO.md) para a explicação completa do sistema
(banco de dados, fluxo de requisição, entidades, relatório, Docker e
implantação).