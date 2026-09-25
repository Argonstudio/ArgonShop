/**
 * ArgonShop — поля формы корзины.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Отвечает за поля формы оформления заказа, не связанные
 * с товарами и корзиной:
 *
 *   1. Клик по области .block-regQuestion переключает
 *      чекбокс .cartReg — «Зарегистрировать вас?».
 *      Сам чекбокс скрыт, клик идёт по всей плашке с текстом.
 *
 *   2. Отметка .cartReg показывает/скрывает блок .cartLogin
 *      с полем логина.
 *
 *   3. Клик по кнопке .labelAddFile программно открывает
 *      диалог выбора файлов у input[type=file][name="fileCart[]"].
 *
 *   4. После выбора файлов — валидация по data-атрибутам
 *      (data-sizeFile, data-amountFiles) и вывод списка
 *      в .loadedFiles.
 *
 * Вся работа с товарами и заказом — в cart.js.
 *
 * ============================================================
 * ПОЧЕМУ ПРЯМЫЕ ОБРАБОТЧИКИ, А НЕ ДЕЛЕГИРОВАНИЕ
 * ============================================================
 *
 * Делегирование на document для .cartReg не работает, потому
 * что чекбокс скрыт через CSS (display:none), это историческая реализация темы.
 * 
 * Решил это не переписывать, прямой обработчик надежнее.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .block-regQuestion    — плашка с чекбоксом и текстом
 * .cartReg              — сам чекбокс (скрыт)
 * .cartLogin            — блок с полем логина (скрыт)
 *
 * .labelAddFile         — кнопка «Прикрепить файлы»
 * input[type=file][name="fileCart[]"]
 *     data-sizeFile    — макс. размер файла в МБ
 *     data-amountFiles — макс. количество файлов
 *
 * .block-fileCartMessage — общий контейнер блока вывода
 * .fileCartMessage      — счётчик и сообщения об ошибках
 * .loadSaccess          — заголовок «ЗАГРУЖЕНО ФАЙЛОВ: N»
 * .loadError            — сообщение об ошибке
 * .loadedFiles          — контейнер списка (скрыт)
 * .loadedFiles > ol > li — строки с именами файлов
 *
 * ============================================================
 */

'use strict';

import { qs, qsa } from '../../shared/dom.js';

/**
 * Модуль полей формы корзины.
 */
class ArsCartForm {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация обработчиков.
     *
     * Безопасна для любой страницы: если .form-cart нет,
     * модуль просто ничего не делает.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        // Формы корзины нет на странице — выходим
        if ( ! qs( '.form-cart' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindRegisterQuestion();
        this.#bindRegisterToggle();
        this.#bindFileAttach();
        this.#bindFileUpload();
    }

    // ============================================================
    // РЕГИСТРАЦИЯ
    // ============================================================

    /**
     * Клик по плашке .block-regQuestion эмулирует клик
     * по скрытому чекбоксу .cartReg.
     *
     * @return {void}
     */
    #bindRegisterQuestion() {

        qsa( '.block-regQuestion' ).forEach( ( question ) => {

            question.addEventListener( 'click', ( event ) => {

                const checkbox = question.querySelector( '.cartReg' );

                if ( ! checkbox ) {
                    return;
                }

                // Клик был по самому чекбоксу — браузер
                // переключит его сам, не вмешиваемся.
                if ( event.target === checkbox ) {
                    return;
                }

                event.preventDefault();
                checkbox.click();
            });
        });
    }

    /**
     * Отметка чекбокса .cartReg показывает или скрывает
     * блок .cartLogin в той же форме.
     *
     * @return {void}
     */
    #bindRegisterToggle() {

