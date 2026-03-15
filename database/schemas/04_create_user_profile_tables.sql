-- ========================= EMPLOYEES ===============================
CREATE TABLE IF NOT EXISTS departments (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    manager_id UUID,                         --deferring FK

    name VARCHAR(100) UNIQUE NOT NULL,       -- Department name: "Sales Department"
    code VARCHAR(50) UNIQUE NOT NULL,        -- Code: "SALES"
    description TEXT,                        -- Purpose and responsibilities

    is_active BOOLEAN DEFAULT true,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_departments_deleted_at ON departments(deleted_at) WHERE deleted_at IS NOT NULL;


CREATE TABLE IF NOT EXISTS employees (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    department_id UUID REFERENCES departments(id) ON DELETE SET NULL,

    full_name VARCHAR(255) NOT NULL,           -- Legal full name
    phone VARCHAR(20),                         -- Contact number
    avatar_path VARCHAR(500),

    hire_date DATE,                            -- Start date
    termination_date DATE,                     -- End date (if applicable)

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_employees_department_id ON employees(department_id);
CREATE INDEX IF NOT EXISTS idx_employees_user_id ON employees(user_id);
CREATE INDEX IF NOT EXISTS idx_employees_deleted_at ON employees(deleted_at) WHERE deleted_at IS NOT NULL;

-- Add FK for departments table
ALTER TABLE departments
    ADD CONSTRAINT fk_dept_manager
    FOREIGN KEY (manager_id)
    REFERENCES employees(id)
    DEFERRABLE INITIALLY DEFERRED;

-- Add missing index
CREATE INDEX IF NOT EXISTS idx_departments_manager_id ON departments(manager_id);


-- ========================= CUSTOMERS ===============================
CREATE TABLE IF NOT EXISTS customers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,

    full_name VARCHAR(255),
    phone VARCHAR(20),
    avatar_path VARCHAR(500),

    total_spent DECIMAL(15,2) DEFAULT 0.00 CHECK (total_spent >= 0),
    loyalty_points INT DEFAULT 0 CHECK (loyalty_points >= 0),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_customers_user_id ON customers(user_id);
CREATE INDEX IF NOT EXISTS idx_customers_deleted_at ON customers(deleted_at) WHERE deleted_at IS NOT NULL;


CREATE TABLE IF NOT EXISTS customer_addresses (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    customer_id UUID REFERENCES customers(id) ON DELETE CASCADE,

    type VARCHAR(20) DEFAULT 'shipping' CHECK (type IN ('shipping', 'billing', 'both')),
    delivery_instructions TEXT,
    province_code VARCHAR(2) REFERENCES provinces(province_code) ON DELETE SET NULL,
    ward_code VARCHAR(5) REFERENCES wards(ward_code) ON DELETE SET NULL,

    -- quick lookup data
    province_name VARCHAR(255),
    ward_name VARCHAR(255),

    -- address_line 1 and 2, 1 contains the number address of the building, house,... 2 contains the room, building, group number (if exists).
    address_data JSONB NOT NULL DEFAULT '{}'::jsonb,

    is_default BOOLEAN DEFAULT false,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_addresses_customer_id ON customer_addresses(customer_id);
CREATE INDEX IF NOT EXISTS idx_addresses_type ON customer_addresses(type);
CREATE INDEX IF NOT EXISTS idx_addresses_province_code ON customer_addresses(province_code);
CREATE INDEX IF NOT EXISTS idx_addresses_ward_code ON customer_addresses(ward_code);
CREATE INDEX IF NOT EXISTS idx_customer_addresses_is_default ON customer_addresses(is_default) WHERE is_default = true;
CREATE UNIQUE INDEX IF NOT EXISTS uq_customer_default_address ON customer_addresses(customer_id)
                                                              WHERE is_default = true;


-- ========================= VENDORS ===============================
CREATE TABLE IF NOT EXISTS vendors (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    verified_by UUID REFERENCES employees(id),

    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) UNIQUE,
    contact_name VARCHAR(255),
    email VARCHAR(255),
    phone VARCHAR(20),
    website VARCHAR(255),
    webhook_url TEXT,
    address TEXT,
    notes TEXT,
    bank_name VARCHAR(255),
    bank_account_number VARCHAR(100),
    bank_account_holder VARCHAR(255),

    api_credentials JSONB,
    shipping_regions JSONB DEFAULT '[]'::jsonb,
    tags JSONB DEFAULT '[]'::jsonb,

    payment_terms_days INT DEFAULT 30,
    lead_time_days INT DEFAULT 7,
    minimum_order_amount DECIMAL(15,2) DEFAULT 0,
    rating DECIMAL(3,2) CHECK (rating >= 0 AND rating <= 5),
    total_orders INT DEFAULT 0,
    total_revenue DECIMAL(15,2) DEFAULT 0.00,

    is_active BOOLEAN DEFAULT true,
    is_preferred BOOLEAN DEFAULT false,

    verified_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_vendors_active ON vendors(is_active) WHERE is_active = true AND deleted_at IS NULL;
CREATE INDEX IF NOT EXISTS idx_vendors_deleted_at ON vendors(deleted_at) WHERE deleted_at IS NOT NULL;
CREATE INDEX IF NOT EXISTS idx_vendors_verified_at ON vendors(verified_at) WHERE verified_at IS NOT NULL;


CREATE TABLE IF NOT EXISTS vendor_users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    user_id UUID NOT NULL UNIQUE REFERENCES users(id) ON DELETE CASCADE,
    vendor_id UUID REFERENCES vendors(id) ON DELETE CASCADE,

    full_name VARCHAR(255),
    phone VARCHAR(20),
    avatar_path VARCHAR(500),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,

    CONSTRAINT uq_vendor_user UNIQUE (user_id, vendor_id)
);
CREATE INDEX IF NOT EXISTS idx_vendor_users_vendor_id ON vendor_users(vendor_id);
CREATE INDEX IF NOT EXISTS idx_vendor_users_user_id ON vendor_users(user_id);
CREATE INDEX IF NOT EXISTS idx_vendor_users_active ON vendor_users(deleted_at) WHERE deleted_at IS NULL;
