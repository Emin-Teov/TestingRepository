<?php

declare(strict_types=1);

namespace Framework\Database;

use BackedEnum;
use DateTimeImmutable;
use DateTimeInterface;
use JsonSerializable;

/**
 * Minimal active-record base class.
 *
 *   final class Item extends Model
 *   {
 *       protected static string $table = 'items';
 *       protected static array $fillable = ['value', 'description'];
 *       protected static array $casts = ['created_at' => 'datetime'];
 *   }
 */
abstract class Model implements JsonSerializable
{
    public const DATE_FORMAT = 'Y-m-d H:i:s';

    protected static string $table;

    /** Attributes that create()/update() may set from user input. */
    protected static array $fillable = [];

    /** Attributes left out of toArray() / JSON. */
    protected static array $hidden = [];

    /** @var array<string, 'int'|'float'|'bool'|'datetime'|class-string<BackedEnum>> */
    protected static array $casts = [];

    protected static bool $timestamps = true;

    protected array $attributes = [];

    public bool $exists = false;

    public static function connection(): Connection
    {
        return app(Connection::class);
    }

    public static function table(): string
    {
        return static::$table;
    }

    /**
     * @return ModelQuery<static>
     */
    public static function query(): ModelQuery
    {
        return new ModelQuery(static::connection()->table(static::$table), static::class);
    }

    /**
     * @return list<static>
     */
    public static function all(): array
    {
        return static::query()->get();
    }

    public static function find(int $id): ?static
    {
        return static::query()->find($id);
    }

    /**
     * Load many models with a single query, keyed by id. Useful to avoid one query per row.
     *
     * @param array<int|null> $ids Duplicates and nulls are ignored.
     * @return array<int, static>
     */
    public static function findMany(array $ids): array
    {
        $ids = array_values(array_unique(array_filter($ids, static fn ($id) => $id !== null)));

        if ($ids === []) {
            return [];
        }

        $models = [];

        foreach (static::query()->whereIn('id', $ids)->get() as $model) {
            $models[$model->getKey()] = $model;
        }

        return $models;
    }

    /**
     * @throws ModelNotFoundException
     */
    public static function findOrFail(int $id): static
    {
        return static::find($id) ?? throw new ModelNotFoundException(static::class . " [$id] not found.");
    }

    public static function create(array $attributes): static
    {
        $model = (new static())->fill($attributes);
        $model->save();

        return $model;
    }

    public static function count(): int
    {
        return static::query()->count();
    }

    /**
     * Build a model from a database row.
     */
    public static function hydrate(array $row): static
    {
        $model = new static();
        $model->attributes = $row;
        $model->exists = true;

        return $model;
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if (in_array($key, static::$fillable, true)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    /**
     * Set attributes regardless of $fillable. Use only with trusted data.
     */
    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function save(): bool
    {
        $now = date(self::DATE_FORMAT);
        $query = static::connection()->table(static::$table);

        if ($this->exists) {
            if (static::$timestamps) {
                $this->attributes['updated_at'] = $now;
            }

            $query->where('id', $this->getKey())->update(array_diff_key($this->attributes, ['id' => true]));

            return true;
        }

        if (static::$timestamps) {
            $this->attributes['created_at'] ??= $now;
        }

        $this->attributes['id'] = $query->insert($this->attributes);
        $this->exists = true;

        return true;
    }

    public function update(array $attributes): bool
    {
        return $this->fill($attributes)->save();
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        static::connection()->table(static::$table)->where('id', $this->getKey())->delete();
        $this->exists = false;

        return true;
    }

    public function getKey(): ?int
    {
        return isset($this->attributes['id']) ? (int) $this->attributes['id'] : null;
    }

    public function getAttribute(string $key): mixed
    {
        $value = $this->attributes[$key] ?? null;
        $cast = static::$casts[$key] ?? null;

        if ($value === null || $cast === null) {
            return $value;
        }

        return match (true) {
            $cast === 'int' => (int) $value,
            $cast === 'float' => (float) $value,
            $cast === 'bool' => (bool) $value,
            $cast === 'datetime' => new DateTimeImmutable((string) $value),
            is_subclass_of($cast, BackedEnum::class) => $cast::from($value),
            default => $value,
        };
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = match (true) {
            $value instanceof BackedEnum => $value->value,
            $value instanceof DateTimeInterface => $value->format(self::DATE_FORMAT),
            is_bool($value) => (int) $value,
            default => $value,
        };
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]);
    }

    public function toArray(): array
    {
        $array = [];

        foreach (array_diff_key($this->attributes, array_flip(static::$hidden)) as $key => $value) {
            $value = $this->getAttribute($key);
            $array[$key] = match (true) {
                $value instanceof BackedEnum => $value->value,
                $value instanceof DateTimeInterface => $value->format(DateTimeInterface::ATOM),
                default => $value,
            };
        }

        return $array;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
