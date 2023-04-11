<!DOCTYPE html>
<!--
To change this license header, choose License Headers in Project Properties.
To change this template file, choose Tools | Templates
and open the template in the editor.
-->
<html>
    <head>
        <meta charset="UTF-8">
        <title></title>
    </head>
    <body>
    <?php

        $tranid = date("YmdGis");
        $signaturecc=sha1('##'.strtoupper('aggregator_tes').'##'.strtoupper('ejeussad').'##'.$tranid.'##1000.00##'.'0'.'##');

        $post = array(
        "TRANSACTIONTYPE"               => '1',
        "RESPONSE_TYPE"                 => '2',
        "LANG"                          => '',
        "MERCHANTID"                    => 'aggregator_tes',
        "PAYMENT_METHOD"                => '1',
        "TXN_PASSWORD"                  => 'ejeussad',
        "MERCHANT_TRANID"               => $tranid,
        "CURRENCYCODE"                  => 'IDR',
        "AMOUNT"                        => '1000.00',
        "CUSTNAME"                      => 'merhcant test CC',
        "CUSTEMAIL"                     => 'testing@faspay.co.id',
        "DESCRIPTION"                   => 'transaski test',
        "RETURN_URL"                    => 'http://localhost/creditcard/merchant_return_page.php',
        "SIGNATURE"                     => $signaturecc,
        "BILLING_ADDRESS"               => 'Jl. pintu air raya',
        "BILLING_ADDRESS_CITY"          => 'Jakarta',
        "BILLING_ADDRESS_REGION"        => 'DKI Jakarta',
        "BILLING_ADDRESS_STATE"         => 'DKI Jakarta',
        "BILLING_ADDRESS_POSCODE"       => '10710',
        "BILLING_ADDRESS_COUNTRY_CODE"  => 'ID',
        "RECEIVER_NAME_FOR_SHIPPING"    => 'Faspay test',
        "SHIPPING_ADDRESS"              => 'Jl. pintu air raya',
        "SHIPPING_ADDRESS_CITY"         => 'Jakarta',
        "SHIPPING_ADDRESS_REGION"       => 'DKI Jakarta',
        "SHIPPING_ADDRESS_STATE"        => 'DKI Jakarta',
        "SHIPPING_ADDRESS_POSCODE"      => '10710',
        "SHIPPING_ADDRESS_COUNTRY_CODE" => 'ID',
        "SHIPPINGCOST"                  => '0.00',
        "PHONE_NO"                      => '0897867688989',
        "MPARAM1"                       => '',
        "MPARAM2"                       => '',
        "PYMT_IND"                      => '',
        "PYMT_CRITERIA"                 => '',
        "PYMT_TOKEN"                    => '',

        /* ==== customize input card page ===== */
        "style_merchant_name"         => 'black',
        "style_order_summary"         => 'black',
        "style_order_no"              => 'black',
        "style_order_desc"            => 'black',
        "style_amount"                => 'black',
        "style_background_left"       => '#fff',
        "style_button_cancel"         => 'grey',
        "style_font_cancel"           => 'white',
        /* ==== logo directly to your url source ==== */
        "style_image_url"           => 'http://url_merchant/image.png',
        );

        //Dev ke = https://fpgdev.faspay.co.id/payment
        $string = '<form method="post" name="form" action="https://fpg.faspay.co.id/payment">';
        if ($post != null) {
        foreach ($post as $name=>$value) {
        $string .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
            }
        }

        $string .= '</form>';
        $string .= '<script> document.form.submit();</script>';
        echo $string;
        exit;

        ?>
    </body>
</html>
