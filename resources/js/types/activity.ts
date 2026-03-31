export interface ActivityLog {
    id: number;
    event: 'created' | 'updated' | 'deleted';
    description: string;
    causer: {
        id: string;
        name: string;
    } | null;
    changes: {
        old: Record<string, any>;
        new: Record<string, any>;
    };
    created_at: string;
    created_at_human: string;
}
