CREATE TABLE service_categories (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

tenant_id BIGINT UNSIGNED NOT NULL,
duka_id BIGINT UNSIGNED NOT NULL,

name VARCHAR(255) NOT NULL,
description TEXT NULL,

created_at TIMESTAMP NULL DEFAULT NULL,
updated_at TIMESTAMP NULL DEFAULT NULL,

CONSTRAINT fk_service_categories_tenant
FOREIGN KEY (tenant_id)
REFERENCES tenants(id)
ON DELETE CASCADE,

CONSTRAINT fk_service_categories_duka
FOREIGN KEY (duka_id)
REFERENCES dukas(id)
ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;



CREATE TABLE services (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

tenant_id BIGINT UNSIGNED NOT NULL,
duka_id BIGINT UNSIGNED NOT NULL,
category_id BIGINT UNSIGNED NOT NULL,

name VARCHAR(255) NOT NULL,
description TEXT NULL,

price DECIMAL(15,2) NOT NULL,
billing_type VARCHAR(50) NOT NULL DEFAULT 'fixed',
is_active TINYINT(1) NOT NULL DEFAULT 1,

created_at TIMESTAMP NULL DEFAULT NULL,
updated_at TIMESTAMP NULL DEFAULT NULL,

CONSTRAINT fk_services_tenant
FOREIGN KEY (tenant_id)
REFERENCES tenants(id)
ON DELETE CASCADE,

CONSTRAINT fk_services_duka
FOREIGN KEY (duka_id)
REFERENCES dukas(id)
ON DELETE CASCADE,

CONSTRAINT fk_services_category
FOREIGN KEY (category_id)
REFERENCES service_categories(id)
ON DELETE CASCADE,

INDEX idx_services_tenant (tenant_id),
INDEX idx_services_duka (duka_id),
INDEX idx_services_category (category_id),
INDEX idx_services_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


ALTER TABLE dukas
ADD COLUMN business_type ENUM('product', 'service', 'both')
NOT NULL
DEFAULT 'product'
AFTER status;


CREATE TABLE service_orders (
id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
order_number VARCHAR(100) UNIQUE NOT NULL,
tenant_id BIGINT UNSIGNED NOT NULL,
duka_id BIGINT UNSIGNED NOT NULL,
customer_id BIGINT UNSIGNED NOT NULL,
service_id BIGINT UNSIGNED NOT NULL,
service_type VARCHAR(50) NOT NULL, -- Snapshot of billing_type at time of order
amount_paid DECIMAL(15, 2) NOT NULL,
status VARCHAR(50) DEFAULT 'pending', -- pending, completed, cancelled
scheduled_at TIMESTAMP NULL,
completed_at TIMESTAMP NULL,
notes TEXT NULL,
created_at TIMESTAMP NULL,
updated_at TIMESTAMP NULL,
FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
FOREIGN KEY (duka_id) REFERENCES dukas(id) ON DELETE CASCADE,
FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE,
FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE
) ENGINE=InnoDB;