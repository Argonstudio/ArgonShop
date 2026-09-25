/**
 * ArgonShop — конфигурация фронтенда.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Централизованное хранение параметров, которые должны быть
 * доступны всем ES-модулям фронтенда. Значения приходят из PHP
 * через wp_localize_script() в файле argon-shop.php:
 *
 *     wp_localize_script( 'ars-main', 'arsWpAjax', array(
 *         'url'   => admin_url( 'admin-ajax.php' ),
 *         'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
 *     ) );
 *
 * Чтобы не привязываться к глобальной переменной arsWpAjax напрямую
 * (её может не оказаться, если скрипт подключён без локализации),
 * здесь выполняется безопасное чтение с fallback-значениями.
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * config — объект с полями:
 *   url    {string} — URL обработчика admin-ajax.php
 *   nonce  {string} — текущий nonce для проверки на сервере
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { config } from '../core/config.js';
 *
 *     console.log( config.url );   // https://site.ru/wp-admin/admin-ajax.php
 *     console.log( config.nonce ); // a1b2c3d4e5
 */

'use strict';

/**
 * Глобальный объект arsWpAjax, созданный WordPress через wp_localize_script().
 *
 * @type {Object|undefined}
 */
const localized = ( typeof window.arsWpAjax !== 'undefined' ) ? window.arsWpAjax : undefined;

/**
 * Конфигурация фронтенда ArgonShop.
 *
 * @type {{ url: string, nonce: string, skipNonceActions: string[] }}
 */
export const config = {

    /**
     * URL обработчика AJAX-запросов WordPress.
     *
     * @type {string}
     */
    url: ( localized && typeof localized.url === 'string' && localized.url !== '' )
        ? localized.url
        : '/wp-admin/admin-ajax.php',

    /**
     * Nonce для проверки AJAX-запросов на сервере.
     * Обновляется автоматически, если сервер вернул новое значение
     * в поле newNonce (см. core/nonce.js).
     *
     * @type {string}
     */
    nonce: ( localized && typeof localized.nonce === 'string' )
        ? localized.nonce
        : '',

    /**
     * URL страницы корзины.
     * Используется модулями product и card-product для редиректа
     * при нажатии кнопки «Купить». Если страница корзины не настроена
     * — пустая строка, редирект не выполняется.
     *
     * @type {string}
     */
    cartUrl: ( localized && typeof localized.cartUrl === 'string' )
        ? localized.cartUrl
        : '',

    /**
     * Список AJAX-действий, к которым nonce не добавляется.
     *
     * @type {string[]}
     */
    skipNonceActions: [
        'deleteAllProducts_shoppingCart',
        'ordersFiltersStatus',
    ],
};