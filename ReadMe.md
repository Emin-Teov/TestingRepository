**Azərbaycanca** | [English](ReadMe.en.md)

# Anbar (Warehouse)

**PHP 8.2+** və **MySQL** üzərində, kiçik MVC freymvorku ilə qurulmuş anbar idarəetmə tətbiqi.
Anbara daxil olan malları qeydə alır: məhsul, ölçü vahidi (t, kg, ədəd, L, …), miqdar, vahid qiyməti,
ümumi dəyər (miqdar × qiymət), mədaxil tarixi, təchizatçı və sənəd nömrəsi.
İnterfeys azərbaycan (əsas dil), ingilis və rus dillərindədir. İstifadəçilər yuxarı paneldəki bayraqlarla dili dəyişə bilər.

Freymvorkun işləməsi üçün heç bir xarici paket lazım deyil. Composer yalnız testləri işə salmaq üçün lazımdır.

## Tez başlanğıc (XAMPP)

1. Layihəni `htdocs/Framework` qovluğuna qoyun, Apache və MySQL-i işə salın.
2. `Session.sql` faylını phpMyAdmin-də idxal edin (və ya `mysql -u root < Session.sql`). O, bütün cədvəlləri,
   xarici açarları və nümunə məlumatları olan `warehouse` bazasını yaradır.
3. `cp .env.example .env`. Fayl artıq `warehouse` bazasına, `root` istifadəçisinə və boş parola uyğundur.
4. `http://localhost/Framework/` ünvanını açın və daxil olun:

| İstifadəçi adı | Parol            | İstifadəçi növü   |
|----------------|------------------|-------------------|
| `admin`        | `admin12345`     | Administrator     |
| `manager`      | `manager123`     | Warehouse manager |
| `storekeeper`  | `storekeeper123` | Storekeeper       |
| `accountant`   | `accountant123`  | Accountant        |

İlk girişdən sonra bu parolları dəyişin.

Bazanı SQL faylı əvəzinə miqrasiyalardan qurmaq üçün `php console migrate:fresh --seed` əmrini işə salın.
Bu halda admin parolu `.env` faylındakı `ADMIN_PASSWORD` dəyərindən götürülür. O boşdursa, təsadüfi parol yaradılır və ekrana çıxarılır.
Yeni istifadəçi: `php console user:create <istifadəçi_adı> <parol> --type="Storekeeper"`.

SSH olmayan hostinqdə layihəni yükləyin, `.env` faylını yaradın və `Session.sql`-i idxal edin. Sonrakı yeniləmələr
yeni miqrasiyalar gətirəndə onların SQL-ini öz kompüterinizdə `php console migrate --pretend` ilə alın və phpMyAdmin-də icra edin.

## Verilənlər bazası

| Cədvəl          | Məzmun                                                                                       |
|-----------------|----------------------------------------------------------------------------------------------|
| `usertypes`     | İstifadəçi növləri (Administrator, Storekeeper, …). Hər icazə ayrıca `can_*` boolean sütunudur. |
| `users`         | Hesablar. `usertype_id` → `usertypes.id` (xarici açar; istifadəçisi olan növ silinə bilməz).  |
| `units`         | Ölçü vahidləri: ad və işarə.                                                                 |
| `categories`    | Məhsul kateqoriyaları.                                                                       |
| `suppliers`     | Təchizatçılar: telefon, e-poçt, ünvan.                                                       |
| `products`      | Kod (SKU), ad, `category_id` → `categories`, `unit_id` → `units`.                            |
| `stock_entries` | Qəbul edilən mallar: `product_id`, `supplier_id`, `user_id`, `quantity`, `unit_price`, `total_value`, `entry_date`, `document_no`, `note`. |

`StockEntry::save()` hər dəfə `total_value = quantity × unit_price` hesablayır, ona görə də bu sütunlar heç vaxt bir-birinə zidd olmur.
Mədaxili olan məhsulun ölçü vahidi dəyişdirilə bilməz. İstifadədə olan məhsul, ölçü vahidi və istifadəçi növü silinə bilməz.

## İcazələr

Hər istifadəçinin bir istifadəçi növü var və həmin növün `can_*` sütunları istifadəçinin nə edə biləcəyini müəyyən edir.
Onları **İstifadəçi növləri** səhifəsində qeyd qutuları ilə və ya birbaşa `usertypes` cədvəlində dəyişə bilərsiniz.

