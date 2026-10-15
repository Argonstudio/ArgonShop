/**
 * ArgonShop — глобальный перехватчик AJAX-ответов для обновления nonce.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Сессии и nonce WordPress становятся недействительными в двух ситуациях:
 *
 * 1. wp_set_password() — при смене пароля в кабинете
 *    все активные сессии пользователя сбрасываются, и старый
 *    nonce становится недействительным.
 *
 * 2. wp_signon() — при регистрации пользователя из формы заказа
 *    сессия тоже пересоздаётся.
 *
 * Если после этого пользователь продолжает работать на странице
 * (например, открывает корзину или меняет фильтр заказов), его
 * AJAX-запросы будут отклонены сервером, потому что nonce устарел.
 *
 * Решение: PHP-обработчики возвращают новый nonce в ответе
 * (поле newNonce). Перехватчик читает это поле и обновляет
 * nonce в config и в arsApi.
 *
 * ============================================================
 * КАК ЭТО РАБОТАЕТ
 * ============================================================
 *
 * Перехватывается XMLHttpRequest.prototype.send — так ловятся все
 * AJAX-запросы страницы, включая сделанные через jQuery и
 * сторонние библиотеки. При каждом успешном ответе текст
 * разбирается как JSON, и если в нём есть newNonce —
 * значение применяется.
 *
 * Этот перехват глобальный и нужен один раз на страницу. Не
 * пытайтесь вызывать initNonceInterceptor() повторно.
 *
 * ВАЖНО: fetch() тоже покрыт, потому что при использовании fetch
 * поверх XMLHttpRequest (в браузерах без нативного fetch)
 * цепочка сохраняется. В современных браузерах fetch не проходит
 * через XHR, но все наши собственные запросы идут через
 * ArsApiClient, который сам проверяет newNonce в ответе.
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * initNonceInterceptor() — включает перехватчик (вызывается один раз)
 *
 * ============================================================
 * ПРИМЕР
 * ============================================================
 *
 *     import { initNonceInterceptor } from './core/nonce.js';
 *
 *     initNonceInterceptor();
 */

'use strict';

import { config } from './config.js';

/**
 * Обновляет nonce в config и везде, где он закэширован.
 *
 * Функция намеренно не импортирует arsApi из api.js, чтобы
 * избежать циклической зависимости (api.js тоже может обновлять
 * nonce через свой метод setNonce). Поэтому здесь меняется только config,
 * а ArsApiClient читает nonce из config при каждом запросе.
 *
 * @param {string} newNonce — новое значение nonce от сервера
 * @return {void}
 */
const applyNonce = ( newNonce ) => {

    if ( typeof newNonce !== 'string' || newNonce === '' ) {
        return;
    }

    if ( config.nonce === newNonce ) {
        return;
    }

    config.nonce = newNonce;

    // Событие для тех, кому нужно знать об обновлении
    // (например, модуль кабинета может показать уведомление).
    window.dispatchEvent( new CustomEvent( 'ars:nonce-updated', {
        detail: { nonce: newNonce },
    }));
};

/**
 * Пытается извлечь newNonce из текста ответа.
 *
 * Ответ может быть:
 *   - JSON-объектом с полем newNonce
 *   - обычным текстом (тогда искать newNonce негде)
 *
 * @param {string} responseText — сырой текст ответа
 * @return {void}
 */
const tryExtractNonce = ( responseText ) => {

    if ( ! responseText || typeof responseText !== 'string' ) {
        return;
    }

    // Быстрая проверка: поиск «newNonce» в тексте дешевле, чем
    // попытка разобрать JSON при каждом ответе (а их может быть много).
    if ( responseText.indexOf( 'newNonce' ) === -1 ) {
        return;
    }

    try {

        const data = JSON.parse( responseText );

        if ( data && typeof data.newNonce === 'string' ) {
            applyNonce( data.newNonce );
        }

    }catch(e) {
        // Не JSON — ничего не делаем
    }
};

/**
 * Устанавливает перехватчик на XMLHttpRequest.
 *
 * Идемпотентна: повторный вызов ничего не делает.
 *
 * @return {void}
 */
export const initNonceInterceptor = () => {

    if ( typeof XMLHttpRequest === 'undefined' ) {
        return;
    }

    // Защита от двойной инициализации
    if ( XMLHttpRequest.prototype.__arsNoncePatched ) {
        return;
    }

    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.send = function( ...args ) {

        this.addEventListener( 'load', () => {

            // Читаем только успешные ответы (2xx) и редиректы
            // с телом — остальные неинтересны
            if ( this.status < 200 || this.status >= 400 ) {
                return;
            }

            tryExtractNonce( this.responseText );
        });

        return originalSend.apply( this, args );
    };

    XMLHttpRequest.prototype.__arsNoncePatched = true;
};

/**
 * Экспортирует applyNonce для случаев, когда модуль получает nonce
 * не из перехвата XHR, а из ответа собственного fetch.
 *
 * Пример: ArsApiClient.postJson() сам разбирает JSON и может
 * вызвать applyNonce напрямую, не дожидаясь перехватчика.
 *
 * @param {string} newNonce — новое значение nonce
 * @return {void}
 */
export { applyNonce };