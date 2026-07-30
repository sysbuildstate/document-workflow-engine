# Document Workflow & State Machine Engine

An enterprise backend compliance engine built with PHP 8.4, Laravel, and SQLite. This system manages legal document lifecycles through a strict finite state machine (FSM) and granular role-based access control (RBAC).

## Stack & Architecture
* PHP 8.4 & Laravel 11
* SQLite Database with Eloquent ORM
* Spatie Laravel Permission (RBAC)
* Pest PHP Feature Testing
* Render Docker Cloud Deployment & GitHub Actions CI/CD

## Installation & Setup

```bash
git clone [https://github.com/sysbuildstate/document-workflow-engine.git](https://github.com/sysbuildstate/document-workflow-engine.git)
cd document-workflow-engine
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate --seed
php artisan serve
```
## Running Automated Tests
```
php artisan test
```
### Step 3: Define AI Assistant Instructions (`agents.md`)
To prevent future AI tools from hallucinating complex architectures or breaking our strict business rules, create a file named `agents.md` in your root folder:

```markdown
# AI Development Instructions & Codebase Conventions

## Project Purpose
This is a Laravel PHP 8.4 backend engine implementing a document lifecycle state machine. Do not suggest frontend frameworks, Tailwind CSS, or SPA architectures.

## Strict Architectural Rules
1. Unidirectional FSM: Documents must strictly transition through `Draft` -> `Pending Legal Review` -> `Manager Approved` -> `Executed`. Never generate code that skips states or moves backward.
2. RBAC Policies: Only users with the `Legal_Compliance` Spatie role can approve legal reviews. Only users with the `Manager` role can execute documents.
3. Immutability: Documents in the `Executed` state are permanently locked.
4. Audit Logging: All state changes must generate a record in the `document_histories` table.

## Commands for Verification
* Run tests: `php artisan test`
* Run database migrations: `php artisan migrate`
