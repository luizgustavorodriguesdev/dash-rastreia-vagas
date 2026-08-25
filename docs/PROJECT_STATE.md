# Estado do projeto

## Objetivo

Construir um rastreador que encontre boas vagas, consolide fontes autorizadas, elimine duplicatas, classifique oportunidades conforme o perfil do usuario e acompanhe candidaturas.

## Decisoes confirmadas

- Backend: Laravel.
- Interface: Blade e Tailwind CSS.
- Banco principal: MySQL, adequado ao ambiente XAMPP.
- Coleta: APIs oficiais, feeds e integracoes permitidas; scraping somente quando autorizado pela fonte.

## Estado atual

- Estrutura operacional do Codex criada.
- Laravel 12 com Breeze, Blade, Tailwind CSS e autenticacao inicial instalado.
- Ambiente verificado com PHP 8.2, Composer local, Node 24 e MariaDB 10.4 do XAMPP.
- Build de frontend concluido; banco MySQL `dash-rastreia-vagas` criado e migrations aplicadas.
- Dominio inicial implementado com fontes, empresas, vagas, tecnologias, factories, relacionamentos e integridade de importacao.
- Camada de importacao implementada com contrato de conectores, validacao, idempotencia, auditoria, falha parcial e execucao em fila por fonte.
- Sincronizacao manual definida para a fase inicial; conector Remotive e comando `jobs:sync` implementados sem agendamento.

## Proximas decisoes

- Modelo de perfil profissional e preferencias do usuario.
- Avaliar a proxima fonte autorizada depois de validar a qualidade dos dados Remotive.
- Repositorio remoto configurado em `https://github.com/luizgustavorodriguesdev/dash-rastreia-vagas.git`.

## Regra de manutencao

Atualize somente decisoes, restricoes e estado que mudem o trabalho futuro. Nao use este arquivo como diario ou changelog.
