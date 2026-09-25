/**
 * ArgonShop — фильтр заказов в личном кабинете.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает изменение выпадающего списка статусов в разделе
 * заказов личного кабинета. При выборе нового статуса отправляется
 * AJAX-запрос, а сервер возвращает HTML с отфильтрованной таблицей
 * заказов. Результат подставляется в блок без перезагрузки страницы.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-orderCabinet-filterStatus
 *   Селект фильтра. Содержит:
 *     data-userid — ID пользователя.
 *   Значение (value) — ID статусов через запятую ("1,2,3")
 *   либо пустая строка, если нужно показать все.
 *
 * .as-ordersCabinet-block
 *   Контейнер, в который выводится HTML-таблица заказов.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * ordersFiltersStatus
 *   Принимает: userID, status.
 *   Возвращает HTML-таблицу заказов.
 *
 *   Nonce к этому действию не добавляется — оно указано в
 *   config.skipNonceActions. Это сделано осознанно: смена email
 *   в ЛК инвалидирует nonce, а фильтр должен продолжать работать.
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsOrders } from './modules/orders.js';
 *     arsOrders.init();
 */

'use strict';

import { arsApi }      from '../core/api.js';
import { qs, on }      from '../../shared/dom.js';

/**
 * Модуль фильтра заказов в ЛК.
 */
class ArsOrders {

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

        const select = qs( '.as-orderCabinet-filterStatus' );

        if ( ! select ) {
            return;
        }

        this.#initialized = true;

        on( select, 'change', () => this.#applyFilter( select ) );
    }

    /**
     * Отправляет запрос фильтрации и обновляет таблицу заказов.
     *
     * @param  {HTMLSelectElement} select — селект фильтра
     * @return {Promise<void>}
     */
    async #applyFilter( select ) {

        const userID      = select.dataset.userid;
        const status      = select.value;
        const blockResult = qs( '.as-ordersCabinet-block' );

        if ( ! blockResult ) {
            return;
        }

        try {

            const html = await arsApi.post( 'ordersFiltersStatus', {
                userID,
                status,
            });

            blockResult.innerHTML = html;

        } catch {
            blockResult.innerHTML = 'Ошибка на сервере';
        }
    }
}

/**
 * Готовый синглтон модуля фильтра заказов.
 *
 * @type {ArsOrders}
 */
export const arsOrders = new ArsOrders();