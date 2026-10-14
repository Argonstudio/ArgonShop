<?php
/**
 * User order list in the customer account (template part)
 *
 * @package ArgonShopTheme
 * @author  Ivan Voitkov
 * 
 * WHERE IT IS USED:
 * Included via get_template_part( 'template-parts/cabinet/ordering' )
 * from the myCabinetPage.php template (customer account page).
 * 
 * WHAT IT OUTPUTS:
 * The list of the user's orders with a status filter.
 * 
 * ============================================================
 * INTERACTION WITH THE ARGON SHOP PLUGIN
 * ============================================================
 * 
 * PLUGIN FUNCTIONS:
 * 
 * as_view_user_orders( $user_ID, $filters )
 *   File: includes/interface/cabinet/userOrders/ordersView.php
 *   Outputs the HTML block with the user's orders.
 *   Internally it calls:
 *     - as_get_user_orders()         — getting the orders (ordersView.php)
 *     - as_get_used_statuses()       — used statuses
 *     - as_create_user_ordersTable() — orders table output
 *     - as_view_user_order()         — output of a single order row
 *   If the orders have more than one status, it outputs a status filter.
 * 
 * FILTER AJAX HANDLER:
 * 
 * as_ordersFiltersStatus_callback()
 *   File: includes/interface/cabinet/userOrders/ordersAjax.php
 *   Receives userID and statuses, returns the orders HTML table
 *   without a page reload.
 * 
 * JS MODULE:
 * 
 * assets/js/interface/modules/orders.js (plugin)
 *   Sends an AJAX request when a status is selected in the filter.
 * 
 * ============================================================
 * VARIABLES FROM THE ACCOUNT TEMPLATE
 * ============================================================
 * 
 * $user_ID — current user ID (get_current_user_id())
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$userdata = get_user_by( 'id', $user_ID );

// Argon Shop plugin function
// (includes/interface/cabinet/userOrders/ordersView.php)
if ( function_exists( 'as_view_user_orders' ) ) {
    as_view_user_orders( $user_ID );
}