/**
 * ArgonShop — добавление товара в корзину.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Общая логика добавления товара в корзину, используемая:
 *   - product.js     — кнопки «В корзину» и «Купить» на странице товара
 *   - card-product.js — те же кнопки в карточках товаров
 *
 * Один AJAX-запрос, единая обработка результата и ошибок.
 * После успешного добавления обновляет виджет корзины в шапке
 * и возвращает обновлённое состояние через колбэки.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * addProducts_shoppingCart
 *   Принимает: productID, amountProduct.
 *   Возвращает JSON:
 *     {
 *       amountProduct,      // сколько единиц этого товара в корзине
 *       productAmountCart,  // всего товаров в корзине
 *       totalPrice,         // сумма корзины
 *     }
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { addProductToCart } from './modules/add-to-cart.js';
 *
 *     await addProductToCart( 42, 3, {
 *         onSuccess: ( data ) => { ... },
 *         onError:   ( error ) => { ... },
 *     });
 *
 * ============================================================
 * ЭКСПОРТ
 * ============================================================
 *
 * addProductToCart( productId, amount, options ) — добавить товар
 */

'use strict';

import { arsApi }        from '../core/api.js';
import { arsCartWidget } from './cart-widget.js';

/**
 * Добавляет товар в корзину.
 *
 * При успешном ответе обновляет виджет корзины в шапке и вызывает
 * onSuccess с полными данными ответа. При ошибке вызывает onError
 * с объектом Error.
 *
 * @param  {string|number} productId — ID товара
 * @param  {string|number} amount    — количество
 * @param  {Object}        [options] — колбэки
 * @param  {Function}      [options.onSuccess] — ( data ) => void
 * @param  {Function}      [options.onError]   — ( error ) => void
 * @return {Promise<Object|null>} Данные ответа или null при ошибке
 */
export const addProductToCart = async ( productId, amount, options = {} ) => {

    const { onSuccess, onError } = options;

    try {

        const data = await arsApi.postJson( 'addProducts_shoppingCart', {
            productID:     productId,
            amountProduct: amount,
        });

        // Обновление виджета корзины в шапке
        arsCartWidget.update({
            productAmountCart: data.productAmountCart,
            totalPrice:        data.totalPrice,
        });

        if ( typeof onSuccess === 'function' ) {
            onSuccess( data );
        }

        return data;

    } catch ( error ) {

        if ( typeof onError === 'function' ) {
            onError( error );
        }

        return null;
    }
};
