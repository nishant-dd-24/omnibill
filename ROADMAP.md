# OmniBill Development Roadmap

## Project Vision
OmniBill is a production-grade, distributed-ready, multi-tenant SaaS billing platform built as a modular monolith on Laravel. This roadmap is the master execution plan for building the system from scratch to version 1.0, ensuring full alignment with the finalized Architecture Blueprint, Software Architecture Document (SAD), High-Level Design (HLD), Low-Level Design (LLD), and Software Requirements Specification (SRS).

## Current Status
- [x] Architecture Blueprint Finalized
- [x] Software Architecture Document (SAD) Finalized
- [x] High-Level Design (HLD) Finalized
- [x] Low-Level Design (LLD) Finalized
- [x] Software Requirements Specification (SRS) Finalized
- [ ] Implementation Started

## Development Philosophy
- **Architecture-First:** Follow the constraints laid out in the documentation. Do not invent functionality or deviate from the modular monolith boundaries.
- **Tenant Isolation:** Tenancy is enforced at two layers (Global Scopes + RLS). No cross-tenant access.
- **Async by Default:** Billing work, integrations, and Side Effects are asynchronous.
- **Data Integrity:** Idempotent endpoints, soft deletes for financial records, and strict immutable invoices.
- **Production-Ready:** Every merged PR must satisfy the Definition of Done (tested, documented, linted).

---

## Progress Legend

- [ ] Not Started
- [~] In Progress
- [x] Completed

---

## Phase 0 — Project Initialization

- [x] Create Laravel 13 project
- [x] Configure repository structure and `Modules/` namespace
- [x] Configure coding standards (Pint)
- [x] Configure PHPStan (Strict rules)
- [x] Configure Pest (Testing framework)
- [x] Configure Docker / Sail environment
- [x] Configure PostgreSQL
- [x] Configure Redis
- [x] Configure Mailhog / Mail trap for local testing
- [x] Configure Object Storage (MinIO for local dev)
- [x] Configure CI/CD pipelines (GitHub Actions)
- [x] Configure local development environment documentation

---

## Phase 1 — Foundation

- [~] Authentication (Sanctum setup) - *Partially complete: package installed but config/User model incomplete*
- [ ] Authorization (Policies base configuration)
- [ ] RBAC (Base Role/Permission definitions)
- [ ] Tenant Resolution (Middleware for `tenant_id` resolution)
- [ ] Base Models (TenantScoped trait, UUIDv7 primary keys)
- [ ] UUID Strategy (Implement generation logic for models)
- [ ] Configuration (Tenant-specific settings architecture)
- [ ] Logging (Structured JSON logger configuration)
- [ ] Error Handling (Global exception handler, consistent JSON output)
- [ ] Validation (Base FormRequests)
- [ ] Global Middleware (Rate Limiter, Correlation ID generator)
- [ ] API Response Standards (API Resources envelope)
- [ ] Testing Infrastructure (Tenant-aware test helpers, Stripe fake/sandbox)

---

## Phase 2 — Core Platform

### Tenant Module
- [ ] Database (Migrations for `tenants`, `tenant_settings`, `tenant_plan_assignments`)
- [ ] Models (`Tenant`, `TenantSettings`, `TenantPlanAssignment`)
- [ ] Services (Tenant lifecycle state machine, settings updates)
- [ ] Controllers (CRUD for tenants)
- [ ] Policies (Tenant access control)
- [ ] Events (`TenantActivated`, `TenantSuspended`, `TenantCancelled`)
- [ ] Jobs (Data retention purge scheduler)
- [ ] Tests (Unit, Feature, Arch)
- [ ] Documentation updated

### Identity & Access Module (User, Role, Permission)
- [ ] Database (Users, Tokens, UserRoles)
- [ ] Models (`User`, `Token`, `UserRole`)
- [ ] Services (User creation, token issuance/revocation, role mapping)
- [ ] Controllers (Auth endpoints, User CRUD)
- [ ] Policies (User management scope)
- [ ] Events (`EmailVerified`)
- [ ] Jobs (RevokeAllTokensForTenant)
- [ ] Tests (Unit, Feature, Arch)
- [ ] Documentation updated

