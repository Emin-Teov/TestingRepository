# Database

Drivers: **mysql** (MySQL/MariaDB, used in production) and **sqlite** (used by the tests in memory).
Write code that works on both: the query builder and the schema grammars handle the differences.
In raw SQL, use plain unquoted identifiers and standard SQL.

## Models

```php
/**
 * @property-read int $id
 * @property string $title
 * @property ?int $category_id
 * @property float $price
 * @property bool $is_active
 * @property ?DateTimeImmutable $created_at
 */
final class Post extends Model
{
    protected static string $table = 'posts';
    protected static array $fillable = ['title', 'category_id', 'price', 'is_active'];  // mass-assignable
    protected static array $hidden = ['secret'];                                       // left out of JSON
    protected static array $casts = [
        'id' => 'int', 'category_id' => 'int', 'price' => 'float', 'is_active' => 'bool',
        'published_on' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime',
        'status' => Status::class,           // backed enum
    ];
    // protected static bool $timestamps = false;   // when there are no created_at/updated_at columns

    public const SEARCHABLE = ['title'];          // used by ->search()
}
```

- Casts: `int`, `float`, `bool`, `datetime` (→ `DateTimeImmutable`, also for DATE columns), and backed-enum classes.
  **Always cast id and foreign-key columns to `int`**, because PDO may return them as strings.
- `Post::create([...])` and `$post->update([...])` respect `$fillable`. `forceFill([...])->save()` ignores it
  (use only with trusted data, e.g. seeders or `user_id` from Auth).
- `find($id)`, `findOrFail($id)` (→ 404), `all()`, `count()`, `findMany($ids)` (→ `[id => model]`, one query).
- `$model->save()`, `->delete()`, `->exists`, `->getKey()`, `->toArray()`. JSON-serializable, with datetimes in ATOM format.
- `save()` sets `created_at` and `updated_at`. Override `save()` for derived columns and call `parent::save()`
  (see `StockEntry::save()`).
- **There are no relations or ORM magic.** Write small explicit methods instead: `$user->userType()` (memoized),
  and `Model::findMany(array_map(fn ($x) => $x->foreign_id, $rows))` to load related rows for a list
  **without N+1 queries** (see `StockEntry::relationsFor()`).

## Query builder

`Post::query()` returns a `ModelQuery` (it hydrates models), and `app(Connection::class)->table('posts')` returns raw arrays.

```php
Post::query()
    ->where('is_active', true)              // where(col, value) or where(col, op, value); op: = != <> < > <= >= LIKE "NOT LIKE"
    ->where('price', '>=', 10)
    ->orWhere('id', 5)
    ->whereIn('category_id', [1, 2])
    ->whereNull('deleted_at')->whereNotNull('published_on')
    ->search(['title', 'body'], $term)      // (title LIKE %t% OR body LIKE %t%), wildcards escaped
    ->orderBy('title')->latest('id')        // latest = orderBy desc
    ->select('id', 'title')
    ->limit(10)->offset(20)
    ->get();                                // list<Post>

->first(), ->find($id), ->count(), ->exists(), ->sum('total'), ->value('col'),
->paginate($perPage, $page)                 // Paginator: ->items, ->total, ->isEmpty(), ->hasPages()...
->update([...]), ->delete(), ->insert([...]) (raw builder: returns the new id)
```

- The builder **mutates itself**. Use `(clone $query)` to reuse a filtered base for both a page and a total
  (see `StockEntryController::index`).
- `where()` clauses are flat (`AND`/`OR` without grouping), except `search()`, which groups its ORs.
  For complex conditions, use raw SQL.
- Identifiers must match `^[A-Za-z_][A-Za-z0-9_]*(\.col)?$`; anything else throws. Values are always bound.
- No joins or group-by in the builder. Use raw SQL for aggregates:

```php
static::connection()->select('SELECT product_id, SUM(quantity) AS q FROM stock_entries WHERE product_id IN (?, ?) GROUP BY product_id', [1, 2]);
app(Connection::class)->selectOne($sql, $bindings);
app(Connection::class)->statement($sql, $bindings);   // INSERT/UPDATE/DELETE/DDL → affected rows
app(Connection::class)->transaction(function (Connection $db) { ... });
```

Put raw SQL in a static model method with a clear return shape (see `Product::stockSummary()`), not in controllers or views.

## Migrations

`php console make:migration create_posts_table` (the name implies `--create=posts`; use `--table=posts` to alter a table).

```php
return new class extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('posts', function (Blueprint $table): void {
            $table->id();                                           // BIGINT UNSIGNED AUTO_INCREMENT PK
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');     // no onDelete = RESTRICT
            $table->string('title', 100)->unique();
            $table->text('body')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->integer('views')->default(0);
            $table->boolean('is_active')->default(true);
            $table->date('published_on')->nullable()->index();
            $table->dateTime('seen_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();                                   // created_at, updated_at (nullable)
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('posts');
    }
};
```

- Column types: `id, integer, bigInteger, foreignId, string(len), text, boolean, decimal(p,s), json, date, dateTime`.
  Modifiers: `nullable(), default(v), unique(), index(), constrained(table), cascadeOnDelete(), nullOnDelete()`.
- `constrained()` without arguments guesses the table as `<column minus _id>` + `s`. Pass the table name explicitly.
- **Don't add `->index()` to `foreignId` columns.** MySQL already indexes foreign keys, so you'd create a duplicate index.
- Alter a table: `$schema->table('posts', fn (Blueprint $t) => $t->string('slug')->nullable())`, and `$t->dropColumn('x')`.
  Changing an existing column type is not supported; add a new column and copy the data.
- Also available: `$schema->hasTable()`, `rename()`, `drop()`.
- Files run in filename order. **Never edit a migration that has run on a shared database.** Add a new one.
- `migrate --pretend` prints the SQL without running it.

## Seeders

`Database\Seeders\DatabaseSeeder::run()` calls the others: `$this->call(A::class, B::class)`. Make seeders
idempotent (check `exists()` or `count()` first) so `db:seed` can run twice safely. Required data and demo data
belong in separate seeders.

## Money and quantities

Store them in `DECIMAL` columns. Cast to `float` in the model. Before saving, normalize with
`number_format($v, $scale, '.', '')` so floats are never written in exponent form. Display them with
`App\Support\Format` (or the project's equivalent).
