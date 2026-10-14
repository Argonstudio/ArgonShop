<?php
/**
 * Cart and checkout — module loading
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * Modules (everything used to be in this file, ~1200 lines):
 * - cart-ajax.php             — cart AJAX: add, quantity, remove, clear, wholesale price
 * - checkout.php              — main checkout handler (submitCart) and order hooks
 * - checkout-fields.php       — form fields, required fields, email/phone validation
 * - checkout-files.php        — checking and temporary storage of order files
 * - checkout-registration.php — buyer registration during checkout
 * - checkout-emails.php       — email HTML and sending
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/cart-ajax.php';
require_once __DIR__ . '/checkout-fields.php';
require_once __DIR__ . '/checkout-files.php';
require_once __DIR__ . '/checkout-registration.php';
require_once __DIR__ . '/checkout-emails.php';
require_once __DIR__ . '/checkout.php';
