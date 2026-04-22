export * from './auth';
export * from './navigation';
export * from './ui';
export * from './lookup';
export * from './department';
export * from './employee';
export * from './order';

export type AttributeType =
    | 'text'
    | 'number'
    | 'boolean'
    | 'color'
    | 'dimensions'
    | 'weight';
export type DimensionUnit = 'mm' | 'cm' | 'm' | 'inch' | 'ft';
export type WeightUnit = 'kg' | 'lb';
export type ProductType = 'noi-that' | 'phu-kien' | 'trang-tri' | 'thap-sang';

export type ProductStatus =
    | 'draft'
    | 'pending_review'
    | 'published'
    | 'hidden'
    | 'archived';
export type VariantStatus = 'active' | 'inactive';
export type AssemblyDifficulty = 'easy' | 'medium' | 'hard';
export type LocationType = 'warehouse' | 'retail' | 'vendor';

export const AssemblyDifficultyLabels: Record<AssemblyDifficulty, string> = {
    easy: 'Dễ',
    medium: 'Trung bình',
    hard: 'Khó'
}