| İcazə (`can_…`)      | Nəyə imkan verir                                 |
|----------------------|--------------------------------------------------|
| `view_entries`       | Mədaxil siyahısı və CSV ixracı                    |
| `create_entries`     | Mal qəbul etmək (mədaxil əlavə etmək)             |
| `edit_entries`       | Mədaxilləri redaktə etmək                         |
| `delete_entries`     | Mədaxilləri silmək                                |
| `manage_products`    | Məhsullar, kateqoriyalar və ölçü vahidləri        |
| `manage_suppliers`   | Təchizatçılar                                     |
| `view_reports`       | Anbar hesabatı                                    |
| `manage_users`       | İstifadəçilər                                     |
| `manage_usertypes`   | İstifadəçi növləri və onların icazələri           |

Daxili Administrator növü (`is_locked = 1`) həmişə bütün icazələrə malikdir və silinə bilməz.
Deaktiv istifadəçilər (`is_active = 0`) daxil ola bilməz. Heç kim öz hesabını silə, deaktiv edə və ya
istifadəçilər və istifadəçi növləri üzərində öz hüququnu ləğv edə bilməz. Marşrutlarda icazə `can` middleware-i ilə
yoxlanılır: `->middleware('can:create_entries')`.

## Səhifələr

- **İdarə paneli**: məhsul sayı, qəbul edilmiş malın ümumi dəyəri, bu ayın mədaxil sayı və dəyəri, son 7 günün qrafiki.
- **Anbar mədaxili**: məhsula, təchizatçıya, tarix aralığına və sənəd nömrəsinə görə süzgəc. Siyahıdakı mədaxillərin cəmini göstərir və onları CSV-yə ixrac edir (Excel-də açılır).
- **Məhsullar, Kateqoriyalar, Ölçü vahidləri, Təchizatçılar**: əsas məlumatlar. Məhsul siyahısı hər məhsuldan nə qədər qəbul edildiyini göstərir.
- **Anbar hesabatı**: dövr və ya kateqoriya üzrə hər məhsulun miqdarı, orta qiyməti və dəyəri. Çap oluna bilər.
- **İstifadəçilər, İstifadəçi növləri**: idarəetmə.

## Proqramçılar üçün: tətbiqi fərdiləşdirmək

Aşağıdakıların hamısını `framework/` qovluğuna toxunmadan dəyişmək olar. Böyük dəyişikliklər üçün addım-addım
təlimatlar (süni intellekt köməkçiləri üçün də yazılıb) [.ai-knowledge/](.ai-knowledge/README.md) qovluğundadır.
[recipes.md](.ai-knowledge/recipes.md) faylından başlayın.

### Parametrlər (`.env`)

| Açar | Standart dəyər | Nəyi dəyişir |
|------|----------------|--------------|
| `APP_NAME` | `Warehouse` | Yan menyuda, səhifə başlıqlarında və altlıqda görünən ad |
| `APP_ENV` / `APP_DEBUG` | `local` / `true` | Canlı serverdə `production` / `false` yazın: xəta detalları gizlənir və təhlükəli əmrlər qorunur |
| `APP_URL` | *(boş)* | Boş saxlayın. Yalnız keçidlər səhv qovluğa aparırsa doldurun. |
| `APP_TIMEZONE` | `Asia/Baku` | Tarixlər və idarə panelindəki "bu ay" |
| `APP_LOCALE` | `az` | Əsas dil (`az`, `en`, `ru`). İstifadəçilər bayraqlarla dəyişə bilər. |
| `APP_CURRENCY` | `AZN` | Hər qiymət və dəyərin yanında göstərilən valyuta |
| `DB_*` | `127.0.0.1`-də `warehouse`, istifadəçi `root`, boş parol | Bazaya qoşulma |
| `SESSION_NAME` | `warehouse_session` | Cookie adı. Eyni domendəki hər layihəyə ayrıca ad verin. |
| `ADMIN_USERNAME` / `ADMIN_PASSWORD` | `admin` / — | `php console db:seed` ilə yaradılan ilk administrator |

`.env` git-ə əlavə edilmir. Yeni parametr əlavə edəndə onu `.env.example` faylına da yazın və `config/*.php` faylında oxuyun.

### Görünüş və brend

