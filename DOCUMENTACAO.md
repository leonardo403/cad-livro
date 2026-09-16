# Documentação do Sistema — Cadastro de Livros

Documento que explica o funcionamento de cada parte do sistema, criado como
parte do desafio técnico. Serve de roteiro para a apresentação e para
implantação do projeto.

---

## 1. Visão geral

O sistema é um **CRUD de livros** com uma tela inicial (menu), CRUDs de
**Livro**, **Autor** e **Assunto**, e um **relatório** que lista os livros
agrupados por autor.

- **Backend:** PHP 8.4 e **Symfony 8.1** (framework MVC).
- **Persistência:** **MySQL 8.4** com **Doctrine ORM** (camada de persistência).
- **Templates:** **Twig** (template engine do Symfony).
- **CSS:** **Bootstrap 5** via CDN (controle de cor/tamanho dos componentes,
  conforme exigência do enunciado).
- **Conteinerização:** Docker Compose com `Dockerfile` + `docker-compose.yml`.

O esquema do banco segue integralmente o modelo proposto (tabelas `Livro`,
`Autor`, `Assunto` e as ligações N:N `Livro_Autor` e `Livro_Assunto`), com o
único ajuste de `AUTO_INCREMENT` nas chaves primárias e a inclusão do campo
**`valor`** (R$) no livro, como pedido no enunciado.

---

## 2. Estrutura de pastas

| Caminho | Finalidade |
|---|---|
| `public/` | Pasta pública (apenas o `index.php`, o *front controller*). |
| `src/Controller/` | Controllers (camada HTTP): Home, Livro, Autor, Assunto, Relatório. |
| `src/Entity/` | Entidades Doctrine (mapeiam as tabelas do banco). |
| `src/Repository/` | Repositórios de acesso a dados. |
| `src/Form/` | Formulários (LivroType, AutorType, AssuntoType). |
| `templates/` | Templates Twig (HTML). |
| `config/` | Configuração do framework (rotas, banco, Twig, etc.). |
| `database/init/` | Scripts SQL executados automaticamente na criação do banco. |
| `docker/` | Configuração auxiliar do Apache (vhost). |
| `tests/` | Testes funcionais (PHPUnit + WebTestCase). |
| `var/` | Cache e logs gerados em tempo de execução. |

---

## 3. Banco de dados

### 3.1. Scripts de criação

Os scripts ficam em `database/init/` e são executados **automaticamente na
primeira subida do container do MySQL** (o Docker executa as pastas montadas
em `/docker-entrypoint-initdb.d` em **ordem alfabética**):

1. **`schema-livros.sql`** — cria as tabelas:
   - `Livro` (Codl PK, Titulo, Editora, Edicao, AnoPublicacao, **valor** DECIMAL(10,2));
   - `Autor` (CodAu PK, Nome);
   - `Assunto` (codAs PK, Descricao);
   - `Livro_Autor` e `Livro_Assunto` (N:N com chave composta e `ON DELETE CASCADE`).
2. **`seed-livros.sql`** — popula dados de exemplo: 8 autores, 5 assuntos e 5
   livros. O livro "Design Patterns" tem **4 autores**, demonstrando o
   relacionamento N:N (um livro pode ter mais de um autor).
3. **`view-relatorio.sql`** — cria a view **`vw_relatorio_livros`**.

Todas as tabelas usam `CHARSET=utf8mb4` (UTF-8 completo). Cada script inicia
com `SET NAMES utf8mb4;` para que o cliente MySQL do container não interprete
os acentos como latin1 (evita "AnÃ©is" em vez de "Anéis").

### 3.2. Modelo de dados

```
Livro (Codl) ──< Livro_Autor >── Autor (CodAu)
     │                 │
     └──────< Livro_Assunto >── Assunto (codAs)
```

- **`Livro`** — cadastro principal; adicionado o campo `valor` (R$) exigido no
  texto do desafio.
- **`Autor`** — nome.
- **`Assunto`** — descrição.
- **`Livro_Autor` / `Livro_Assunto`** — tabelas de ligação; a chave primária
  composta evita duplicatas e o `ON DELETE CASCADE` apaga os vínculos quando o
  livro/autor/assunto é excluído.

### 3.3. View do relatório (`vw_relatorio_livros`)

