export function formatPrice(value: string | number): string {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(num);
}

export function formatNumber(value: number | string) {
    const num = typeof value === 'string' ? parseFloat(value) : value;
    if (isNaN(num)) return '';
    return new Intl.NumberFormat('vi-VN').format(Number(num));
}

export function stripNumber(value: string) {
    return value.replace(/\D/g, '');
}

export function handleNumericInput(event: Event, key: string, form: any) {
    const input = event.target as HTMLInputElement;

    // 1. Strip illegal characters using our helper
    const rawValue = stripNumber(input.value);
    const numValue = Number(rawValue);

    // 2. Update the form state (works with Inertia useForm)
    form[key] = numValue;

    // 3. Force the input field to display the formatted version
    // This removes the illegal characters from the UI immediately
    input.value = formatNumber(numValue);
}
