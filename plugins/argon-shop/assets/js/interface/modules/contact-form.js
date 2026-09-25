/**
 * ArgonShop — обработка формы обратной связи (Contact Form 7).
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обеспечивает работу формы обратной связи, встроенной в модальное
 * окно Fancybox:
 *
 *   1. Отключает Fancybox и подменяет поведение кнопки
 *      «Обратная связь» на обычную ссылку, если браузер не
 *      поддерживает FormData (например, старый IE) — тогда форма
 *      отправляется традиционным способом на страницу /kontakty/.
 *
 *   2. Закрывает модальное окно после успешной отправки формы
 *      через событие wpcf7mailsent, которое генерирует CF7.
 *
 *   3. Показывает сообщение об успехе, если пользователь отправил
 *      форму без прямого клика по кнопке (например, программным
 *      событием). Это защита от «залипания» модального окна в
 *      редких сценариях.
 *
 * ============================================================
 * ВНЕШНИЕ ЗАВИСИМОСТИ
 * ============================================================
 *
 * - Contact Form 7 — генерирует событие wpcf7mailsent
 *   на контейнере формы с id вида wpcf7-f{ID}-o{номер}.
 *
 * - Fancybox 3 (на jQuery) — используется для показа модального
 *   окна. Событие закрытия вызывается через глобальный объект
 *   window.jQuery.fancybox.close().
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * #linkFeedback         — ссылка «Обратная связь» в шапке.
 * #wpcf7-f7-o1          — контейнер формы CF7 (ID зависит от
 *                         настроек плагина).
 * .wpcf7-submit         — кнопка отправки формы.
 * .wpcf7-response-output — блок ответа CF7.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsContactForm } from './modules/contact-form.js';
 *     arsContactForm.init();
 *
 * Модуль безопасен для вызова на любой странице: если формы
 * обратной связи нет, ничего не выполняется.
 */

'use strict';

import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Задержка перед закрытием модального окна, в миллисекундах.
 * Даёт пользователю время увидеть сообщение об отправке.
 *
 * @type {number}
 */
const CLOSE_DELAY_MS = 1500;

/**
 * Модуль формы обратной связи.
 */
class ArsContactForm {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация. Безопасна для повторного вызова.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        this.#initialized = true;

        this.#fallbackForOldBrowsers();
        this.#bindCf7Success();
        this.#bindSubmitClick();
    }

    // ============================================================
    // ССЫЛКА БЕЗ FANCYBOX ДЛЯ СТАРЫХ БРАУЗЕРОВ
    // ============================================================

    /**
     * Если браузер не поддерживает FormData, убирает у ссылки
     * «Обратная связь» атрибуты Fancybox и ведёт на страницу
     * контактов.
     *
     * @return {void}
     */
    #fallbackForOldBrowsers() {

        if ( typeof window.FormData === 'function' ) {
            return;
        }

        const link = qs( '#linkFeedback' );

        if ( ! link ) {
            return;
        }

        link.setAttribute( 'href', '/kontakty/' );
        link.removeAttribute( 'data-fancybox' );
        link.removeAttribute( 'data-src' );
    }

    // ============================================================
    // УСПЕШНАЯ ОТПРАВКА CF7
    // ============================================================

    /**
     * Подписывается на событие wpcf7mailsent и закрывает модальное
     * окно после успешной отправки формы.
     *
     * @return {void}
     */
    #bindCf7Success() {

        const cf7Container = qs( '[id^="wpcf7-f"]' );

        if ( ! cf7Container ) {
            return;
        }

        cf7Container.addEventListener( 'wpcf7mailsent', () => {
            this.#closeFancyboxWithDelay();
        }, false );
    }

    // ============================================================
    // ОБРАБОТКА КЛИКА ПО КНОПКЕ ОТПРАВКИ
    // ============================================================

    /**
     * Обрабатывает клик по кнопке отправки формы.
     *
     * Если клик был совершён вне активной области кнопки
     * (программный клик или событие без координат) — прерывает
     * отправку и показывает сообщение об успехе.
     *
     * @return {void}
     */
    #bindSubmitClick() {

        const buttons = qsa( '.wpcf7-submit' );

        if ( ! buttons.length ) {
            return;
        }

        buttons.forEach( button => {

            on( button, 'click', ( event ) => {

                const rect = button.getBoundingClientRect();

                const relativeX = event.clientX - rect.left;
                const relativeY = event.clientY - rect.top;

                // Реальный клик мышью — координаты внутри кнопки
                if ( relativeX > 0 && relativeY > 0 ) {
                    return;
                }

                // Программный или нестандартный клик
                event.preventDefault();

                const responseBlock = qs( '.wpcf7-response-output' );

                if ( responseBlock ) {
                    responseBlock.classList.add( 'wpcf7-mail-sent-ok' );
                    responseBlock.style.display = 'block';
                    responseBlock.textContent = 'Ваше сообщение успешно отправлено';
                }

                this.#closeFancyboxWithDelay();
            });
        });
    }

    // ============================================================
    // ЗАКРЫТИЕ МОДАЛЬНОГО ОКНА
    // ============================================================

    /**
     * Закрывает текущее модальное окно Fancybox через заданную
     * задержку.
     *
     * Fancybox подключён через jQuery, поэтому вызывается через
     * глобальный window.jQuery. Если по какой-то причине объект
     * недоступен — просто ничего не делаем.
     *
     * @return {void}
     */
    #closeFancyboxWithDelay() {

        window.setTimeout( () => {

            if ( window.jQuery && window.jQuery.fancybox ) {
                window.jQuery.fancybox.close();
            }
        }, CLOSE_DELAY_MS );
    }
}

/**
 * Готовый синглтон модуля формы обратной связи.
 *
 * @type {ArsContactForm}
 */
export const arsContactForm = new ArsContactForm();