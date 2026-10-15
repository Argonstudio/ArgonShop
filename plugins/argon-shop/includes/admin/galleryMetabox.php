<?php
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
 * Добавляет метабокс с галереей изображений товара. Заменяет
 * сторонний плагин ACF Photo Gallery Field, встроенный
 * в ArgonShop без зависимости от ACF.
 *
 * ============================================================
 * ФОРМАТ ХРАНЕНИЯ
 * ============================================================
 *
 * Мета-поле: slider_imgs
 * Значение:  строка из ID изображений через запятую,
 *            например «12,15,18,22».
 *
 * Формат совместим со старым плагином ACF Photo Gallery —
 * данные из существующих галерей читаются без миграции.
 *
 * ============================================================
 * МЕХАНИКА СОХРАНЕНИЯ
 * ============================================================
 *
 * Каждое изображение в списке сопровождается скрытым полем:
 *
 *     <input type="hidden" name="slider_imgs[]" value="12">
 *
 * При сохранении записи WordPress передаёт массив ID в $_POST.
 * Обработчик save_post_gallery() собирает его, очищает и
 * сохраняет в мета-поле единой строкой.
 *
 * Порядок изображений в форме = порядок полей в DOM.
 * Пользователь меняет порядок перетаскиванием
 * (jQuery UI Sortable из ядра WordPress), DOM перестраивается,
 * и порядок сохраняется при сохранении.
 *
 * ============================================================
 * ЗАГРУЗКА
 * ============================================================
 *
 * Файл подключается в argon-shop.php через require_once.
 * Регистрация метабокса — хук add_meta_boxes.
 * Обработчик сохранения — хук save_post_product.
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// ============================================================
// РЕГИСТРАЦИЯ МЕТАБОКСА
// ============================================================

add_action( 'add_meta_boxes', 'ars_gallery_register_metabox' );

/**
 * Регистрирует метабокс «Галерея» на странице товара.
 *
 * @return void
 */
function ars_gallery_register_metabox() {

    add_meta_box(
        'ars_gallery_metabox',
        __( 'Gallery', 'argon-shop' ),
        'ars_gallery_render_metabox',
        'product',
        'normal',
        'default'
    );
}

// ============================================================
// ВЫВОД МЕТАБОКСА
// ============================================================

/**
 * Выводит HTML метабокса галереи.
 *
 * @param  WP_Post $post — редактируемый товар
 * @return void
 */
function ars_gallery_render_metabox( $post ) {

    // Nonce для проверки при сохранении
    wp_nonce_field( 'ars_gallery_save', 'ars_gallery_nonce' );

    // Список ID изображений (совместим со старым форматом)
    $ids = function_exists( 'as_get_product_gallery_ids' )
        ? as_get_product_gallery_ids( $post->ID )
        : array();

    ?>

    <div class="ars-gallery" data-postid="<?php echo esc_attr( $post->ID ); ?>">

        <ul class="ars-gallery-list">

            <?php foreach ( $ids as $image_id ) : ?>

                <?php
                $thumb_url = wp_get_attachment_thumb_url( $image_id );

                if ( ! $thumb_url ) {
                    continue;
                }
                ?>

                <li class="ars-gallery-item" data-id="<?php echo esc_attr( $image_id ); ?>">

                    <input type="hidden"
                           name="slider_imgs[]"
                           value="<?php echo esc_attr( $image_id ); ?>" />

                    <img src="<?php echo esc_url( $thumb_url ); ?>"
                         alt="" />

                    <button type="button"
                            class="ars-gallery-remove"
                            title="<?php esc_attr_e( 'Remove image', 'argon-shop' ); ?>">
                        <span class="dashicons dashicons-dismiss"></span>
                    </button>

                </li>

            <?php endforeach; ?>

        </ul>

        <button type="button"
                class="button button-primary button-large ars-gallery-add">
            <?php esc_html_e( 'Add images', 'argon-shop' ); ?>
        </button>

        <p class="ars-gallery-hint">
            <?php esc_html_e( 'Drag images with the mouse to change the order. The first image is the main one and is shown first.', 'argon-shop' ); ?>
        </p>

    </div>

    <?php
}

// ============================================================
// СОХРАНЕНИЕ
// ============================================================

add_action( 'save_post_product', 'ars_gallery_save_metabox' );

/**
 * Сохраняет список изображений галереи при сохранении товара.
 *
 * @param  int $post_id — ID товара
 * @return void
 */
function ars_gallery_save_metabox( $post_id ) {

    // Проверка nonce
    if ( ! isset( $_POST['ars_gallery_nonce'] )
        || ! wp_verify_nonce( $_POST['ars_gallery_nonce'], 'ars_gallery_save' ) ) {
        return;
    }

    // Пропускаем автосохранение
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Пропускаем ревизии
    if ( wp_is_post_revision( $post_id ) ) {
        return;
    }

    // Проверка прав
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }

    // Читаем массив ID из формы
    if ( ! isset( $_POST['slider_imgs'] ) || ! is_array( $_POST['slider_imgs'] ) ) {
        delete_post_meta( $post_id, 'slider_imgs' );
        return;
    }

    // Очищаем: absint для каждого элемента, отбрасываем нули и дубликаты
    $ids = array_map( 'absint', wp_unslash( $_POST['slider_imgs'] ) );
    $ids = array_filter( $ids );
    $ids = array_unique( $ids );
    $ids = array_values( $ids );

    if ( empty( $ids ) ) {
        delete_post_meta( $post_id, 'slider_imgs' );
        return;
    }

    // Сохраняем строкой через запятую — совместимо со старым форматом
    update_post_meta( $post_id, 'slider_imgs', implode( ',', $ids ) );
}