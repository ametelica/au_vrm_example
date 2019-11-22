<?php
/*
 * Внешний расчётный модуль (ВРМ) - принимает, обрабатывает и отдаёт обратно блоксхеме JSON данные, в случаях, когда необходимы
 * сложные операции обработки, поиск, сравнение или просто когда одним этим блоком можно заменить сразу несколько других.
 * Ссылка: http://activeusers.ru/vrm/searchandshow.php
 */

set_time_limit(0); // Будем работать сколько надо

/*
 * Текущий режим:
 * options - загрузка настроек
 * run - обработка данных
 * */

$act = $_REQUEST['act'];

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Режим OPTIONS - в котором ВРМ создаёт в схеме блок управления  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

if($act == 'options') {
    // Этот массив описывает окно настройки ВРМ, которое будет видеть пользователь при редактировании блока
    // Обязательным в нём являются только title и out, а всё остальное (хранилища, платёжки, переменные и т.д)
    // подключаются по желанию и необходимости

    $responce = [
        'title' => 'ВРМ Заголовок',     // Это заголовок блока, который будет виден на схеме
        'data' => [                     // Группа полей, в которых можно задать доступ к хранилищам
            'data1' => [                // ключ data1 станет именем поля, которое покажется в настройках блока
                'title' => 'Хранилище с настройками', // Подпись блока с настроками
            ],                          // тут можно насоздавать ещё полей для хранилищь, но нам сейчас нужно только одно
        ],
        'vars' => [                     // переменные, которые можно будет настроить в блоке, будут видны в виде поля input или textarea
            'search' => [
                'title' => 'Имя поля',  // заголовок поля
                'desc' => 'Описание',   // описание поля, можно пару строк
                'default' => 0          // значение по умолчанию
            ],
            'author' => [
                'title' => 'Роль',      // Пример поля с выпадающим списком
                'values' => [
                    0 => 'Исполнитель',
                    1 => 'Автор'
                ]
            ],
            'about' => [
                'title' => 'Описание',  // Пример большого поля ввода с textarea
                'format' => 'textarea'
            ],
        ],
        'paysys' => [                   // Группа полей, отвечающая за интеграцию с платёжными системами и внешними сервисами.
            'ps' => [                   // ВРМ получит доступ к ID аккаунта, секретному ключу и другим атрибутам выбранной системы
                'title' => 'Платёжная система Яндекс.Касса',
            ]
        ],
        'out' => [                      // Это блоки выходов, мы задаём им номера и подписи (будут видны на схеме)
            1 => [                      // Номер 0 означает красный выход блока ВРМ, зарезервированный для случаев сбоя
                'title' => 'Найдено',   // название выхода 1, не должно быть длинным, иначе вылезет за пределы блока
            ],
            2 => [
                'title' => 'Не найдено',
            ],
            3 => [
                'title' => 'Уже называли',
            ]
        ]
    ];

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * Режим RUN - в котором ВРМ получает, обрабатывает и возвращает  *
 * полученные от схемы данные                                   *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
} elseif($act == 'run') {              // Схема прислала данные, обрабатываем

    $target = $_REQUEST['target'];  // Пользователь, от имени которого выполняется блок
    $ums    = $_REQUEST['ums'];     // Данные об активности пользователя, массив в котором есть
                                    // id - номер элемента (комментария, поста, смотря о чём речь в активности)
                                    // from_id - UID пользователя
                                    // date - дата в формате timestamp
                                    // text - текст комментария, сообщения и т.д.
    $data1  = $_REQUEST['data1'];   // Вот сюда придёт JSON хранилища, ID которого хранится в настройке блока под именем data1
                                    // Можно передавать несколько хранилищ, но не увлекайтесь с объёмом
    $ps = $_REQUEST['paysys']['ps'];// Сюда придут настройки выбранной платёжной системы
    $out    = 0;                    // Номер выхода по умолчанию. Если дальнейший код не назначит другой выход - значит что-то не так

    /* Теперь, начинаем работать с тем, что прислала активность */

    $str = $ums['text'];                  // назначаем переменную $str значением текста из события
    $str = mb_strtolower($str, 'UTF-8');  // приводим текст к нижнему регистру
    $str = trim($str);                    // обрезка пробелов по краям строки
    $len = mb_strlen($str, 'UTF-8');      // считаем длину полученной строки

    /* Пример обработки присланного хранилища */

    if(!empty($data1['sprites'])) {                              // в хранилище есть спрайты?
        foreach ($data1['sprites'] as $k => $v) {                // перебираем их по одному в переменную $v и ключём массива $k
            if ($v['search'] == $str) {                          // если поле search равно тексту активности пользователя...
                if($data1['sprites'][$k]['res']['min'] > 0) {    // и минимальное значение спрайта больше ноля
                    $data1['sprites'][$k]['res']['min'] = 0;     // устанавливаем минимальное значение в ноль
                    $data1['sprites'][$k]['res']['max'] = 1000;  // чтобы спрайт показался всем, независимо от наличия ресурса
                    $out = 1;                                    // выход 1 - слово найдено
                } else {                                         // min уже сброшено в ноль
                    $out = 3;                                    // выход 3 - этот вариант был назван
                }
            }
        }

        if(!$out) {
            $out = 2;                                            // ничего не найдено
        }
    }

    // Сформировать массив данных на отдачу

    $responce = [
        'out' => $out,         // Обязательно должен быть номер выхода out, отличный от нуля!
        'value' => [           // Ещё можно отдать ключ value и переменные в нём будут доступны в схеме через $bN_value.ваши_ключи_массива
            'len' => $len,     // где N - порядковый номер блока в схеме
        ]
    ];

} elseif($act == '') {
    // Действие не задано, и что же нам сделать? Вы можете дополнить ВРМ своими действиями

}

// Отдать JSON, не кодируя кириллические символы в кракозябры
echo json_encode($responce, JSON_UNESCAPED_UNICODE);