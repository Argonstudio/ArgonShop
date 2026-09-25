/**
 * ArgonShop — блок популярных запросов в каталоге.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет блоком «Популярные запросы» на странице категории
 * каталога:
 *
 *   1. Если блок популярных запросов не помещается по высоте
 *      (контент обрезается через CSS max-height), показывает
 *      кнопку «Смотреть все».
 *
 *   2. При клике по кнопке блок разворачивается на полную высоту,
 *      при повторном клике — снова сворачивается.
 *
 * Разворачивание и сворачивание выполняется переключением класса
 * .is-expanded. Правило CSS для этого класса должно быть добавлено
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
 * Модуль безопасен для вызова на любой странице: если блока нет,
 * ничего не выполняется.
 */

'use strict';

import { qs, on } from '../../shared/dom.js';

/**
 * Класс, который добавляется блоку в развёрнутом состоянии.
 *
 * @type {string}
 */
const EXPANDED_CLASS = 'is-expanded';

/**
 * Запас в пикселях, на который допустимо «недоразворот» блока.
 * Если разница между высотой контента и видимой высотой меньше
 * этого значения — кнопка «Смотреть все» не показывается.
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
     * Инициализация. Безопасна для повторного вызова.
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

        // Показываем кнопку, только если контент обрезается
        if ( this.#isContentClipped( block ) ) {
            button.style.display = 'block';
        }

        on( button, 'click', () => {
            block.classList.toggle( EXPANDED_CLASS );
        });
    }

    /**
     * Проверяет, обрезается ли содержимое блока по высоте.
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