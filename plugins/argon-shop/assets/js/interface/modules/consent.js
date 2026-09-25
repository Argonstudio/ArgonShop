/**
 * ArgonShop — чекбоксы согласия.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
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
 *   - в ЛК чекбокс управляет кнопкой «Сохранить» (аккаунт);
 *   - в ЛК чекбокс управляет кнопкой «Сохранить» (email/пароль).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * input[type="checkbox"][data-consent-target]
 *   Чекбокс согласия.
 *
 *   Атрибут data-consent-target содержит CSS-селектор для
 *   целевых кнопок. Пока чекбокс не отмечен — кнопки disabled;
 *   как только отмечен — disabled снимается.
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
 * Модуль безопасен для вызова на любой странице: если чекбоксов
 * согласия нет, ничего не выполняется.
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
     * Инициализация. Безопасна для повторного вызова.
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

            // Устанавливаем исходное состояние кнопок
            this.#applyState( checkbox );

            // Реакция на изменение
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