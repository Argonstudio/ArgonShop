<?php
/**
 * Форма быстрого оформления заказа (шаблон части)
 *
 * @package ArgonShopTheme
 * @author  Иван Войтков (Ivan Voitkov)
 * @version 1.0.0
 * 
 * ГДЕ ИСПОЛЬЗУЕТСЯ:
 * Подключается через get_template_part( 'template-parts/cart/quickOrder' )
 * из шаблона shoppingCartPage.php (страница корзины).
 * 
 * ЧТО ВЫВОДИТ:
 * Краткую форму оформления заказа — минимум полей для быстрого
 * оформления. Используется как форма по умолчанию для неавторизованных
 * пользователей.
 * 
 * ============================================================
 * ВЗАИМОДЕЙСТВИЕ С ПЛАГИНОМ ARGON SHOP
 * ============================================================
 * 
 * ФУНКЦИИ ПЛАГИНА:
 * 
 * show_user_fields( $user_ID, $type, $wrap, $class50, $class100, $classTitle, $classInput, $required )
 *   Файл: includes/api/getSetting.php
 *   Выводит поля формы из настроек магазина (settingShop).
 *   Для типа "quick" — минимальный набор полей быстрого заказа.
 *   Параметр $required — массив обязательных полей.
 * 
 * ============================================================
 * ДИНАМИЧЕСКИЕ ПОЛЯ ФОРМЫ
 * ============================================================
 * 
 * Часть полей формы задаётся администратором в настройках магазина:
 * 
 * WordPress Админка → Настройки → Магазин → Данные пользователя
 * 
 * Формат строки для каждого поля:
 *   ID[Имя, Placeholder, class]
 * 
 * Где:
 * - ID          — атрибут name в HTML
 * - Имя         — заголовок поля
 * - Placeholder — подсказка внутри поля
 * - class       — 50 или 100 (ширина поля)
 * 
 * Пример полей для быстрого заказа:
 *   telefonUser[Телефон,+7 (922) xxx-xx-xx,100]
 *   addressUser[Адрес,Московская область/запятая/ ДНП Янтарь,100]
 * 
 * Разделитель — запятая. Для запятой внутри текста используется
 * метка "/запятая/".
 * 
 * ============================================================
 * ОБРАБОТКА ПОЛЕЙ В ПЛАГИНЕ
 * ============================================================
 * 
 * Все поля из настроек автоматически собираются в AJAX-обработчике
 * submitCart_shoppingCart_callback() через get_valueFieldsCart()
 * с типом формы "quick".
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

// Защита от прямого вызова файла
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<form enctype="multipart/form-data" method="post" data-type="quick" class="form-cart form-cartquick">

    <?php 
    
    $required = array( 'telefonUser', 'addressUser' );
    
    // Функция плагина Argon Shop (includes/api/getSetting.php)
    if ( function_exists( 'show_user_fields' ) ) {
        show_user_fields( $user_ID, 'quick', 'div', 'cartFields50', 'cartFields100', 'cartFieldsTitle', 'cartFieldInput', $required );
    }
    
    ?>
    
    <div class="cartFields100">
        
        <div class="cartFieldsTitle">Комментарий:</div>
        <textarea name="messageUser" placeholder="Ваш комментарий"></textarea>
        
    </div>
    
				
    <input value="ОТПРАВИТЬ ЗАКАЗ" type="submit" class="submitCart" disabled>
    <div class="submitError"><span></span></div>
                    
</form>