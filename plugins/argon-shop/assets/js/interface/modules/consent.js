/**
 * ArgonShop — чекбоксы согласия.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет чекбоксами согласия на обработку данных, которые
 * блокируют кнопки отправки, пока пользователь не поставит
 * галочку.
 *
 * Примеры:
 *   - в корзине чекбокс управляет кнопкой «Отправить заказ»;
 *   - в кабинете чекбокс управляет кнопкой «Сохранить» (аккаунт);
 *   - в кабинете чекбокс управляет кнопкой «Сохранить» (email/пароль).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * input[type="checkbox"][data-consent-target]
 *   Чекбокс согласия.
 *
 *   Атрибут data-consent-target содержит CSS-селектор
 *   целевых кнопок. Пока чекбокс не отмечен, кнопки отключены;
 *   как только он отмечен, disabled снимается.
 *
 * Примеры разметки:
 *
 *   <input type="checkbox" data-consent-target=".submitCart">
 *   <input type="checkbox" data-consent-target="#accountSaveButton">
 *   <input type="checkbox" data-consent-target="#editEmailPasswordButton">
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsConsent } from './modules/consent.js';
 *     arsConsent.init();
 *
 * Модуль безопасно вызывать на любой странице: если чекбоксов
 * согласия нет, ничего не происходит.
 */

'use strict';

import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Модуль чекбоксов согласия.
 */
class ArsConsent {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация. Безопасно вызывать повторно.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const checkboxes = qsa( 'input[type="checkbox"][data-consent-target]' );

        if ( ! checkboxes.length ) {
            return;
        }

        this.#initialized = true;

        checkboxes.forEach( checkbox => {

            // Задаём начальное состояние кнопок
            this.#applyState( checkbox );

            // Реагируем на изменения
            on( checkbox, 'change', () => this.#applyState( checkbox ) );
        });
    }

    /**
     * Применяет состояние чекбокса к целевым кнопкам.
     *
     * @param  {HTMLInputElement} checkbox — чекбокс согласия
     * @return {void}
     */
    #applyState( checkbox ) {

        const selector = checkbox.dataset.consentTarget;

        if ( ! selector ) {
            return;
        }

        const targets = qsa( selector );

        targets.forEach( target => {
            target.disabled = ! checkbox.checked;
        });
    }
}

/**
 * Готовый синглтон модуля чекбоксов согласия.
 *
 * @type {ArsConsent}
 */
export const arsConsent = new ArsConsent();