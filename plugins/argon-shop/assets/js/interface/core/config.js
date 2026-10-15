/**
 * ArgonShop — конфигурация фронтенда.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Центральное хранилище параметров, которые должны быть
 * доступны всем ES-модулям фронтенда. Значения приходят из PHP
 * через wp_localize_script() в argon-shop.php:
 *
 *     wp_localize_script( 'ars-main', 'arsWpAjax', array(
 *         'url'   => admin_url( 'admin-ajax.php' ),
 *         'nonce' => wp_create_nonce( 'argon_shop_nonce' ),
 *     ) );
 *
 * Чтобы не зависеть от глобальной переменной arsWpAjax напрямую
 * (её может не быть, если скрипт подключён без локализации),
 * здесь она читается безопасно, с запасными значениями.
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * config — объект с полями:
 *   url    {string} — URL обработчика admin-ajax.php
 *   nonce  {string} — текущий nonce для проверки на сервере
 *
 * t( key, ...args ) — возвращает переведённую строку интерфейса. Переводы
 *   приходят из PHP (arsWpAjax.i18n, подготовлены через __() в argon-shop.php,
 *   text domain "argon-shop"). If a key is missing, the English default
 *   используется значение из I18N_DEFAULTS. Поддерживает подстановки в стиле printf
 *   %s, %d и нумерованные %1$s / %2$d.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { config } from '../core/config.js';
 *
 *     console.log( config.url );   // https://site.ru/wp-admin/admin-ajax.php
 *     console.log( config.nonce ); // a1b2c3d4e5
 *
 *     import { t } from '../core/config.js';
 *
 *     el.textContent = t( 'filesUploaded', 3 ); // "Files uploaded: 3"
 */

'use strict';

/**
 * Глобальный объект arsWpAjax, который создаёт WordPress через wp_localize_script().
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
     * URL AJAX-обработчика WordPress.
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
    // Раньше здесь были deleteAllProducts_shoppingCart и ordersFiltersStatus —
    // теперь сервер проверяет nonce у всех действий.
    skipNonceActions: [],
};
/**
 * Английские значения по умолчанию для строк интерфейса, используемых модулями фронтенда.
 * Переведённые значения передаются из PHP в arsWpAjax.i18n
 * (см. argon_shop_js_i18n() в argon-shop.php). Держите оба списка синхронными.
 *
 * @since 1.1.0
 * @type {Object<string, string>}
 */
const I18N_DEFAULTS = {
    serverError:        'Server error',
    dataNotSaved:       'Error: the data was not saved',
    requestError:       'Request failed',
    tooManyFiles:       'You can attach no more than %d files.',
    fileTooBig:         'The file "%1$s" exceeds %2$s MB.',
    filesUploaded:      'Files uploaded: %d',
    cartEmpty:          'Cart is empty',
    productNotRemoved:  'Server error, the product was not removed',
    productsNotRemoved: 'Server error, the products were not removed',
    changesNotAccepted: 'Server error, the changes were not accepted',
    orderNotSent:       'Server error: the order was not sent',
    messageSent:        'Your message has been sent',
    searching:          'Searching...',
};

/**
 * Переведённые строки: значения из PHP перекрывают английские значения по умолчанию.
 *
 * @type {Object<string, string>}
 */
const i18n = Object.assign(
    {},
    I18N_DEFAULTS,
    ( localized && localized.i18n && typeof localized.i18n === 'object' ) ? localized.i18n : {}
);

/**
 * Возвращает переведённую строку интерфейса с подставленными значениями.
 *
 * @since 1.1.0
 * @param  {string}    key  — ключ строки (см. I18N_DEFAULTS)
 * @param  {...*}      args — значения для подстановок %s / %d / %1$s
 * @return {string}
 */
export function t( key, ...args ) {
    const template = ( typeof i18n[ key ] === 'string' ) ? i18n[ key ] : key;
    let index = 0;

    return template.replace( /%(?:(\d+)\$)?([sd%])/g, ( match, position, type ) => {
        if ( type === '%' ) {
            return '%';
        }
        const value = position ? args[ Number( position ) - 1 ] : args[ index++ ];
        if ( value === undefined ) {
            return match;
        }
        return ( type === 'd' ) ? String( parseInt( value, 10 ) ) : String( value );
    } );
}