### Customer Module
- [ ] Database (Customers, PaymentMethods)
- [ ] Models (`Customer`, `PaymentMethod` — tokenized only)
- [ ] Services (Customer management, payment method references)
- [ ] Controllers (Customer CRUD)
- [ ] Policies (Customer access)
- [ ] Events (`CustomerCreated`, `CustomerPaymentMethodAttached`)
- [ ] Tests (Unit, Feature, Arch)
- [ ] Documentation updated

### Catalog & Settings (Plan, Pricing)
- [ ] Database (Plans, Prices)
- [ ] Models (`Plan`, `Price`)
- [ ] Services (Plan management, Pricing calculation, Feature Flags)
- [ ] Controllers (Read-only catalog for tenants, management for Super Admin)
- [ ] Tests (Unit, Feature, Arch)
- [ ] Documentation updated

### Cross-Cutting Platform Utilities
- [ ] Audit Log (Append-only writer for all state changes)
- [ ] Notifications base scaffolding

---

## Phase 3 — Billing Engine

### Subscriptions
- [ ] Database (Subscriptions, SubscriptionItems)
- [ ] Models (`Subscription`, `SubscriptionItem`)
- [ ] Services (Creation, Upgrades/Downgrades, Cancellation, Proration, State Machine)
- [ ] Events (`SubscriptionActivated`, `SubscriptionCancelled`, `SubscriptionPastDue`)
- [ ] Jobs (Renewal evaluations)
- [ ] Tests (Unit, Feature with complex proration logic)

### Invoices
- [ ] Database (Invoices, InvoiceLineItems, CreditNotes)
- [ ] Models (`Invoice`, `InvoiceLineItem` — strict immutability after Open)
- [ ] Services (Draft generation, Finalization, Credit Note logic, Tax application)
- [ ] Events (`InvoiceFinalized`, `InvoicePaid`, `InvoicePaymentFailed`)
- [ ] Tests (Unit, Feature, Immutability validation)

### Payments
- [ ] Database (Payments, PaymentAttempts)
- [ ] Models (`Payment`, `PaymentAttempt`)
- [ ] Services (Transaction tracking, State machine driven by Webhooks)
- [ ] Events (`PaymentSucceeded`, `PaymentFailed`, `PaymentRefunded`)
- [ ] Jobs (Dunning and Retry logic orchestration)
- [ ] Tests (Unit, Feature)

---

## Phase 4 — Integrations

### Stripe & Payments
- [ ] Stripe SDK Integration (Cashier wrapped within M-SUB Application layer)
- [ ] Checkout / Payment Intent Initiation
- [ ] Webhook Processing (Inbound, signature verification, persist-then-process)

### System Workflows
- [ ] Outbound Webhooks (Deliver Integration Events to Tenant URLs)
- [ ] Email (Transactional delivery via Notification module)
- [ ] PDF Generation (Invoice rendering and export)
- [ ] Object Storage (Save generated PDFs to S3/MinIO)

### Asynchronous Machinery
- [ ] Background Jobs (Base `TenantAwareJob` integration)
- [ ] Queues (Configure `billing-critical`, `invoicing`, `notifications`, `webhooks`)
- [ ] Outbox Pattern (Dispatcher daemon for `outbox_events` -> Queue)
- [ ] Idempotency (Redis storage for client-supplied `Idempotency-Key`)

---

## Phase 5 — Public API

- [ ] REST API endpoints for all exposed Application Services
- [ ] Pagination (Cursor-based standard)
- [ ] Filtering & Sorting
- [ ] Searching endpoints
- [ ] API Versioning (`/api/v1/`)
- [ ] Rate Limiting (Tier-based, per-tenant Redis sliding window)
- [ ] OpenAPI Documentation (Swagger/Redoc specification generation)
- [ ] SDK Preparation (Generate generic client libraries)

---

## Phase 6 — Operations

- [ ] Monitoring (Laravel Pulse configuration in production)
- [ ] Metrics (Custom business metrics: MRR, Active Subscriptions)
- [ ] Health Checks (DB, Redis, Stripe Connectivity)
- [ ] Logging (Ensure full structured coverage)
- [ ] Tracing (Correlation ID threading through queues/logs)
- [ ] Caching (Cache-aside optimization for Plan catalog and Settings)
- [ ] Backups (PostgreSQL dumping and S3 retention)
- [ ] Security Review (Test isolation layers, RLS config, data encryption)
- [ ] Performance Testing (P95 < 300ms reads, < 800ms writes)
- [ ] Load Testing
- [ ] Disaster Recovery (Restore tests)
- [ ] Deployment Validation