| Nə | Harada |
|----|--------|
| Rənglər (tünd mövzu) | [public/assets/css/app.css](public/assets/css/app.css) faylının əvvəlindəki CSS dəyişənləri (`--bg`, `--surface`, `--accent`, …) |
| İdarə paneli düzümü, cədvəllər, formalar | [public/assets/css/admin.css](public/assets/css/admin.css) |
| Loqo və favicon | `public/assets/img/logo.png` |
| Yan menyu (sıra, ikonlar, kim nəyi görür) | [resources/views/layouts/admin.php](resources/views/layouts/admin.php) faylının əvvəlindəki `$links` massivi |
| Altlıqdakı əlaqə məlumatları | [config/app.php](config/app.php) faylında `contact` |
| Rəqəm formatı (onluq hissə, minlik ayırıcı) | [app/Support/Format.php](app/Support/Format.php) |
| Səhifədəki sətir sayı | hər nəzarətçinin `index()` metodundakı `paginate(20, …)` |

### Mətnlər və dillər

- Bütün yazılar və mesajlar [lang/az.php](lang/az.php), [lang/en.php](lang/en.php) və [lang/ru.php](lang/ru.php) fayllarındadır.
  Mətni orada dəyişin. Açarlar üç faylda eyni olmalıdır.
- Əsas dil `.env` faylında `APP_LOCALE=az` ilə təyin olunur. İkinci dil ingilis dilidir.
  Tərcüməsi olmayan açarlar ingilis dilinə (`fallback_locale`) keçir, ona görə də `lang/en.php` tam olmalıdır.
- Yeni dil: `lang/en.php` faylını `lang/<kod>.php` adı ilə kopyalayın, bayrağı `public/assets/flags/<kod>.png` kimi əlavə edin
  və kodu `config/app.php` faylındakı `locales` siyahısına yazın.
- Dili silmək: onun sətrini `config/app.php` faylındakı `locales` siyahısından silin (bayrağı yuxarı paneldən yox olur).

### Biznes məlumatları

- **Ölçü vahidləri, kateqoriyalar, təchizatçılar, istifadəçi növləri**: tətbiqin özündə dəyişin. Kod lazım deyil.
- **Yeni quraşdırma üçün ilkin ölçü vahidləri və istifadəçi növləri**: [database/seeders/UnitSeeder.php](database/seeders/UnitSeeder.php)
  və [UserTypeSeeder.php](database/seeders/UserTypeSeeder.php).
- **Nümunə məlumatlar**: [DemoSeeder.php](database/seeders/DemoSeeder.php). Təmiz quraşdırma üçün onu `DatabaseSeeder`-dən çıxarın.
- **Yoxlama limitləri** (maksimum miqdar, mətn uzunluğu, …): hər nəzarətçidəki `RULES` sabiti və ya `rules()` metodu.

### İcazələr

