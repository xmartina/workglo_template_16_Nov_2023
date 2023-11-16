INSERT INTO payment_types (type, value) SELECT * FROM (SELECT 'iyzico', '0') AS tmp WHERE NOT EXISTS ( SELECT * FROM payment_types WHERE type = 'iyzico' ) LIMIT 1;

UPDATE `system_configurations` SET `value` = '3.4.0' WHERE `system_configurations`.`type` = 'current_version';

COMMIT;