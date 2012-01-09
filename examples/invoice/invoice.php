<?php

require '../../src/picoMapper.php';


/**
 * @table invoices
 */
class Invoice extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type decimal
     * @rule required
     * @rule >= 0
     */
    public $amount;

    /**
     * @type datetime
     */
    public $created_at;

    /**
     * @hasMany InvoiceLine
     */
    public $lines;

    /**
     * @belongsTo Customer
     */
    public $customer;

    /**
     * @foreignKey Customer
     */
    public $customer_id;
}

/**
 * @table invoices_lines
 */
class InvoiceLine extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @foreignKey Invoice
     */
    public $invoice_id;

    /**
     * @type decimal
     * @rule required
     * @rule >= 0
     */
    public $amount;

    /**
     * @type string
     * @rule required
     * @rule maxlength 250
     */
    public $name;

    /**
     * @type decimal
     * @rule required
     * @rule >= 0
     */
    public $quantity;

    /**
     * @type decimal
     * @rule required
     * @rule >= 0
     */
    public $price;
}


/**
 * @table customers
 */
class Customer extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;

    /**
     * @type string
     */
    public $name;

    /**
     * @hasOne CustomerAddress
     */
    public $addresses;
}


/**
 * @table customers_addresses
 */
class CustomerAddress extends \picoMapper\Model {

    /**
     * @type primaryKey
     */
    public $id;


    /**
     * @foreignKey Customer
     */
    public $customer_id;


    /**
     * @type text
     */
    public $name;

    /**
     * @type string
     */
    public $type;
}


\picoMapper\Database::config('sqlite::memory:');

$input = array(
    'customer' => array(
        'name' => 'Polo',
        'addresses' => array(
            'name' => 'rue temporaire',
            'type' => 'facturation'
        )
    ),
    'amount' => 45,
    'lines' => array(
        array(
            'name' => 'P2',
            'quantity' => 3,
            'price' => 4.67,
            'amount' => 3*4.67
        )
));

try {

    $invoice = new Invoice($input);
    $invoice->saveAll();

    // 201
    var_dump($invoice->id);
}
catch (\picoMapper\DatabaseException $e) {

    // 500
}
catch (\picoMapper\ValidatorException $e) {

    // 400
}

$i = Invoice::findById($invoice->id);
var_dump($i->customer->addresses->name);
var_dump($i->customer->name);
var_dump($i->lines[0]->name);

