/**
 * ArgonShop — скрипты страниц администратора.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет интерактивными элементами админки плагина:
 *
 *   1. Дерево категорий в форме «Характеристики»:
 *      - раскрытие и сворачивание вложенных уровней
 *        по кнопке-стрелке;
 *      - каскадная отметка чекбоксов (выбор родителя
 *        отмечает всех потомков, выбор чекбокса «Выбрать все»
 *        отмечает всё дерево).
 *
 *   2. Пересчёт списка характеристик товара при смене
 *      категории каталога: собирает текущее состояние полей
 *      характеристик и выбранные категории, затем передаёт
 *      их в глобальную функцию updateCharacteristics(),
 *      определённую в PHP (см. includes/admin/fieldsProduct.php).
 *
 *   3. Кнопка сохранения настроек магазина: передаёт ID
 *      страниц корзины и кабинета в глобальную функцию
 *      saveSettingPage().
 *
 * ============================================================
 * СТРУКТУРА ФАЙЛА
 * ============================================================
 *
 * Файл самостоятельный: не импортирует core-утилиты плагина
 * из интерфейса.
 *
 * ============================================================
 * ГЛОБАЛЬНЫЕ ФУНКЦИИ ИЗ PHP
 * ============================================================
 *
 * updateCharacteristics( categories, textFields, checkboxFields )
 *   Пересчёт блока характеристик при смене категории.
 *   Определена в includes/admin/fieldsProduct.php через
 *   admin_print_footer_scripts.
 *
 * saveSettingPage( cartPageID, cabinetPageID )
 *   Сохранение ID страниц корзины и кабинета.
 *   Опциональна: если функция не определена — вызов молча
 *   игнорируется.
 */

'use strict';

