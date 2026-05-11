export * from './auth';
export * from './booking';
export * from './bundle';
export * from './category';
export * from './collection';
export * from './customer';
export * from './department';
export * from './designer';
export * from './discount';
export * from './employee';
export * from './invoice';
export * from './location';
export * from './lookup';
export * from './navigation';
export * from './order';
export * from './payment';
export * from './product';
export * from './refund';
export * from './shipment';
export * from './shipping-method';
export * from './stock-movement';
export * from './stock-transfer';
export * from './ui';
export * from './vendor';

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
export type LocationType = 'warehouse' | 'retail';

export const StatusLabels: Record<Status, string> = {
    active: 'Hoạt động',
    inactive: 'Không hoạt động',
}

export const Gateway: Record<string, string> = {
    cash: 'Tiền mặt',
    vnpay: 'VNPay',
}

export interface Pagination<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url: string | null;
    prev_page_url: string | null;
}


