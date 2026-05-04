USE gogetgro;

ALTER TABLE products
    ADD COLUMN IF NOT EXISTS stock_quantity INT NOT NULL DEFAULT 0;

UPDATE products
SET stock_quantity = CASE
    WHEN MOD(id, 11) = 0 THEN 3
    WHEN MOD(id, 7) = 0 THEN 5
    WHEN MOD(id, 5) = 0 THEN 8
    WHEN MOD(id, 3) = 0 THEN 14
    ELSE 22
END;
