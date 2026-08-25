---
name: job-tracker-backend
description: Projetar ou implementar o backend Laravel do Rastreador de Vagas, incluindo dominio, banco, APIs, importadores, filas, deduplicacao, ranking e testes. Nao usar para tarefas somente visuais.
---

# Backend do Rastreador de Vagas

Leia `docs/PROJECT_STATE.md` antes de tomar decisoes estruturais.

## Invariantes do dominio

- Preserve a vaga original e sua procedencia; dados normalizados nao devem apagar o payload bruto quando ele for permitido e util para auditoria.
- Identifique registros externos por fonte e chave externa. Reimportacoes devem atualizar o registro correto sem duplicar vagas.
- Trate deduplicacao entre fontes como decisao explicavel, sem apagar automaticamente registros que ainda nao tenham correspondencia confiavel.
- Separe coleta, normalizacao, deduplicacao e ranking para que cada etapa possa ser repetida e testada isoladamente.
- Execute conectores em jobs com timeout, retentativas limitadas e registro de falha por fonte.
- Valores de ranking devem guardar fatores ou sinais suficientes para explicar a pontuacao ao usuario.

## Implementacao Laravel

- Valide entrada com Form Requests e autorize acesso com policies quando houver dados de usuario.
- Use transacoes somente ao redor de invariantes realmente atomicos; nao envolva chamadas HTTP externas em transacoes de banco.
- Projete indices a partir das consultas reais, especialmente para fonte/chave externa, status, publicacao, localidade e modalidade.
- Simule provedores externos nos testes; nao dependa da rede na suite automatizada.
- Ao adicionar ou mudar uma decisao duravel, atualize de forma concisa `docs/PROJECT_STATE.md`.
