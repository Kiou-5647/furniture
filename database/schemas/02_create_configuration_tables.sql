-- Contains all settings for the system.
CREATE TABLE IF NOT EXISTS settings (
    id SERIAL PRIMARY KEY,

    namespace VARCHAR(50) NOT NULL,             -- Logical grouping: 'HR', 'Order', 'Inventory', 'System' that will categorize settings into groups.
    key VARCHAR(100) NOT NULL,                  -- Unique setting identifier within category
    value JSONB,                                -- Setting value (string, number, JSON)
    description TEXT,                           -- Human-readable description

    is_system BOOLEAN DEFAULT false,            -- System-managed (true) vs user-editable (false)

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE(namespace, key)                       -- Prevent duplicate keys per category
);
CREATE INDEX IF NOT EXISTS idx_settings_key ON settings(key);
CREATE INDEX IF NOT EXISTS idx_settings_is_system ON settings(is_system) WHERE is_system = true;

-- Human resources positions,
CREATE TABLE IF NOT EXISTS positions (
    id SERIAL PRIMARY KEY,

    name VARCHAR(255) UNIQUE NOT NULL,          -- Display name: "Store Manager"
    code VARCHAR(50) UNIQUE NOT NULL,           -- Code: "STORE_MANAGER"
    description TEXT,                           -- Role responsibilities

    is_system BOOLEAN DEFAULT false,            -- System positions that define a position that manage by the system, like super_admin, admin,...

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Table to define datatypes and pre-defines lookup values for system. The ideas is this table is used as a lookup dictionary for datatypes and values, and will not be referenced by other table, for examples:
-- == A namespace datatype contain pre-defined datatypes like text, number, boolean,... or more advanced and customized types like color, weight, dimensions,...
-- === text, number, boolean or other basic datatypes is easy, the proble is custom datatypes like color, weight, dimensions,... For examples, dimensions datatype, we should define a validation rule inside the metadata column, like: [input] showing the input fields as height, width, depth,... that will show up when this datatype is chosen, as well as some other validation fields that will appear for the value that references to this datatypes like min, max, required,...
-- == Not only datatypes, lookups table also contain predefined values, like in the namespace we named 'Colors' we defined the [value] column to store the color name, and [metadata] column to contain value like the image, hex code,... Or a namespace like rooms and styles to contain the corresponding values and their metadata,....
-- = This way we have a table for quick values lookup, pre-defined values and rules for stricter validation while keeping versatility.
CREATE TABLE IF NOT EXISTS lookups (
    id SERIAL PRIMARY KEY,
    namespace VARCHAR(64) CHECK (namespace IN ('colors', 'rooms', 'styles', 'features', 'datatypes')),                                     -- Container name to categorize lookups into groups
    key VARCHAR(64) UNIQUE,                                             -- key (slug)
    display_name VARCHAR(255) NOT NULL,

    metadata JSONB DEFAULT '{}'::jsonb,                                 -- to store metadata of the lookup like images, validation rules, configuaration, hex color code,....

    is_system BOOLEAN DEFAULT false,                                    -- define that a lookups belong to system and cannot be deleted, but can be somewhat modified.

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (namespace, key)
);
CREATE INDEX IF NOT EXISTS idx_lookups_namespace ON lookups(namespace);
CREATE INDEX IF NOT EXISTS idx_lookups_display_name_trgm ON lookups USING GIN(display_name gin_trgm_ops);
CREATE INDEX IF NOT EXISTS idx_lookups_system ON lookups(is_system) WHERE is_system = true;
CREATE INDEX IF NOT EXISTS idx_lookups_custom ON lookups(is_system) WHERE is_system = false;


-- Metafields table contains pre-defined key dictionary for specifications that varied between product types. like a wooden chair may have 'arm type' but not 'foam density', while a sofa have both, and a table have none of the twos.
-- The idea of this table is more complex, but as the name show, this is the table for pre-defined 'key' for [metafields] columns in products and product_variants tables.
-- Not like the lookups table, which defines both values and datatypes, metafields table exists to define the key for the values that will appear in the [metafields] JSONB column.
-- For example, the [dimensions] column of product_variants table will contain basic information of dimensions of the variant, which means the width, height, and depth and only those for quick lookup, for other specifications related to 'dimensions' like package dimensions, leg height, arm width,... that will varied between product types, we define the key inside the metafields table, then use it as a dictionary for inputing values to [metafields] column.
-- For better understanding, imagine we create a metafields record with namespace dimensions, which mean it will appear under the dimensions tab of the product or variant specifications. and give it the key 'leg-height' and set it to the owner_table = product_variants. Then when inputing metadata for a variant, a Leg Height option will appear for the user to choose and input leg height value or not.
-- The [type] column is the combination of namespace:key of the lookups record, for example we choose dimensions datatype in the lookups, the [type] will be 'datatype:dimensions'. metadata contain a copy of input fields or similar metadata of the reference, in this case the dimensions datatype lookup, like the input fields, the validation fields,... then the user configure the [validation_config] for the metafields. For example, the dimensions have validation rules in lookup table contain min and max, then the validation_config in the metafields column fill set the specific values for those validations. Then when user input metafield in the product creating page, the input values must satisfy the validation_config.
-- If the [type] is not in the lookups tables but another table, for example the materials table. then the type is the name of the table, system look for record on that table to apply for hinting the metafield.
-- Ultimately, the metafields is a dictionary for the keys that will appear on certain product types and tables.
CREATE TABLE IF NOT EXISTS metafields (
    id SERIAL PRIMARY KEY,

    owner_table VARCHAR(255) DEFAULT 'any',
    namespace VARCHAR(255) DEFAULT 'system',    -- Container name for categorization
    key VARCHAR(64) NOT NULL,                   -- key for attributes
    display_name VARCHAR(255) NOT NULL,
    description TEXT,
    type VARCHAR(64) NOT NULL,                  -- contain lookup namespace for references data or datatypes

    validation_config JSONB,
    metadata JSONB,

    is_filterable BOOLEAN DEFAULT false,
    is_storefront BOOLEAN DEFAULT false,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (namespace, key)
);
CREATE INDEX IF NOT EXISTS idx_metafields_is_filterable ON metafields(is_filterable) WHERE is_filterable = true;
CREATE INDEX IF NOT EXISTS idx_metafields_owner_table ON metafields(owner_table);

CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



