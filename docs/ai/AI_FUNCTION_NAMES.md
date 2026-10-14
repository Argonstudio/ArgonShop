# Function names: old → new (since 1.1.0)

Since 1.1.0 plugin functions have the `as_` prefix (and the breadcrumbs class — `AS_`). Exceptions that still exist in the code (do not copy them in new code): `argon_shop_js_i18n`, `ars_add_module_type`, `ars_gallery_register_metabox`, `ars_gallery_render_metabox`, `ars_gallery_save_metabox`, `argon_search_ajax_*`, `argon_search_fix_keyboard_layout`. This prevents "Cannot redeclare function" fatal errors when another plugin or theme declares a function with the same name (`getCookie`, `sanitize_callback`, `kama_breadcrumbs`, etc.).

- **Use only the new names in new code.**
- "Wrapper" = "yes": the old name keeps working via `includes/legacy-functions.php` (declared only if the name is free). This is a public API for third-party themes and snippets.
- The other old names (AJAX handlers, meta boxes, internal functions) no longer exist. **AJAX action names (`action`) did not change** — no JS changes needed.
- Hook names (`apply_filters( 'kama_breadcrumbs' … )`), CSS classes and options did not change either.
- Functions added in 1.1.0 (no old names): `as_get_currencies`, `as_get_currency`, `as_get_currency_symbol`, `as_get_currency_position`, `as_price_html`, `as_get_currency_per_piece` (`includes/api/getSetting.php`), `as_currency_callback` (`settingPage.php`), `argon_shop_js_i18n` (`argon-shop.php`), `as_search_language`, `as_search_layout_map`, `as_search_layout_variants`, `as_search_stop_words`, `as_search_singular`, `as_search_word_variants`, `as_search_where_sql` (`includes/interface/search/searchLanguage.php`; `as_search_get_words` moved there too). `as_fixKeyboardlayout` is kept and now returns the first variant from `as_search_layout_variants()`.

