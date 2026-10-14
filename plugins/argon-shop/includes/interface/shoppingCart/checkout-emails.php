<?php
/**
 * Checkout — email texts and sending
 *
 * @package Argon_Shop
 * @author  Ivan Voitkov
 *
 * HOOKS:
 * - filter argon_shop_email ( array $mail, string $type, array $order )
 *   $mail = [ to, subject, message, headers, attachments ], $type = admin|customer.
 *   Return false to skip sending the email.
 */

// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


/**
 * FUNCTION: Build the HTML text of the email
 * 
 * Returns plain HTML. The MIME wrapper, header encoding and
 * attachments are handled by wp_mail() + PHPMailer.
 * 
 * @param string $type                 — email type: messageForAdmin or messageForUser
 * @param int    $actuallyCounterOrder — order number
 * @param array  $productsCart         — products in the order
 * @param int    $productAmountCart    — number of products
 * @param float  $cartPrice            — total
 * @param string $shipping             — delivery method
 * @param string $payment              — payment method
 * @param array  $fieldsCart           — buyer data
 * @param string $registrationMessage  — registration message (login/password)
 * 
 * @return string Plain HTML text of the email
 */
function as_emailTextsGenerator(
    string $type = 'messageForAdmin',
    int $actuallyCounterOrder = 0,
    array $productsCart = array(),
    int $productAmountCart = 0,
    float $cartPrice = 0,
    string $shipping = '',
    string $payment = '',
    array $fieldsCart = array(),
    $registrationMessage = null
) {
    
    ob_start();
    
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title><?php esc_html_e( 'Order', 'argon-shop' ); ?></title>
    </head>
    <body style="font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.5;">
    
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <h2 style="color: #808080; font-size: 18px; margin: 0 0 20px 0;"><?php esc_html_e( 'Order details:', 'argon-shop' ); ?></h2>
    
    <?php if ( $type === 'messageForAdmin' ) { ?>
        <p><b><?php esc_html_e( 'Order number:', 'argon-shop' ); ?></b> <?php echo $actuallyCounterOrder; ?></p>
    <?php } elseif ( $type === 'messageForUser' ) { ?>
        <p><b><?php esc_html_e( 'Your order number:', 'argon-shop' ); ?></b> <?php echo $actuallyCounterOrder; ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $productAmountCart ) ) { ?>
        <p><b><?php esc_html_e( 'Number of items:', 'argon-shop' ); ?></b> <?php echo $productAmountCart; ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $cartPrice ) ) { ?>
        <p><b><?php esc_html_e( 'Total:', 'argon-shop' ); ?></b> <?php echo as_price_html( esc_html( $cartPrice ) ); ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $shipping ) ) { ?>
        <p><b><?php esc_html_e( 'Shipping method:', 'argon-shop' ); ?></b> <?php echo esc_html( $shipping ); ?></p>
    <?php } ?>
    
    <?php if ( ! empty( $payment ) ) { ?>
        <p><b><?php esc_html_e( 'Payment method:', 'argon-shop' ); ?></b> <?php echo esc_html( $payment ); ?></p>
    <?php } ?>
    
    <h3 style="color: #808080; font-size: 16px; margin: 30px 0 15px 0;"><?php esc_html_e( 'List of products:', 'argon-shop' ); ?></h3>
    
    <table style="width: 100%; border-collapse: collapse;">
        <?php foreach ( $productsCart as $key => $value ) { 
            
            $key = absint( $key );
            
            $product_link  = isset( $value['link'] ) ? esc_url( $value['link'] ) : '';
            $product_name  = isset( $value['name'] ) ? esc_html( $value['name'] ) : '';
            $amount        = isset( $value['amountProduct'] ) ? floatval( $value['amountProduct'] ) : 0;
            $price         = isset( $value['price'] ) ? floatval( $value['price'] ) : 0;
            
            ?>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px 0;">
                    <a href="<?php echo $product_link; ?>" style="color: #0066cc;"><?php echo $product_name; ?></a>
                    
                    <?php if ( $type === 'messageForAdmin' ) { 
                        $productArticle = get_post_meta( $key, '_article', true );
                        
                        if ( ! empty( $productArticle ) ) { ?>
                            <br><small style="color: #888;"><?php esc_html_e( 'SKU:', 'argon-shop' ); ?> <?php echo esc_html( $productArticle ); ?></small>
                        <?php } ?>
                    <?php } ?>
                </td>
                <td style="padding: 10px 0; text-align: right;">
                    <?php echo $amount; ?> <?php esc_html_e( 'pcs ×', 'argon-shop' ); ?> <?php echo as_price_html( $price ); ?>
                </td>
                <td style="padding: 10px 0; text-align: right; white-space: nowrap;">
                    <b><?php echo as_price_html( $price * $amount ); ?></b>
                </td>
            </tr>
        <?php } ?>
    </table>
    
    <?php if ( ! empty( $fieldsCart ) && is_array( $fieldsCart ) ) { ?>
    
        <h3 style="color: #808080; font-size: 16px; margin: 30px 0 15px 0;">
            <?php echo ( $type === 'messageForAdmin' ) ? __( 'Customer information:', 'argon-shop' ) : __( 'The information you provided:', 'argon-shop' ); ?>
        </h3>
        
        <table style="width: 100%; border-collapse: collapse;">
            <?php foreach ( $fieldsCart as $key => $value ) { 
                
                if ( empty( $value['value'] ) ) {
                    continue;
                }
                
                $field_name  = isset( $value['name'] ) ? esc_html( $value['name'] ) : '';
                $field_value = esc_html( $value['value'] );
                ?>
                <tr>
                    <td style="padding: 5px 0; vertical-align: top;"><b><?php echo $field_name; ?>:</b></td>
                    <td style="padding: 5px 0;"><?php echo $field_value; ?></td>
                </tr>
            <?php } ?>
        </table>
        
    <?php } ?>
    
    <?php if ( ! empty( $registrationMessage ) && $type === 'messageForUser' ) { ?>
    
        <div style="background: #f5f5f5; padding: 15px; margin: 20px 0; border-left: 4px solid #0066cc;">
            <?php echo $registrationMessage; ?>
        </div>
        
    <?php } ?>
    
    <?php if ( $type === 'messageForUser' ) { ?>
    
        <p style="color: #666; margin-top: 30px;">
            <?php esc_html_e( 'Thank you for your order! Our manager will contact you shortly.', 'argon-shop' ); ?>
        </p>
        <p style="color: #666;">
            <?php esc_html_e( 'To change your order details, contact the store manager by the phone number listed on the site.', 'argon-shop' ); ?>
        </p>
    
    <?php } ?>
    
    </div>
    </body>
    </html>
    <?php
    
    return ob_get_clean();
}