Yeni icazə əlavə etmək üçün miqrasiya ilə `usertypes` cədvəlinə `can_…` sütunu, [app/Enums/Permission.php](app/Enums/Permission.php)
faylına isə yeni `case` əlavə edin. Sonra onu `UserType::$fillable` siyahısına yazın, bütün dillərdə `permissions` bölməsində tərcümə edin
və marşrutları `->middleware('can:<ad>')` ilə qoruyun. İstifadəçi növləri səhifəsi yeni qeyd qutusunu avtomatik göstərir.
Tam siyahı: [.ai-knowledge/recipes.md](.ai-knowledge/recipes.md#add-a-permission).

### Yeni səhifələr və cədvəllər

[.ai-knowledge/recipes.md](.ai-knowledge/recipes.md) faylındakı "Add a CRUD module" bölməsinə əməl edin. Qısaca: miqrasiya,
model, nəzarətçi (`SupplierController`-i kopyalayın), icazəli marşrutlar, iki görünüş (`admin/suppliers`-i kopyalayın),
yan menyu keçidi, tərcümələr və test. Artıq icra olunmuş miqrasiyanı heç vaxt dəyişməyin; yenisini əlavə edin.

### Riayət edilməli qaydalar

- Çıxışı `e()` ilə qoruyun, girişi `$request->validate()` ilə yoxlayın, hər formaya `csrf_field()` əlavə edin.
- İcazəni **marşrutda** yoxlayın. Düyməni gizlətmək kifayət deyil.
- Commit etməzdən əvvəl `php vendor/bin/phpunit` işə salın. Testlər yaddaşdakı SQLite bazasından istifadə edir və MySQL-ə toxunmur.
- Sxem dəyişəndən sonra `Session.sql` faylını yenidən yaradın ([.ai-knowledge/project.md](.ai-knowledge/project.md) necə ediləcəyini izah edir).

### Canlıya çıxmazdan əvvəl

1. `APP_ENV=production` və `APP_DEBUG=false` yazın, güclü parolu olan baza istifadəçisi istifadə edin.
2. Nümunə parolları dəyişin və ya nümunə istifadəçiləri (`manager`, `storekeeper`, `accountant`) silin.
3. `storage/` qovluğunun veb server tərəfindən yazıla bildiyinə əmin olun.
4. Saytı HTTPS üzərindən açın. Sessiya cookie-si avtomatik `Secure` olur.

### Unudulmuş parollar

Parollar birtərəfli bcrypt hash kimi saxlanılır, ona görə də heç kim onları geri oxuya bilməz, hətta bazadan da.
Parolu sıfırlamaq üçün **İstifadəçilər → Redaktə et → Parol** bölməsindən istifadə edin. Heç bir administrator daxil ola bilmirsə, yeni administrator yaradın:

```bash
php console user:create newadmin "güclü-parol" --type="Administrator"
```

## Serverə yerləşdirmə

Tətbiqə **PHP 8.2+** (`pdo_mysql` və `mbstring` genişlənmələri), **MySQL 5.7+ və ya MariaDB 10.3+**
və `mod_rewrite` ilə Apache (və ya veb kökü `public/` olan nginx) lazımdır.

**Paylaşılan hostinq (cPanel / DirectAdmin / pulsuz hostlar, SSH-sız):**

1. İdarəetmə panelində MySQL bazası və istifadəçisi yaradın.
2. `Session.sql` faylının əvvəlində `CREATE DATABASE` / `USE` sətirlərindəki `warehouse` adını öz bazanızın adı ilə əvəz edin,
   və ya bu iki sətri silib bazanı phpMyAdmin-də seçin. Sonra faylı phpMyAdmin-də idxal edin.
3. Bütün layihə fayllarını (`.git`, `tests`, `vendor` istisna olmaqla) saytın qovluğuna yükləyin, məsələn `htdocs/` və ya `public_html/`.
   Kökdəki `.htaccess` bütün sorğuları `public/` qovluğuna yönləndirir və `.env` faylını bağlayır.
4. Serverdə `.env.example` əsasında `.env` yaradın: hostinqin baza məlumatları, `APP_ENV=production` və `APP_DEBUG=false`.
5. `storage/` qovluğunu yazıla bilən edin, saytı açın, daxil olun və nümunə parolları dəyişin.

**VPS / bulud server (Ubuntu):** `apache2 php8.3 php8.3-mysql php8.3-mbstring mariadb-server` paketlərini quraşdırın,
virtual hostun `DocumentRoot` dəyərini `/var/www/warehouse/public` edin, `mod_rewrite`-ı aktivləşdirin,
`Session.sql`-i idxal edin, `.env` yaradın, `chown -R www-data storage` əmrini işə salın və HTTPS-i
`certbot --apache` ilə qoşun.

## Konsol

```
php console                       bütün əmrlərin siyahısı
php console serve                 inkişaf serveri  [--host=] [--port=]
php console migrate               gözləyən miqrasiyaları icra et  [--seed] [--pretend]
php console migrate:status        hansı miqrasiyaların icra olunduğu
php console migrate:rollback      son paketi geri qaytar  [--step=N]
php console migrate:reset         bütün miqrasiyaları geri qaytar
php console migrate:fresh         bütün cədvəlləri sil və yenidən qur  [--seed]
php console db:seed               seeder-ləri işə sal  [--class=DatabaseSeeder]
php console make:migration name   yeni miqrasiya  [--create=table] [--table=table]
php console make:model Name       yeni model  [--migration]
php console make:controller Name  yeni nəzarətçi (məs. Admin/PostController)
php console make:seeder Name      yeni seeder
php console route:list            adları və middleware-ləri ilə bütün marşrutlar
php console user:create           yeni istifadəçi  <ad> <parol> [--type="İstifadəçi növü"]
```

Təhlükəli əmrlər (rollback, reset, fresh) `APP_ENV=production` olduqda `--force` verilmədən işləmir.

## JSON API

API idarə panelinin girişindən istifadə edir. Daxil olmayanlar 401, icazəsi olmayanlar 403 cavabı alır.

| Ünvan                   | İcazə          | Təsvir                                          |
|-------------------------|----------------|-------------------------------------------------|
| `GET /api/stock`        | `view_reports` | Hər məhsul üzrə qəbul edilən miqdar və dəyər    |
| `GET /api/entries`      | `view_entries` | Səhifələnmiş mədaxillər: `?page=`, `?per_page=` (maks. 100), `?product_id=`, `?supplier_id=`, `?from=`, `?to=`, `?q=` |
| `GET /api/entries/{id}` | `view_entries` | Bir mədaxil və ya 404                            |

## Layihənin quruluşu

```
app/                Tətbiq: nəzarətçilər, modellər, enum-lar, middleware, konsol əmrləri
bootstrap/          Tətbiqi yaradır və xidmətləri konteynerdə qeydiyyata alır
config/             app, auth, database və middleware parametrləri (dəyərlər .env-dən gəlir)
database/
  migrations/       Versiyalanmış sxem dəyişiklikləri
  seeders/          İlkin və nümunə məlumatlar
framework/          Freymvorkun özü (namespace Framework\)
lang/               az.php, en.php, ru.php
public/             Veb kök: index.php və statik fayllar
resources/views/    Düzüm, bölmə və hissələrdən ibarət sadə PHP şablonları
routes/             web.php və api.php
storage/            Loglar, keş və SQLite bazası (git-ə əlavə edilmir)
tests/              PHPUnit testləri (composer install && composer test)
.ai-knowledge/      Süni intellekt köməkçiləri üçün freymvork bilik bazası
```

## Kod yazmaq

**Marşrutlar** (`routes/web.php`):

```php
$router->get('/posts/{id:\d+}', [PostController::class, 'show'])->name('posts.show');
$router->group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'can:manage_posts']], function (Router $router) {
    $router->resource('posts', Admin\PostController::class);   // index, create, store, edit, update, destroy
});
```

**Nəzarətçilər** marşrut parametrlərini və xidmətləri metod imzası vasitəsilə alır:

```php
public function update(int $id, Request $request): RedirectResponse
{
    $data = $request->validate(['title' => 'required|string|max:100']);
    Post::findOrFail($id)->update($data);

    return to_route('admin.posts.index')->with('success', __('posts.updated'));
}
```

**Miqrasiyalar** (`php console make:migration create_posts_table`):

```php
$schema->create('posts', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('title', 100)->unique();
    $table->text('body');
    $table->boolean('published')->default(false);
    $table->timestamps();
});
```

**Sorğular** həmişə hazırlanmış ifadələrdən (prepared statements) istifadə edir:

```php
Post::query()->where('published', true)->search(['title', 'body'], $term)->latest()->paginate(15, $page);
app(Connection::class)->table('posts')->where('id', 5)->update(['title' => 'New']);
```

**Görünüşlər** `e()` ilə qorunur:

```php
<?php $this->extend('layouts/app', ['title' => __('posts.title')]) ?>
<h1><?= e($post->title) ?></h1>
```

Yoxlama qaydaları: `required, nullable, string, integer, numeric, boolean, email, min, max, in,
alpha_dash, confirmed, date (Y-m-d), unique:table,column[,ignoreId], exists:table,column`.
Boş qalmış istəyə bağlı sahələr `null` kimi qaytarılır.

## Təhlükəsizlik

- Parollar `password_hash()` ilə saxlanılır. 5 uğursuz cəhddən sonra giriş müvəqqəti bloklanır.
- Hər sorğu bağlanmış parametrlərdən istifadə edir. Cədvəl və sütun adları ciddi şablonla yoxlanılır.
- Hər sessiyanın bir CSRF tokeni var və o, hər POST, PUT, PATCH və DELETE sorğusunda yoxlanılır.
  Girişdən sonra sessiya identifikatoru dəyişir.
- Şablonlarda çıxış qorunur. Cavablar `X-Frame-Options`, `X-Content-Type-Options` və `Referrer-Policy` başlıqlarını göndərir.
- Canlı serverdə `APP_ENV=production` və `APP_DEBUG=false` yazın.
