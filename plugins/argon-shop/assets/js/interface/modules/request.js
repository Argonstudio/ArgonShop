/**
 * ArgonShop — блок популярных запросов в каталоге.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет блоком «Популярные запросы» на странице категории
 * каталога:
 *
 *   1. Если блок популярных запросов не помещается по высоте
 *      (содержимое обрезано CSS max-height), показывает кнопку
 *      «Смотреть все».
 *
 *   2. Клик по кнопке раскрывает блок на полную высоту,
 *      повторный клик сворачивает его.
 *
 * Раскрытие и сворачивание выполняется переключением
 * класса .is-expanded. CSS-правило для этого класса нужно добавить
 * в тему:
 *
 *     .block-cards-request.is-expanded {
 *         max-height: none;
 *     }
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .block-cards-request      — контейнер с карточками запросов.
 * .view-allCards-request    — кнопка «Смотреть все».
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsRequest } from './modules/request.js';
 *     arsRequest.init();
 *
 * Модуль безопасно вызывать на любой странице: если блока нет,
 * ничего не выполняется.
 */

'use strict';

import { qs, on } from '../../shared/dom.js';

/**
 * Класс, добавляемый блоку в раскрытом состоянии.
 *
 * @type {string}
 */
const EXPANDED_CLASS = 'is-expanded';

/**
 * Допуск в пикселях, на который блок может «недораскрыться».
 * Если разница между высотой содержимого и видимой высотой меньше
 * этого значения, кнопка «Смотреть все» не показывается.
 *
 * @type {number}
 */
const HEIGHT_THRESHOLD = 20;

/**
 * Модуль блока популярных запросов.
 */
class ArsRequest {

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

        const block  = qs( '.block-cards-request' );
        const button = qs( '.view-allCards-request' );

        if ( ! block || ! button ) {
            return;
        }

        this.#initialized = true;

        // Показываем кнопку, только если содержимое обрезано
        if ( this.#isContentClipped( block ) ) {
            button.style.display = 'block';
        }

        on( button, 'click', () => {
            block.classList.toggle( EXPANDED_CLASS );
        });
    }

    /**
     * Проверяет, обрезано ли содержимое блока по высоте.
     *
     * @param  {Element} block — контейнер карточек запросов
     * @return {boolean}
     */
    #isContentClipped( block ) {

        return block.scrollHeight > block.clientHeight + HEIGHT_THRESHOLD;
    }
}

/**
 * Готовый синглтон модуля популярных запросов.
 *
 * @type {ArsRequest}
 */
export const arsRequest = new ArsRequest();