/**
 * ArgonShop — кнопки карточки товара.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает кнопки добавления в корзину на карточках товаров
 * в каталоге, на главной, в сайдбаре и в результатах
 * поиска.
 *
 * Карточки товаров выводятся в нескольких местах сайта и содержат
 * две кнопки:
 *
 *   .card-inBascet — «В корзину»: добавляет без перехода.
 *   .card-buy      — «Купить»: добавляет и переходит в корзину.
 *
 * После успешного добавления через «В корзину» блок кнопок
 * скрывается, а вместо него показывается блок «Товар добавлен в корзину»
 * (.card-alreadyAdded) со ссылкой на страницу
 * корзины.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .card-inBascet[data-productid]      — кнопка «В корзину».
 * .card-buy[data-productid]           — кнопка «Купить».
 * .block-cardProductBascet            — блок с обеими кнопками.
 * .card-alreadyAdded                  — блок «Товар добавлен в корзину».
 * .card-addProductError               — блок ошибки на карточке.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsCardProduct } from './modules/card-product.js';
 *     arsCardProduct.init();
 *
 * Модуль безопасно вызывать на любой странице: если карточек
 * с кнопками нет, обработчики не срабатывают.
 */

'use strict';

import { addProductToCart } from './add-to-cart.js';
import { t } from '../core/config.js';
import { delegate } from '../../shared/dom.js';

/**
 * Модуль кнопок карточки товара.
 */
class ArsCardProduct {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Инициализация. Идемпотентна.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        this.#initialized = true;

        this.#bindAddToCart();
        this.#bindBuyNow();
    }

    /**
     * Кнопка «В корзину» на карточке.
     *
     * @return {void}
     */
    #bindAddToCart() {

        delegate( document, 'click', '.card-inBascet', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#showAdded( button ),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * Кнопка «Купить» на карточке.
     *
     * @return {void}
     */
    #bindBuyNow() {

        delegate( document, 'click', '.card-buy', async ( event, button ) => {

            const productId = button.dataset.productid;

            await addProductToCart( productId, 1, {
                onSuccess: () => this.#goToCart(),
                onError:   ( error ) => this.#showError( button, error ),
            });
        });
    }

    /**
     * Показывает блок «Товар добавлен в корзину».
     *
     * @param  {Element} button — нажатая кнопка
     * @return {void}
     */
    #showAdded( button ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock  = buttonsBlock.nextElementSibling;
        const addedBlock  = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-alreadyAdded' )
            : null;

        if ( errorBlock && errorBlock.classList.contains( 'card-addProductError' ) ) {
            errorBlock.style.display = 'none';
            errorBlock.textContent = '';
        }

        buttonsBlock.style.display = 'none';

        if ( addedBlock ) {
            addedBlock.style.display = 'block';
        }
    }

    /**
     * Показывает блок ошибки на карточке.
     *
     * @param  {Element} button — нажатая кнопка
     * @param  {Error}   error  — объект ошибки
     * @return {void}
     */
    #showError( button, error ) {

        const buttonsBlock = button.closest( '.block-cardProductBascet' );

        if ( ! buttonsBlock ) {
            return;
        }

        const errorBlock = buttonsBlock.parentElement
            ? buttonsBlock.parentElement.querySelector( '.card-addProductError' )
            : null;

        if ( errorBlock ) {
            errorBlock.textContent = t( 'requestError' );
            errorBlock.style.display = 'block';
        }
    }

    /**
     * Переход на страницу корзины.
     *
     * @return {void}
     */
    #goToCart() {

        const cartUrl = window.arsWpAjax && window.arsWpAjax.cartUrl
            ? window.arsWpAjax.cartUrl
            : '';

        if ( cartUrl ) {
            window.location.href = cartUrl;
        }
    }
}

/**
 * Готовый синглтон модуля кнопок карточки товара.
 *
 * @type {ArsCardProduct}
 */
export const arsCardProduct = new ArsCardProduct();