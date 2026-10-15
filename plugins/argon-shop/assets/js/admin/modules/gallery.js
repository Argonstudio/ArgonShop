/**
 * ArgonShop — метабокс «Галерея» на странице товара.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Управляет интерактивными элементами метабокса галереи:
 *
 *   1. Добавление изображений через wp.media (медиабиблиотеку
 *      WordPress). Поддерживает множественный выбор.
 *
 *   2. Удаление изображения из галереи.
 *
 *   3. Сортировка изображений перетаскиванием через jQuery UI
 *      Sortable. Порядок миниатюр в DOM определяет порядок,
 *      сохраняемый в базе данных.
 *
 * ============================================================
 * ЗАВИСИМОСТИ
 * ============================================================
 *
 * Файл использует:
 *
 *   - jQuery — глобальную зависимость WordPress.
 *     Нужен, потому что jQuery UI Sortable и wp.media работают
 *     через jQuery. Это интерфейс ядра CMS, переписывать его
 *     на нативный JS нет смысла (основной код — на нативном JS)
 *
 *   - jquery-ui-sortable — из ядра WordPress.
 *
 *   - wp.media — из ядра WordPress.
 *
 * Файл обёрнут в IIFE, чтобы не засорять
 * глобальную область видимости админки. Собственных утилит
 * он не объявляет — вся работа идёт через jQuery.
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
 * Порядок input в DOM = порядок изображений.
 * Сохранение выполняет PHP-обработчик на хуке save_post_product
 * (см. includes/admin/galleryMetabox.php).
 *
 * ============================================================
 */

'use strict';

( function ( $ ) {

    /**
     * Тексты интерфейса. Приходят из PHP (wp_localize_script → arsGalleryI18n)
     * и переводятся файлами .po плагина; английский — запасной вариант.
     *
     * @type {{ selectImages: string, addToGallery: string, removeImage: string }}
     */
    const i18n = Object.assign( {
        selectImages: 'Select images',
        addToGallery: 'Add to gallery',
        removeImage:  'Remove image',
    }, window.arsGalleryI18n || {} );

    /**
     * Экранирует строку для HTML-атрибута.
     *
     * @param  {string} value
     * @return {string}
     */
    const escAttr = ( value ) => String( value )
        .replace( /&/g, '&amp;' ).replace( /"/g, '&quot;' )
        .replace( /</g, '&lt;' ).replace( />/g, '&gt;' );

    /**
     * Инициализирует все метабоксы галереи на странице.
     *
     * На странице может быть несколько метабоксов, если
     * галерея используется не только для товаров. Но сейчас он только
     * один — поэтому работаем с первым.
     *
     * @return {void}
     */
    const initGallery = () => {

        const $gallery = $( '.ars-gallery' );

        if ( ! $gallery.length ) {
            return;
        }

        // Инициализация Sortable — параметры точно такие же, как
        // в оригинальном плагине ACF Photo Gallery Field.
        //
        //   containment: 'parent' — helper не выходит за пределы
        //     списка. Это критично для корректной перестановки с
        //     tolerance: 'pointer'.
        //
        //   placeholder — отдельный класс без .ars-gallery-item.
        //
        //   disableSelection() — убирает выделение текста и иконок
        //     при перетаскивании.
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
     * Открывает wp.media с multiple: true. После
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
                title:    i18n.selectImages,
                button:   { text: i18n.addToGallery },
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
        // иначе просто url вложения.
        let thumbUrl = data.url || '';

        if ( data.sizes && data.sizes.thumbnail && data.sizes.thumbnail.url ) {
            thumbUrl = data.sizes.thumbnail.url;
        }

        const $list = $gallery.find( '.ars-gallery-list' );

        const html =
            '<li class="ars-gallery-item" data-id="' + id + '">' +
                '<input type="hidden" name="slider_imgs[]" value="' + id + '" />' +
                '<img src="' + thumbUrl + '" alt="" />' +
                '<button type="button" class="ars-gallery-remove" title="' + escAttr( i18n.removeImage ) + '">' +
                    '<span class="dashicons dashicons-dismiss"></span>' +
                '</button>' +
            '</li>';

        $list.append( html );

        // Обновляем состояние sortable, чтобы плейсхолдер
        // корректно работал с новыми элементами.
        if ( $list.hasClass( 'ui-sortable' ) ) {
            $list.sortable( 'refresh' );
        }
    };

    // ============================================================
    // ЗАПУСК
    // ============================================================

    $( document ).ready( () => {
        initGallery();
    });

})( jQuery );