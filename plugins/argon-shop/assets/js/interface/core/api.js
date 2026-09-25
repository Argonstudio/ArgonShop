/**
 * ArgonShop — AJAX-клиент для работы с admin-ajax.php.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Единая точка для всех AJAX-запросов фронтенда. Заменяет
 * десятки разбросанных $.ajax({...}) из старого кода.
 *
 * Автоматически:
 *   - подставляет URL из config.url
 *   - добавляет action и nonce в тело запроса
 *   - не добавляет nonce к actions из config.skipNonceActions
 *   - парсит JSON и обновляет nonce, если сервер вернул newNonce
 *   - приводит ошибки сети и HTTP к единому виду (throw Error)
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * ArsApiClient  — класс клиента
 * arsApi        — готовый синглтон для использования во всех модулях
 *
 * ============================================================
 * МЕТОДЫ
 * ============================================================
 *
 * post( action, data )              — POST, возвращает текст ответа
 * postJson( action, data )          — POST, возвращает распарсенный JSON
 * postFormData( action, formData )  — POST с FormData (для файлов)
 * setNonce( nonce )                 — обновить nonce вручную
 * getNonce()                        — получить текущий nonce
 *
 * ============================================================
 * ПРИМЕРЫ
 * ============================================================
 *
 *     import { arsApi } from '../core/api.js';
 *
 *     // Текстовый ответ
 *     const text = await arsApi.post( 'deleteProducts_shoppingCart', {
 *         productID: 42,
 *     });
 *
 *     // JSON-ответ
 *     const data = await arsApi.postJson( 'amountProducts_shoppingCart', {
 *         productID: 42,
 *         productAmount: 3,
 *     });
 *
 *     // Форма с файлами
 *     const result = await arsApi.postFormData( 'submitCart_shoppingCart', formData );
 */

'use strict';

import { config } from './config.js';
import { applyNonce } from './nonce.js';

/**
 * AJAX-клиент ArgonShop.
 */
export class ArsApiClient {

    constructor() {
        // Никакого кэша nonce здесь нет — читаем config каждый раз,
        // чтобы не расходиться с перехватчиком (nonce.js).
    }

    /**
     * URL обработчика admin-ajax.php.
     *
     * @return {string}
     */
    get url() {
        return config.url;
    }

    /**
     * Текущий nonce.
     *
     * @return {string}
     */
    getNonce() {
        return config.nonce;
    }

    /**
     * Обновляет nonce вручную.
     * Используется, если модуль получает nonce не из XHR-перехвата.
     *
     * @param {string} nonce — новый nonce
     * @return {void}
     */
    setNonce( nonce ) {
        applyNonce( nonce );
    }

    /**
     * Проверяет, нужно ли добавлять nonce к данному действию.
     *
     * @param  {string} action — имя AJAX-действия
     * @return {boolean}
     */
    shouldSkipNonce( action ) {
        return config.skipNonceActions.indexOf( action ) !== -1;
    }

    /**
     * Формирует тело запроса как URLSearchParams.
     *
     * @param  {string} action — имя AJAX-действия
     * @param  {Object} data   — данные запроса
     * @return {URLSearchParams}
     */
    buildBody( action, data = {} ) {

        const body = new URLSearchParams();

        body.append( 'action', action );

        if ( ! this.shouldSkipNonce( action ) && config.nonce ) {
            body.append( 'nonce', config.nonce );
        }

        for ( const [ key, value ] of Object.entries( data ) ) {

            if ( value === undefined || value === null ) {
                continue;
            }

            if ( Array.isArray( value ) ) {
                value.forEach( item => body.append( `${key}[]`, item ) );
                continue;
            }

            body.append( key, value );
        }

        return body;
    }

    /**
     * POST-запрос, возвращает сырой текст ответа.
     *
     * @param  {string} action — имя AJAX-действия
     * @param  {Object} [data={}] — данные запроса
     * @return {Promise<string>}
     *
     * @throws {Error} при ошибке сети или HTTP-статусе вне 2xx
     */
    async post( action, data = {} ) {

        const response = await fetch( config.url, {
            method:      'POST',
            credentials: 'same-origin',
            headers:     { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body:        this.buildBody( action, data ),
        });

        if ( ! response.ok ) {
            throw new Error( `HTTP ${response.status} при запросе "${action}"` );
        }

        const text = await response.text();

        // Проверяем newNonce вручную — на случай, если XHR-перехватчик
        // не сработал (например, сервер вернул JSON-ответ не через XHR).
        this.tryApplyNonceFromText( text );

        return text;
    }

    /**
     * POST-запрос, возвращает распарсенный JSON.
     *
     * Если ответ не является валидным JSON — возвращает объект
     * { raw: <текст ответа> }, чтобы вызывающий код мог решить,
     * что делать. Это лучше, чем бросать исключение: некоторые
     * AJAX-обработчики WordPress возвращают простые строки.
     *
     * @param  {string} action — имя AJAX-действия
     * @param  {Object} [data={}] — данные запроса
     * @return {Promise<Object>}
     *
     * @throws {Error} при ошибке сети или HTTP-статусе вне 2xx
     */
    async postJson( action, data = {} ) {

        const text = await this.post( action, data );

        try {
            return JSON.parse( text );
        } catch {
            return { raw: text };
        }
    }

    /**
     * POST-запрос с FormData (для форм с файлами).
     *
     * @param  {string}   action   — имя AJAX-действия
     * @param  {FormData} formData — готовая FormData с данными формы
     * @return {Promise<Object>} Распарсенный JSON-ответ
     *
     * @throws {Error} при ошибке сети или HTTP-статусе вне 2xx
     */
    async postFormData( action, formData ) {

        formData.append( 'action', action );

        if ( ! this.shouldSkipNonce( action ) && config.nonce ) {
            formData.append( 'nonce', config.nonce );
        }

        const response = await fetch( config.url, {
            method:      'POST',
            credentials: 'same-origin',
            body:        formData,
        });

        if ( ! response.ok ) {
            throw new Error( `HTTP ${response.status} при запросе "${action}"` );
        }

        const text = await response.text();

        this.tryApplyNonceFromText( text );

        try {
            return JSON.parse( text );
        } catch {
            return { raw: text };
        }
    }

    /**
     * Ищет newNonce в тексте ответа и применяет его.
     *
     * @param  {string} text — текст ответа
     * @return {void}
     */
    tryApplyNonceFromText( text ) {

        if ( ! text || text.indexOf( 'newNonce' ) === -1 ) {
            return;
        }

        try {

            const data = JSON.parse( text );

            if ( data && typeof data.newNonce === 'string' ) {
                applyNonce( data.newNonce );
            }

        } catch {
            // Не JSON — игнорируем
        }
    }
}

/**
 * Готовый синглтон AJAX-клиента.
 *
 * Используйте его во всех модулях:
 *
 *     import { arsApi } from '../core/api.js';
 *     const data = await arsApi.postJson( 'some_action', { ... } );
 *
 * @type {ArsApiClient}
 */
export const arsApi = new ArsApiClient();
