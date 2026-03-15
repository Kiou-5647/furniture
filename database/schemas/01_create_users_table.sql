-- Single source of truth for authentication and identity, using for customer, employee and vendor users
CREATE TABLE IF NOT EXISTS users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),

    type VARCHAR(20) NOT NULL CHECK (type IN ('employee', 'vendor', 'customer')) DEFAULT 'customer',

    name VARCHAR(255) NOT NULL,

    -- Authentication
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,

    -- Verification and status
    is_active BOOLEAN DEFAULT true,
    is_verified BOOLEAN DEFAULT false,
    email_verified_at TIMESTAMP,

    -- Audit
    last_login_ip INET,
    last_login_at TIMESTAMP,

    remember_token VARCHAR(100),

    two_factor_secret TEXT,
    two_factor_recovery_codes TEXT,
    two_factor_confirmed_at TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,

    -- Indexes
    CONSTRAINT users_email_uniq UNIQUE (email)
);
CREATE INDEX IF NOT EXISTS idx_users_type ON users(type);
CREATE INDEX IF NOT EXISTS idx_users_is_active ON users(is_active) WHERE is_active = true;
CREATE INDEX IF NOT EXISTS idx_users_deleted_at ON users(deleted_at) WHERE deleted_at IS NOT NULL;

