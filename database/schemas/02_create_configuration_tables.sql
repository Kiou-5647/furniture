-- Contains all settings for the system.
CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,
    namespace VARCHAR(50) NOT NULL, -- Logical grouping: 'HR', 'Order', 'Inventory', 'System' that will categorize settings into groups.
    key VARCHAR(100) NOT NULL, -- Unique setting identifier within category
    value JSONB, -- Setting value (string, number, JSON)
    description TEXT, -- Human-readable description
    is_system BOOLEAN DEFAULT false, -- System-managed (true) vs user-editable (false)
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (namespace, key) -- Prevent duplicate keys per category
);

CREATE INDEX IF NOT EXISTS idx_settings_key ON settings (key);

CREATE INDEX IF NOT EXISTS idx_settings_is_system ON settings (is_system)
WHERE
    is_system = true;

-- Human resources positions,
CREATE TABLE IF NOT EXISTS positions (
    id SERIAL PRIMARY KEY,
    name VARCHAR(255) UNIQUE NOT NULL, -- Display name: "Store Manager"
    code VARCHAR(50) UNIQUE NOT NULL, -- Code: "STORE_MANAGER"
    description TEXT, -- Role responsibilities
    is_system BOOLEAN DEFAULT false, -- System positions that define a position that manage by the system, like super_admin, admin,...
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lookups (
    id SERIAL PRIMARY KEY,
    namespace VARCHAR(64),
    slug VARCHAR(64) NOT NULL,
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    metadata JSONB DEFAULT '{}'::jsonb, -- to store metadata of the lookup like images, validation rules, configuaration, hex color code,....
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX unq_lookup_namespace_slug_active ON lookups (namespace, slug)
WHERE
    deleted_at IS NULL;

CREATE INDEX idx_lookups_namespace ON lookups (namespace);

CREATE INDEX idx_lookups_display_name_trgm ON lookups USING GIN (display_name gin_trgm_ops);

CREATE INDEX idx_lookups_deleted ON lookups (deleted_at)
WHERE
    deleted_at IS NOT NULL;

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
