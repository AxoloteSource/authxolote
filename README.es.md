# Authxolote - Sistema de Autenticación y Gestión de Roles

Authxolote es una API robusta construida con Laravel 12 que proporciona un sistema de gestión de autenticación, roles y permisos (RBAC) listo para usar. Utiliza Laravel Passport para la emisión de tokens OAuth2 y un sistema dinámico llamado "Flow" para la gestión de recursos. Es una API sin frontend.

## 🚀 Requisitos Previos

- PHP >= 8.4
- Composer
- MySQL / PostgreSQL / SQLite

## 🛠️ Instalación y Configuración

Sigue estos pasos para poner en marcha el proyecto:

1. **Clonar el repositorio:**
   ```bash
   git clone https://github.com/AxoloteSource/Authxolote
   cd Authxolote
   ```

2. **Instalar dependencias:**
   ```bash
   composer install
   ```

3. **Configurar el entorno:**
   Copia el archivo de ejemplo y configura tus credenciales de base de datos y la información del usuario root.
   ```bash
   cp .env.example .env
   ```
   En tu archivo `.env`, asegúrate de establecer las siguientes variables para el seeder inicial:
   - `ROOT_USER_NAME`: El nombre del usuario root.
   - `ROOT_USER_EMAIL`: El correo electrónico del usuario root.
   - `ROOT_USER_PASSWORD`: La contraseña para el usuario root.

   **Variables de entorno para el envío de OTP:**
   Los códigos OTP se envían de forma asíncrona mediante el servicio externo Messages. Configura:
   - `OTP_DELIVERY_DRIVER`: Driver de envío. Usa `messages` (por defecto) para enviar a través del servicio Messages, o `fake` para solo registrar los códigos enviados (útil para desarrollo local y pruebas).
   - `MESSAGES_BASE_URL`: URL base del servicio Messages. Es obligatoria cuando se usa el driver `messages`.
   - `MESSAGES_OTP_ENDPOINT`: Ruta del endpoint que recibe los mensajes. Por defecto `/messages`.
   - `MESSAGES_TOKEN`: Token Bearer utilizado para autenticarse contra el servicio Messages.
   - `MESSAGES_TIMEOUT`: Tiempo de espera HTTP en segundos para las peticiones al servicio Messages. Por defecto `10`.

   Si `MESSAGES_BASE_URL` está vacío, el envío de OTP falla con el mensaje `The messages service is not configured.`.

   Genera la clave de la aplicación:
   ```bash
   php artisan key:generate
   ```

4. **Migraciones y Seeders:**
   Ejecuta las migraciones para crear las tablas necesarias, incluyendo las de Passport.
   ```bash
   php artisan migrate --seed
   ```

5. **Instalar Laravel Passport:**
   Genera las claves de cifrado necesarias para los tokens de acceso personal y clientes.
   ```bash
   php artisan passport:client --personal --name="Laravel Personal Access Client"
   ```

6. **Iniciar el servidor:**
   ```bash
   php artisan serve
   ```

---

## 🏗️ Arquitectura y Modelos

### Modelos Principales
- **User**: Representa a los usuarios del sistema. Cada usuario tiene asignado un `role_id`.
- **Role**: Define los roles disponibles (ej. Root, Admin, User).
- **Action**: Representa un permiso o acción específica dentro del sistema (ej. `auth.role.index`).
- **Setting**: Sistema de configuración dinámica.
- **SettingValueType**: Define los tipos de valores para las configuraciones.

### Middleware `IsAllow`
El sistema utiliza un middleware personalizado (`AxoloteSource\Logics\Middleware\IsAllow`) para verificar si el usuario autenticado tiene el permiso (`Action`) necesario para ejecutar una ruta específica.

---

## 📡 Documentación de APIs

Todas las APIs se encuentran bajo el prefijo `/api/v1`.

### 🔐 Autenticación (`routes/modules/auth.php`)

