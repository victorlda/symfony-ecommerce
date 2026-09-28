# Roadmap

Este documento descreve a evolução planejada do projeto. Cada marco termina com o repositório em estado apresentável: testes passando, CI verde e documentação atualizada.

## Concluído

A base do projeto já está implementada:

- [x] Ambiente Docker com FrankenPHP, PHP 8.4, PostgreSQL 18 e Node 24
- [x] Monólito modular com os módulos `Identity`, `Catalog` e `Shared`
- [x] Cadastro de usuários, login com JWT e perfis (`ROLE_USER`, `ROLE_ADMIN`)
- [x] Refresh token em cookie `httpOnly`, com rotação a cada uso e detecção de reuso
- [x] CRUD de produtos com soft delete, reativação e listagem administrativa com busca
- [x] Erros padronizados no formato Problem Details (RFC 9457), com mensagens em português
- [x] Documentação OpenAPI com Swagger UI, exportada e verificada no CI
- [x] Frontend em React 19, TypeScript, Vite, Tailwind CSS 4 e shadcn/ui
- [x] Cliente HTTP tipado a partir do OpenAPI, com renovação automática do token
- [x] Telas de catálogo, login, cadastro e painel administrativo de produtos
- [x] PHPUnit, PHPStan, PHP CS Fixer, ESLint e CI com jobs de backend e frontend

---

## Marco 1: versão 1.0 apresentável

**Objetivo:** tornar o projeto fácil de avaliar por quem nunca o viu.

- [ ] Dados de exemplo com Foundry (admin, clientes e cerca de 30 produtos) e comando `make seed`
- [ ] ADRs em `docs/adr/` para as decisões principais:
  - [ ] Monólito modular em vez de microsserviços
  - [ ] UUID v7 como identificador
  - [ ] Valores monetários em centavos
  - [ ] Soft delete de produtos
  - [ ] Refresh token em cookie `httpOnly` com rotação e detecção de reuso
  - [ ] Formato de erro Problem Details com campo `code`
  - [ ] ESLint em vez de Oxlint
  - [ ] Override do TypeScript no `openapi-typescript`
- [ ] README reescrito como um case: problema, arquitetura, prints das telas e decisões técnicas
- [ ] Diagrama de arquitetura (C4 ou Mermaid)
- [ ] Pendências de segurança:
  - [ ] Esconder o header `X-Powered-By` em produção (`expose_php = Off`)
  - [ ] Restrição `CHECK (email <> '')` na tabela `users`
  - [ ] Revisar a política de senhas conforme o NIST SP 800-63B (`NotCompromisedPassword`)
- [ ] Deploy da demo online:
  - [ ] Build de produção do frontend servido pelo FrankenPHP (mesma origem)
  - [ ] Secrets do Symfony para credenciais de produção
  - [ ] HTTPS e usuário demo divulgado no README
- [ ] Release `v1.0` no GitHub

**Critério de conclusão:** qualquer pessoa consegue acessar a demo, entrar com o usuário demo e usar a loja e o painel.

---

## Marco 2: estoque, carrinho e checkout

**Objetivo:** garantir, e provar, que o sistema nunca vende mais do que tem em estoque.

- [ ] Módulo `Inventory` com quantidade disponível e reservada por produto
- [ ] Módulo `Cart`: adicionar, remover e alterar quantidades
- [ ] Checkout com reserva de estoque síncrona, em transação, com `SELECT ... FOR UPDATE`
- [ ] Expiração automática de reservas não pagas
- [ ] Cupons de desconto
- [ ] Cálculo de frete simulado
- [ ] Header `Idempotency-Key` no checkout para evitar pedidos duplicados
- [ ] Teste de carga com k6: 200 compras simultâneas de um produto com 10 unidades
- [ ] Resultado do teste de carga documentado no README
- [ ] Telas de página do produto, carrinho e checkout

**Critério de conclusão:** o teste de carga resulta em exatamente 10 vendas, de forma reproduzível.

---

## Marco 3: pedidos e mensageria

**Objetivo:** demonstrar consistência entre sistemas e processamento assíncrono confiável.

- [ ] Módulo `Ordering` com itens que guardam preço e nome no momento da compra
- [ ] Ciclo de vida do pedido com o Workflow Component: pendente, pago, enviado, cancelado e reembolsado
- [ ] Módulo `Payment` com interface `PaymentGateway` e adaptador simulado
- [ ] Adaptador opcional para o sandbox da Stripe
- [ ] RabbitMQ com Symfony Messenger para os eventos de pedido
- [ ] Consumidores para e-mail (Mailpit em desenvolvimento) e confirmação ou liberação de estoque
- [ ] Outbox Pattern para não perder eventos entre o banco e a fila
- [ ] Idempotência nos consumidores
- [ ] Retries e dead letter queue
- [ ] Telas de "Meus pedidos" para o cliente e gestão de pedidos no painel

**Critério de conclusão:** derrubar o RabbitMQ durante a criação de um pedido não perde nenhum evento, e o comportamento está documentado.

---

## Marco 4: Redis

**Objetivo:** performance e proteção contra abuso.

- [ ] Rate limiting no login, no cadastro e na renovação de token
- [ ] Cache do catálogo com invalidação ao editar produtos
- [ ] Locks distribuídos com o componente Lock
- [ ] Limpeza agendada de refresh tokens expirados com o Symfony Scheduler

**Critério de conclusão:** tentativas repetidas de login recebem `429 Too Many Requests`, e o ganho do cache está medido no README.

---

## Evoluções opcionais

Itens que agregam valor, mas não são prioridade:

- [ ] Categorias, variações (tamanho e cor) e imagens de produto
- [ ] API GraphQL para a vitrine, com DataLoader e limites de profundidade e complexidade
- [ ] PHPStan no nível máximo
- [ ] Schema `Problem` documentado no OpenAPI
- [ ] Observabilidade com OpenTelemetry e Grafana, com correlation ID entre API e workers
- [ ] Testes E2E com Playwright
- [ ] Content Security Policy
- [ ] Testes de mutação com Infection
- [ ] Deptrac para validar as fronteiras entre os módulos
