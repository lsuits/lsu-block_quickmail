<?php

$string['pluginname'] = 'Quickmail';
$string['quickmail:cansend'] = "Разрешить пользователям отправлять email через Quickmail";
$string['quickmail:canconfig'] = "Разрешить пользователям настраивать Quickmail.";
$string['quickmail:canimpersonate'] = "Разрешить пользователям доступ к логам и истории.";
$string['quickmail:allowalternate'] = "Разрешить пользователям добавлять альтернативные email к курсу.";
$string['alternate'] = 'Альтернативные Email';
$string['composenew'] = 'Отправить Email';
$string['email'] = 'Email';
$string['drafts'] = 'Просмотреть проект';
$string['history'] = 'Посмотреть историю';
$string['log'] = $string['history'];
$string['from'] = 'От';
$string['selected'] = 'Выберите получателей';
$string['add_button'] = 'Добавить';
$string['remove_button'] = 'Удалить';
$string['add_all'] = 'Добавить еще';
$string['remove_all'] = 'Удалить все';
$string['role_filter'] = 'Фильтр роли';
$string['no_filter'] = 'Нет фильтров';
$string['potential_users'] = 'Потенциальные получатели';
$string['potential_sections'] = 'Потенциальные разделы';
$string['no_section'] = 'Нет в разделе';
$string['all_sections'] = 'Все разделы';
$string['attachment'] = 'Вложение(я)';
$string['subject'] = 'Тема';
$string['message'] = 'Сообщение';
$string['send_email'] = 'Отправить Email';
$string['save_draft'] = 'Сохранить проект';
$string['actions'] = 'Действия';
$string['signature'] = 'Подписи';
$string['delete_confirm'] = 'Вы действительно хотите удалить сообщение со следующими деталями: {$a}';
$string['title'] = 'Заголовок';
$string['sig'] ='Подпись';
$string['default_flag'] = 'По-умолчанию';
$string['config'] = 'Настройки';
$string['receipt'] = 'Отправить копию';

$string['no_alternates'] = 'Для {$a->fullname} альтернативных emails не найдено. Проверьте конфигурацию.';

$string['select_users'] = 'Выберите пользователей ...';
$string['select_groups'] = 'Выберите раздел ...';

// Config form strings
$string['allowstudents'] = 'Разрешить студентам использовать Quickmail';
$string['select_roles'] = 'Фильтр для роли';
$string['reset'] = 'Восстановить настройки системы по-умолчанию';

$string['no_type'] = '{$a} не приемлемый вид просмотра. Пожалуйста, используйте приложение правильно.';
$string['no_email'] = 'Нет email {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'У вас нет истории отправленных сообщений.';
$string['no_drafts'] = 'У вас нет проектов email.';
$string['no_subject'] = 'Укажите тему';
$string['no_course'] = 'Некорректный курс для {$a}';
$string['no_permission'] = 'У вас прав для отправки emails с Quickmail.';
$string['no_users'] = 'Нет пользователей, которым вы можете оправить email.';
$string['no_selected'] = 'Вы должны выбрать пользователей для оправки сообщений.';
$string['not_valid'] = 'Недействительный адрес email для просмотра логов: {$a}';
$string['not_valid_user'] = 'Вы не можете просмотреть другую историю email.';
$string['not_valid_action'] = 'Вы должны указать действие: {$a}';
$string['not_valid_typeid'] = 'Вы должны указать правильный email для {$a}';
$string['delete_failed'] = 'Ошибка удаления email';
$string['required'] = 'Пожалуйста, заполните необходимые поля.';
$string['prepend_class'] = 'Добавить название курса';
$string['prepend_class_desc'] = 'Добавить короткое название курса в тему этого email.';
$string['courselayout'] = 'Макет курса';
$string['courselayout_desc'] = 'Используйте макет курса (Use _Course_ page layout) при отображении страниц Quickmail. Использование этого параметра позволит использовать форму Moodle фиксированной ширины.';

$string['are_you_sure'] = 'Вы действительно хотите удалить  {$a->title}? Это действие нельзя отменить.';

// Alternate Email strings
$string['alternate_new'] = 'Добавить альтернативный адрес';
$string['sure'] = 'Вы действительно хотите удалить  {$a->address}? Это действие нельзя отменить.';
$string['valid'] = 'Активация статуса';
$string['approved'] = 'Одобрено';
$string['waiting'] = 'Ожидайте';
$string['entry_activated'] = 'Альтернативный email {$a->address} не может быть использован для {$a->course}.';
$string['entry_key_not_valid'] = 'Активация ссылки больше недействительна {$a->address}. Продолжить активацию ссылки.';
$string['entry_saved'] = 'Альтернативный адрес {$a->address} сохранен.';
$string['entry_success'] = 'Это сообщение было выслано на {$a->address}, чтобы подтвердить, что адрес email действительно  существует. 
Инструкция по активации адреса содержится в его содержании.';
$string['entry_failure'] = 'Сообщение не может быть отправлено на {$a->address}. Пожалуйста, запишите {$a->address} правильно, или убедитесь, что этот адрес действительно существует. Затем повторите попытку.';
$string['alternate_from'] = 'Moodle: Quickmail';
$string['alternate_subject'] = 'Проверка альтернативного email';
$string['alternate_body'] = '
<p>
{$a->fullname} был добавлен {$a->address} как альтернативный адрес для  {$a->course}.
</p>

<p>
Это письмо было отправлено с целью убедиться, что этот адрес существует, и владелец
этого адреса имеет соответствующие права в Moodle.
</p>

<p>
Если вы хотите  завершить процесс проверки, пожалуйста, перейдите по следующему адресу (или скопируйте в адресную строку вашего браузера): {$a->url}.
</p>

<p>
Если это письмо не имеет никакого отношения к вам, то просто проигнорируйте это сообщение.
</p>

Спасибо.
';
