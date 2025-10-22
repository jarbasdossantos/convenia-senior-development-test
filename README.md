## Sistema de Gerenciamento de Colaboradores

[![Coverage](https://codecov.io/gh/jarbasdossantos/convenia-senior-developer-test/branch/main/graph/badge.svg)](https://codecov.io/gh/jarbasdossantos/convenia-senior-developer-test)

API REST desenvolvida em Laravel 12 para gerenciamento de colaboradores com autenticação via Laravel Sanctum.

## Funcionalidades

### Autenticação
- **Login de Usuários**: Endpoint de autenticação que retorna token de acesso via Sanctum
- **Proteção de Rotas**: Middleware de autenticação para proteger endpoints privados

### Gerenciamento de Colaboradores
- **CRUD Completo**: Criação, leitura, atualizaçãoo e exclusão de colaboradores, com validações e sanitizações necessarias.

### Importação de Dados
- **Importação via CSV**: Upload e processamento assíncrono de arquivos CSV
- **Processamento em Background**: Utiliza filas (queue) para processar importações de forma assíncrona (por arquivo)

### Documentação
- **Swagger/OpenAPI**: Documentação interativa da API disponível via L5-Swagger
- **Endpoints Documentados**: Todos os endpoints possuem anotações Swagger completas

### Testes de Feature
- **AuthControllerTest**: Testes de autenticação e login
- **CollaboratorControllerTest**: Testes dos endpoints CRUD e importação CSV

### Testes Unitários
- **UserTest**: Testes do modelo User
- **CollaboratorTest**: Testes do modelo Collaborator (mutators, validações)
- **CpfValidationRuleTest**: Testes da regra de validação de CPF
- **ProcessCollaboratorsCsvJobTest**: Testes do job de processamento de CSV

Para executar os testes:

```bash
composer test
```

## Pré-requisitos

- PHP 8.2 ou superior
- Composer
- MySQL/PostgreSQL

## =' Instalação e Configuração Local

### 1. Clone o repositório

```bash
git clone git@github.com:jarbasdossantos/convenia-senior-development-test.git
cd convenia-senior-developer-test
```

### 2. Instale as dependências

```bash
composer install
```

### 3. Configure o ambiente

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure o banco de dados

O projeto está configurado para usar SQLite por padrão. Crie o arquivo do banco:

```bash
touch database/database.sqlite
touch database/testing.sqlite
```

Para usar MySQL ou PostgreSQL, edite o arquivo `.env` e configure as variáveis `DB_*`.

### 5. Execute as migrações

```bash
php artisan migrate
```

### 6. Popule o banco com dados de teste

```bash
php artisan db:seed
```

### 7. Gere a documentação Swagger

```bash
php artisan l5-swagger:generate
```

## Executando a Aplicação

### Modo Desenvolvimento Simples

```bash
php artisan serve
```

A aplicação estará disponível em `http://localhost:8000`

### Modo Desenvolvimento Completo (com Queue e Logs)

```bash
composer dev
```

Este comando inicia simultaneamente:
- Servidor Laravel (`php artisan serve`)
- Worker de filas (`php artisan queue:listen`)
- Logs em tempo real (`php artisan pail`)

### Apenas o Worker de Filas

Para processar os jobs de importação CSV:

```bash
php artisan queue:work
```

## Documentação da API

Após iniciar a aplicação, acesse:

- **Swagger UI**: http://localhost:8000/api/documentation
- **JSON OpenAPI**: http://localhost:8000/api/documentation.json

## Endpoints Principais

### Autenticação
- `POST /api/login` - Autenticar usuário e obter token

### Colaboradores (requer autenticação)
- `GET /api/collaborators` - Listar todos os colaboradores
- `POST /api/collaborators` - Criar novo colaborador
- `GET /api/collaborators/{id}` - Ver detalhes de um colaborador
- `PUT /api/collaborators/{id}` - Atualizar colaborador
- `DELETE /api/collaborators/{id}` - Remover colaborador
- `POST /api/collaborators/import-csv` - Importar colaboradores via CSV

## Tecnologias Utilizadas

- **Framework**: Laravel 12
- **Autenticação**: Laravel Sanctum
- **Processamento CSV**: League CSV 9
- **Documentação**: L5-Swagger (Swagger/OpenAPI)
- **Banco de Dados**: SQLite (padrão) / MySQL / PostgreSQL
- **Filas**: Database (padrão)

## Roadmap/To-Do
- Soft deletes em colaboradores.
- Idempotência no CSV (hash por linha).
- Chunks para arquivos muito grandes.
- Melhorar validações.

## Licença

Projeto de teste técnico. Uso educativo/avaliativo.
