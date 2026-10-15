/**
 * ArgonShop — модуль личного кабинета.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет интерактивными элементами кабинета:
 *
 *   1. Включение поля email (кнопка «Изменить»).
 *   2. Отправка формы смены email и пароля.
 *   3. Сохранение данных аккаунта (физлицо и юрлицо).
 *   4. Переключение блока полей юрлица (чекбокс «Юридическое лицо»).
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * Вкладка «Настройки»:
 *   #editEmail                       — поле email (по умолчанию отключено).
 *   #editEmailActive                 — кнопка «Изменить» (разблокирует поле).
 *   #editOldPass, 
 *   #editNewPass, 
 *   #editNewPassConfirm              — поля паролей.
 *   #editEmailPasswordButton         — кнопка «Сохранить».
 *
 *   .editEmailError span             — сообщение об ошибке email.
 *   .editPassError span              — сообщение об ошибке старого пароля.
 *   .editNewPassError span           — сообщение об ошибке нового пароля.
 *   .editNewPassConfirmError span    — сообщение о несовпадении паролей.
 *   .editResult span                 — результат сохранения.
 *
 * Вкладка «Аккаунт и адрес»:
 *   .accountCustomCheckbox           — стилизованный чекбокс «Юридическое лицо».
 *   #accountLegal                    — настоящий скрытый чекбокс.
 *   #accountLegalBlock               — таблица полей юрлица.
 *   #accountSaveButton               — кнопка «Сохранить» (data-userid).
 *   .accountResult span              — результат сохранения.
 *
 * Классы полей формы:
 *   .accountDetail                   — поля физлица.
 *   .accountLegalDetail              — поля юрлица.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЯ
 * ============================================================
 *
 * cabinetEditEmailPassword
 *   Принимает: userID, email, oldEmail, pass, newPass, newPassConfirm.
 *   Возвращает JSON:
 *     {
 *       edited:   { newEmail?: '...', newPass?: '...' },
 *       errors:   { email?: '...', pass?: '...', newPass?: '...' },
 *       newNonce: '...'   // если вызывался wp_set_password
 *     }
 *
 * cabinetAccountSave
 *   Принимает: userID, forBack (JSON-строка).
 *   Возвращает текст: сообщение об успехе или ошибке.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCabinet } from './modules/cabinet.js';
 *     arsCabinet.init();
 */

'use strict';

import { arsApi }        from '../core/api.js';
import { t }             from '../core/config.js';
import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Шаблон допустимых имён полей формы.
 * Допустимы латинские и кириллические буквы, цифры и подчёркивание (ключи сохранённых данных).
 * Случайные поля от расширений браузера отфильтровываются.
 *
 * @type {RegExp}
 */
const FIELD_NAME_PATTERN = /^[\w\u0410-\u044F\u0451]+$/;

/**
 * Модуль личного кабинета.
 */
class ArsCabinet {

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

        // Мы в кабинете, если есть хотя бы один
        // из ключевых элементов.
        if ( ! qs( '#editEmailPasswordButton' ) && ! qs( '#accountSaveButton' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindEmailActivation();
        this.#bindEmailPasswordSubmit();
        this.#bindAccountSave();
        this.#bindLegalBlockToggle();
    }

    // ============================================================
    // АКТИВАЦИЯ EMAIL
    // ============================================================

    /**
     * Кнопка «Изменить» разблокирует поле email.
     *
     * @return {void}
     */
    #bindEmailActivation() {

        const button = qs( '#editEmailActive' );
        const input  = qs( '#editEmail' );

        if ( ! button || ! input ) {
            return;
        }

        on( button, 'click', () => {

            const currentValue = input.value;

            input.disabled = false;
            input.focus();
            input.value = currentValue;

            button.style.display = 'none';
        });
    }

    // ============================================================
    // СМЕНА EMAIL И ПАРОЛЯ
    // ============================================================

    /**
     * Отправка формы смены email и пароля.
     *
     * @return {void}
     */
    #bindEmailPasswordSubmit() {

        const button = qs( '#editEmailPasswordButton' );

        if ( ! button ) {
            return;
        }

