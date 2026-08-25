---
name: job-tracker-ui
description: Criar ou alterar a interface Blade e Tailwind CSS do Rastreador de Vagas, incluindo dashboard, filtros, cards, formularios, responsividade e acessibilidade. Nao usar para regras exclusivas do backend.
---

# Interface do Rastreador de Vagas

Leia `docs/PROJECT_STATE.md` e inspecione os componentes existentes antes de criar novos padroes.

## Direcao da interface

- Priorize leitura rapida: titulo, empresa, local/modalidade, faixa salarial, data, fonte e aderencia devem ter hierarquia clara.
- Filtros precisam refletir na URL para permitir compartilhamento, retorno e paginacao previsivel.
- Estados vazio, carregando, erro, indisponivel e sem resultados filtrados devem ser distintos.
- Nao comunique ranking apenas por cor; mostre rotulo, valor ou fatores relevantes.
- Use componentes Blade para padroes repetidos e utilitarios Tailwind diretamente; evite CSS customizado sem necessidade concreta.
- Preserve navegacao por teclado, foco visivel, labels, contraste e mensagens de validacao associadas aos campos.
- Comece pelo menor viewport e confirme comportamento em telas grandes.

## Verificacao

- Verifique os estados principais e dados extremos, como titulos longos, salario ausente e muitas tecnologias.
- Depois de alterar assets, execute o build do frontend quando o scaffold estiver disponivel.
- Atualize `docs/PROJECT_STATE.md` apenas se a alteracao estabelecer uma decisao duravel de produto ou design.
