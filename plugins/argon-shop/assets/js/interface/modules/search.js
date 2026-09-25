/**
 * ArgonShop — AJAX-поиск по сайту.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет формой AJAX-поиска в шапке сайта и на странице
 * результатов поиска. Отправляет запрос при вводе текста,
 * показывает результаты в выпадающем блоке и скрывает его
 * при клике вне формы.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .asInputSearchForm
 *   Поле ввода поиска. Может присутствовать на странице несколько
 *   раз (шапка, страница поиска).
 *
 * Атрибуты поля ввода:
 *   data-ajax         — JSON-строка с параметрами поиска
 *                       (productResult, catalogResult, postResult).
 *                       Если атрибута нет — это обычный GET-поиск,
 *                       модуль его не обрабатывает.
 *
 *   data-blockresult  — CSS-класс блока, в который выводятся
 *                       результаты поиска (например,
 *                       "topSearchBlockResult").
 *
 * Блок результатов:
 *   .{classBlockResult}  — контейнер с результатами; показывается
 *                          как display: block, скрывается как none.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * getAjaxSearchResult
 *   Принимает: dataAjax, searchStr.
 *   Возвращает готовый HTML с блоками результатов.
 *
 * ============================================================
 * ПОВЕДЕНИЕ
 * ============================================================
 *
 * 1. При вводе текста длиной > 1 символа — через 300 мс
 *    отправляется AJAX-запрос (debounce защищает от лишних
 *    запросов при быстром наборе).
 *
 * 2. Если текст короче 2 символов — блок результатов скрывается,
 *    а таймер предыдущего запроса сбрасывается.
 *
 * 3. Каждому запросу присваивается токен. Если за время ожидания
 *    ответа поступил новый ввод — старый ответ игнорируется.
 *    Это исключает ситуацию, когда при быстром стирании текста
 *    на экране появляются устаревшие результаты.
 *
 * 4. При фокусе на поле с уже введённым текстом результаты
 *    появляются сразу, без debounce.
 *
 * 5. При клике вне формы — блок результатов скрывается.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsSearch } from './modules/search.js';
 *     arsSearch.init();
 */

'use strict';

import { arsApi }       from '../core/api.js';
import { qs, qsa, on }  from '../../shared/dom.js';

/**
 * Задержка перед отправкой запроса при вводе, в миллисекундах.
 * Защищает от избыточных запросов при быстром наборе текста.
 *
 * @type {number}
 */
const SEARCH_DEBOUNCE_MS = 300;

/**
 * Минимальная длина запроса, при которой запускается поиск.
 * Более короткие строки игнорируются.
 *
 * @type {number}
 */
const SEARCH_MIN_LENGTH = 2;

/**
 * Модуль AJAX-поиска.
 */
class ArsSearch {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Таймеры debounce для каждого поля ввода.
     * Ключ — сам элемент input.
     *
     * @type {WeakMap<Element, number>}
     */
    #timers = new WeakMap();

    /**
     * Токены текущих запросов для каждого поля.
     * Новый запрос перезаписывает токен — так старые ответы
     * распознаются и игнорируются.
     *
     * @type {WeakMap<Element, symbol>}
     */
    #tokens = new WeakMap();

    /**
     * Инициализация. Безопасна для повторного вызова.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const inputs = qsa( '.asInputSearchForm' );

        if ( ! inputs.length ) {
            return;
        }

        this.#initialized = true;

        inputs.forEach( input => {

            on( input, 'input', () => this.#onInput( input ) );
            on( input, 'focus', () => this.#onFocus( input ) );
        });

        // Глобальный обработчик: клик вне формы скрывает результаты.
        on( document, 'mouseup', ( event ) => this.#onDocumentClick( event ) );
    }

    // ============================================================
    // ОБРАБОТЧИКИ
    // ============================================================

    /**
     * Обработчик ввода в поле поиска.
     *
     * @param  {HTMLInputElement} input — поле ввода
     * @return {void}
     */
    #onInput( input ) {

        if ( ! this.#hasAjaxParams( input ) ) {
            return;
        }

        const searchStr   = input.value.trim();
        const resultBlock = this.#getResultBlock( input );

        // Всегда сбрасываем предыдущий таймер — иначе старый
        // debounce может сработать после очистки поля и показать
        // устаревшие результаты.
        this.#cancelPending( input );

        // Слишком короткий запрос — скрываем блок и инвалидируем
        // текущий запрос, чтобы его ответ не отрисовался.
        if ( searchStr.length < SEARCH_MIN_LENGTH ) {

            if ( resultBlock ) {
                resultBlock.style.display = 'none';
            }

            return;
        }

