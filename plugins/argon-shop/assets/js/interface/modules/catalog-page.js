/**
 * ArgonShop — мобильные блоки страницы каталога.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет сворачиваемыми блоками на странице каталога, которые
 * скрыты на мобильных устройствах и раскрываются по клику:
 *
 *   - «Описание категории»;
 *   - «Дочерние категории»;
 *   - «Популярные запросы»;
 *   - «Сортировать по».
 *
 * Классы и структура задаются шаблоном темы taxonomy-catalog.php.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .catalogPage_openMobile
 *   Заголовок сворачиваемого блока (кнопка).
 *
 * .catalogPage_mobileExtensible
 *   Содержимое блока, следующий соседний элемент после кнопки.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCatalogPage } from './modules/catalog-page.js';
 *     arsCatalogPage.init();
 *
 * Модуль безопасно вызывать на любой странице: если блоков нет,
 * ничего не происходит.
 */

'use strict';

import { delegate } from '../../shared/dom.js';

/**
 * Модуль мобильных блоков каталога.
 */
class ArsCatalogPage {

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

        this.#initialized = true;

        delegate( document, 'click', '.catalogPage_openMobile', ( event, button ) => {

            const content = button.nextElementSibling;

            if ( ! content || ! content.classList.contains( 'catalogPage_mobileExtensible' ) ) {
                return;
            }

            const isVisible = content.style.display === 'block';

            content.style.display = isVisible ? 'none' : 'block';
        });
    }
}

/**
 * Готовый синглтон модуля мобильных блоков каталога.
 *
 * @type {ArsCatalogPage}
 */
export const arsCatalogPage = new ArsCatalogPage();