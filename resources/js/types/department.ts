export interface Department {
    id: string;
    name: string;
    code: string;
    description: string | null;
    manager: {
        id: string;
        full_name: string;
    } | null;
    is_active: boolean;
    employees_count: number;
    created_at: string;
    updated_at: string;
}

export interface DepartmentPagination {
    data: Department[];
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
}

export interface DepartmentFilterData {
    is_active?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
