CREATE TABLE IF NOT EXISTS categories (
    id SERIAL PRIMARY KEY,
    group_id INTEGER REFERENCES lookups (id) ON DELETE RESTRICT,
    product_type VARCHAR(20),
    display_name VARCHAR(255) NOT NULL,
    slug VARCHAR(64) NOT NULL,
    description TEXT,
    image_path VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    metadata JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX unq_categories_slug_active ON categories (slug)
WHERE
    deleted_at IS NULL;

CREATE INDEX idx_categories_group ON categories (group_id);

CREATE INDEX idx_categories_product_type ON categories (product_type);

CREATE INDEX idx_categories_display_name_trgm ON categories USING GIN (display_name gin_trgm_ops);

CREATE INDEX idx_categories_active ON categories (is_active)
WHERE
    is_active = true
    AND deleted_at IS NULL;

CREATE INDEX idx_categories_deleted ON categories (deleted_at)
WHERE
    deleted_at IS NOT NULL;

CREATE TABLE IF NOT EXISTS category_room (
    category_id INTEGER REFERENCES categories (id) ON DELETE CASCADE,
    room_id INTEGER REFERENCES lookups (id) ON DELETE CASCADE,
    PRIMARY KEY (category_id, room_id)
);
