CREATE TABLE admin_accounts (
    id INT NOT NULL,
    username VARCHAR(60) NOT NULL,
    display_name VARCHAR(120) NOT NULL,
    role VARCHAR(20) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT admin_accounts_pk PRIMARY KEY (id),
    CONSTRAINT admin_accounts_username_uk UNIQUE (username)
);

CREATE TABLE categories (
    slug VARCHAR(80) NOT NULL,
    name VARCHAR(120) NOT NULL,
    icon_class VARCHAR(80) NOT NULL,
    home_featured SMALLINT DEFAULT 0 NOT NULL,
    home_sort INT DEFAULT 0 NOT NULL,
    CONSTRAINT categories_pk PRIMARY KEY (slug)
);

CREATE TABLE products (
    id INT NOT NULL,
    category_slug VARCHAR(80) NOT NULL,
    name VARCHAR(180) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    stock_quantity INT DEFAULT 0 NOT NULL,
    CONSTRAINT products_pk PRIMARY KEY (id)
);

CREATE TABLE users (
    id INT NOT NULL,
    full_name VARCHAR(120) NOT NULL,
    username VARCHAR(60) NOT NULL,
    email VARCHAR(150) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT users_pk PRIMARY KEY (id),
    CONSTRAINT users_username_uk UNIQUE (username),
    CONSTRAINT users_email_uk UNIQUE (email)
);

CREATE TABLE cart_items (
    id INT NOT NULL,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT DEFAULT 1 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT cart_items_pk PRIMARY KEY (id),
    CONSTRAINT cart_items_uk UNIQUE (user_id, product_id)
);

CREATE TABLE user_addresses (
    id INT NOT NULL,
    user_id INT NOT NULL,
    label VARCHAR(60) DEFAULT 'Home' NOT NULL,
    address_line VARCHAR(300) NOT NULL,
    is_default SMALLINT DEFAULT 0 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT user_addresses_pk PRIMARY KEY (id)
);

CREATE TABLE user_payment_gateways (
    id INT NOT NULL,
    user_id INT NOT NULL,
    gateway_key VARCHAR(40) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT user_payment_gateways_pk PRIMARY KEY (id),
    CONSTRAINT user_payment_gateways_uk UNIQUE (user_id, gateway_key)
);

CREATE TABLE orders (
    id INT NOT NULL,
    user_id INT NOT NULL,
    order_number VARCHAR(30) NOT NULL,
    status VARCHAR(30) DEFAULT 'To Pay' NOT NULL,
    fulfillment_type VARCHAR(40) DEFAULT 'Regular Delivery' NOT NULL,
    delivery_type VARCHAR(20) DEFAULT 'regular' NOT NULL,
    delivery_address_id INT,
    subtotal DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    delivery_fee DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    total_amount DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT orders_pk PRIMARY KEY (id),
    CONSTRAINT orders_order_number_uk UNIQUE (order_number)
);

CREATE TABLE order_items (
    id INT NOT NULL,
    order_id INT NOT NULL,
    product_id INT,
    product_name VARCHAR(200) NOT NULL,
    quantity INT DEFAULT 1 NOT NULL,
    unit_price DECIMAL(10,2) DEFAULT 0.00 NOT NULL,
    CONSTRAINT order_items_pk PRIMARY KEY (id)
);

CREATE TABLE subscribers (
    id INT NOT NULL,
    email VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    CONSTRAINT subscribers_pk PRIMARY KEY (id),
    CONSTRAINT subscribers_email_uk UNIQUE (email)
);

-- Foreign Keys

ALTER TABLE products
    ADD CONSTRAINT fk_products_category FOREIGN KEY (category_slug) REFERENCES categories (slug);

ALTER TABLE cart_items
    ADD CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products (id);

ALTER TABLE cart_items
    ADD CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE user_addresses
    ADD CONSTRAINT fk_user_addresses_user FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE user_payment_gateways
    ADD CONSTRAINT fk_user_payment_gateways_user FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE orders
    ADD CONSTRAINT fk_orders_user FOREIGN KEY (user_id) REFERENCES users (id);

ALTER TABLE orders
    ADD CONSTRAINT fk_orders_delivery_address FOREIGN KEY (delivery_address_id) REFERENCES user_addresses (id);

ALTER TABLE order_items
    ADD CONSTRAINT fk_order_items_order FOREIGN KEY (order_id) REFERENCES orders (id);

ALTER TABLE order_items
    ADD CONSTRAINT fk_order_items_product FOREIGN KEY (product_id) REFERENCES products (id);
