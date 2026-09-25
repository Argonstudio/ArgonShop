/**
 * ArgonShop — метабокс «Галерея» на странице товара.
 *
 * @package ArgonShop
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет интерактивными элементами метабокса галереи:
 *
 *   1. Добавление изображений через wp.media (медиабиблиотека
 *      WordPress). Поддерживает множественный выбор.
 *
 *   2. Удаление изображения из галереи.
 *
 *   3. Сортировка изображений drag-and-drop через jQuery UI
 *      Sortable. Порядок миниатюр в DOM определяет порядок
 *      сохранения в базе.
 *
 * ============================================================
 * ЗАВИСИМОСТИ
 * ============================================================
 *
 * Файл использует:
 *
 *   - jQuery — глобальная зависимость WordPress.
 *     Нужна, потому что jQuery UI Sortable и wp.media работают
 *     через jQuery. Это интерфейс ядра cms, переписывать его на
 *     нативный JS смысла нет(основной код на нативном JS)
 *
 *   - jquery-ui-sortable — из ядра WordPress.
 *
 *   - wp.media — из ядра WordPress.
 *
 * Стилистически файл обёрнут в IIFE, чтобы не засорять
 * глобальную область админки. Никаких собственных утилит
 * не определяет — вся работа идёт через jQuery.
 *
 * ============================================================
 * DOM-ЭЛЕМЕНТЫ
 * ============================================================
 *
 * .ars-gallery                  — контейнер метабокса
 * .ars-gallery-list             — список миниатюр
 * .ars-gallery-item             — одна миниатюра
 * .ars-gallery-remove           — кнопка удаления
 * .ars-gallery-add              — кнопка «Добавить изображения»
 *
 * ============================================================
 * СОХРАНЕНИЕ
 * ============================================================
 *
 * Каждая миниатюра содержит скрытый input:
 *
 *     <input type="hidden" name="slider_imgs[]" value="12">
 *
 * Порядок input'ов в DOM = порядок изображений.
 * Сохранение выполняет PHP-обработчик на хуке save_post_product
 * (см. includes/admin/galleryMetabox.php).
 *
 * ============================================================
 */

'use strict';

( function ( $ ) {

    /**
     * Инициализирует все метабоксы галереи на странице.
     *
     * На одной странице может быть несколько метабоксов, если
     * галерея используется не только в товаре. Но сейчас только
     * один — поэтому работаем с первым.
     *
     * @return {void}
     */
    const initGallery = () => {

        const $gallery = $( '.ars-gallery' );

        if ( ! $gallery.length ) {
            return;
        }

        // Инициализация sortable — параметры один в один как
        // в оригинальном плагине ACF Photo Gallery Field.
        //
        //   containment: 'parent' — helper не выходит за пределы
        //     списка. Это критично для корректного swap по
        //     tolerance: 'pointer'.
        //
        //   placeholder — отдельный класс без .ars-gallery-item.
        //
        //   disableSelection() — убирает выделение текста и
        //     иконок при перетаскивании.
        $gallery.find( '.ars-gallery-list' ).sortable({
            containment: 'parent',
            placeholder: 'ars-gallery-sortable-placeholder',
            tolerance:   'pointer',
        }).disableSelection();

        // Делегированные обработчики — работают и для новых миниатюр
        bindAddImages( $gallery );
        bindRemoveImage( $gallery );
    };

    /**
     * Добавление изображений через медиабиблиотеку.
     *
     * Открывает wp.media с параметром multiple: true. После
     * подтверждения выбора добавляет каждое изображение в
     * список галереи.
     *
     * @param  {jQuery} $gallery — контейнер метабокса
     * @return {void}
     */
    const bindAddImages = ( $gallery ) => {

        $gallery.on( 'click', '.ars-gallery-add', ( event ) => {

            event.preventDefault();

            if ( typeof wp === 'undefined' || ! wp.media ) {
                return;
            }

            const frame = wp.media({
                title:    'Выберите изображения',
                button:   { text: 'Добавить в галерею' },
                library:  { type: 'image' },
                multiple: true,
            });

            frame.on( 'select', () => {

                const selection = frame.state().get( 'selection' );

                selection.each( ( attachment ) => {

                    const data = attachment.toJSON();

                    addImage( $gallery, data );
                });
            });

            frame.open();
        });
    };

    /**
     * Удаление изображения из галереи.
     *
     * @param  {jQuery} $gallery — контейнер метабокса
     * @return {void}
     */
    const bindRemoveImage = ( $gallery ) => {

        $gallery.on( 'click', '.ars-gallery-remove', ( event ) => {

            event.preventDefault();

            const $item = $( event.currentTarget ).closest( '.ars-gallery-item' );

            if ( $item.length ) {
                $item.remove();
            }
        });
    };

    /**
     * Добавляет одну миниатюру в список галереи.
     *
     * Если изображение с таким ID уже есть — не добавляем повторно.
     *
     * @param  {jQuery} $gallery — контейнер метабокса
     * @param  {Object} data     — данные вложения из wp.media
     * @return {void}
     */
    const addImage = ( $gallery, data ) => {

        if ( ! data || ! data.id ) {
            return;
        }

        const id = parseInt( data.id, 10 );

        if ( ! id ) {
            return;
        }

        // Проверка на дубликат
        const exists = $gallery
            .find( '.ars-gallery-item[data-id="' + id + '"]' )
            .length > 0;

        if ( exists ) {
            return;
        }

        // URL миниатюры. Используем sizes.thumbnail, если он есть,
        // иначе — просто url вложения.
        let thumbUrl = data.url || '';

        if ( data.sizes && data.sizes.thumbnail && data.sizes.thumbnail.url ) {
            thumbUrl = data.sizes.thumbnail.url;
        }

        const $list = $gallery.find( '.ars-gallery-list' );

        const html =
            '<li class="ars-gallery-item" data-id="' + id + '">' +
                '<input type="hidden" name="slider_imgs[]" value="' + id + '" />' +
                '<img src="' + thumbUrl + '" alt="" />' +
                '<button type="button" class="ars-gallery-remove" title="Удалить изображение">' +
                    '<span class="dashicons dashicons-dismiss"></span>' +
                '</button>' +
            '</li>';

        $list.append( html );

        // Обновляем состояние sortable, чтобы placeholder
        // корректно работал с новыми элементами.
        if ( $list.hasClass( 'ui-sortable' ) ) {
            $list.sortable( 'refresh' );
        }
    };

    // ============================================================
    // СТАРТ
    // ============================================================

    $( document ).ready( () => {
        initGallery();
    });

})( jQuery );