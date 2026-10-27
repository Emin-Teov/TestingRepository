# Authentication and permissions

## Authentication (framework)

- `Framework\Auth\Auth` is session based. The user model and username column come from `config/auth.php`.
- `auth()->attempt($username, $password)` checks the password and rehashes it if needed, then calls `login()`,
  which regenerates the session id and the CSRF token. `logout()` invalidates the session.
- `auth()->user()` loads the user once per request. `id()`, `check()`, `guest()`.
- Passwords: `Framework\Auth\Hash::make()` and `check()` (`password_hash` with `PASSWORD_DEFAULT`, bcrypt).
  **Hashes are one-way.** A forgotten password is reset, never read back.
- Login throttling: `RateLimiter` (files in `storage/cache/rate-limits`). After 5 failures the username+IP
  pair is locked for 60 s (`config/auth.php` → `throttle`).
- `AuthController::store` also refuses accounts with `is_active = 0`.

## Permissions (application layer, reused by every project)

The model: **users → usertypes → boolean `can_*` columns**.

```
usertypes: id, name (unique), description, can_<permission>... (bool), is_locked (bool), timestamps
users:     id, usertype_id → usertypes.id (FK, RESTRICT), username, full_name, password, is_active, timestamps
```

- `App\Enums\Permission` lists every permission. The case value is the name, and `column()` returns `'can_' . value`.
  `label()` is the translated name from `lang/*.php` → `permissions.<value>`.
- `UserType::allows(Permission)` returns true when `is_locked` (the built-in Administrator) or when the column is 1.
  `UserType::permissions()` lists the granted permissions.
- `User::can(Permission ...$p)` requires **all** of them, and is false for an inactive user. `User::userType()` is memoized.
- Routes check with the `can` middleware (`App\Middleware\RequirePermission`):
  - `'can'` with no parameters: the user must exist and be active. Otherwise it logs out and redirects to login with `auth.inactive`.
  - `'can:view_entries'` or `'can:a,b'` returns 403 unless the user has all of them.
- Views check with `$user->can(Permission::X)` to hide links and buttons. **That is only cosmetic.**
  The route middleware is the real check.
- The user types screen (`UserTypeController`) saves checkboxes named `permissions[]` with the enum values.
  Unknown values are ignored.

### Safety rules already enforced (keep them when changing these screens)

- The locked type (`is_locked = 1`) cannot be deleted and its permissions cannot be edited (it always has all of them).
- A type that still has users cannot be deleted (the FK would block it anyway, but the user gets a friendly message).
- You cannot delete or deactivate yourself, or move yourself to a type without `manage_users`.
- You cannot remove `manage_usertypes` from your own type.

### Adding a permission

See [recipes.md](recipes.md#add-a-permission). In short: a migration adding a `can_x` column, an enum case,
the `UserType::$fillable` entry, translations in every language, route middleware, view checks, and a test.
