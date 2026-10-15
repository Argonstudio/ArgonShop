/**
 * ArgonShop — фильтр заказов в личном кабинете.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Обрабатывает изменение выпадающего списка статусов в разделе заказов
 * личного кабинета. При выборе нового статуса отправляется
 * AJAX-запрос, и сервер возвращает HTML с отфильтрованной
 * таблицей заказов. Результат вставляется в блок без перезагрузки страницы.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .as-orderCabinet-filterStatus
 *   Список фильтра. Содержит:
 *     data-userid — ID пользователя.
 *   Значение — ID статусов через запятую ("1,2,3")
 *   или пустая строка, чтобы показать все заказы.
 *
 * .as-ordersCabinet-block
 *   Контейнер, в который вставляется HTML-таблица заказов.
 *
 * ============================================================
 * AJAX-ДЕЙСТВИЕ
 * ============================================================
 *
 * ordersFiltersStatus
 *   Принимает: userID, status.
 *   Возвращает HTML-таблицу заказов.
 *
 *   Nonce добавляется автоматически, как и для всех остальных действий
 *   (config.skipNonceActions пуст).
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
import { t }            from '../core/config.js';
import { qs, on }      from '../../shared/dom.js';

/**
 * Модуль фильтра заказов кабинета.
 */
class ArsOrders {

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

        const select = qs( '.as-orderCabinet-filterStatus' );

        if ( ! select ) {
            return;
        }

        this.#initialized = true;

        on( select, 'change', () => this.#applyFilter( select ) );
    }

    /**
     * Отправляет запрос фильтра и обновляет таблицу заказов.
     *
     * @param  {HTMLSelectElement} select — список фильтра
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
            blockResult.innerHTML = t( 'serverError' );
        }
    }
}

/**
 * Готовый синглтон модуля фильтра заказов.
 *
 * @type {ArsOrders}
 */
export const arsOrders = new ArsOrders();