        qsa( '.cartReg' ).forEach( ( checkbox ) => {

            checkbox.addEventListener( 'change', () => {

                const form = checkbox.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const loginBlock = form.querySelector( '.cartLogin' );

                if ( ! loginBlock ) {
                    return;
                }

                loginBlock.style.display = checkbox.checked ? 'block' : 'none';
            });
        });
    }

    // ============================================================
    // ПРИКРЕПЛЕНИЕ ФАЙЛОВ
    // ============================================================

    /**
     * Клик по кнопке .labelAddFile открывает диалог выбора
     * файлов у соответствующего input.
     *
     * @return {void}
     */
    #bindFileAttach() {

        qsa( '.labelAddFile' ).forEach( ( label ) => {

            label.addEventListener( 'click', ( event ) => {

                event.preventDefault();

                const form = label.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const input = form.querySelector( 'input[type="file"][name="fileCart[]"]' );

                if ( input ) {
                    input.click();
                }
            });
        });
    }

    /**
     * Обработка выбора файлов: валидация, счётчик, список.
     *
     * @return {void}
     */
    #bindFileUpload() {

        qsa( 'input[type="file"][name="fileCart[]"]' ).forEach( ( input ) => {

            input.addEventListener( 'change', () => {

                const form = input.closest( '.form-cart' );

                if ( ! form ) {
                    return;
                }

                const blockMessage    = form.querySelector( '.block-fileCartMessage' );
                const fileCartMessage = form.querySelector( '.fileCartMessage' );
                const loadedFiles     = form.querySelector( '.loadedFiles' );

                if ( ! blockMessage || ! fileCartMessage || ! loadedFiles ) {
                    return;
                }

                // Показываем контейнер
                blockMessage.style.display = 'block';

                // Сброс прошлого вывода
                fileCartMessage.innerHTML = '';
                loadedFiles.innerHTML = '';
                loadedFiles.style.display = 'none';

                const files = Array.from( input.files || [] );

                // Файлы убраны — прячем весь блок
                if ( ! files.length ) {
                    blockMessage.style.display = 'none';
                    return;
                }

                // ---------- Проверки ----------

                const maxSizeMb = parseFloat( input.dataset.sizefile ) || 0;
                const maxFiles  = parseInt( input.dataset.amountfiles, 10 ) || 0;
                const maxBytes  = maxSizeMb * 1024 * 1024;

                if ( maxFiles > 0 && files.length > maxFiles ) {
                    this.#showFileError( fileCartMessage, loadedFiles, 'Можно прикрепить не более ' + maxFiles + ' файлов.' );
                    input.value = '';
                    return;
                }

                for ( let i = 0; i < files.length; i++ ) {

                    if ( maxBytes > 0 && files[i].size > maxBytes ) {
                        this.#showFileError( fileCartMessage, loadedFiles, 'Файл "' + files[i].name + '" превышает ' + maxSizeMb + ' МБ.' );
                        input.value = '';
                        return;
                    }
                }

                // ---------- Заголовок-счётчик ----------

                const counter = document.createElement( 'div' );
                counter.className = 'loadSaccess';
                counter.textContent = 'ЗАГРУЖЕНО ФАЙЛОВ: ' + files.length;

                fileCartMessage.appendChild( counter );

                // ---------- Список файлов ----------

                const list = document.createElement( 'ol' );

                files.forEach( ( file ) => {

                    const item = document.createElement( 'li' );
                    const sizeMb = ( file.size / 1048576 ).toFixed( 2 );

                    item.textContent = file.name + ' [' + sizeMb + 'mb]';

                    list.appendChild( item );
                });

                loadedFiles.appendChild( list );
                loadedFiles.style.display = 'block';
            });
        });
    }

    /**
     * Показывает сообщение об ошибке выбора файлов.
     *
     * @param  {Element} fileCartMessage — контейнер сообщений
     * @param  {Element} loadedFiles     — контейнер списка
     * @param  {string}  message         — текст ошибки
     * @return {void}
     */
    #showFileError( fileCartMessage, loadedFiles, message ) {

        const err = document.createElement( 'div' );
        err.className = 'loadError';
        err.textContent = message;

        fileCartMessage.appendChild( err );

        loadedFiles.style.display = 'none';
    }
}

/**
 * Готовый синглтон модуля полей формы корзины.
 *
 * @type {ArsCartForm}
 */
export const arsCartForm = new ArsCartForm();