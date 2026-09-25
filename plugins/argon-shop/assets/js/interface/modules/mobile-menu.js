/**
 * ArgonShop — мобильное меню сайта.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет открытием и закрытием мобильного меню в шапке сайта,
 * а также раскрытием вложенных подменю.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .openMobileMenu
 *   Кнопка открытия и закрытия меню.
 *
 * #block-menu
 *   Контейнер меню.
 *
 * .menu-item-has-children
 *   Пункт меню с подменю.
 *
 * .sub-menu
 *   Вложенное подменю внутри .menu-item-has-children.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsMobileMenu } from './modules/mobile-menu.js';
 *     arsMobileMenu.init();
 */

'use strict';

import { qs, qsa, on, delegate } from '../../shared/dom.js';


/**
 * Пороговая ширина окна, ниже которой работает мобильное меню.
 *
 * @type {number}
 */
const MOBILE_WIDTH = 1200;

/**
 * Модуль мобильного меню сайта.
 */
class ArsMobileMenu {

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

        if ( ! qs( '.openMobileMenu' ) || ! qs( '#block-menu' ) ) {
            return;
        }

        this.#initialized = true;

        this.#bindToggle();
        this.#bindSubmenu();
    }

    /**
     * Кнопка открытия и закрытия меню.
     *
     * @return {void}
     */
    #bindToggle() {

        const button = qs( '.openMobileMenu' );
        const menu   = qs( '#block-menu' );

        if ( ! button || ! menu ) {
            return;
        }

        on( button, 'click', () => {

            const isVisible = menu.style.display === 'block';

            if ( isVisible ) {
                menu.style.display = 'none';
                button.style.backgroundColor = '';
            } else {
                menu.style.display = 'block';
                button.style.backgroundColor = '#659bdd';
            }
        });
    }

    /**
     * Раскрытие вложенных подменю.
     *
     * @return {void}
     */
    #bindSubmenu() {

        delegate( document, 'click', '.menu-item-has-children', ( event, item ) => {

            if ( window.innerWidth > MOBILE_WIDTH ) {
                return;
            }

            const target = event.target;

            // Реагируем только на клик по самому пункту или
            // по его прямому потомку — не по ссылке в подменю.
            if ( target !== item && target.parentNode !== item ) {
                return;
            }

            const submenu = qs( '.sub-menu', item );

            if ( ! submenu ) {
                return;
            }

            const isVisible = submenu.style.display === 'block';

            if ( isVisible ) {
                submenu.style.display = 'none';
            } else {
                event.preventDefault();
                submenu.style.display = 'block';
            }
        });
    }
}

/**
 * Готовый синглтон модуля мобильного меню.
 *
 * @type {ArsMobileMenu}
 */
export const arsMobileMenu = new ArsMobileMenu();