# Rastreador de Vagas

Aplicação para agregar vagas de fontes autorizadas, normalizar dados, identificar duplicidades e destacar oportunidades compatíveis com o perfil profissional do usuário.

## Tecnologias

- PHP 8.2 e Laravel 12
- Laravel Breeze com Blade
- Tailwind CSS e Alpine.js
- MySQL ou MariaDB
- Vite

## Requisitos

- PHP 8.2 ou superior, com as extensões exigidas pelo Laravel
- Composer 2
- Node.js e npm
- MySQL ou MariaDB

## Configuração local

```bash
composer install
npm install
copy .env.example .env
php artisan key:generate
```

Configure a conexão no arquivo `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dash-rastreia-vagas
DB_USERNAME=root
DB_PASSWORD=
```

Prepare o banco e os assets:

```bash
php artisan migrate
npm run build
```

Para desenvolvimento, execute `composer run dev`. No XAMPP, a aplicação também pode ser acessada por `http://localhost/dash-vagas/public`.

## Qualidade

```bash
php artisan test
vendor/bin/pint --test
npm run build
```

Os testes usam SQLite em memória e não alteram o banco MySQL local.

## Escopo do produto

1. Fontes, empresas, vagas e tecnologias.
2. Importação idempotente e histórico de sincronizações.
3. Normalização e deduplicação entre fontes.
4. Perfil profissional e preferências do usuário.
5. Ranking explicável das oportunidades.
6. Favoritos e acompanhamento de candidaturas.

As integrações devem usar APIs oficiais, feeds ou meios expressamente permitidos. O projeto não deve contornar autenticação, CAPTCHA, `robots.txt` ou termos de uso dos portais.

## Arquitetura de importação

Cada fonte implementa `JobSourceConnector` e entrega registros no formato normalizado. `ImportJobsFromSource` valida e persiste cada item em uma transação independente, enquanto `RunJobSourceImport` executa o conector pela fila com timeout, retentativas e exclusividade por fonte.

Cada execução fica registrada em `job_import_runs`, incluindo totais criados, atualizados, inalterados e inválidos. A chave composta `job_source_id + external_id` impede duplicidade durante reimportações.

## Estado do projeto

As decisões confirmadas e o estado atual ficam registrados em [`docs/PROJECT_STATE.md`](docs/PROJECT_STATE.md).

## Licença

A licença do projeto ainda não foi definida.
