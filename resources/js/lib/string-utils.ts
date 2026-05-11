export function slugify(str: string): string {
    if (!str) return '';

    let slug = str.toLowerCase();

    slug = slug.replace(/[áàảãạăắằẳẵặâấầẩẫậ]/g, 'a');
    slug = slug.replace(/[éèẻẽẹêếềểễệ]/g, 'e');
    slug = slug.replace(/[iíìỉĩị]/g, 'i');
    slug = slug.replace(/[óòỏõọôốồổỗộơớờởỡợ]/g, 'o');
    slug = slug.replace(/[úùủũụưứừửữự]/g, 'u');
    slug = slug.replace(/[ýỳỷỹỵ]/g, 'y');
    slug = slug.replace(/đ/g, 'd');
    slug = slug.replace(/&/g, 'va');

    return slug
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-');
}