| Old name | New name | Wrapper |
|---|---|---|
| `add_admin_styleScript` | `as_add_admin_styleScript` |  |
| `add_interface_styleScript` | `as_add_interface_styleScript` |  |
| `add_new_custom_fields` | `as_add_new_custom_fields` |  |
| `addCookieHistory_history_callback` | `as_addCookieHistory_history_callback` |  |
| `addProducts_shoppingCart_callback` | `as_addProducts_shoppingCart_callback` |  |
| `amountProducts_shoppingCart_callback` | `as_amountProducts_shoppingCart_callback` |  |
| `are_all_categories_checked` | `as_are_all_categories_checked` |  |
| `array_partial_merge` | `as_array_partial_merge` | yes |
| `button_card_product` | `as_button_card_product` | yes |
| `cabinetAccountSave_callback` | `as_cabinetAccountSave_callback` |  |
| `cabinetEditEmailPassword_callback` | `as_cabinetEditEmailPassword_callback` |  |
| `cabinetPage_callback` | `as_cabinetPage_callback` |  |
| `change_nav_menu_objects` | `as_change_nav_menu_objects` |  |
| `characteristics_metabox` | `as_characteristics_metabox` |  |
| `cheak_detailed_fields` | `as_cheak_detailed_fields` | yes |
| `check_saleSteps` | `as_check_saleSteps` | yes |
| `cleanParameters` | `as_cleanParameters` |  |
| `convert_id_to_term_in_query` | `as_convert_id_to_term_in_query` |  |
| `countDigits` | `as_countDigits` | yes |
| `create_user_ordersTable` | `as_create_user_ordersTable` | yes |
| `createTableProduct` | `as_createTableProduct` |  |
| `deleteAllProducts_shoppingCart_callback` | `as_deleteAllProducts_shoppingCart_callback` |  |
| `deleteProducts_shoppingCart_callback` | `as_deleteProducts_shoppingCart_callback` |  |
| `detailBuyer_metabox` | `as_detailBuyer_metabox` |  |
| `detailedfields_add_meta_box` | `as_detailedfields_add_meta_box` |  |
| `detailedfields_remove_meta_box` | `as_detailedfields_remove_meta_box` |  |
| `detailOrders_metabox` | `as_detailOrders_metabox` |  |
| `edit_new_custom_fields` | `as_edit_new_custom_fields` |  |
| `emailTextsGenerator` | `as_emailTextsGenerator` | yes |
| `fixKeyboardlayout` | `as_fixKeyboardlayout` |  |
| `generateResultHTML` | `as_generateResultHTML` |  |
| `get_cabinetPageID` | `as_get_cabinetPageID` | yes |
| `get_cabinetPageURL` | `as_get_cabinetPageURL` | yes |
| `get_cartPageID` | `as_get_cartPageID` | yes |
| `get_cartPageURL` | `as_get_cartPageURL` | yes |
| `get_catalog_terms` | `as_get_catalog_terms` | yes |
| `get_catalog_termsWP` | `as_get_catalog_termsWP` | yes |
| `get_charact` | `as_get_charact` | yes |
| `get_history_products` | `as_get_history_products` | yes |
| `get_marked_products` | `as_get_marked_products` | yes |
| `get_product_parentterms` | `as_get_product_parentterms` |  |
| `get_safe_server_name` | `as_get_safe_server_name` |  |
| `get_sort_products` | `as_get_sort_products` | yes |
| `get_tax_query_marked_products` | `as_get_tax_query_marked_products` | yes |
| `get_terms_catalog` | `as_get_terms_catalog` |  |
| `get_terms_ids_marked_products` | `as_get_terms_ids_marked_products` | yes |
| `get_used_statuses` | `as_get_used_statuses` | yes |
| `get_user_orders` | `as_get_user_orders` | yes |
| `get_valueFieldsCart` | `as_get_valueFieldsCart` | yes |
| `getActualPrice` | `as_getActualPrice` | yes |
| `getActualPriceAllProducts` | `as_getActualPriceAllProducts` | yes |
| `getActualStepDiscont` | `as_getActualStepDiscont` | yes |
| `getAjaxSearchResult_callback` | `as_getAjaxSearchResult_callback` |  |
| `getClassActiveItem` | `as_getClassActiveItem` | yes |
| `getCookie` | `as_getCookie` | yes |
| `getDataCart` | `as_getDataCart` | yes |
| `getDiscount` | `as_getDiscount` | yes |
| `kama_breadcrumbs` | `as_kama_breadcrumbs` | yes |
| `Kama_Breadcrumbs` | `AS_Kama_Breadcrumbs` |  |
| `kama_pagenavi` | `as_kama_pagenavi` | yes |
| `legalPerson_callback` | `as_legalPerson_callback` |  |
| `mainParameters_metabox` | `as_mainParameters_metabox` |  |
| `merge_search_result` | `as_merge_search_result` |  |
| `my_function_admin_bar` | `as_my_function_admin_bar` |  |
| `my_login_redirect` | `as_my_login_redirect` |  |
| `orders_add_meta_box` | `as_orders_add_meta_box` |  |
| `ordersFiltersStatus_callback` | `as_ordersFiltersStatus_callback` |  |
| `person_callback` | `as_person_callback` |  |
| `plugin_settings` | `as_plugin_settings` |  |
| `post_in_term` | `as_post_in_term` | yes |
| `prepCatalog` | `as_prepCatalog` |  |
| `prepPost` | `as_prepPost` |  |
| `prepProduct` | `as_prepProduct` |  |
| `queryAllocatorSearchResult` | `as_queryAllocatorSearchResult` |  |
| `queryManagerSearchResult` | `as_queryManagerSearchResult` |  |
| `queryPostSearchResult` | `as_queryPostSearchResult` |  |
| `queryProductSearchResult` | `as_queryProductSearchResult` |  |
| `queryTaxonomySearchResult` | `as_queryTaxonomySearchResult` |  |
| `quick_callback` | `as_quick_callback` |  |
| `redirect_after_logout` | `as_redirect_after_logout` |  |
| `restrict_posts_by_money` | `as_restrict_posts_by_money` |  |
| `sanitize_callback` | `as_sanitize_settings` |  |
| `save_custom_taxonomy_meta` | `as_save_custom_taxonomy_meta` |  |
| `save_data_order` | `as_save_data_order` |  |
| `save_detailed_fields` | `as_save_detailed_fields` |  |
| `search_custom_orderby` | `as_search_custom_orderby` |  |
| `search_custom_where` | `as_search_custom_where` |  |
| `search_distinct` | `as_search_distinct` |  |
| `search_filter_taxonomy` | `as_search_filter_taxonomy` |  |
| `search_get_words` | `as_search_get_words` |  |
| `search_is_custom_search` | `as_search_is_custom_search` |  |
| `search_join_article` | `as_search_join_article` |  |
| `searchFieldsTerm` | `as_searchFieldsTerm` |  |
| `setting_register_admin_page` | `as_setting_register_admin_page` |  |
| `settings_shop_rendering` | `as_settings_shop_rendering` |  |
| `shoppingCartPage_callback` | `as_shoppingCartPage_callback` |  |
| `show_user_fields` | `as_show_user_fields` | yes |
| `showResult` | `as_showResult` |  |
| `sort_terms_clause` | `as_sort_terms_clause` |  |
| `sortingCatalog_callback` | `as_sortingCatalog_callback` |  |
| `splitMenu` | `as_splitMenu` | yes |
| `statusOrders_metabox` | `as_statusOrders_metabox` |  |
| `submitCart_shoppingCart_callback` | `as_submitCart_shoppingCart_callback` |  |
| `typeSale_callback` | `as_typeSale_callback` |  |
| `updateCharacteristicsAjax` | `as_updateCharacteristicsAjax` |  |
| `updateCharacteristicsFunction` | `as_updateCharacteristicsFunction` |  |
| `view_menu_elements` | `as_view_menu_elements` | yes |
| `view_products_list` | `as_view_products_list` | yes |
| `view_terms` | `as_view_terms` |  |
| `view_user_order` | `as_view_user_order` | yes |
| `view_user_orders` | `as_view_user_orders` | yes |
| `wholesalePrice_metabox` | `as_wholesalePrice_metabox` |  |
| `wholesalePrice_shoppingCart_callback` | `as_wholesalePrice_shoppingCart_callback` |  |
| `zg_lw_setcookie` | `as_zg_lw_setcookie` |  |
| `zg_lwp_header` | `as_zg_lwp_header` |  |
| `zg_lwp_init` | `as_zg_lwp_init` |  |
| `zg_lwp_widget` | `as_zg_lwp_widget` |  |
| `zg_lwp_widget_control` | `as_zg_lwp_widget_control` |  |
| `zg_recently_viewed` | `as_zg_recently_viewed` |  |
