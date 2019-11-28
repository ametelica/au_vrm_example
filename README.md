Внешний расчётный модуль (далее ВРМ) представляет собой скрипт на любом языке, которые может принимать и отправлять JSON-данные. На схеме он появляется в виде блока "условие", у этого блока может быть произвольное количество выходов и настроек в интерфейсе редактирования блока. Делать ВРМ может очень многое - от проверки условий, до сложных расчётов и интеграции с платёжными системами. Вы можете создавать ВРМ с нужными свойствами самостоятельно используя представленный шаблон, в котором использованы примеры разных возможностей.

## Взаимодействие с ВРМ

У ВРМ есть несколько режимов, которые переключаются GET-переменной `act` - это: 
`options` - в нём АЮ получает информацию о том, какие настройки нужно показать пользователю на экране редактирования блока
`run` - обработка данных, полученных от АЮ и возврат переменных
`help` - справочные данные о ВРМ, это необязательный режим, но он может пригодиться, если кто-то другой будет использовать в схемах ваш блок

## Оptions

Чтение настроек происходит при подключении или переподключении ВРМ. Если добавили новые поля - в настройках редактирования они не появятся, пока не переподключите! Настройки полей отдаются в виде массива, со следующими ключами:
`title` - заголовок блока ВРМ
`vars` - поля ввода, в которых могут быть тексты, выпадающие списки, переключатели, у каждого из которых также должно быть имя, описание, значение по умолчании и дополнительные опции (смотрим в комментариях к коду примера)

## Run

Основной рабочий режим, в нём ВРМ получает данные события и переменные, которые пользователь определил в настройках.

## Help

В это режиме отдаётся массив с Markdown разметкой, который будет виден пользователю при выборе иконки Помощь в заголовке редактирования блока.

## Отладка ВРМ

ВРМ может запоминать один крайний запрос к себе и ответ на него, для этого откройте секцию дополнительных настроек в редактировании блока и активируйте переключатель отладки. В заголовке ВРМ появится иконка "жучка" (нужно перезайти в редактор блока), по клику на которую будут видны тексты запросов - входящего и исходящего.
