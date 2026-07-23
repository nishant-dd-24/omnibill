# OmniBill — Software Requirements Specification (SRS)

**Conformance:** IEEE 29148-2018  

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Product Overview](#2-product-overview)
3. [Overall Description](#3-overall-description)
4. [Functional Requirements](#4-functional-requirements)
5. [Non-Functional Requirements](#5-non-functional-requirements)
6. [Business Rules](#6-business-rules)
7. [External Interfaces](#7-external-interfaces)
8. [Data Requirements](#8-data-requirements)
9. [Use Cases](#9-use-cases)
10. [Acceptance Criteria](#10-acceptance-criteria)
11. [Requirements Traceability Matrix](#11-requirements-traceability-matrix)
12. [Appendix](#12-appendix)

---

## 1. Introduction

### 1.1 Purpose
The purpose of this Software Requirements Specification (SRS) is to formally document the functional and non-functional requirements, business rules, and use cases for the OmniBill platform. This document defines *what* the system MUST do, providing a canonical reference for product owners, business analysts, developers, and QA engineers.

### 1.2 Scope
OmniBill is a multi-tenant SaaS billing platform designed to allow businesses to plug in subscription billing, invoicing, and payment processing without building custom machinery. The scope of this document encompasses tenant lifecycle management, customer and subscription management, invoice generation, payment processing via Stripe, and integration/webhook capabilities.

### 1.3 Intended Audience
- **Product Owners & Business Analysts:** To validate that business requirements are captured.
- **Developers & Architects:** To guide implementation in alignment with the architecture.
- **QA & Test Engineers:** To formulate test plans and validate acceptance criteria.
- **Project Managers:** To track feature completeness.

### 1.4 Definitions
Definitions of terms (e.g., Tenant, Plan, Subscription, Invoice) are provided in the [Glossary](#121-glossary) in the Appendix.

### 1.5 References
1. *OmniBill Architecture Blueprint* (Canonical architectural source)
2. *OmniBill Software Architecture Document (SAD)*
3. *OmniBill High-Level Design (HLD)*
4. *OmniBill Low-Level Design (LLD)*

### 1.6 Document Overview
The remainder of this document outlines the product perspective, detailed functional and non-functional requirements, business rules, data models, and use cases required for the delivery of OmniBill.

---

## 2. Product Overview

### 2.1 Product Perspective
OmniBill operates as a centralized billing layer, sitting between a SaaS company's product (the Tenant) and their payment gateway (Stripe). It provides APIs for the Tenant's systems to manage billing logic.

### 2.2 Product Vision
To provide a production-grade, distributed-ready, multi-tenant SaaS billing platform that prioritizes tenant data isolation, billing correctness, and auditability over operational complexity.

### 2.3 Product Goals
1. Provide a reliable, idempotent REST API for billing operations.
2. Ensure strict multi-tenant data isolation.
3. Decouple business logic from the synchronous HTTP request lifecycle.
4. Guarantee correct financial state through webhook-driven transitions.

### 2.4 Business Context
SaaS companies frequently rebuild billing infrastructure. OmniBill monetizes this pain point by offering a highly reliable, drop-in billing engine that handles dunning, invoicing, and payment state machines.

### 2.5 Stakeholders
- **Tenants:** SaaS businesses using OmniBill to bill their users.
- **Tenant Customers:** End-users subscribing to the Tenant's services.
- **Platform Operators (Super Admins):** The OmniBill internal team managing the platform.

---

## 3. Overall Description

### 3.1 Product Functions
- Tenant onboarding and lifecycle management.
- User authentication and role-based access control.
- Product plan catalog management.
- Customer payment method collection.
- Subscription state machine and dunning enforcement.
- Automated invoice generation and PDF rendering.
- Webhook-driven payment state resolution.
- Transactional email notifications.

### 3.2 User Classes
- **Super Admin:** OmniBill platform administrator. Has cross-tenant visibility for support.
- **Tenant Admin:** Manages a single Tenant's settings, users, and billing operations.
- **Tenant Billing Manager:** Can manage billing configuration but cannot manage users.
- **Tenant User:** Read-only access to billing data and can view only invoices they own (or limited scope).
- **API Consumer:** Machine-to-machine integrations calling the REST API using Tenant tokens.

### 3.3 Operating Environment
OmniBill operates as a cloud-hosted web application exposing a REST API over HTTPS. 

### 3.4 Assumptions
- All monetary transactions are processed through Stripe.
- Clients can safely generate standard UUIDs for idempotency keys.

### 3.5 Dependencies
- Stripe API for payment intent creation and customer synchronization.
- External SMTP/Email provider for transactional notifications.
- Object Storage provider for archiving invoice PDFs.

### 3.6 Constraints
- Multi-currency conversion is out of scope for v1 (single currency per tenant).
- Usage-based/metered billing is out of scope for v1.
- No cross-tenant data merging or reseller hierarchies are supported.

---

## 4. Functional Requirements

### 4.1 Tenant Management
**FR-TEN-001: Register Tenant**
- **Description:** The system SHALL allow a new user to register a Tenant account.
- **Inputs:** Company Name, User Email, Password.
- **Outputs:** Created Tenant in `Pending` state, created User.
- **Business Rules:** Tenant remains pending until email is verified.

**FR-TEN-002: Suspend Tenant**
- **Description:** A Super Admin SHALL be able to suspend a Tenant.
- **Preconditions:** Tenant is Active.
- **Postconditions:** Tenant status changes to `Suspended`. All active tokens for the Tenant's users are revoked.

**FR-TEN-003: Configure Settings**
- **Description:** A Tenant Admin SHALL be able to configure billing currency, locale, and webhook URLs.

### 4.2 Authentication & Authorization
**FR-AUTH-001: User Login**
- **Description:** The system SHALL authenticate users using email and password.
- **Outputs:** Short-lived access token with specified abilities.

**FR-AUTHZ-001: Role Enforcement**
- **Description:** The system SHALL deny access to resources if the user's role lacks permissions (e.g., Tenant User attempting to change settings).

### 4.3 Customer Management
**FR-CUS-001: Create Customer**
- **Description:** The system SHALL allow a Tenant to create a customer profile.
- **Inputs:** Customer Name, Email, External Reference ID.

**FR-CUS-002: Attach Payment Method**
- **Description:** The system SHALL store a tokenized reference to a Stripe payment method against a Customer.
- **Constraints:** Raw card numbers MUST NOT be accepted by the API.

### 4.4 Subscription & Plan Management
**FR-SUB-001: Create Subscription**
- **Description:** The system SHALL allow a Tenant to subscribe a Customer to a Plan.
- **Inputs:** Customer ID, Plan ID, Idempotency Key.
- **Preconditions:** Customer has a valid payment method (if no trial).
- **Postconditions:** Subscription created in `Active` or `Trialing` state.

**FR-SUB-002: Cancel Subscription**
- **Description:** The system SHALL process subscription cancellations.
- **Main Flow:** Marks subscription as `Cancelled` and initiates final prorated invoice generation.

### 4.5 Invoice Management
**FR-INV-001: Generate Draft Invoice**
- **Description:** The system SHALL automatically generate a `Draft` invoice triggered by subscription events (activation, renewal, plan change, cancellation).
- **Outputs:** Invoice record with computed line items.

**FR-INV-002: Finalize Invoice**
- **Description:** The system SHALL transition an invoice from `Draft` to `Open`, locking its line items and generating a PDF.
- **Business Rules:** Line items MUST NOT be altered after finalization (BR-002).

**FR-INV-003: Issue Credit Note**
- **Description:** The system SHALL allow issuing a Credit Note against an `Open` or `Paid` invoice to adjust balances without mutating the original invoice.

### 4.6 Payment Processing
**FR-PAY-001: Record Payment Attempt**
- **Description:** The system SHALL record a `Pending` payment attempt when an invoice is finalized.
- **Inputs:** Invoice ID, Amount.

**FR-PAY-002: Initiate Refund**
- **Description:** A Tenant Admin SHALL be able to initiate a full or partial refund for a `Captured` payment.

### 4.7 Stripe Integration & Webhook Processing
**FR-WHK-001: Ingest Inbound Webhook**
- **Description:** The system SHALL accept and verify inbound webhooks from Stripe.
- **Main Flow:** Verify signature → Persist raw event (unique ID check) → Return HTTP 200 → Dispatch for asynchronous processing.

**FR-WHK-002: Payment State Resolution**
- **Description:** The system SHALL transition Payment status (`Captured`, `Failed`) exclusively based on confirmed Stripe webhooks (`payment_intent.succeeded`, `invoice.payment_failed`).

**FR-WHK-003: Dispatch Outbound Webhook**
- **Description:** The system SHALL send versioned Integration Events to the Tenant's configured webhook URL upon significant business state changes (e.g., `InvoicePaid`).

### 4.8 Notifications
**FR-NOT-001: Transactional Emails**
- **Description:** The system SHALL send transactional emails (receipts, failure alerts, cancellations) to Customers based on Tenant preferences.

### 4.9 Audit & API
**FR-AUD-001: Audit Log**
- **Description:** The system SHALL record an append-only audit entry for any state change to financial or configuration entities.
- **Constraints:** Audit entries MUST survive standard log-retention purges.

**FR-API-001: Idempotency Enforcement**
- **Description:** The API SHALL enforce at-most-once execution for any state-mutating billing endpoint using the client-supplied `Idempotency-Key` header.

---

## 5. Non-Functional Requirements

### 5.1 Performance
- **NFR-PERF-001:** The API SHALL return read-only requests in < 300ms (P95).
- **NFR-PERF-002:** The API SHALL return state-mutating requests in < 800ms (P95), offloading billing side effects to asynchronous queues.

### 5.2 Reliability
- **NFR-REL-001:** Asynchronous jobs MUST retry upon failure with exponential backoff.
- **NFR-REL-002:** Jobs exceeding maximum retries MUST be routed to a dead-letter queue.
- **NFR-REL-003:** An operator MUST be paged if the `billing-critical` dead-letter queue depth is > 0.

### 5.3 Scalability
- **NFR-SCA-001:** Application and worker nodes MUST be entirely stateless, storing sessions and cache in Redis.

### 5.4 Security
- **NFR-SEC-001:** Tenant data MUST be isolated at the query layer (Global Scope) and database layer (PostgreSQL RLS).
- **NFR-SEC-002:** API access MUST be rate-limited per Tenant based on their assigned platform tier.
- **NFR-SEC-003:** All sensitive credentials and API keys MUST be field-level encrypted in the database.

### 5.5 Observability
- **NFR-OBS-001:** All log entries MUST be structured (JSON) and include a `correlation_id` that traces a business workflow across HTTP boundaries and background queues.

---

## 6. Business Rules

- **BR-001 (Multi-Tenancy):** No operation may process or return data belonging to more than one Tenant, unless explicitly requested by a `SUPER_ADMIN` with an audit trail.
- **BR-002 (Invoice Immutability):** Once an Invoice transitions to `Open`, its line items and total amounts are strictly immutable. Corrections require a Credit Note.
- **BR-003 (Payment Truth):** Payment success or failure is determined strictly by inbound Stripe webhooks, never by a synchronous API response.
- **BR-004 (Data Retention):** Financial entities (Subscription, Invoice, Payment) MUST be soft-deleted. Hard deletion occurs only via scheduled jobs after a legally compliant retention window expires.
- **BR-005 (Amount Integrity):** Billing amounts are always computed server-side using the Plan catalog. The system SHALL NOT accept client-supplied arbitrary prices for catalog items.

---

## 7. External Interfaces

### 7.1 User Interfaces
- OmniBill provides a REST API. A frontend Dashboard/SPA consumes this API to provide human-readable interfaces for Tenant Admins and Super Admins.

### 7.2 REST API
- Exposed over HTTPS only.
- Prefixed with `/api/v1/`.
- Uses cursor-based pagination for collections.
- Accepts and returns `application/json`.
- Requires `Authorization: Bearer <token>` for all endpoints (except public webhooks/auth flows).

### 7.3 External Systems
- **Stripe API:** Used to create customers, subscriptions, and payment intents.
- **Email Provider (SMTP/API):** Used to deliver transactional emails.
- **Object Storage (S3-compatible):** Used to store generated PDF invoices.

---

## 8. Data Requirements

### 8.1 Business Entities
1. **Tenant:** The SaaS business. Owns all other business entities.
2. **User:** Individuals who log in to the OmniBill dashboard. Assigned roles within a Tenant.
3. **Customer:** The Tenant's end-users. Owns Payment Methods.
4. **Plan & Price:** The product catalog defining billing intervals and amounts.
5. **Subscription:** A link between a Customer and a Plan. Contains a state machine.
6. **Invoice:** A financial document billing a Customer for a specific period.
7. **Payment:** A transaction attempt to settle an Invoice.
8. **Webhook Event:** A raw log of an inbound event from Stripe.
9. **Notification Log:** An audit record of emails sent to Customers.

### 8.2 Entity Lifecycle & Ownership
- All entities (except global Plans and Super Admins) belong to a single Tenant.
- Entity lifecycles are explicitly modeled as state machines (e.g., Subscriptions transition through `Trialing`, `Active`, `PastDue`, `Cancelled`).

---

## 9. Use Cases

### UC-001: Register Tenant
- **Actors:** Prospective Tenant
- **Preconditions:** None.
- **Main Flow:**
  1. Actor submits company details, email, and password.
  2. System creates Tenant (`Pending`) and User.
  3. System sends verification email.
- **Postconditions:** Actor receives email to verify account.

### UC-002: Create Subscription
- **Actors:** API Consumer / Tenant Admin
- **Preconditions:** Customer exists and has a payment method. Plan exists.
- **Main Flow:**
  1. Actor sends request with Customer ID, Plan ID, and Idempotency Key.
  2. System checks idempotency store.
  3. System calls Stripe API to initiate subscription.
  4. System records `Subscription` locally.
  5. System queues `SubscriptionActivated` event.
- **Alternative Flow (Idempotency Hit):**
  - If key exists, return cached response immediately.
- **Postconditions:** Subscription is created. Background jobs will trigger Invoice generation.

### UC-003: Process Payment Webhook
- **Actors:** Stripe (System)
- **Preconditions:** An Invoice and pending Payment exist.
- **Main Flow:**
  1. Stripe POSTs `payment_intent.succeeded` webhook.
  2. System verifies signature.
  3. System saves raw webhook to database to ensure uniqueness.
  4. System returns 200 OK.
  5. Background worker translates webhook to `PaymentSucceeded` domain event.
  6. System updates Payment state to `Captured` and Invoice to `Paid`.

### UC-004: Suspend Tenant
- **Actors:** Super Admin
- **Preconditions:** Tenant is `Active`.
- **Main Flow:**
  1. Admin selects Tenant and issues suspension command.
  2. System updates Tenant state to `Suspended`.
  3. System queues event to revoke all tokens for that Tenant's users.

---

## 10. Acceptance Criteria

| Feature | Acceptance Criteria |
|---|---|
| **Tenancy** | A user token assigned to Tenant A cannot retrieve an Invoice belonging to Tenant B (returns 404). |
| **Idempotency** | Sending identical POST `/subscriptions` requests with the same `Idempotency-Key` results in only one Stripe subscription being created. |
| **Invoicing** | Attempting to modify line items on an `Open` invoice returns an HTTP 409 Conflict. |
| **Webhooks** | Inbound webhooks with invalid Stripe signatures are rejected with HTTP 400 and logged as security warnings. |
| **Performance** | Listing paginated invoices returns a response in under 300ms (P95) under simulated load. |

---

## 11. Requirements Traceability Matrix

| Business Goal | Functional Requirement | Business Rule | Use Case |
|---|---|---|---|
| Multi-tenant Isolation | FR-AUTHZ-001 | BR-001 | UC-004 |
| Reliable Billing | FR-WHK-002 | BR-003 | UC-003 |
| Correct Financial State | FR-INV-002, FR-API-001 | BR-002, BR-005 | UC-002 |
| Auditable Records | FR-AUD-001 | BR-004 | *Applies to all* |

---

## 12. Appendix

### 12.1 Glossary
- **Tenant:** A B2B customer of OmniBill (the SaaS business).
- **Customer:** The end-user who pays the Tenant.
- **Dunning:** The process of methodically communicating with customers to ensure the collection of accounts receivable (e.g., retrying failed payments).
- **Idempotency:** A property of operations that ensures applying them multiple times has the same effect as applying them once.

### 12.2 Abbreviations
- **API:** Application Programming Interface
- **RBAC:** Role-Based Access Control
- **RLS:** Row-Level Security
- **SaaS:** Software as a Service
- **SPA:** Single Page Application
- **SRS:** Software Requirements Specification

---
*End of Document*
