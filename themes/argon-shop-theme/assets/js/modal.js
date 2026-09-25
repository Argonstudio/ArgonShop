/**
 * ArgonShopTheme — лёгкое модальное окно.
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.1
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Заменяет Fancybox для карты и формы обратной связи. Не тянет
 * никаких библиотек: чистый ES6+ и DOM API.
 *
 * Поддерживает три типа открытия:
 *
 *   1. AJAX — запрос к URL, полученный HTML вставляется в модалку.
 *      Используется для карты (шаблон «Карта AJAX» в теме).
 *
 *   2. INLINE — перенос DOM-элемента в модалку по селектору.
 *      Используется для формы CF7 (#feedback в header.php).
 *      Элемент НЕ клонируется, а перемещается — это сохраняет
 *      обработчики Contact Form 7, привязанные к исходному узлу.
 *
 *   3. IFRAME — открытие URL в iframe. На будущее, сейчас
 *      не используется.
 *
 * ============================================================
 * РАЗМЕТКА
 * ============================================================
 *
 * Кнопка, открывающая модалку, должна иметь один из атрибутов:
 *
 *   data-modal-ajax="/url/"      — загрузить через AJAX
 *   data-modal-inline="#elem"    — открыть как inline
 *   data-modal-iframe="/url/"    — открыть в iframe
 *
 * Пример:
 *
 *   <a href="#" data-modal-ajax="/adres-na-karte-moskva/">Карта</a>
 *   <a href="#feedback" data-modal-inline="#feedback">Обратная связь</a>
 *
 * ============================================================
 * ЗАКРЫТИЕ
 * ============================================================
 *
 *   - клик по фону вне окна;
 *   - клик по кнопке с data-modal-close;
 *   - нажатие Esc.
 *
 * ============================================================
 * ОСОБЕННОСТИ РАБОТЫ С INLINE И CF7
 * ============================================================
 *
 * Contact Form 7 подменяет содержимое формы на сообщение
 * «Спасибо» после успешной отправки — прямо внутри того же
 * DOM-узла. Если просто вернуть этот узел на место при
 * закрытии, при повторном открытии пользователь увидит
 * уже не форму, а success-сообщение.
 *
 * Поэтому при открытии мы запоминаем исходный HTML и исходный
 * inline-стиль элемента, а при закрытии:
 *   - если CF7 заменил содержимое на success — восстанавливаем
 *     исходный HTML;
 *   - возвращаем исходный inline-стиль (обычно display: none).
 *
 * ============================================================
 */

'use strict';