/**
 * FUNCTION: Send the order emails (to the administrator and the buyer)
 *
 * @param array $order       — order context (see submitCart_shoppingCart_callback)
 * @param array $attachments — paths to the order files
 *
 * @return array [ 'admin' => bool, 'customer' => bool ]
 *
 * @since 1.1.0
 */
function as_checkout_send_emails( array $order, array $attachments = array() ) {

    $site_domain  = wp_parse_url( home_url(), PHP_URL_HOST );
    $site_domain  = str_replace( 'www.', '', (string) $site_domain );
    $from_noreply = 'noreply@' . $site_domain;
    $admin_email  = get_option( 'admin_email' );
    $email        = $order['email'];

    $sent = array( 'admin' => false, 'customer' => false );

    $emails = array(
        'admin' => array(
            'to'          => $admin_email,
            'subject'     => __( 'New order received', 'argon-shop' ),
            'message'     => as_emailTextsGenerator( 'messageForAdmin', $order['number'], $order['products'], $order['amount'], $order['total'], $order['shipping_label'], $order['payment_label'], $order['fields'], $order['registration_admin'] ),
            'headers'     => array( 'Content-Type: text/html; charset=UTF-8', 'From: ArgonShop <' . $from_noreply . '>' ),
            'attachments' => $attachments,
        ),
    );

    if ( ! empty( $email ) && is_email( $email ) ) {

        $emails['admin']['headers'][] = 'Reply-To: ' . $email;

        $emails['customer'] = array(
            'to'          => $email,
            'subject'     => __( 'Your order has been placed', 'argon-shop' ),
            'message'     => as_emailTextsGenerator( 'messageForUser', $order['number'], $order['products'], $order['amount'], $order['total'], $order['shipping_label'], $order['payment_label'], $order['fields'], $order['registration_customer'] ),
            'headers'     => array( 'Content-Type: text/html; charset=UTF-8', 'From: ArgonShop <' . $from_noreply . '>', 'Reply-To: ' . $admin_email ),
            'attachments' => $attachments,
        );
    }

    foreach ( $emails as $type => $mail ) {

        $mail = apply_filters( 'argon_shop_email', $mail, $type, $order );

        if ( ! is_array( $mail ) || empty( $mail['to'] ) ) {
            continue;
        }

        $sent[ $type ] = wp_mail( $mail['to'], $mail['subject'], $mail['message'], $mail['headers'], $mail['attachments'] );
    }

    return $sent;
}
