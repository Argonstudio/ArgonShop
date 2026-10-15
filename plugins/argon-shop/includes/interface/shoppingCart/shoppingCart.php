<?php
/**
 * Корзина и оформление заказа — загрузка модулей
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * Модули (раньше всё было в этом файле, ~1200 строк):
 * - cart-ajax.php             — AJAX корзины: добавление, количество, удаление, очистка, оптовая цена
 * - checkout.php              — основной обработчик оформления (submitCart) и хуки заказа
 * - checkout-fields.php       — поля формы, обязательные поля, проверка email/телефона
 * - checkout-files.php        — проверка и временное хранение файлов заказа
 * - checkout-registration.php — регистрация покупателя при оформлении
 * - checkout-emails.php       — HTML писем и отправка
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/cart-ajax.php';
require_once __DIR__ . '/checkout-fields.php';
require_once __DIR__ . '/checkout-files.php';
require_once __DIR__ . '/checkout-registration.php';
require_once __DIR__ . '/checkout-emails.php';
require_once __DIR__ . '/checkout.php';
