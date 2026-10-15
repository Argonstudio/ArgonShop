/**
 * ArgonShop — точка входа для ES-модулей фронтенда.
 *
 * @package ArgonShop
 * @author  Ivan Voitkov
 *
 * ============================================================
 * НАЗНАЧЕНИЕ
 * ============================================================
 *
 * Этот файл подключается в argon-shop.php как <script type="module">
 * и является единственной точкой входа для всего современного
 * JavaScript-кода плагина. Все остальные модули — ES-модули,
 * импортируемые отсюда.
 *
 * Модули лежат в двух папках:
 *
 *   core/     — базовые утилиты (config, dom, format, api, nonce)
 *   modules/  — функциональные модули (cart, product, search, ...)
 *
 * Каждый модуль экспортирует объект-синглтон с методом init().
 * Метод init() проверяет наличие своих элементов
 * на странице и ничего не делает, если их нет, —
 * поэтому init() всех модулей можно безопасно вызывать на всех
 * страницах без риска ошибок.
 *
 * ============================================================
 * ПОЧЕМУ НЕТ document.ready
 * ============================================================
 *
 * Скрипты с атрибутом type="module" браузер выполняет
 * отложенно — после полного разбора HTML,
 * но до события DOMContentLoaded. К моменту выполнения кода
 * DOM уже готов, поэтому обёртка вида $(document).ready() или
 * document.addEventListener('DOMContentLoaded', ...) не нужна.
 *
 * ============================================================
 * jQuery не используется
 * ============================================================
 *
 * Этот файл и все его модули НЕ используют jQuery. jQuery может
 * по-прежнему подключаться на странице (например, для слайдеров
 * Slick или сторонних плагинов), но новый код ArgonShop
 * работает только с нативным DOM API.
 *
 * ============================================================
 * ПОРЯДОК ИНИЦИАЛИЗАЦИИ
 * ============================================================
 *
 * 1. initNonceInterceptor() — глобальный перехватчик AJAX-ответов.
 *    Включается ПЕРВЫМ, до любых запросов, чтобы он мог обновить
 *    nonce, если сервер вернёт новое значение.
 *
 * 2. Модули — вызываются в произвольном порядке, каждый сам решает,
 *    что делать на текущей странице.
 *
 * ============================================================
 * КАК ДОБАВИТЬ НОВЫЙ МОДУЛЬ
 * ============================================================
 *
 * 1. Создайте файл в modules/ (например, modules/new-feature.js).
 * 2. Экспортируйте из него синглтон с методом init():
 *
 *        export const arsNewFeature = {
 *            init() { ... }
 *        };
 *
 * 3. Импортируйте его в этом файле и вызовите init() в initApp().
 *
 * ============================================================
 */

'use strict';

import { initNonceInterceptor } from './core/nonce.js';

// ============================================================
// ИМПОРТ МОДУЛЕЙ
// ============================================================

// Корзина — страница корзины
import { arsCart } from './modules/cart.js';

import { arsCartForm } from './modules/cart-form.js';

// Страница товара
import { arsProduct } from './modules/product.js';

// Кнопки карточки товара (В корзину / Купить)
import { arsCardProduct } from './modules/card-product.js';

// AJAX-поиск
import { arsSearch } from './modules/search.js';

// Сортировка каталога
import { arsSort } from './modules/sort.js';

// Кабинет — email, пароль, данные аккаунта
import { arsCabinet } from './modules/cabinet.js';

// Кабинет — фильтр заказов
import { arsOrders } from './modules/orders.js';

// Чекбоксы согласия на обработку данных
import { arsConsent } from './modules/consent.js';

// Переключаемые панели (вкладки)
import { arsControlPanels } from './modules/control-panels.js';

// Мобильное меню сайта
import { arsMobileMenu } from './modules/mobile-menu.js';

// Меню каталога
import { arsMenuCatalog } from './modules/menu-catalog.js';

// Выбор города в шапке
import { arsHeader } from './modules/header.js';

// Форма обратной связи (CF7)
import { arsContactForm } from './modules/contact-form.js';

// Каталог — открытие мобильных блоков
import { arsCatalogPage } from './modules/catalog-page.js';

// Блок популярных запросов
import { arsRequest } from './modules/request.js';

// Слайдер главной страницы (Swiper)
import { arsSliderMain } from './modules/slider-main.js';

// Слайдер изображений товара (Swiper + GLightbox)
import { arsSliderProduct } from './modules/slider-product.js';

// ============================================================
// ИНИЦИАЛИЗАЦИЯ ПРИЛОЖЕНИЯ
// ============================================================

/**
 * Точка входа. Вызывается один раз при загрузке модуля.
 *
 * Порядок вызовов:
 *   1. Перехватчик nonce — до любых запросов.
 *   2. Инициализация модулей — каждый решает, что делать.
 *
 * @return {void}
 */
const initApp = () => {

    // 1. Глобальный перехватчик AJAX-ответов для обновления nonce.
    //    Должен быть включён до первого AJAX-запроса.
    initNonceInterceptor();

    // 2. Модули.
    arsCart.init();
    arsCartForm.init();
    arsProduct.init();
    arsCardProduct.init();
    arsSearch.init();
    arsSort.init();
    arsCabinet.init();
    arsOrders.init();
    arsConsent.init();
    arsControlPanels.init();
    arsMobileMenu.init();
    arsMenuCatalog.init();
    arsHeader.init();
    arsContactForm.init();
    arsCatalogPage.init();
    arsRequest.init();
    arsSliderMain.init();
    arsSliderProduct.init();
};


// ============================================================
// ЗАПУСК
// ============================================================

// Модуль подключается как <script type="module">, поэтому браузер
// гарантирует выполнение после разбора DOM.
initApp();
