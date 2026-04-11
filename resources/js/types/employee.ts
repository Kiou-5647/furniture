export interface Employee {
    id: string;
    user_id: string;
    user: {
        id: string;
        name: string;
        email: string;
        is_active: boolean;
        email_verified_at: string | null;
        roles: string[];
        permissions: string[];
    } | null;
    department: {
        id: string;
        name: string;
        code: string;
    } | null;
    full_name: string;
    phone: string | null;
    location_id: string | null;
    hire_date: string | null;
    termination_date: string | null;
    avatar_url?: string;
    created_at: string;
    updated_at: string;
}

export interface EmployeePagination {
    data: Employee[];
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

export interface EmployeeFilterData {
    department_id?: string | null;
    is_active?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}