        // Debounce: запускаем новый таймер
        const timer = window.setTimeout( () => {
            this.#requestSearch( input, searchStr );
        }, SEARCH_DEBOUNCE_MS );

        this.#timers.set( input, timer );
    }

    /**
     * Обработчик фокуса на поле поиска.
     *
     * Если в поле уже есть достаточно длинный текст — сразу
     * запускаем поиск, без debounce.
     *
     * @param  {HTMLInputElement} input — поле ввода
     * @return {void}
     */
    #onFocus( input ) {

        if ( ! this.#hasAjaxParams( input ) ) {
            return;
        }

        const searchStr = input.value.trim();

        if ( searchStr.length >= SEARCH_MIN_LENGTH ) {
            this.#requestSearch( input, searchStr );
        }
    }

    /**
     * Обработчик клика по документу.
     *
     * Скрывает все открытые блоки результатов, если клик был
     * не внутри формы поиска и не внутри самого блока результатов.
     *
     * @param  {MouseEvent} event — событие клика
     * @return {void}
     */
    #onDocumentClick( event ) {

        const target = event.target;

        if ( target.closest( '.asInputSearchForm' ) ) {
            return;
        }

        qsa( '.searchAjaxResult' ).forEach( block => {

            if ( block.style.display !== 'block' ) {
                return;
            }

            if ( block.contains( target ) ) {
                return;
            }

            block.style.display = 'none';
        });
    }

    // ============================================================
    // AJAX-ЗАПРОС
    // ============================================================

    /**
     * Отправляет запрос поиска и выводит результаты.
     *
     * Каждому запросу присваивается токен. Если за время ожидания
     * ответа был запущен новый запрос (или поле очищено) — ответ
     * игнорируется.
     *
     * @param  {HTMLInputElement} input     — поле ввода
     * @param  {string}           searchStr — текст поиска
     * @return {Promise<void>}
     */
    async #requestSearch( input, searchStr ) {

        const resultBlock = this.#getResultBlock( input );

        if ( ! resultBlock ) {
            return;
        }

        // Запоминаем токен этого запроса
        const token = Symbol( 'search' );
        this.#tokens.set( input, token );

        // Показываем блок сразу — иначе пользователь не поймёт,
        // что поиск начался.
        resultBlock.style.display = 'block';

        if ( resultBlock.innerHTML === '' ) {
            resultBlock.innerHTML = 'Ищем результаты...';
        }

        const dataAjax = input.dataset.ajax || '';

        try {

            const html = await arsApi.post( 'getAjaxSearchResult', {
                dataAjax:  dataAjax,
                searchStr: searchStr,
            });

            // Если за это время был другой запрос — игнорируем ответ
            if ( this.#tokens.get( input ) !== token ) {
                return;
            }

            // Если поле уже очищено или слишком короткое — не показываем
            if ( input.value.trim().length < SEARCH_MIN_LENGTH ) {
                resultBlock.style.display = 'none';
                return;
            }

            resultBlock.innerHTML = html;

        } catch {

            // Ошибку показываем только если запрос всё ещё актуален
            if ( this.#tokens.get( input ) !== token ) {
                return;
            }

            resultBlock.innerHTML = 'Ошибка на сервере';
        }
    }

    // ============================================================
    // ВСПОМОГАТЕЛЬНЫЕ
    // ============================================================

    /**
     * Сбрасывает отложенный debounce-таймер и инвалидирует
     * активный запрос для указанного поля.
     *
     * @param  {HTMLInputElement} input — поле ввода
     * @return {void}
     */
    #cancelPending( input ) {

        const timer = this.#timers.get( input );

        if ( timer ) {
            clearTimeout( timer );
            this.#timers.delete( input );
        }

        // Смена токена на «пустой» — старые ответы не отрисуются
        this.#tokens.set( input, Symbol( 'cancelled' ) );
    }

    /**
     * Проверяет, настроен ли у поля AJAX-поиск.
     *
     * @param  {HTMLInputElement} input — поле ввода
     * @return {boolean}
     */
    #hasAjaxParams( input ) {

        const dataAjax = input.dataset.ajax;

        if ( ! dataAjax ) {
            return false;
        }

        try {
            JSON.parse( dataAjax );
            return true;
        } catch {
            return false;
        }
    }

    /**
     * Возвращает блок результатов, привязанный к полю ввода.
     *
     * @param  {HTMLInputElement} input — поле ввода
     * @return {Element|null}
     */
    #getResultBlock( input ) {

        const classBlockResult = input.dataset.blockresult;

        if ( ! classBlockResult ) {
            return null;
        }

        return qs( '.' + classBlockResult );
    }
}

/**
 * Готовый синглтон модуля поиска.
 *
 * @type {ArsSearch}
 */
export const arsSearch = new ArsSearch();
