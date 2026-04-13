export interface Designer {
    id: string;
    user: {
        id: string;
        name: string;
        email: string;
    } | null;
    employee: {
        id: string;
        full_name: string;
    } | null;
    full_name: string | null;
    phone: string | null;
    display_name: string;
    avatar_url?: string;
    hourly_rate: string;
    auto_confirm_bookings: boolean;
    is_active: boolean;
    availabilities?: Record<
        number,
        {
            id: string;
            day_of_week: number;
            start_time: string;
            end_time: string;
        }
    >;
    created_at: string;
    updated_at: string;
}

export interface DesignerPagination {
    data: Designer[];
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

export interface DesignerFilterData {
    is_active?: boolean | null;
    search?: string;
    order_by?: string;
    order_direction?: 'asc' | 'desc' | null;
    per_page?: number;
}

export type WeeklySlots = Record<number, boolean[]>;

export interface DateAvailability {
    id: string;
    date: string;
    start_hour: number;
    end_hour: number;
    is_available: boolean;
}
