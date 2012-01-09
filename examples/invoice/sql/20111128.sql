PRAGMA foreign_keys = ON;

CREATE TABLE customers (
    id INTEGER PRIMARY KEY,
    name TEXT
);

CREATE TABLE customers_addresses (
    id INTEGER PRIMARY KEY,
    name TEXT,
    type INTEGER,
    customer_id INTEGER
    --FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE invoices (
    id INTEGER PRIMARY KEY,
    amount NUMERIC,
    customer_id INTEGER
    --FOREIGN KEY(customer_id) REFERENCES customers(id) ON DELETE CASCADE
);

CREATE TABLE invoices_lines (
    id INTEGER PRIMARY KEY,
    name TEXT,
    price NUMERIC,
    quantity NUMERIC,
    amount NUMERIC,
    invoice_id INTEGER,
    FOREIGN KEY(invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
);


