# Authxolote - Authentication and Role Management System

Authxolote is a robust API built with Laravel 12 that provides an out-of-the-box authentication, role, and permission management system (RBAC). It uses Laravel Passport for OAuth2 token issuance and a dynamic system called "Flow" for resource management.

## 🚀 Prerequisites

- PHP >= 8.4
- Composer
- MySQL / PostgreSQL / SQLite
- Node.js & NPM (optional, for asset compilation if necessary)

## 🛠️ Installation and Configuration

Follow these steps to get the project up and running:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/AxoloteSource/Authxolote
   cd Authxolote
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Configure the environment:**
   Copy the example file and configure your database credentials and root user information.
   ```bash
   cp .env.example .env
   ```
   In your `.env` file, make sure to set the following variables for the initial seeder:
   - `ROOT_USER_NAME`: The name of the root user.
   - `ROOT_USER_EMAIL`: The email address of the root user.
   - `ROOT_USER_PASSWORD`: The password for the root user.

   **Frontend environment variables (Backoffice):**
   - `VITE_MENU_SLUG`: The slug of the menu rendered in the Backoffice via the `MenuV2` component. It must match the `slug` of an existing menu in the database (e.g. `auth`).

   **OTP delivery environment variables:**
   OTP codes are delivered asynchronously through the external Messages service. Configure:
   - `OTP_DELIVERY_DRIVER`: Delivery driver. Use `messages` (default) to send through the Messages service, or `fake` to only record the sent codes (useful for local development and testing).
   - `MESSAGES_BASE_URL`: Base URL of the Messages service. Required when using the `messages` driver.
   - `MESSAGES_OTP_ENDPOINT`: Path of the endpoint that receives the messages. Defaults to `/messages`.
   - `MESSAGES_TOKEN`: Bearer token used to authenticate against the Messages service.
   - `MESSAGES_TIMEOUT`: HTTP timeout in seconds for requests to the Messages service. Defaults to `10`.

   If `MESSAGES_BASE_URL` is empty, OTP delivery fails with the message `The messages service is not configured.`.

   Generate the application key:
   ```bash
   php artisan key:generate
   ```

4. **Migrations and Seeders:**
   Run migrations to create the necessary tables, including Passport tables.
   ```bash
   php artisan migrate --seed
   ```

5. **Install Laravel Passport:**
   Generate the encryption keys required for personal access and client tokens.
   ```bash
   php artisan passport:client --personal --name="Laravel Personal Access Client"
   ```

6. **Start the server:**
   ```bash
   php artisan serve
   ```

---

## 🏗️ Architecture and Models

### Main Models
- **User**: Represents system users. Each user is assigned a `role_id`.
- **Role**: Defines available roles (e.g., Root, Admin, User).
- **Action**: Represents a specific permission or action within the system (e.g., `auth.role.index`).
- **Setting**: Dynamic configuration system.
- **SettingValueType**: Defines value types for configurations.

### `IsAllow` Middleware
The system uses a custom middleware (`AxoloteSource\Logics\Middleware\IsAllow`) to verify if the authenticated user has the necessary permission (`Action`) to execute a specific route.

---

## 📡 API Documentation

All APIs are located under the `/api/v1` prefix.

### 🔐 Authentication (`routes/modules/auth.php`)

| Method | Endpoint | Description | Parameters | Response | Requires Auth |
|---|---|---|---|---|---------------|
| POST | `/login` | Logs in and returns a Passport token. | `email`, `password` | User data + `access_token` | No            |
| POST | `/register` | Registers a new user. | `name`, `email`, `password`, `password_confirmation`, `role_key` | User data | No            |
| POST | `/logout` | Revokes the current access token. | None | `{"message": "Logged out"}` | Yes           |
| POST | `/me` | Gets the authenticated user's information. | None | User data + `role` | Yes           |
| POST | `/is-allowed` | Checks if the user has specific permissions. | `actions` (array) | `{"allowed": bool}` | Yes           |
| POST | `/recovery-password` | Initiates password recovery. | `email` | `token`, `expires_at`, `code_debug`* | No            |
| POST | `/change-password` | Initiates password change. | None | `token`, `expires_at`, `code_debug`* | Yes           |
| POST | `/reset-password` | Resets password using OTP. | `token`, `otp_code`, `password`, `password_confirmation` | Success message | No            |

\* `code_debug` is only returned if `APP_ENV` is not `production`.

### 👥 Role Management (`routes/modules/roles.php`)

Requires authentication via Bearer Token.

| Method | Endpoint | Description | Parameters | Required Permission |
|---|---|---|---|---|
| GET | `/roles` | Lists all roles. | `page`, `limit`, `order`, `search`, `filters` | `auth.role.index` |
| POST | `/roles/attach/actions` | Assigns actions/permissions to roles. | `roles` (array of `{id: role_id, actions: [ids]}`) | `auth.role.attach.actions` |
| GET | `/roles/{id}/actions` | Lists actions associated with a role. | `id` (path), `page`, `limit`, `order`, `search` | `auth.role.actions.index` |
| PUT | `/roles/{id}/actions/{actionId}` | Updates a specific action for a role. | `id` (path), `actionId` (path), `active` (bool) | `auth.role.actions.update` |

### 🌊 Flow API (`routes/modules/flow.php`)

Flow is a dynamic system that allows performing CRUD operations on registered models in a generic way.

**Currently supported models:** `actions` (more can be added in `FlowIndexLogic.php`).

| Method | Endpoint | Description | Parameters | Required Permission |
|---|---|---|---|---|
| GET | `/{model}` | Lists model records with search and pagination support. | `page`, `limit`, `order`, `search`, `filters` | `auth.flow.index` |
| POST | `/{model}` | Creates a new record in the model. | Model-specific fields | `auth.flow.store` |
| GET | `/{model}/{id}` | Gets a specific record by ID. | `id` (path) | `auth.flow.show` |
| PUT | `/{model}/{id}` | Updates an existing record. | `id` (path) + Fields to update | `auth.flow.update` |
| DELETE | `/{model}/{id}` | Deletes a record (supports SoftDelete). | `id` (path) | `auth.flow.delete` |

---

## 🛠️ Technologies Used

- **Laravel 12**: Core framework.
- **Laravel Passport**: OAuth2 authentication.
- **Spatie Laravel Data**: For structured data transfer (DTOs).
- **Laravel Idea**: Support for IDE development.

## 📦 SDK
You can use the official SDK for faster integration: [Authxolote SDK](https://github.com/AxoloteSource/authxolote-sdk)

---

## 📄 License
This project is open-source software licensed under the [MIT license](https://opensource.org/licenses/MIT).
