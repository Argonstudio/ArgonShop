<?php
/**
 * Список заказов пользователя в кабинете (часть шаблона)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cabinet/ordering' )
 * из шаблона myCabinetPage.php (страница кабинета покупателя).
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
 * as_view_user_orders( $user_ID, $filters )
 *   File: includes/interface/cabinet/userOrders/ordersView.php
 *   Выводит HTML-блок с заказами пользователя.
 *   Внутри вызывает:
 *     - as_get_user_orders()         — получение заказов (ordersView.php)
 *     - as_get_used_statuses()       — используемые статусы
 *     - as_create_user_ordersTable() — вывод таблицы заказов
 *     - as_view_user_order()         — вывод строки одного заказа
 *   Если у заказов больше одного статуса, выводит фильтр по статусу.
 * 
 * AJAX-ОБРАБОТЧИК ФИЛЬТРА:
 * 
 * as_ordersFiltersStatus_callback()
 *   File: includes/interface/cabinet/userOrders/ordersAjax.php
 *   Принимает userID и статусы, возвращает HTML-таблицу заказов
 *   без перезагрузки страницы.
 * 
 * JS-МОДУЛЬ:
 * 
 * assets/js/interface/modules/orders.js (plugin)
 *   Отправляет AJAX-запрос при выборе статуса в фильтре.
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КАБИНЕТА
 * ============================================================
 * 
 * $user_ID — ID текущего пользователя (get_current_user_id())
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$userdata = get_user_by( 'id', $user_ID );

// Функция плагина Argon Shop
// (includes/interface/cabinet/userOrders/ordersView.php)
if ( function_exists( 'as_view_user_orders' ) ) {
    as_view_user_orders( $user_ID );
}