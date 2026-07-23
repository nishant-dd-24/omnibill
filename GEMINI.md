# GEMINI.md

# OmniBill AI Engineering Guide

Version: 1.0

This document defines the engineering standards, architectural constraints, development workflow, and coding conventions for all AI agents working on the OmniBill project.

---

# Project Overview

OmniBill is a production-grade, distributed-ready, multi-tenant SaaS billing platform.

The project is designed as a **modular monolith** built using Laravel 13.

The objective is to build an enterprise-quality billing platform emphasizing:

- correctness
- maintainability
- auditability
- security
- scalability

over premature optimization.

---

# Source of Truth

When making decisions, documents MUST be consulted in the following order.

1. Architecture Blueprint
2. Software Architecture Document (SAD)
3. High Level Design (HLD)
4. Low Level Design (LLD)
5. Software Requirements Specification (SRS)

If documents disagree,

Blueprint always wins.

Never invent architecture that contradicts these documents.

---

# Engineering Principles

Always prioritize

1. Correctness
2. Readability
3. Maintainability
4. Security
5. Performance

Never reverse this order.

---

# Architectural Rules

The following rules are absolute.

## Modular Monolith

Never introduce microservices.

Never split modules.

Never introduce network boundaries between modules.

Modules communicate only through

- Application Services
- Domain Events

Never through direct model access.

---

## Multi Tenancy

Tenant isolation is non-negotiable.

Every tenant-owned query MUST be tenant scoped.

Never disable tenant scope except through the officially documented bypass mechanism.

Never write code that could expose another tenant's data.

---

## Billing

Billing correctness is more important than latency.

Never trust synchronous Stripe responses.

Payment state changes ONLY after verified webhook processing.

---

## Transactions

Never call

- Stripe
- Email
- PDF
- HTTP APIs

inside a database transaction.

Use the Outbox pattern.

---

## Async

Heavy work never belongs in HTTP requests.

Always use queues for

- Billing
- Email
- PDFs
- Notifications
- Webhooks

---

## Money

Money must never use float.

Always use the project's Money abstraction.

---

## UUID Strategy

Use UUIDv7 consistently.

Never introduce integer IDs.

Never introduce ULIDs.

---

Before implementing any feature:

1. Read the relevant section of the Blueprint.
2. Verify architectural constraints in the SAD.
3. Review module interactions in the HLD.
4. Follow implementation guidance in the LLD.
5. Ensure the implementation satisfies the SRS.
6. Write tests.
7. Update documentation if architectural behavior changes.

---


# Coding Philosophy

Write boring code.

Readable code.

Predictable code.

Avoid cleverness.

Future maintainers should immediately understand the code.

---

# Laravel Guidelines

Follow Laravel best practices.

Prefer framework conventions.

Do not fight the framework.

Use dependency injection.

Use constructor injection.

Use Form Requests.

Use Policies.

Use Events.

Use Jobs.

Use Resources.

Keep Controllers thin.

Keep Services focused.

---

# Module Rules

Each module owns its own

- Domain
- Application
- Infrastructure
- HTTP

Never access another module's internals.

---

# Domain Rules

Business logic belongs inside Domain Services.

Controllers never contain business logic.

Jobs never contain business logic.

Repositories never contain business logic.

---

# Controllers

Controllers should

- validate
- authorize
- delegate
- return response

Nothing else.

---

# Application Services

Application Services orchestrate use cases.

They coordinate

- repositories
- domain services
- events
- jobs

They should not become God classes.

---

# Repositories

Repositories perform persistence only.

No business decisions.

---

# Events

Prefer events over tight coupling.

Domain Events

- internal

Integration Events

- external

Never confuse the two.

---

# Queues

Jobs must be

- idempotent
- retry safe

Never assume exactly-once execution.

---

# Database

Favor additive migrations.

Never create destructive migrations without explicit approval.

Avoid breaking production compatibility.

---

# APIs

Follow REST conventions.

Maintain backwards compatibility.

Breaking changes require versioning.

---

# Security

Never expose secrets.

Never log secrets.

Never expose stack traces.

Never trust client supplied prices.

Never trust client supplied payment state.

---

# Logging

Logs should be structured.

Include

- correlation id
- tenant id
- request id

whenever applicable.

---

# Testing

Every feature should include

- Unit Tests
- Feature Tests

Critical billing logic should have integration tests.

Bug fixes should include regression tests.

---

# Documentation

When architecture changes,

update

- Blueprint
- SAD
- HLD
- LLD
- SRS

when applicable.

Documentation is part of the implementation.

docs
├── blueprint
│   └── OmniBill_Architecture_Blueprint.md
├── hld
│   └── OmniBill_HLD.md
├── lld
│   └── OmniBill_LLD.md
├── sad
│   └── OmniBill_SAD.md
└── srs
    └── OmniBill_SRS.md


---

# Code Quality

Prefer

small functions

small classes

meaningful names

clear responsibilities

Avoid

large methods

deep nesting

duplicated logic

magic values

---

# Git

Create logical commits.

Commit messages should describe

WHAT changed

not

HOW it changed.

---

# AI Instructions

When implementing code

DO NOT

- rewrite unrelated files
- reformat entire project
- rename symbols unnecessarily
- change architecture
- introduce new dependencies without justification
- generate placeholder implementations

When uncertain,

follow existing project conventions.

When architecture decisions are required,

consult the documentation before making changes.

Never invent architecture.

---

# Response Style

When asked to implement something

1. Understand the request.
2. Identify affected modules.
3. Explain the approach briefly.
4. Implement.
5. Keep changes minimal.
6. Preserve backward compatibility.

---

# Project Goal

The objective is not merely to build software.

The objective is to build an enterprise-grade billing platform whose codebase remains maintainable for years.

Every change should move the project closer to that goal.
