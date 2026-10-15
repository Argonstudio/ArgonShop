/**
 * ArgonShop — сортировка товаров каталога.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает клики по кнопкам сортировки каталога. При каждом
 * клике отправляется AJAX-запрос, и сервер возвращает уже
 * отсортированный HTML-список товаров. Результат вставляется
 * в блок .catalogProductList без перезагрузки страницы.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-sort
 *   Кнопка сортировки. Имеет атрибут name — тип сортировки
 *   ('price', 'name', 'date'). Классы sort-min / sort-max
 *   показывают текущее направление: по возрастанию или по убыванию.
 *
 * .sortParameters
 *   Контейнер над кнопками. Хранит:
 *     data-cat-id  — ID текущей категории каталога
 *     data-page    — номер текущей страницы пагинации
 *
 * .catalogProductList
 *   Блок, в который выводится список товаров.
 *
 * .as-error-sort
 *   Блок для вывода ошибок сортировки.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * sortingCatalog
 *   Принимает: typeSort, howSorting, catId, pageNum.
 *   Возвращает HTML-список товаров.
 *
 * ============================================================
 * ЛОГИКА НАПРАВЛЕНИЯ СОРТИРОВКИ
 * ============================================================
 *
 * Если у кнопки уже есть класс sort-min, текущая
 * сортировка была «по возрастанию», и новый клик переключает на
 * убывание (DESC).
 *
 * Во всех остальных случаях новое направление — ASC (по возрастанию),
 * и это переключение запоминается классом sort-min.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsSort } from './modules/sort.js';
 *     arsSort.init();
 */

'use strict';

import { arsApi }             from '../core/api.js';
import { t }            from '../core/config.js';
import { qs, qsa, delegate }  from '../../shared/dom.js';

/**
 * Модуль сортировки каталога.
 */
class ArsSort {

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

        // Если на странице вообще нет кнопок сортировки,
        // это не страница каталога — выходим.
        if ( ! qs( '.as-sort' ) ) {
            return;
        }

        this.#initialized = true;

        delegate( document, 'click', '.as-sort', ( event, button ) => {
            event.preventDefault();
            this.#handleSort( button );
        });
    }

    // ============================================================
    // ОБРАБОТКА КЛИКА
    // ============================================================

    /**
     * Обрабатывает клик по кнопке сортировки.
     *
     * @param  {Element} button — кнопка .as-sort
     * @return {Promise<void>}
     */
    async #handleSort( button ) {

        const sortParameters = button.closest( '.sortParameters' );

        if ( ! sortParameters ) {
            return;
        }

        const typeSort = button.getAttribute( 'name' );
        const catId    = parseInt( sortParameters.dataset.catId, 10 ) || 0;
        const pageNum  = parseInt( sortParameters.dataset.page, 10 )  || 1;

        // Определяем направление сортировки
        const howSorting = button.classList.contains( 'sort-min' ) ? 'DESC' : 'ASC';

        try {

            const html = await arsApi.post( 'sortingCatalog', {
                typeSort:   typeSort,
                howSorting: howSorting,
                catId:      catId,
                pageNum:    pageNum,
            });

            this.#showResult( html, button, howSorting );

        } catch {
            this.#showError();
        }
    }

    // ============================================================
    // ОБНОВЛЕНИЕ ИНТЕРФЕЙСА
    // ============================================================

    /**
     * Применяет результат сортировки к странице.
     *
     * @param  {string}  html       — готовый HTML-список товаров
     * @param  {Element} button     — нажатая кнопка
     * @param  {string}  howSorting — 'ASC' или 'DESC'
     * @return {void}
     */
    #showResult( html, button, howSorting ) {

        // Сбрасываем подсветку у всех кнопок
        qsa( '.as-sort' ).forEach( el => {
            el.classList.remove( 'sort-min', 'sort-max' );
        });

        // Подсвечиваем активную кнопку с направлением
        if ( howSorting === 'ASC' ) {
            button.classList.add( 'sort-min' );
        } else {
            button.classList.add( 'sort-max' );
        }

        // Обновляем список товаров
        const productList = qs( '.catalogProductList' );

        if ( productList ) {
            productList.innerHTML = html;
        }

        // Скрываем блок ошибки, если он был виден
        const errorBlock = qs( '.as-error-sort' );

        if ( errorBlock ) {
            errorBlock.style.display = 'none';
        }
    }

    /**
     * Показывает сообщение об ошибке сортировки.
     *
     * @return {void}
     */
    #showError() {

        const errorBlock = qs( '.as-error-sort' );

        if ( ! errorBlock ) {
            return;
        }

        errorBlock.textContent = t( 'serverError' );
        errorBlock.style.display = 'block';
    }
}

/**
 * Готовый синглтон модуля сортировки.
 *
 * @type {ArsSort}
 */
export const arsSort = new ArsSort();