```sql
CREATE OR REPLACE VIEW vw_relatorio_livros AS
SELECT a.Nome AS nome_autor, l.Codl AS cod_livro, l.Titulo, l.Editora,
       l.Edicao, l.AnoPublicacao, l.valor
FROM Livro l
INNER JOIN Livro_Autor la ON la.Livro_Codl = l.Codl
INNER JOIN Autor a       ON a.CodAu = la.Autor_CodAu
ORDER BY a.Nome, l.Titulo;
```

Por ser um `INNER JOIN` pela tabela de ligação, um livro com mais de um autor
aparece uma vez para **cada** autor — exatamente o comportamento exigido
("agrupando os dados por autor"). O relatório é acessado por essa view no
banco, cumprindo o requisito do enunciado.

---

## 4. O fluxo de uma requisição

1. O Apache (dentro do Docker) direciona tudo para `public/index.php`
   (*front controller*), graças ao vhost de `docker/apache/symfony.conf`
   (`FallbackResource /index.php`).
2. O Kernel do Symfony interpreta o `Request` e consulta as rotas
   (`#[Route(...)]` definidas nos controllers).
3. O controller recebe a requisição, chama o repositório/formulário, executa a
   lógica e devolve um `Response` renderizando um template Twig.
4. O template Twig gera o HTML final (com Bootstrap) enviado ao navegador.

Exemplo com `GET /livros`:
`index.php → App\Kernel → LivroController::index() → LivroRepository::findAll() → templates/livro/index.html.twig → HTML`.

---

## 5. Entidades (Doctrine ORM)

As entidades (`src/Entity/`) espelham **exatamente** os nomes de tabelas e
colunas do modelo (ex.: `#[ORM\Table(name: 'Livro')]`, coluna `Codl`, `valor`).
Dois detalhes do mapeamento do `Livro`:

- `ManyToMany` com **join table explícita**:
  `#[ORM\JoinTable(name: 'Livro_Autor')]`, colunas `Livro_Codl` / `Autor_CodAu`
  (e `Livro_Assunto` / `Assunto_codAs` no outro relacionamento).
- `valor` é `DECIMAL(10,2)` — no Doctrine é lido como string para não perder
  precisão.

Validações ficam nas propriedades com atributos `#[Assert\...]` (ex.: título
obrigatório e máximo 40 caracteres).

---

## 6. Controllers e tratamento de erros

Cada recurso tem seu controller em `src/Controller/`:

- `HomeController` — página inicial com os links/menu.
- `LivroController` — `index` (lista), `new` (criar), `edit` (atualizar),
  `delete` (remover via POST com token CSRF).
- `AutorController` e `AssuntoController` — idem para seus recursos.
- `RelatorioController` — chama a view e entrega os dados para o template.

**Tratamento de erros:** evitando `try/catch` genéricos, os controllers capturam
apenas erros **específicos de banco**:

