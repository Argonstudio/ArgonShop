<?php
/**
 * Форма быстрого заказа (часть шаблона)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cart/quickOrder' )
 * из шаблона shoppingCartPage.php (страница корзины).
 * 
 * ЧТО ВЫВОДИТ:
 * Короткая форма оформления — минимум полей для быстрого
 * заказа. Используется как форма по умолчанию для
 * неавторизованных пользователей.
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * as_show_user_fields( $user_ID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required )
 *   File: includes/api/getSetting.php
 *   Выводит поля формы из настроек магазина (settingShop).
 *   Для типа «quick» — минимальный набор полей быстрого заказа.
 *   Параметр $required — массив обязательных полей.
 * 
 * ============================================================
 * ДИНАМИЧЕСКИЕ ПОЛЯ ФОРМЫ
 * ============================================================
 * 
 * Некоторые поля формы задаёт администратор в настройках магазина:
 * 
 * Админка WordPress → Настройки → Магазин → Данные пользователя
 * 
 * Формат строки для каждого поля:
 *   ID[Название, Плейсхолдер, class]
 * 
 * Где:
 * - ID          — HTML-атрибут name
 * - Name        — подпись поля
 * - Placeholder — подсказка внутри поля
 * - class       — 50 или 100 (ширина поля)
 * 
 * Пример полей для быстрого заказа:
 *   telefonUser[Phone,+1 (555) xxx-xxxx,100]
 *   addressUser[Address,New York/comma/ 5th Avenue,100]
 * 
 * Разделитель — запятая. Для запятой внутри текста используйте
 * маркер «/comma/».
 * 
 * ============================================================
 * ОБРАБОТКА ПОЛЕЙ В ПЛАГИНЕ
 * ============================================================
 * 
 * Все поля из настроек собираются автоматически в AJAX-обработчике
 * as_submitCart_shoppingCart_callback() via as_get_valueFieldsCart()
 * с типом формы «quick».
 * 
 * Фиксированные поля, обрабатываемые плагином отдельно:
 *   - messageUser   — комментарий *
 * 
 * ============================================================
 * ПЕРЕМЕННЫЕ ИЗ ШАБЛОНА КОРЗИНЫ
 * ============================================================
 * 
 * $user_ID — ID текущего пользователя (get_current_user_id())
 */

// Запрет прямого доступа к файлу
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<form enctype="multipart/form-data" method="post" data-type="quick" class="form-cart form-cartquick">

    <?php 
    
    // Список обязательных полей приходит из плагина (тот же, что проверяет сервер)
    $required = function_exists( 'as_get_required_fields' ) ? as_get_required_fields( 'quick' ) : array( 'telefonUser', 'addressUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'as_show_user_fields' ) ) {
        as_show_user_fields( $user_ID, 'quick', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle"><?php esc_html_e( 'Comment:', 'argon-shop-theme' ); ?></div>
        <textarea name="messageUser" placeholder="<?php esc_attr_e( 'Your comment', 'argon-shop-theme' ); ?>"></textarea>
        
    </div>
    
				
    <input value="<?php esc_attr_e( 'PLACE ORDER', 'argon-shop-theme' ); ?>" type="submit" class="submitCart" disabled>
    <div class="submitError"><span></span></div>
                    
</form>