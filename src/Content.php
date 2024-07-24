<?php
    defined('EXEC') or die;

        class Content{
            /**
             * Get multilingual content for view.
             * 
             * @var array<int|string>
             */
            public $set = array(
                "en"=>array(
                    // buttons
                    "login"=>"Login",
                    "logout"=>"Logout",
                    "add"=>"Add Item",
                    "yes"=>"Yes",
                    "no"=>"No",
                    "search"=>"Search",

                    // input values
                    "name"=>"Enter name",
                    "password"=>"Enter password",
                    "value"=>"Set value",
                    "description"=>"Set description",

                    //heads
                    "values"=>"Value",
                    "descriptions"=>"Description",
                    "created"=>"Created at",
                    "updated"=>"Updated at",

                    //title
                    "delete_data"=>"Are you sure you want to delete that item?",
                    "no_data"=>"Database is empty.",
                    "no_user"=>"User is not found.",
                    "no_update"=>"The item never updated.",
                    "copy"=>"Email address copied.",

                    //warning
                    "request"=>"This input does not have to be empty.",
                    "min"=>"The minimum length for input `%s` is %d.",
                    "min"=>"The maximum length for input `%s` is %d.",
                    "email"=>"This is not an email address.", 
                ),
                "az"=>array(
                    // buttons
                    "login"=>"Daxil ol",
                    "logout"=>"Çıx",
                    "add"=>"Əlavə et",
                    "yes"=>"Hə",
                    "no"=>"Yox",
                    "search"=>"Axtar",

                    // input values
                    "name"=>"İstifadəçi adını daxil edin",
                    "password"=>"Parolunuzu daxil edin",
                    "value"=>"Başlığı daxil edin",
                    "description"=>"Izzahı daxil edin",

                    //heads
                    "values"=>"Başlıqlar",
                    "descriptions"=>"İzzahlar",
                    "created"=>"Yaradıldıt",
                    "updated"=>"Dəyişdirildi",

                    //title
                    "delete_data"=>"Həmin elementi silmək istədiyinizə əminsiniz?",
                    "no_data"=>"Verilənlər bazası boşdur.",
                    "no_user"=>"İstifadəçi tapılmadı.",
                    "no_update"=>"Element heç vaxt yenilənməyib.",
                    "copy"=>"Poçt ünvanı kopyalandı.",

                    //warning
                    "request"=>"Bu giriş boş olmamalıdır.",
                    "min"=>"`%s` daxil edilməsi üçün minimum uzunluq %d-dir.",
                    "min"=>"`%s` daxil edilməsi üçün maksimum uzunluq %d-dir.",
                    "email"=>"Bu elektron poçt ünvanı.", 
                ),
                "ru"=>array(
                    // buttons
                    "login"=>"Войти",
                    "logout"=>"Выход",
                    "add"=>"Добавить элемент",
                    "yes"=>"Да",
                    "no"=>"Нет",
                    "search"=>"Поиск",

                    // input values
                    "name"=>"Введите имя",
                    "password"=>"Введите пароль",
                    "value"=>"Установить значение",
                    "description"=>"Установить описание",

                    //heads
                    "values"=>"Значение",
                    "descriptions"=>"Описание",
                    "created"=>"Создано в",
                    "updated"=>"Обновлено в",

                    //title
                    "delete_data"=>"Вы уверены, что хотите удалить этот элемент?",
                    "no_data"=>"База данных пуста.",
                    "no_user"=>"Пользователь не найден.",
                    "no_update"=>"Элемент никогда не обновлялся.",
                    "copy"=>"Адрес почты скопирован.",

                    //warning
                    "request"=>"Эта запись не должна быть пустой.",
                    "min"=>"TМинимальная длина для вставки `%s` — %d.",
                    "min"=>"Максимальная длина вставки `%s` — %d.",
                    "email"=>"Это не адрес электронной почты.", 
                ),
            );
        }
