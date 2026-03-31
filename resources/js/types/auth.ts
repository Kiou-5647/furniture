export type User = {
    id: string;
    name: string;
    email: string;
    type: 'employee' | 'customer' | 'vendor' | 'admin';
    avatar_url: string | null;
    email_verified_at: string | null;
    permissions?: string[];
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
