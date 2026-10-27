# AI knowledge base

Notes for AI coding assistants (Claude Code, Copilot, Cursor, …) working on any project built on this
PHP framework. Read this file first, then only the files relevant to the task.

| File | Read it when you… |
|------|-------------------|
| [project.md](project.md) | need to know what *this* application does: domain, tables, permissions, demo data. **The only project-specific file.** |
| [architecture.md](architecture.md) | need the big picture: request lifecycle, container, folders, configuration |
| [routing-and-middleware.md](routing-and-middleware.md) | add or change URLs, route groups, middleware |
| [controllers.md](controllers.md) | write controllers: requests, responses, validation, redirects, flash messages |
| [database.md](database.md) | touch the database: models, query builder, migrations, schema, seeders, raw SQL |
| [views-and-frontend.md](views-and-frontend.md) | write templates, CSS or JS, or add translations |
| [auth-and-permissions.md](auth-and-permissions.md) | deal with login, users, user types or permission checks |
| [console-and-testing.md](console-and-testing.md) | run or write console commands or PHPUnit tests |
| [recipes.md](recipes.md) | do a common task step by step (new CRUD module, new permission, new language, …) |
| [gotchas.md](gotchas.md) | debug something odd. Read it before your first change anyway. |

## Golden rules

1. **Framework vs application.** `framework/` (namespace `Framework\`) is the reusable core shared by
   every project. `app/`, `routes/`, `resources/`, `database/`, `lang/` and `config/` belong to the project.
   Keep framework changes generic (no project words such as "product" in `framework/`), backwards
   compatible, and covered by a unit test.
2. **No dependencies.** The framework has no runtime Composer packages and must keep running on shared
   hosting without Composer (`bootstrap/autoload.php` has a fallback autoloader). PHPUnit is the only dev
   dependency. Don't add packages without asking.
3. **PHP 8.2+, strict.** Every PHP file starts with `declare(strict_types=1);`. Use readonly classes,
   enums, constructor promotion, first-class callables and `match`, as the existing code does.
4. **Security defaults are mandatory.** Escape all output with `e()`. Values go through the query
   builder or `?` placeholders, never string concatenation. Every non-GET form has `csrf_field()`.
   Validate all input with `$request->validate()`.
5. **Every user-visible string is translated** in *all* files under `lang/` (currently `en`, `az`, `ru`).
6. **Schema changes are migrations**, never manual SQL. After changing the schema, regenerate the SQL dump
   if the project ships one (see project.md).
7. **Run the tests** (`php vendor/bin/phpunit`) before saying a task is finished, and add tests for new behaviour.
8. Match the surrounding style: short docblocks that explain *why*, final classes, early returns,
   names spelled out in full.

## Keeping this folder current

- Generic facts about the framework go in the topic files. Project facts go only in `project.md`.
- When you change framework behaviour, update the matching file here in the same change.
- To start a new project on this framework, copy the whole folder and rewrite `project.md`.
