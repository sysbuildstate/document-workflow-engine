# Enterprise Document Workflow & State Machine Engine

## System Overview
A backend compliance engine built with PHP 8.4 and Laravel that manages legal document lifecycles through a finite state machine (FSM) and role-based access control (RBAC).

## Strict Corporate Business Rules
1. **Unidirectional State Machine:** Documents must strictly transition through: `Draft` -> `Pending Legal Review` -> `Manager Approved` -> `Executed`. Skipping steps or moving backwards is mathematically blocked at the database and model layer.
2. **Granular Role-Based Access (RBAC):**
    * Only users with the `Legal_Compliance` role can transition a document out of `Pending Legal Review`.
    * Only users with the `Manager` role can transition a document to `Executed`.
3. **Immutability Enforcement:** Once a document reaches the `Executed` state, it is permanently locked. Any subsequent `PUT`, `PATCH`, or `DELETE` requests will be intercepted by custom Laravel Policies and return an immediate `403 Forbidden`.
4. **Audit Trail Logging:** Every state transition must automatically fire a Laravel Event that records a timestamped, tamper-proof record in a dedicated `document_history` table.
