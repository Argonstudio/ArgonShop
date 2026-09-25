/**
 * ArgonShop — сортировка каталога товаров.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает клики по кнопкам сортировки каталога. При каждом
 * клике отправляется AJAX-запрос, а сервер возвращает уже
 * отсортированный HTML-список товаров. Результат подставляется
 * в блок .catalogProductList без перезагрузки страницы.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-sort
 *   Кнопка сортировки. Имеет атрибут name — тип сортировки
 *   ('price', 'name', 'date'). Классы sort-min / sort-max
 *   показывают текущее направление: от меньшего или к большему.
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
 *   Блок для отображения ошибок сортировки.
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
 * Если на кнопке уже стоит класс sort-min — значит, текущая
 * сортировка была «по возрастанию», новый клик переключит на
 * убывание (DESC).
 *
 * Во всех остальных случаях новое направление — ASC (возрастание),
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
import { qs, qsa, delegate }  from '../../shared/dom.js';

/**
 * Модуль сортировки каталога.
 */
class ArsSort {

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

        // Если на странице нет ни одной кнопки сортировки —
        // это не страница каталога, выходим.
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
     * @param  {Element} button     — кнопка, по которой кликнули
     * @param  {string}  howSorting — 'ASC' или 'DESC'
     * @return {void}
     */
    #showResult( html, button, howSorting ) {

        // Сбрасываем подсветку со всех кнопок
        qsa( '.as-sort' ).forEach( el => {
            el.classList.remove( 'sort-min', 'sort-max' );
        });

        // Подсвечиваем активную кнопку направлением
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

        // Прячем блок ошибки, если он был виден
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

        errorBlock.textContent = 'Ошибка на сервере';
        errorBlock.style.display = 'block';
    }
}

/**
 * Готовый синглтон модуля сортировки.
 *
 * @type {ArsSort}
 */
export const arsSort = new ArsSort();