| Método | Endpoint | Descripción | Parámetros | Respuesta | Requiere Auth |
|---|---|---|---|---|---------------|
| POST | `/login` | Inicia sesión y devuelve un token de Passport. | `email`, `password` | Datos del usuario + `access_token` | No            |
| POST | `/validate-credentials` | Valida credenciales sin emitir un token de Passport. Requiere la acción `auth.validate-credentials`. | `email`, `password` | `{"valid": bool}` | Sí            |
| POST | `/register` | Registra un nuevo usuario. | `name`, `email`, `password`, `password_confirmation`, `role_key` | Datos del usuario | No            |
| POST | `/logout` | Revoca el token de acceso actual. | Ninguno | `{"message": "Logged out"}` | Sí            |
| POST | `/me` | Obtiene la información del usuario autenticado. | Ninguno | Datos del usuario + `role` | Sí            |
| POST | `/is-allowed` | Verifica si el usuario tiene permisos específicos. | `actions` (array) | `{"allowed": bool}` | Sí            |
| POST | `/recovery-password` | Inicia la recuperación de contraseña. | `email` | `token`, `expires_at`, `code_debug`* | No            |
| POST | `/change-password` | Inicia el cambio de contraseña. | Ninguno | `token`, `expires_at`, `code_debug`* | Sí            |
| POST | `/reset-password` | Restablece la contraseña usando el OTP. | `token`, `otp_code`, `password`, `password_confirmation` | Mensaje de éxito | No            |

\* `code_debug` solo se devuelve si `APP_ENV` no es `production`.

### 👥 Gestión de Roles (`routes/modules/roles.php`)

Requiere autenticación mediante Bearer Token.

| Método | Endpoint | Descripción | Parámetros | Permiso Requerido |
|---|---|---|---|---|
| GET | `/roles` | Lista todos los roles. | `page`, `limit`, `order`, `search`, `filters` | `auth.role.index` |
| POST | `/roles/attach/actions` | Asigna acciones/permisos a roles. | `roles` (array de `{id: role_id, actions: [ids]}`) | `auth.role.attach.actions` |
| GET | `/roles/{id}/actions` | Lista las acciones asociadas a un rol. | `id` (ruta), `page`, `limit`, `order`, `search` | `auth.role.actions.index` |
| PUT | `/roles/{id}/actions/{actionId}` | Actualiza una acción específica de un rol. | `id` (ruta), `actionId` (ruta), `active` (bool) | `auth.role.actions.update` |

### 🌊 Flow API (`routes/modules/flow.php`)

Flow es un sistema dinámico que permite realizar operaciones CRUD sobre modelos registrados de forma genérica.

**Modelos soportados actualmente:** `actions` (pueden añadirse más en `FlowIndexLogic.php`).

| Método | Endpoint | Descripción | Parámetros | Permiso Requerido |
|---|---|---|---|---|
| GET | `/{model}` | Lista registros del modelo con soporte para búsqueda y paginación. | `page`, `limit`, `order`, `search`, `filters` | `auth.flow.index` |
| POST | `/{model}` | Crea un nuevo registro en el modelo. | Campos específicos del modelo | `auth.flow.store` |
| GET | `/{model}/{id}` | Obtiene un registro específico por ID. | `id` (ruta) | `auth.flow.show` |
| PUT | `/{model}/{id}` | Actualiza un registro existente. | `id` (ruta) + campos a actualizar | `auth.flow.update` |
| DELETE | `/{model}/{id}` | Elimina un registro (soporta SoftDelete). | `id` (ruta) | `auth.flow.delete` |

---

## 🛠️ Tecnologías Utilizadas

- **Laravel 12**: Framework principal.
- **Laravel Passport**: Autenticación OAuth2.
- **Spatie Laravel Data**: Para la transferencia de datos estructurados (DTOs).
- **Laravel Idea**: Soporte para desarrollo en IDE.

## 📦 SDK
Puedes usar el SDK oficial para una integración más rápida: [Authxolote SDK](https://github.com/AxoloteSource/authxolote-sdk)

---

## 📄 Licencia
Este proyecto es software de código abierto bajo la licencia [MIT](https://opensource.org/licenses/MIT).
