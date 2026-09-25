<?php
/**
 * Список заказов пользователя в личном кабинете (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/ordering' )
 * из шаблона myCabinetPage.php (страница личного кабинета).
 * 
 * ЧТО ВЫВОДИТ:
 * Список заказов пользователя с фильтром по статусу.
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * view_user_orders( $user_ID, $filters )
 *   Файл: includes/interface/cabinet/userOrders/ordersView.php
 *   Выводит HTML-блок с заказами пользователя.
 *   Внутри вызывает:
 *     - get_user_orders()         — получение заказов (ordersView.php)
 *     - get_used_statuses()       — используемые статусы
 *     - create_user_ordersTable() — вывод таблицы заказов
 *     - view_user_order()         — вывод одной строки заказа
 *   Если заказов больше одного статуса — выводит фильтр по статусу.
 * 
 * AJAX-ОБРАБОТЧИК ФИЛЬТРА:
 * 
 * ordersFiltersStatus_callback()
 *   Файл: includes/interface/cabinet/userOrders/ordersAjax.php
 *   Принимает userID и статусы, возвращает HTML-таблицу заказов
 *   без перезагрузки страницы.
 * 
 * JS-ФУНКЦИЯ:
 * 
 * ordersFiltersStatus() (из assets/cabinet/orders/orders.js)
 *   Отправляет AJAX-запрос при выборе статуса в фильтре.
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КАБИНЕТА
 * ============================================================
 * 
 * $user_ID — ID текущего пользователя (get_current_user_id())
 */

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$userdata = get_user_by( 'id', $user_ID );

// Функция плагина Argon Shop
// (includes/interface/cabinet/userOrders/ordersView.php)
if ( function_exists( 'view_user_orders' ) ) {
    view_user_orders( $user_ID );
}