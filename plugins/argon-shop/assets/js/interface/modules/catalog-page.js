/**
 * ArgonShop — мобильные блоки страницы каталога.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет сворачиваемыми блоками на странице каталога, которые
 * на мобильных устройствах скрыты и раскрываются по клику:
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
 *   Содержимое блока, следующий сосед кнопки.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCatalogPage } from './modules/catalog-page.js';
 *     arsCatalogPage.init();
 *
 * Модуль безопасен для вызова на любой странице: если блоков нет,
 * ничего не выполняется.
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
     * Инициализация. Безопасна для повторного вызова.
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