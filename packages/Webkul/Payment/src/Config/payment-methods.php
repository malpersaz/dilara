<?php

use Webkul\Payment\Payment\CashOnDelivery;
use Webkul\Payment\Payment\KuveytTurk;
use Webkul\Payment\Payment\MoneyTransfer;

return [
    'cashondelivery' => [
        'class' => CashOnDelivery::class,
        'code' => 'cashondelivery',
        'title' => 'Cash On Delivery',
        'description' => 'Cash On Delivery',
        'active' => true,
        'generate_invoice' => false,
        'sort' => 7,
    ],

    'moneytransfer' => [
        'class' => MoneyTransfer::class,
        'code' => 'moneytransfer',
        'title' => 'Money Transfer',
        'description' => 'Money Transfer',
        'active' => true,
        'generate_invoice' => false,
        'sort' => 8,
    ],

    'kuveytturk' => [
        'class' => KuveytTurk::class,
        'code' => 'kuveytturk',
        'title' => 'Kuveyt Türk Sanal POS',
        'description' => 'Kuveyt Türk Sanal POS Ödeme Yöntemi',
        'active' => false,
        'sandbox' => true,
        'merchant_id' => '',
        'customer_id' => '',
        'username' => '',
        'password' => '',
        'sort' => 3,
    ],
];
