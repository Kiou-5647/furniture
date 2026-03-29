CREATE TABLE IF NOT EXISTS collections (
    id SERIAL PRIMARY KEY,
    display_name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT,
    image_path VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    is_featured BOOLEAN DEFAULT false,
    metadata JSONB DEFAULT '{}'::jsonb,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE UNIQUE INDEX unq_collections_slug_active ON collections (slug)
WHERE
    deleted_at IS NULL;

CREATE INDEX idx_collections_display_name_trgm ON collections USING GIN (display_name gin_trgm_ops);

CREATE INDEX idx_collections_active ON collections (is_active)
WHERE
    is_active = true
    AND deleted_at IS NULL;

CREATE INDEX idx_collections_featured ON collections (is_featured)
WHERE
    is_featured = true;

CREATE INDEX idx_collections_deleted ON collections (deleted_at)
WHERE
    deleted_at IS NOT NULL;
