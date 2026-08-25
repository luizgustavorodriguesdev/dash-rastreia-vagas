# Rastreador de Vagas

## Stack e objetivo

- Aplicacao Laravel com Blade, Tailwind CSS, MySQL e filas do Laravel.
- O produto agrega, normaliza, classifica e acompanha vagas de fontes autorizadas.
- Integracoes devem preferir APIs oficiais, feeds e importacoes permitidas. Nao contorne autenticacao, CAPTCHA, robots.txt ou termos de uso.

## Acordos de trabalho

- Antes de alterar codigo, localize o fluxo real com `rg` e leia apenas os arquivos diretamente envolvidos.
- Preserve alteracoes existentes e nao reformate arquivos fora do escopo.
- Mantenha controllers finos; regras de negocio pertencem a actions/services e persistencia a models/query objects quando necessario.
- Toda importacao de vaga deve ser idempotente, rastreavel por fonte e tolerante a falhas parciais.
- Nao exponha credenciais, dados pessoais ou respostas integrais de provedores em logs.
- Prefira testes de feature para fluxos HTTP/importacao e testes unitarios para ranking, normalizacao e deduplicacao.
- Execute os testes proporcionais ao risco. Quando o projeto estiver inicializado, use `php artisan test` e, para alteracoes de frontend, `npm run build`.

## Conhecimento sob demanda

- Use `$job-tracker-backend` para dominio, banco, importadores, filas, ranking ou API Laravel.
- Use `$job-tracker-ui` para Blade, componentes, acessibilidade e Tailwind CSS.
- Decisoes aprovadas e estado atual devem ser registrados em `docs/PROJECT_STATE.md`; mantenha-o curto e factual.

## Agentes opcionais

- Delegue somente quando o usuario pedir agentes ou trabalho paralelo; subagentes consomem mais tokens.
- Para tarefas delegadas, prefira papeis de leitura: arquitetura Laravel, revisao de UI e lacunas de testes.
- Nao delegue uma edicao pequena. Prefira agentes de leitura/revisao para reduzir conflitos.
