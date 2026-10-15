/**
 * ArgonShopTheme — лёгкое модальное окно.
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Заменяет Fancybox для карты и формы обратной связи. Не тянет
 * никаких библиотек: чистый ES6+ и API DOM.
 *
 * Поддерживает три способа открытия:
 *
 *   1. AJAX — запрос к URL, полученный HTML вставляется в модальное окно.
 *      Используется для карты (шаблон «Map AJAX» в теме).
 *
 *   2. INLINE — перемещение DOM-элемента в модальное окно по селектору.
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
 * Кнопка, открывающая модальное окно, должна иметь один из атрибутов:
 *
 *   data-modal-ajax="/url/"      — загрузка через AJAX
 *   data-modal-inline="#elem"    — открыть как inline
 *   data-modal-iframe="/url/"    — открыть в iframe
 *
 * Пример:
 *
 *   <a href="#" data-modal-ajax="/adres-na-karte-moskva/">Map</a>
 *   <a href="#feedback" data-modal-inline="#feedback">Feedback</a>
 *
 * ============================================================
 * ЗАКРЫТИЕ
 * ============================================================
 *
 *   - клик по подложке вне окна;
 *   - клик по кнопке с data-modal-close;
 *   - нажатие Esc.
 *
 * ============================================================
 * ЗАМЕЧАНИЯ О INLINE И CF7
 * ============================================================
 *
 * Contact Form 7 заменяет содержимое формы сообщением «Спасибо»
 * после успешной отправки — прямо внутри того же
 * DOM-узла. Если просто вернуть этот узел на место при
 * закрытии, при следующем открытии пользователь увидит
 * сообщение об успехе вместо формы.
 *
 * Поэтому при открытии мы запоминаем исходный HTML и исходный
 * inline-стиль элемента, а при закрытии:
 *   - если CF7 заменил содержимое сообщением об успехе, восстанавливаем
 *     исходный HTML;
 *   - возвращаем исходный inline-стиль (обычно display: none).
 *
 * ============================================================
 */

'use strict';

( () => {

    /**
     * Тексты интерфейса. Приходят из PHP (wp_localize_script →
     * arsModalI18n, текстовый домен argon-shop-theme); английский — запасной.
     *
     * @since 1.1.0
     * @type {{ close: string, loading: string, loadError: string }}
     */
    const i18n = Object.assign( {
        close:     'Close',
        loading:   'Loading…',
        loadError: 'Loading failed. Please try again.',
    }, window.arsModalI18n || {} );

    /**
     * Экранирует строку для HTML-текста.
     *
     * @param  {string} value
     * @return {string}
     */
    const escapeHtml = ( value ) => String( value )
        .replace( /&/g, '&amp;' )
        .replace( /</g, '&lt;' )
        .replace( />/g, '&gt;' );

    /**
     * Экранирует строку для HTML-атрибута.
     *
     * @param  {string} value
     * @return {string}
     */
    const escapeAttr = ( value ) => escapeHtml( value ).replace( /"/g, '&quot;' );

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
     * Исходный HTML inline-элемента — чтобы восстановить форму
     * после того, как CF7 заменит её сообщением об успехе.
     *
     * @type {string}
     */
    let inlineOriginalHTML = '';

    /**
     * Исходный inline-стиль элемента. null — атрибута style не было;
     * пустая строка — был, но пустой;
     * иначе — строка со значением.
     *
     * @type {string|null}
     */
    let inlineOriginalStyle = null;

    /**
     * Элемент, имевший фокус до открытия модального окна.
     * Используется для возврата фокуса при закрытии.
     *
     * @type {Element|null}
     */
    let lastFocused = null;

    // ============================================================
    // КАРКАС
    // ============================================================

    /**
     * Создаёт каркас модального окна в DOM, если его ещё нет.
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
                '<button type="button" class="ars-modal__close" data-modal-close aria-label="' + escapeAttr( i18n.close ) + '">' +
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
     * Открывает модальное окно с переданным содержимым.
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
     * Закрывает модальное окно.
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

            // Если CF7 заменил форму сообщением об успехе,
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

        // Очищаем тело с задержкой, чтобы анимация закрытия
        // не мигала пустым блоком.
        window.setTimeout( () => {
            if ( ! isOpen && modalBody ) {
                modalBody.innerHTML = '';
            }
        }, 200 );

        isOpen = false;

        // Возвращаем фокус элементу, открывшему модальное окно
        if ( lastFocused && typeof lastFocused.focus === 'function' ) {
            lastFocused.focus();
        }

        lastFocused = null;
    };

    // ============================================================
    // ТИПЫ СОДЕРЖИМОГО
    // ============================================================

    /**
     * Открывает модальное окно с AJAX-содержимым.
     *
     * @param  {string} url — адрес страницы
     * @return {Promise<void>}
     */
    const openAjax = async ( url ) => {

        // Если предыдущее окно ещё открыто — закрываем его
        if ( isOpen ) {
            close();
        }

        open( '<div class="ars-modal__loader">' + escapeHtml( i18n.loading ) + '</div>' );

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
                modalBody.innerHTML = '<div class="ars-modal__error">' + escapeHtml( i18n.loadError ) + '</div>';
            }
        }
    };

    /**
     * Открывает модальное окно с inline-содержимым.
     *
     * ПЕРЕМЕЩАЕТ элемент в модальное окно вместо клонирования.
     * Клонирование не работает с Contact Form 7: его скрипты
     * привязаны к исходному DOM-узлу, а у клона нет
     * обработчиков — форма видна, но не реагирует.
     *
     * На месте исходного элемента оставляется комментарий-якорь,
     * по которому мы возвращаем его при закрытии.
     *
     * @param  {string} selector — CSS-селектор элемента
     * @return {void}
     */
    const openInline = ( selector ) => {

        const source = document.querySelector( selector );

        if ( ! source ) {
            return;
        }

        // Если предыдущее окно ещё открыто — закрываем его
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

        // Запоминаем элемент и перемещаем его в модальное окно
        inlineElement = source;

        // Сначала убираем исходный inline-стиль (display: none),
        // затем явно показываем элемент
        inlineElement.removeAttribute( 'style' );
        inlineElement.style.display = 'block';

        // Открываем пустое модальное окно и вставляем в него элемент
        open( '' );
        modalBody.appendChild( inlineElement );
    };

    /**
     * Открывает модальное окно с iframe.
     *
     * @param  {string} url — адрес
     * @return {void}
     */
    const openIframe = ( url ) => {

        // Если предыдущее окно ещё открыто — закрываем его
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
    // ЗАПУСК
    // ============================================================

    document.addEventListener( 'click', onClick );
    document.addEventListener( 'keydown', onKeyDown );

})();