( function () {

    /**
     * querySelector — короткая обёртка.
     *
     * @param  {string}           selector             — CSS-селектор
     * @param  {Element|Document} [context=document]   — контекст поиска
     * @return {Element|null}
     */
    const qs = ( selector, context = document ) => {

        if ( ! context ) {
            return null;
        }

        return context.querySelector( selector );
    };

    /**
     * querySelectorAll — короткая обёртка.
     * Возвращает настоящий массив для удобства .forEach / .map / .filter.
     *
     * @param  {string}           selector             — CSS-селектор
     * @param  {Element|Document} [context=document]   — контекст поиска
     * @return {Element[]}
     */
    const qsa = ( selector, context = document ) => {

        if ( ! context ) {
            return [];
        }

        return Array.from( context.querySelectorAll( selector ) );
    };

    // ============================================================
    // ДЕРЕВО КАТЕГОРИЙ В ФОРМЕ «ХАРАКТЕРИСТИКИ»
    // ============================================================

    /**
     * Раскрытие и сворачивание вложенного списка категорий.
     *
     * Кнопка с классом .showHideButton (input[type=button])
     * находится внутри li.checkCategory и управляет видимостью
     * вложенного <ul> в этом же li.
     *
     * Обработчик делегированный — работает для динамически
     * добавленных элементов дерева.
     *
     * @return {void}
     */
    const initTreeToggle = () => {

        document.addEventListener( 'click', ( event ) => {

            const button = event.target.closest( '.choiceCategory input[type="button"]' );

            if ( ! button ) {
                return;
            }

            const li = button.closest( 'li' );

            if ( ! li || ! li.classList.contains( 'checkCategory' ) ) {
                return;
            }

            const sublist = li.querySelector( 'ul' );

            if ( ! sublist ) {
                return;
            }

            sublist.style.display = ( sublist.style.display === 'block' ) ? 'none' : 'block';
        });
    };

    /**
     * Каскадная отметка чекбоксов.
     *
     * Правила:
     *   - чекбокс внутри li.checkCategory — устанавливает то же
     *     состояние всем чекбоксам внутри этого li (включая
     *     вложенные уровни);
     *   - чекбокс внутри li.checkAllCategory — устанавливает то
     *     же состояние всем чекбоксам дерева .choiceCategory.
     *
     * Обработчик делегированный.
     *
     * @return {void}
     */
    const initTreeCheckboxes = () => {

        document.addEventListener( 'change', ( event ) => {

            const checkbox = event.target;

            if ( checkbox.type !== 'checkbox' ) {
                return;
            }

            if ( ! checkbox.closest( '.choiceCategory' ) ) {
                return;
            }

            const li = checkbox.closest( 'li' );

            if ( ! li ) {
                return;
            }

            if ( li.classList.contains( 'checkCategory' ) ) {

                qsa( 'input[type="checkbox"]', li ).forEach( ( cb ) => {
                    cb.checked = checkbox.checked;
                });

                return;
            }

            if ( li.classList.contains( 'checkAllCategory' ) ) {

                qsa( '.choiceCategory input[type="checkbox"]' ).forEach( ( cb ) => {
                    cb.checked = checkbox.checked;
                });
            }
        });
    };

    // ============================================================
    // ПЕРЕСЧЁТ ХАРАКТЕРИСТИК ПРИ СМЕНЕ КАТЕГОРИИ
    // ============================================================

    /**
     * Извлекает ID категории из id чекбокса таксономии.
     *
     * WordPress формирует id вида:
     *   in-catalog-123
     *   in-catalog-123-hierarchy
     *
     * Алгоритм:
     *   1. Разбиваем id по дефису.
     *   2. Если получилось 3 части — убираем первую (in),
     *      берём вторую часть как ID.
     *   3. Если получилось 4 части — убираем первые две
     *      (in + название таксономии), берём вторую часть
     *      оставшихся как ID.
     *
     * @param  {string} checkboxId — id чекбокса
     * @return {string}            — ID категории или пустая строка
     */
    const extractCategoryId = ( checkboxId ) => {

        if ( ! checkboxId || typeof checkboxId !== 'string' ) {
            return '';
        }

        const parts = checkboxId.split( '-' );

        if ( parts.length === 3 ) {
            parts.splice( 0, 1 );
        } else if ( parts.length === 4 ) {
            parts.splice( 0, 2 );
        }

        return parts[ 1 ] || '';
    };

    /**
     * Собирает ID выбранных категорий в контейнере #taxonomy-catalog.
     *
     * @return {string[]|string} Массив ID или строка 'no categories'
     */
    const collectSelectedCategories = () => {

        const container = qs( '#taxonomy-catalog' );

        if ( ! container ) {
            return 'no categories';
        }

        const ids = [];

        qsa( 'input[type="checkbox"]:checked', container ).forEach( ( checkbox ) => {

            const id = extractCategoryId( checkbox.id );

            if ( ! id ) {
                return;
            }

            if ( ids.indexOf( id ) === -1 ) {
                ids.push( id );
            }
        });

        if ( ids.length < 1 ) {
            return 'no categories';
        }

        return ids;
    };

    /**
     * Собирает значения текстовых характеристик из полей формы.
     *
     * Ищет input[name^="characteristics_text_field"] внутри
     * .advanced-field, извлекает ID характеристики из имени
     * вида characteristics_text_field[24] и складывает значение
     * в объект { 24: 'значение' }.
     *
     * @return {Object<string, string>}
     */
    const collectTextFields = () => {

        const result = {};

        qsa( '.advanced-field input[name^="characteristics_text_field"]' ).forEach( ( input ) => {

            const name = input.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const match = name.match( /\[([^\]]+)\]/ );

            if ( ! match ) {
                return;
            }

            result[ match[ 1 ] ] = input.value;
        });

        return result;
    };

    /**
     * Собирает значения характеристик-чекбоксов из полей формы.
     *
     * Ищет отмеченные input[name^="characteristics_checkbox_field"]
     * внутри .advanced-field. Родительский ID берётся из
     * data-parentid у родительского элемента (обычно это
     * div.checkbox-advanced-fields).
     *
     * @return {Object<string, Object<string, string>>}
     */
    const collectCheckboxFields = () => {

        const result = {};

        qsa( '.advanced-field input[name^="characteristics_checkbox_field"][type="checkbox"]:checked' ).forEach( ( input ) => {

            const name = input.getAttribute( 'name' );

            if ( ! name ) {
                return;
            }

            const match = name.match( /\[([^\]]+)\]/ );

            if ( ! match ) {
                return;
            }

            const elementId = match[ 1 ];

            const parent = input.closest( '[data-parentid]' );

            if ( ! parent ) {
                return;
            }

            const parentId = parent.getAttribute( 'data-parentid' );

            if ( ! parentId ) {
                return;
            }

            if ( ! result[ parentId ] ) {
                result[ parentId ] = {};
            }

            result[ parentId ][ elementId ] = input.value;
        });

        return result;
    };

    /**
     * Обработчик смены категории каталога.
     *
     * При каждом изменении чекбокса внутри #taxonomy-catalog
     * собирает актуальное состояние категорий и полей
     * характеристик и передаёт всё в PHP-функцию
     * updateCharacteristics(), которая обновляет метабокс
     * характеристик через AJAX.
     *
     * @return {void}
     */
    const initCategoryChange = () => {

        document.addEventListener( 'change', ( event ) => {

            const checkbox = event.target;

            if ( checkbox.type !== 'checkbox' ) {
                return;
            }

            if ( ! checkbox.closest( '#taxonomy-catalog' ) ) {
                return;
            }

            const categories     = collectSelectedCategories();
            const textFields     = collectTextFields();
            const checkboxFields = collectCheckboxFields();

            if ( typeof window.updateCharacteristics === 'function' ) {
                window.updateCharacteristics( categories, textFields, checkboxFields );
            }
        });
    };

    // ============================================================
    // СОХРАНЕНИЕ НАСТРОЕК МАГАЗИНА
    // ============================================================

    /**
     * Кнопка сохранения настроек магазина.
     *
     * Передаёт ID страниц корзины и кабинета в глобальную
     * функцию saveSettingPage(). Если функция не определена
     * (в текущей версии плагина она не используется) — вызов
     * игнорируется.
     *
     * @return {void}
     */
    const initSettingSave = () => {

        document.addEventListener( 'click', ( event ) => {

            const button = event.target.closest( '#settingPageSave' );

            if ( ! button ) {
                return;
            }

            const cartPageInput    = qs( '#shoppingCartPage' );
            const cabinetPageInput = qs( '#cabinetPage' );

            const cartPageID    = cartPageInput    ? cartPageInput.value    : '';
            const cabinetPageID = cabinetPageInput ? cabinetPageInput.value : '';

            if ( typeof window.saveSettingPage === 'function' ) {
                window.saveSettingPage( cartPageID, cabinetPageID );
            }
        });
    };

    // ============================================================
    // ИНИЦИАЛИЗАЦИЯ
    // ============================================================

    /**
     * Точка входа. Вызывается после готовности DOM.
     *
     * @return {void}
     */
    const initApp = () => {

        initTreeToggle();
        initTreeCheckboxes();
        initCategoryChange();
        initSettingSave();
    };

    if ( document.readyState === 'loading' ) {
        document.addEventListener( 'DOMContentLoaded', initApp );
    } else {
        initApp();
    }

})();
