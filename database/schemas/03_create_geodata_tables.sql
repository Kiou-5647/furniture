CREATE TABLE IF NOT EXISTS provinces (
    province_code VARCHAR(2) PRIMARY KEY,    -- 2-digit GSO code: "01", "79"
    name VARCHAR(255) NOT NULL,              -- Full name: "Thành phố Hà Nội"
    short_name VARCHAR(255),                 -- Short name: "Hà Nội"
    code VARCHAR(10),                        -- Short code: "HNI"
    place_type VARCHAR(50),                  -- Type: "Thành phố Trung Ương", "Tỉnh"

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_provinces_name ON provinces USING GIN (name gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_provinces_place_type ON provinces(place_type);


CREATE TABLE IF NOT EXISTS wards (
    ward_code VARCHAR(5) PRIMARY KEY,        -- 5-digit GSO code: "00004", "00008"
    province_code VARCHAR(2) NOT NULL REFERENCES provinces(province_code) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,              -- Full name: "Phường Ba Đình"

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_wards_name ON wards USING GIN (name gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_wards_province_code ON wards(province_code);