- `UniqueConstraintViolationException` — mensagem própria (ex.: "Já existe um
  livro com esses dados");
- `DBALException` (base das exceções do driver) — "Não foi possível salvar no
  banco de dados".

O restante continua sendo tratado pelo mecanismo padrão do Symfony (página de
erro + log). Mensagens ao usuário são exibidas via **flash messages** Bootstrap.

---

## 7. Formulários e validação

Os formulários (`src/Form/`) usam o componente **Form** do Symfony:

- `LivroType` — campos de título, editora, edição, ano e **`MoneyType` com
  moeda `BRL`** (campo de moeda formatado, como pede o enunciado) além de dois
  `EntityType` múltiplos para escolher **autores** e **assuntos**.
- `AutorType` / `AssuntoType` — campos simples.

A validação (notação `#[Assert\...]` nas entidades) roda automaticamente no
submit. O layout do formulário usa o tema **`bootstrap_5_layout`**
(configurado em `config/packages/twig.yaml`), por isso os campos já recebem as
classes `.form-control`/`.form-select` do Bootstrap.

A exclusão ("Excluir") é feita por um pequeno formulário **POST** com **token
CSRF** (proteção contra falsificação de requisição), que valida a identidade do
clique.

---

## 8. A camada de apresentação (Twig)

- `templates/base.html.twig` — layout base com o menu, flash messages e o
  carregamento do Bootstrap 5 (CSS + JS via CDN).
- `templates/home/index.html.twig` — menu inicial em cards.
- `templates/livro/`, `templates/autor/`, `templates/assunto/` — listas
  (`index`) e formulários de novo/editar, com um `_form.html.twig` reaproveitado.
- `templates/relatorio/index.html.twig` — relatório agrupado por autor com
  botão "Imprimir".

**Formatações aplicadas:**
- Valor monetário: `R$ {{ livro.valor|number_format(2, ',', '.') }}` →
  mostra `R$ 34,90`.
- Ano de publicação como texto de 4 dígitos, edição como número inteiro.

---

## 9. O relatório

1. No banco, o script `view-relatorio.sql` cria a view `vw_relatorio_livros`.
2. `LivroRepository::consultarRelatorio()` consulta essa view via
   `fetchAllAssociative()` (o resultado **vem da view**, como exigido).
3. `RelatorioController` agrupa as linhas por autor em um array associativo.
4. O template `relatorio/index.html.twig` desenha uma tabela para cada autor,
   com os livros e seus valores.

É possível imprimir clicando em "Imprimir" (o navegador formata a página).

---

## 10. Docker: como a aplicação sobe

### Dockerfile
Base `php:8.4-apache` com:
- extensão `pdo_mysql` (conexão com o MySQL);
- `mod_rewrite` e ferramentas do Composer (`unzip`, `git`);
- instalação das dependências com `composer install`;
- vhost apontando a raiz para `public/` (pastas do Symfony).
- permissões de escrita em `var/` para o Apache.

### docker-compose.yml
| Serviço | Imagem | Papel |
|---|---|---|
| `db` | `mysql:8.4` | Banco de dados; monta `database/init/` em `/docker-entrypoint-initdb.d` (schema+seed+view são aplicados no primeiro start). Healthcheck de prontidão. Volume `mysql_data` persistente. Porta `3307:3306`. |
| `app` | build local (Dockerfile) | Aplicação Symfony. Porta `8080:80`. `DATABASE_URL` aponta para o serviço `db`. Só inicia quando o banco está saudável. |

**Como subir:**

```bash
docker compose up -d --build
```

Abra **http://localhost:8080**. O banco já vem populado (seed).

**Notas:**
- A porta `8080` foi usada porque a `8000` (padrão do Symfony) já costuma estar
  ocupada; para mudar, edite `docker-compose.yml`.
- O banco é exposto na porta `3307` do host para inspeção com o MySQL Workbench
  ou cliente `mysql` (`mysql -u app -p app123 -h 127.0.0.1 -P 3307 cadastro_livro`).
- Alterou o código? Reconstrua com `docker compose up -d --build`.

### Rodando sem Docker (opcional)
Com PHP 8.4, Composer e um MySQL local:

```bash
composer install
# crie as tabelas + view e o seed no seu MySQL local (ou use doctrine:schema:create)
# DATABASE_URL no .env já aponta para 127.0.0.1:3307/cadastro_livro
symfony serve        # ou: php -S 127.0.0.1:8000 -t public
```

---

## 11. Testes

Usando PHPUnit + `WebTestCase` (`tests/Controller/`):

- `LivroControllerTest` — a lista de livros responde 200, mostra "Dom Casmurro"
  e o valor formatado; a página de novo livro abre.
- `AutorControllerTest` / `AssuntoControllerTest` — listas respondem 200 e
  mostram os dados de exemplo.
- `RelatorioControllerTest` — o relatório lista os autores e o livro com
  autoria múltipla.

```bash
php bin/phpunit
```

Os testes funcionais fazem requisições HTTP de verdade e exigem o banco de pé
(rodam contra o banco `cadastro_livro` populado pelo seed).

---

## 12. Scripts e instruções de implantação

Tudo que é necessário para implantar está versionado no repositório:

1. **Banco** — `database/init/schema-livros.sql`, `database/init/seed-livros.sql`
   e `database/init/view-relatorio.sql` (aplicados automaticamente pelo Docker).
2. **Aplicação** — `Dockerfile`, `docker/apache/symfony.conf`,
   `docker-compose.yml` e o código-fonte.
3. **Passo a passo:** `git clone <url>` → `docker compose up -d --build` →
   acessar `http://localhost:8080`.

Em produção, ajuste ao menos:
- `APP_ENV=prod` e `APP_SECRET` forte (variáveis de ambiente);
- senhas do MySQL (`MYSQL_PASSWORD`, `MYSQL_ROOT_PASSWORD` / `DATABASE_URL`);
- executar `composer dump-env prod` e usar cache de produção.