import { usePage } from "@inertiajs/vue3";

export function CheckUserPermission(abilities: string): boolean {
    const permissions = usePage().props.auth.user.permissions;

    console.info(permissions)
    if(permissions?.includes('*')) {
        return true;
    }

    if(permissions?.includes(abilities)) {
        return true;
    }

    return false;
}