( () => {

    /** @type {Element|null} */
    let modalRoot = null;

    /** @type {Element|null} */
    let modalBody = null;

    /** @type {boolean} */
    let isOpen = false;

    /** @type {Element|null} */
    let inlineElement = null;

    /** @type {Comment|null} */
    let inlineAnchor = null;

    /**
     * Исходный HTML inline-элемента — для восстановления формы
     * после того, как CF7 заменит её на success-сообщение.
     *
     * @type {string}
     */
    let inlineOriginalHTML = '';

    /**
     * Исходный inline-стиль элемента. null — атрибута style
     * изначально не было; пустая строка — был, но пустой;
     * иначе — строка со значением.
     *
     * @type {string|null}
     */
    let inlineOriginalStyle = null;

    /**
     * Элемент, который был в фокусе до открытия модалки.
     * Используется для возврата фокуса при закрытии.
     *
     * @type {Element|null}
     */
    let lastFocused = null;

    // ============================================================
    // КАРКАС
    // ============================================================

    /**
     * Создаёт каркас модалки в DOM, если его ещё нет.
     *
     * @return {void}
     */
    const ensureRoot = () => {

        if ( modalRoot ) {
            return;
        }

        modalRoot = document.createElement( 'div' );
        modalRoot.className = 'ars-modal';
        modalRoot.setAttribute( 'role', 'dialog' );
        modalRoot.setAttribute( 'aria-modal', 'true' );
        modalRoot.setAttribute( 'aria-hidden', 'true' );

        modalRoot.innerHTML =
            '<div class="ars-modal__backdrop" data-modal-close></div>' +
            '<div class="ars-modal__window" role="document">' +
                '<button type="button" class="ars-modal__close" data-modal-close aria-label="Закрыть">' +
                    '<svg viewBox="0 0 24 24" width="20" height="20" aria-hidden="true">' +
                        '<path d="M6 6 L18 18 M18 6 L6 18" ' +
                              'fill="none" stroke="currentColor" ' +
                              'stroke-width="2" stroke-linecap="round"/>' +
                    '</svg>' +
                '</button>' +
                '<div class="ars-modal__body"></div>' +
            '</div>';

        document.body.appendChild( modalRoot );

        modalBody = modalRoot.querySelector( '.ars-modal__body' );
    };

    // ============================================================
    // ОТКРЫТИЕ / ЗАКРЫТИЕ
    // ============================================================

    /**
     * Открывает модалку с заданным содержимым.
     *
     * @param  {string} html — готовый HTML
     * @return {void}
     */
    const open = ( html ) => {

        ensureRoot();

        modalBody.innerHTML = html;

        modalRoot.classList.add( 'ars-modal--open' );
        modalRoot.setAttribute( 'aria-hidden', 'false' );

        document.body.classList.add( 'ars-modal-open' );

        isOpen = true;

        // Возврат фокуса — запоминаем, куда его вернуть при закрытии
        lastFocused = document.activeElement;

        // Фокус на кнопку закрытия
        const closeButton = modalRoot.querySelector( '.ars-modal__close' );

        if ( closeButton ) {
            closeButton.focus();
        }
    };

    /**
     * Закрывает модалку.
     *
     * @return {void}
     */
    const close = () => {

        if ( ! modalRoot || ! isOpen ) {
            return;
        }

        // Возвращаем inline-элемент на исходное место.
        // Делаем это до очистки modalBody, чтобы не потерять ссылку.
        if ( inlineElement && inlineAnchor && inlineAnchor.parentNode ) {

            // Если CF7 заменил форму на success-сообщение —
            // восстанавливаем исходный HTML.
            const hasSuccess = inlineElement.querySelector( '.wpcf7-mail-sent-ok' );

            if ( hasSuccess && inlineOriginalHTML ) {
                inlineElement.innerHTML = inlineOriginalHTML;
            }

            // Возвращаем элемент на место
            inlineAnchor.parentNode.insertBefore( inlineElement, inlineAnchor );

            // Возвращаем исходный inline-стиль
            if ( inlineOriginalStyle !== null ) {
                inlineElement.setAttribute( 'style', inlineOriginalStyle );
            } else {
                inlineElement.removeAttribute( 'style' );
            }

            // Сбрасываем сохранённые ссылки
            inlineElement         = null;
            inlineAnchor          = null;
            inlineOriginalHTML    = '';
            inlineOriginalStyle   = null;
        }

        modalRoot.classList.remove( 'ars-modal--open' );
        modalRoot.setAttribute( 'aria-hidden', 'true' );

        document.body.classList.remove( 'ars-modal-open' );

        // Очищаем тело через задержку — чтобы анимация закрытия
        // не мигала пустым блоком.
        window.setTimeout( () => {
            if ( ! isOpen && modalBody ) {
                modalBody.innerHTML = '';
            }
        }, 200 );

        isOpen = false;

        // Возврат фокуса на элемент, который открыл модалку
        if ( lastFocused && typeof lastFocused.focus === 'function' ) {
            lastFocused.focus();
        }

        lastFocused = null;
    };

    // ============================================================
    // ТИПЫ КОНТЕНТА
    // ============================================================

    /**
     * Открывает модалку с AJAX-контентом.
     *
     * @param  {string} url — адрес страницы
     * @return {Promise<void>}
     */
    const openAjax = async ( url ) => {

        // Если предыдущая модалка ещё открыта — закрываем
        if ( isOpen ) {
            close();
        }

        open( '<div class="ars-modal__loader">Загрузка…</div>' );

        try {

            const response = await fetch( url, {
                credentials: 'same-origin',
                headers:     { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if ( ! response.ok ) {
                throw new Error( 'HTTP ' + response.status );
            }

            const html = await response.text();

            if ( isOpen ) {
                modalBody.innerHTML = html;
            }

        } catch {
            if ( isOpen ) {
                modalBody.innerHTML = '<div class="ars-modal__error">Ошибка загрузки. Попробуйте ещё раз.</div>';
            }
        }
    };

    /**
     * Открывает модалку с inline-контентом.
     *
     * ПЕРЕМЕЩАЕТ элемент в модалку, а не клонирует его.
     * Клонирование не работает с Contact Form 7: его скрипты
     * вешаются на исходный DOM-узел, а у клона обработчиков
     * нет — форма визуально есть, но не реагирует.
     *
     * На месте исходного элемента оставляем якорь-комментарий,
     * по которому вернём его при закрытии.
     *
     * @param  {string} selector — CSS-селектор элемента
     * @return {void}
     */
    const openInline = ( selector ) => {

        const source = document.querySelector( selector );

        if ( ! source ) {
            return;
        }

        // Если предыдущая модалка ещё открыта — закрываем
        if ( isOpen ) {
            close();
        }

        // Сохраняем исходный inline-стиль, чтобы вернуть его при закрытии
        inlineOriginalStyle = source.getAttribute( 'style' );

        // Сохраняем эталонный HTML формы (для восстановления после CF7)
        inlineOriginalHTML = source.innerHTML;

        // Оставляем якорь на месте будущего возврата
        inlineAnchor = document.createComment( 'ars-modal-anchor' );
        source.parentNode.insertBefore( inlineAnchor, source );

        // Запоминаем элемент и переносим его в модалку
        inlineElement = source;

        // Сначала убираем исходный inline-стиль (display: none),
        // потом явно показываем элемент
        inlineElement.removeAttribute( 'style' );
        inlineElement.style.display = 'block';

        // Открываем пустую модалку и вставляем туда элемент
        open( '' );
        modalBody.appendChild( inlineElement );
    };

    /**
     * Открывает модалку с iframe.
     *
     * @param  {string} url — адрес
     * @return {void}
     */
    const openIframe = ( url ) => {

        // Если предыдущая модалка ещё открыта — закрываем
        if ( isOpen ) {
            close();
        }

        const html =
            '<iframe src="' + url + '" ' +
                    'frameborder="0" ' +
                    'allowfullscreen ' +
                    'loading="lazy" ' +
                    'style="width:100%;height:100%;border:0;display:block;"></iframe>';

        open( html );
    };

    // ============================================================
    // ОБРАБОТЧИКИ
    // ============================================================

    /**
     * Обработчик клика по документу.
     *
     * @param  {MouseEvent} event — событие
     * @return {void}
     */
    const onClick = ( event ) => {

        // На случай клика по текстовому узлу — приводим target
        // к элементу.
        const target = ( event.target instanceof Element )
            ? event.target
            : event.target.parentElement;

        if ( ! target ) {
            return;
        }

        // Закрытие по клику на data-modal-close
        const closeTarget = target.closest( '[data-modal-close]' );

        if ( closeTarget ) {
            event.preventDefault();
            close();
            return;
        }

        // Открытие
        const trigger = target.closest( '[data-modal-ajax], [data-modal-inline], [data-modal-iframe]' );

        if ( ! trigger ) {
            return;
        }

        event.preventDefault();

        if ( trigger.hasAttribute( 'data-modal-ajax' ) ) {
            openAjax( trigger.getAttribute( 'data-modal-ajax' ) );
            return;
        }

        if ( trigger.hasAttribute( 'data-modal-inline' ) ) {
            openInline( trigger.getAttribute( 'data-modal-inline' ) );
            return;
        }

        if ( trigger.hasAttribute( 'data-modal-iframe' ) ) {
            openIframe( trigger.getAttribute( 'data-modal-iframe' ) );
        }
    };

    /**
     * Закрытие по Esc.
     *
     * @param  {KeyboardEvent} event — событие
     * @return {void}
     */
    const onKeyDown = ( event ) => {

        if ( event.key === 'Escape' && isOpen ) {
            close();
        }
    };

    // ============================================================
    // СТАРТ
    // ============================================================

    document.addEventListener( 'click', onClick );
    document.addEventListener( 'keydown', onKeyDown );

})();