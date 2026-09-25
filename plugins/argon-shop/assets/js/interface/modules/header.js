/**
 * ArgonShop — выбор города в шапке сайта.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * При смене города в выпадающем списке выполняется переход на
 * соответствующую страницу текущего раздела другого города.
 *
 * Правила перехода:
 *
 *   1. Если пользователь на странице акций — переходим на страницу
 *      акций того же города.
 *
 *   2. Если пользователь на странице архива акций — переходим
 *      на архив акций другого города.
 *
 *   3. Во всех остальных случаях — переходим на тот же URL
 *      пути, но в другом городе.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .sitySelect
 *   Селект выбора города. Значение (value) — базовый URL города.
 *
 * Опции селекта могут иметь атрибуты:
 *   data-promocategory — путь к странице акций города.
 *   data-promoarchive  — путь к архиву акций города.
 *
 * main
 *   Контейнер контента. Его id используется для определения
 *   текущей страницы:
 *     #block-contentPromo       — страница акций.
 *     #block-contentPromoPage   — отдельная акция.
 *     #block-contentPromoArchive — архив акций.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsHeader } from './modules/header.js';
 *     arsHeader.init();
 */

'use strict';


import { qs, on } from '../../shared/dom.js';

/**
 * Модуль выбора города.
 */
class ArsHeader {

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

        const select = qs( '.sitySelect' );

        if ( ! select ) {
            return;
        }

        this.#initialized = true;

        on( select, 'change', () => this.#handleChange( select ) );
    }

    /**
     * Обрабатывает смену города.
     *
     * @param  {HTMLSelectElement} select — селект городов
     * @return {void}
     */
    #handleChange( select ) {

        const value    = select.value;
        const selected = select.options[ select.selectedIndex ];

        if ( ! value || ! selected ) {
            return;
        }

        const main = qs( 'main' );
        const mainId = main ? main.id : '';

        // Страница акций или отдельная акция
        if ( mainId === 'block-contentPromo' || mainId === 'block-contentPromoPage' ) {

            const promoCategory = selected.dataset.promocategory || '';
            window.location.href = value + promoCategory;
            return;
        }

        // Архив акций
        if ( mainId === 'block-contentPromoArchive' ) {

            const promoArchive = selected.dataset.promoarchive || '';
            window.location.href = value + promoArchive;
            return;
        }

        // Все остальные страницы — тот же путь на другом домене
        window.location.href = value + window.location.pathname;
    }
}

/**
 * Готовый синглтон модуля выбора города.
 *
 * @type {ArsHeader}
 */
export const arsHeader = new ArsHeader();