---

## Phase 7 — Production Readiness

- [ ] Documentation Review
- [ ] Architecture Review (Ensure no deviations from Blueprint/SAD)
- [ ] Dependency Audit (Composer, NPM vulnerabilities)
- [ ] Accessibility (If a dashboard UI is included)
- [ ] Final QA
- [ ] Release Checklist
- [ ] Production Deployment
- [ ] Version 1.0 Release

---

## Long-Term Roadmap (Post v1)

- [ ] Usage-based / Metered billing
- [ ] Multiple payment providers (Adyen, Braintree)
- [ ] Multi-currency support (Currency conversion engines)
- [ ] Marketplace billing / Sub-tenant hierarchies
- [ ] Revenue recognition (RevRec)
- [ ] Advanced analytics and reporting
- [ ] Customer Portal (Hosted invoice management for end-users)
- [ ] Mobile API
- [ ] GraphQL API layer
- [ ] Plugin System (Custom tenant integrations)

---

## Implementation Order

To respect architectural dependencies, development **MUST** occur in the following order:

1. **Foundation** (Infrastructure, Logging, Tenancy)
2. **↓**
3. **Tenant** (Lifecycle, Settings)
4. **↓**
5. **Authentication** (Sanctum, Identity)
6. **↓**
7. **RBAC** (Roles, Policies)
8. **↓**
9. **Customers** (CRM Base)
10. **↓**
11. **Plans** (Catalog, Pricing)
12. **↓**
13. **Subscriptions** (Engine, Lifecycle)
14. **↓**
15. **Invoices** (Generation, Immutability)
16. **↓**
17. **Payments** (Local tracking)
18. **↓**
19. **Stripe** (Webhooks, External Integration)
20. **↓**
21. **Notifications** (Emails, Outbound webhooks)
22. **↓**
23. **Public API** (Endpoints, Docs)
24. **↓**
25. **Production** (Pulse, Scaling, QA)

---

## Development Workflow

For every feature or module implemented, developers must adhere to this cycle:

1. **Read SRS** (Understand the *what* and the business rules)
2. **↓**
3. **Review HLD** (Understand the logical boundaries and communication)
4. **↓**
5. **Review LLD** (Understand the file placement, structure, and database strategy)
6. **↓**
7. **Implement** (Code the feature)
8. **↓**
9. **Write Tests** (Unit, Feature, Arch testing)
10. **↓**
11. **Run Static Analysis** (PHPStan, Pint formatting)
12. **↓**
13. **Commit** (Logical, descriptive commits)
14. **↓**
15. **Update Documentation** (If any minor implementation details emerge)

---

## Definition of Done

A feature, module, or phase is considered "Done" only if ALL the following criteria are met:

- [ ] Implementation is entirely finished and satisfies SRS requirements.
- [ ] Unit tests are written and pass.
- [ ] Feature tests covering edge cases pass.
- [ ] Static analysis (PHPStan strict) reports 0 errors.
- [ ] Code formatting (Pint) passes perfectly.
- [ ] Documentation is updated (Swagger/OpenAPI for endpoints).
- [ ] No `// TODO` or `// FIXME` placeholders remain in the codebase for the feature.
- [ ] A logical, well-structured Git commit is created.
- [ ] Architecture tests pass, verifying module boundary rules are respected.

---

## Coding Standards

- **Correctness Over Latency:** We do not sacrifice auditable billing data for a few milliseconds of response time.
- **Strict Tenancy:** The Global Scope and RLS apply to every query. Bypasses are only for explicit, audited Super Admin operations.
- **Avoid Cleverness:** Boring, readable, predictable code wins. Keep controllers thin; Application Services handle orchestration; Domain Services handle pure logic.
- **Event-Driven:** Do not intertwine module logic. Use the Outbox pattern to dispatch Domain Events.
- **Idempotency:** Jobs and webhooks will fail and retry. Everything must be safe to execute multiple times.
- **Money Values:** Do not perform math on bare floats. Use the `Money` value object consistently.
- **UUIDv7:** Standardize on time-ordered UUIDs for all primary keys.

---
*End of Roadmap. Let's get to work.*
