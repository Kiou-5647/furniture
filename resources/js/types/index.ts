export * from './auth';
export * from './navigation';
export * from './ui';
export * from './lookup';
export * from './department';
export * from './employee';
export * from './order';
export * from './discount';

export type ProductType = 'noi-that' | 'trang-tri';

export type ProductStatus =
    | 'draft'
    | 'published'
    | 'archived';
export type Status = 'active' | 'inactive';
export const AssemblyDifficultyLabels: Record<AssemblyDifficulty, string> = {
    easy: 'Dễ',
    medium: 'Trung bình',
    hard: 'Khó'
}
export type AssemblyDifficulty = 'easy' | 'medium' | 'hard';
export type LocationType = 'warehouse' | 'retail' | 'vendor';

export const StatusLabels: Record<Status, string> = {
    active: 'Hoạt động',
    inactive: 'Không hoạt động',
}
