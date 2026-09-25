/**
 * ArgonShop — панели управления (табы).
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет переключением блоков страницы через горизонтальные
 * панели-переключатели. Используется:
 *   - в личном кабинете (Аккаунт / Настройки / Заказы);
 *   - в корзине (Краткое оформление / Физлица / Юрлица);
 *   - на странице товара (Описание / Характеристики / Применение).
 *
 * Модуль поддерживает два режима:
 *
 *   switch       — переключение между блоками: активен только один
 *                  элемент, остальные скрыты.
 *
 *   openingList  — сворачиваемый список: клик открывает или
 *                  закрывает связанный блок, несколько могут быть
 *                  открыты одновременно.
 *
 * Дополнительно поддерживается мобильный набор элементов —
 * .mobileItemControlPanel с атрибутом data-mobileWidth. Если
 * ширина окна меньше указанной, используется мобильный набор.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .itemControlPanel, .mobileItemControlPanel
 *   Кликабельные панели-переключатели. Атрибуты:
 *     data-type — тип поведения: "switch" или "openingList".
 *     name      — имя блока; связывает панель с блоком
 *                 #block_{name}.
 *
 * .blockItemPage[id="block_{name}"]
 *   Блок, который показывается или скрывается.
 *
 * .itemControlPanelActive
 *   Класс активной панели в режиме switch.
 *
 * ============================================================
 * ПРИМЕР РАЗМЕТКИ
 * ============================================================
 *
 *   <div class="itemControlPanel itemControlPanelActive"
 *        data-type="switch" name="account">Аккаунт</div>
 *   <div class="itemControlPanel"
 *        data-type="switch" name="settings">Настройки</div>
 *
 *   <div class="blockItemPage" id="block_account">...</div>
 *   <div class="blockItemPage" id="block_settings">...</div>
 *
 * ============================================================
 * ИСПОЛЬЗОВАНИЕ
 * ============================================================
 *
 *     import { arsControlPanels } from './modules/control-panels.js';
 *     arsControlPanels.init();
 */

'use strict';

import { qs, qsa, on } from '../../shared/dom.js';

/**
 * Модуль панелей управления.
 */
class ArsControlPanels {

    /** @type {boolean} */
    #initialized = false;

    /**
     * Активный селектор панелей (.itemControlPanel или
     * .mobileItemControlPanel — зависит от ширины окна).
     *
     * @type {string}
     */
    #activeSelector = '.itemControlPanel';

    /**
     * Ширина окна, ниже которой используется мобильный набор панелей.
     * 0 — мобильный набор не используется.
     *
     * @type {number}
     */
    #mobileWidth = 0;

    /**
     * Инициализация. Безопасна для повторного вызова.
     *
     * @return {void}
     */
    init() {

        if ( this.#initialized ) {
            return;
        }

        const hasDesktop = qsa( '.itemControlPanel' ).length > 0;
        const hasMobile  = qsa( '.mobileItemControlPanel' ).length > 0;

        if ( ! hasDesktop && ! hasMobile ) {
            return;
        }

        this.#initialized = true;

        if ( hasMobile ) {

            const mobileSample = qs( '.mobileItemControlPanel' );
            this.#mobileWidth = parseInt( mobileSample.dataset.mobilewidth, 10 ) || 0;

            if ( this.#mobileWidth > 0 && window.innerWidth <= this.#mobileWidth ) {
                this.#activeSelector = '.mobileItemControlPanel';
            }
        }

        this.#bindClicks();
        this.#applyInitialState();
        this.#bindResize();
    }

    // ============================================================
    // ОБРАБОТЧИКИ
    // ============================================================

    /**
     * Клики по панелям.
     *
     * @return {void}
     */
    #bindClicks() {

        on( document, 'click', ( event ) => {

            const panel = event.target.closest( '.itemControlPanel, .mobileItemControlPanel' );

            if ( ! panel ) {
                return;
            }

            // Если это не активный набор панелей (мобильный vs десктопный) — игнорируем.
            if ( ! panel.matches( this.#activeSelector ) ) {
                return;
            }

            this.#handlePanelClick( panel );
        });
    }

    /**
     * Обрабатывает клик по одной панели.
     *
     * @param  {Element} panel — панель-переключатель
     * @return {void}
     */
    #handlePanelClick( panel ) {

        const type = panel.dataset.type || 'switch';
        const name = panel.getAttribute( 'name' );

        if ( ! name ) {
            return;
        }

        if ( type === 'switch' ) {
            this.#handleSwitch( panel, name );
            return;
        }

        if ( type === 'openingList' ) {
            this.#handleOpeningList( panel, name );
        }
    }

    /**
     * Режим "switch": активирует одну панель, остальные деактивирует.
     *
     * @param  {Element} panel — панель
     * @param  {string}  name  — имя панели
     * @return {void}
     */
    #handleSwitch( panel, name ) {

        qsa( this.#activeSelector ).forEach( item => {
            item.classList.remove( 'itemControlPanelActive' );
        });

        panel.classList.add( 'itemControlPanelActive' );

        // Скрываем все блоки и показываем активный
        qsa( '.blockItemPage' ).forEach( block => {
            block.style.display = 'none';
        });

        const activeBlocks = qsa( `${this.#activeSelector}.itemControlPanelActive` );

        activeBlocks.forEach( item => {

            const itemName = item.getAttribute( 'name' );

            if ( ! itemName ) {
                return;
            }

            const block = qs( `#block_${itemName}` );

            if ( block ) {
                block.style.display = 'block';
            }
        });
    }

    /**
     * Режим "openingList": клик открывает или закрывает связанный блок.
     *
     * @param  {Element} panel — панель
     * @param  {string}  name  — имя панели
     * @return {void}
     */
    #handleOpeningList( panel, name ) {

        const block = qs( `#block_${name}` );

        if ( ! block ) {
            return;
        }

        const isActive = panel.classList.contains( 'itemControlPanelActive' );

        if ( isActive ) {
            panel.classList.remove( 'itemControlPanelActive' );
            block.style.display = 'none';
        } else {
            panel.classList.add( 'itemControlPanelActive' );
            block.style.display = 'block';
        }
    }

    // ============================================================
    // НАЧАЛЬНОЕ СОСТОЯНИЕ И РЕСАЙЗ
    // ============================================================

    /**
     * Применяет начальное состояние при загрузке страницы:
     * в режиме switch показывается только активный блок.
     *
     * @return {void}
     */
    #applyInitialState() {

        // Скрываем все блоки
        qsa( '.blockItemPage' ).forEach( block => {
            block.style.display = 'none';
        });

        // Показываем блоки, связанные с активными панелями
        qsa( `${this.#activeSelector}.itemControlPanelActive` ).forEach( item => {

            const name = item.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const block = qs( `#block_${name}` );

            if ( block ) {
                block.style.display = 'block';
            }
        });
    }

    /**
     * Переключение между мобильным и десктопным набором панелей
     * при изменении ширины окна.
     *
     * @return {void}
     */
    #bindResize() {

        if ( this.#mobileWidth === 0 ) {
            return;
        }

        window.addEventListener( 'resize', () => {

            const wasMobile = this.#activeSelector === '.mobileItemControlPanel';
            const isMobile  = window.innerWidth <= this.#mobileWidth;

            if ( wasMobile === isMobile ) {
                return;
            }

            this.#activeSelector = isMobile
                ? '.mobileItemControlPanel'
                : '.itemControlPanel';

            this.#applyInitialState();
        });
    }
}

/**
 * Готовый синглтон модуля панелей управления.
 *
 * @type {ArsControlPanels}
 */
export const arsControlPanels = new ArsControlPanels();