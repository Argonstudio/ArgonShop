/**
 * ArgonShop — виджет корзины в шапке сайта.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет отображением числа товаров и суммы корзины в шапке
 * сайта. Виджет присутствует на всех страницах, кроме страницы
 * корзины (см. includes/interface/shoppingCart/infoCart.php).
 *
 * Модуль используется другими модулями:
 *   - add-to-cart.js — после добавления товара
 *   - cart.js        — при удалении или очистке корзины
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .viewBlock-amountProducts span
 *   Число товаров в корзине.
 *
 * .viewBlock-priceProducts
 *   Контейнер суммы. Скрывается, если корзина пуста.
 *
 * .viewBlock-priceProducts span
 *   Сумма товаров в корзине.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCartWidget } from './modules/cart-widget.js';
 *
 *     arsCartWidget.update({
 *         productAmountCart: 5,
 *         totalPrice: 12500,
 *     });
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * arsCartWidget.update( data )   — обновить число и сумму
 * arsCartWidget.clear()          — сбросить виджет (корзина пуста)
 */

'use strict';

import { qs } from '../../shared/dom.js';
import { formatPrice } from '../../shared/format.js';

/**
 * Виджет корзины в шапке сайта.
 */
class ArsCartWidget {

    /**
     * Обновляет число товаров и сумму в виджете.
     *
     * @param  {Object} data                         — данные корзины
     * @param  {number} [data.productAmountCart=0]   — число товаров
     * @param  {number} [data.totalPrice=0]          — сумма корзины
     * @return {void}
     */
    update( data ) {

        const amount   = Number( data && data.productAmountCart ) || 0;
        const total    = Number( data && data.totalPrice ) || 0;

        const amountSpan  = qs( '.viewBlock-amountProducts span' );
        const priceBlock  = qs( '.viewBlock-priceProducts' );
        const priceSpan   = qs( '.viewBlock-priceProducts span' );

        if ( amountSpan ) {
            amountSpan.classList.add( 'viewBlock-productsStock' );
            amountSpan.textContent = String( amount );
        }

        if ( ! priceBlock || ! priceSpan ) {
            return;
        }

        if ( total > 0 ) {
            priceSpan.textContent = formatPrice( total );
            priceBlock.style.display = 'block';
        } else {
            priceSpan.textContent = '';
            priceBlock.style.display = 'none';
        }
    }

    /**
     * Сбрасывает виджет — используется после полной очистки корзины.
     *
     * @return {void}
     */
    clear() {
        this.update({ productAmountCart: 0, totalPrice: 0 });
    }
}

/**
 * Готовый синглтон виджета корзины.
 *
 * @type {ArsCartWidget}
 */
export const arsCartWidget = new ArsCartWidget();