        on( button, 'click', async () => {

            const userID      = button.dataset.userid;
            const emailInput  = qs( '#editEmail' );
            const oldPassInput = qs( '#editOldPass' );
            const newPassInput = qs( '#editNewPass' );
            const confirmInput = qs( '#editNewPassConfirm' );

            if ( ! emailInput || ! oldPassInput || ! newPassInput || ! confirmInput ) {
                return;
            }

            const email          = emailInput.value;
            const oldEmail       = emailInput.dataset.oldemail || '';
            const pass           = oldPassInput.value;
            const newPass        = newPassInput.value;
            const newPassConfirm = confirmInput.value;

            try {

                const data = await arsApi.postJson( 'cabinetEditEmailPassword', {
                    userID,
                    email,
                    oldEmail,
                    pass,
                    newPass,
                    newPassConfirm,
                });

                this.#handleEmailPasswordResponse( data, email );

            } catch {
                this.#setResultMessage( t( 'serverError' ) );
            }
        });
    }

    /**
     * Обрабатывает ответ сервера на смену email/пароля.
     *
     * @param  {Object} response — ответ сервера
     * @param  {string} email    — введённый email (для обновления data-oldemail)
     * @return {void}
     */
        /**
     * Обрабатывает ответ сервера на смену email/пароля.
     *
     * @param  {Object} response — ответ сервера
     * @param  {string} email    — введённый email (для обновления data-oldemail)
     * @return {void}
     */
    #handleEmailPasswordResponse( response, email ) {

        // Полностью сбрасываем все сообщения об ошибках
        this.#clearAllErrors();

        // Очищаем результат
        const resultBlock = qs( '.editResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = '';
        }

        const hasErrors = response.errors && Object.keys( response.errors ).length > 0;

        // Показываем ошибки, если они есть
        if ( hasErrors ) {
            this.#showErrors( response.errors );
            return;
        }

        // Обрабатываем успешные изменения
        if ( ! response.edited ) {
            return;
        }

        const edited = response.edited;
        const messages = [];

        // Смена пароля
        if ( edited.newPass ) {

            this.#clearPasswordFields();

            messages.push( edited.newPass );

            // Сессия и nonce стали недействительными — самый надёжный вариант
            // получить свежее состояние новым запросом.
            if ( resultBlock ) {
                resultBlock.textContent = messages.join( ', ' );
            }

            window.setTimeout( () => window.location.reload(), 2000 );
            return;
        }

        // Смена email
        if ( edited.newEmail ) {

            const emailInput = qs( '#editEmail' );

            if ( emailInput ) {
                emailInput.dataset.oldemail = email;
            }

            // После смены email очищаем поля паролей — иначе пароль
            // остаётся на виду в поле, а ошибки старого пароля
            // продолжают висеть на экране.
            this.#clearPasswordFields();

            messages.push( edited.newEmail );
        }

        if ( resultBlock ) {
            resultBlock.textContent = messages.join( ', ' );
        }
    }
    
    /**
     * Сбрасывает все сообщения об ошибках формы смены email/пароля.
     *
     * Убирает класс editErrorActive с контейнеров и очищает текст
     * внутри их элементов span.
     *
     * @return {void}
     */
    #clearAllErrors() {

        qsa( '.editError' ).forEach( block => {

            block.classList.remove( 'editErrorActive' );

            const span = qs( 'span', block );

            if ( span ) {
                span.textContent = '';
            }
        });
    }

    /**
     * Полностью очищает поля паролей и связанные с ними сообщения
     * об ошибках. Используется после успешной смены email или пароля.
     *
     * @return {void}
     */
    #clearPasswordFields() {

        // Очищаем значения полей
        const oldPassInput = qs( '#editOldPass' );
        const newPassInput = qs( '#editNewPass' );
        const confirmInput = qs( '#editNewPassConfirm' );

        if ( oldPassInput ) oldPassInput.value = '';
        if ( newPassInput ) newPassInput.value = '';
        if ( confirmInput ) confirmInput.value = '';

        // Явно убираем класс и очищаем текст у трёх блоков,
        // связанных с паролями
        [
            '.editPassError',
            '.editNewPassError',
            '.editNewPassConfirmError',
        ].forEach( selector => {

            const block = qs( selector );

            if ( ! block ) {
                return;
            }

            block.classList.remove( 'editErrorActive' );

            const span = qs( 'span', block );

            if ( span ) {
                span.textContent = '';
            }
        });
    }

    /**
     * Показывает ошибки формы смены email/пароля.
     *
     * @param  {Object} errors — карта ошибок { email, pass, newPass, newPassConfirm }
     * @return {void}
     */
    #showErrors( errors ) {

        const map = {
            email:          '.editEmailError span',
            pass:           '.editPassError span',
            newPass:        '.editNewPassError span',
            newPassConfirm: '.editNewPassConfirmError span',
        };

        for ( const [ key, selector ] of Object.entries( map ) ) {

            if ( ! errors[ key ] ) {
                continue;
            }

            const span = qs( selector );

            if ( ! span ) {
                continue;
            }

            span.textContent = errors[ key ];
            span.parentElement.classList.add( 'editErrorActive' );
        }
    }

    /**
     * Выводит сообщение в блок результата смены email/пароля.
     *
     * @param  {string} message — текст сообщения
     * @return {void}
     */
    #setResultMessage( message ) {

        const resultBlock = qs( '.editResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = message;
        }
    }

    // ============================================================
    // СОХРАНЕНИЕ АККАУНТА
    // ============================================================

    /**
     * Сохранение данных аккаунта.
     *
     * @return {void}
     */
    #bindAccountSave() {

        const button = qs( '#accountSaveButton' );

        if ( ! button ) {
            return;
        }

        on( button, 'click', async () => {

            const userID   = button.dataset.userid;
            const checkbox = qs( '#accountLegal' );

            const forBack = {
                accountDetail: this.#collectFields( '.accountDetail' ),
            };

            if ( checkbox && checkbox.checked ) {

                const legalFields = this.#collectFields( '.accountLegalDetail' );

                if ( Object.keys( legalFields ).length > 0 ) {
                    forBack.accountLegalDetail = legalFields;
                }
            }

            try {

                const text = await arsApi.post( 'cabinetAccountSave', {
                    userID,
                    forBack: JSON.stringify( forBack ),
                });

                this.#setAccountResult( text );

            } catch {
                this.#setAccountResult( t( 'dataNotSaved' ) );
            }
        });
    }

    /**
     * Собирает поля формы в объект { name: value }.
     *
     * @param  {string} selector — CSS-селектор полей
     * @return {Object}
     */
    #collectFields( selector ) {

        const fields = qsa( selector );

        const result = {};

        fields.forEach( field => {

            const name = field.getAttribute( 'name' );

            if ( ! name || ! FIELD_NAME_PATTERN.test( name ) ) {
                return;
            }

            result[ name ] = field.value;
        });

        return result;
    }

    /**
     * Выводит сообщение в блок результата сохранения аккаунта.
     *
     * @param  {string} message — текст сообщения
     * @return {void}
     */
    #setAccountResult( message ) {

        const resultBlock = qs( '.accountResult span' );

        if ( resultBlock ) {
            resultBlock.textContent = message;
        }
    }

    // ============================================================
    // ЧЕКБОКС ЮРЛИЦА
    // ============================================================

    /**
     * Переключение блока полей юрлица.
     *
     * Логика:
     *   - клик по .accountCustomCheckbox переключает #accountLegal
     *     и показывает или скрывает #accountLegalBlock;
     *   - при загрузке страницы блок показывается, только если
     *     чекбокс отмечен.
     *
     * @return {void}
     */
    #bindLegalBlockToggle() {

        const checkbox = qs( '#accountLegal' );
        const block    = qs( '#accountLegalBlock' );
        const custom   = qs( '.accountCustomCheckbox' );

        if ( ! checkbox || ! block ) {
            return;
        }

        // Начальное состояние при загрузке страницы
        block.style.display = checkbox.checked ? 'table' : 'none';

        const toggle = () => {

            const nextState = ! checkbox.checked;

            checkbox.checked = nextState;
            block.style.display = nextState ? 'table' : 'none';
        };

        // Клик по стилизованному чекбоксу
        if ( custom ) {
            on( custom, 'click', toggle );
        }

        // Клик по настоящему чекбоксу (на случай, если он видим)
        on( checkbox, 'click', () => {
            block.style.display = checkbox.checked ? 'table' : 'none';
        });
    }
}

/**
 * Готовый синглтон модуля кабинета.
 *
 * @type {ArsCabinet}
 */
export const arsCabinet = new ArsCabinet();