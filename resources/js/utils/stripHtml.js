/**
 * Plain text from HTML (for list previews).
 *
 * @param {string} html
 * @returns {string}
 */
export function stripHtml(html) {
    if (!html || typeof html !== 'string') {
        return '';
    }

    return html
        .replace(/<br\s*\/?>/gi, ' ')
        .replace(/<\/p>/gi, ' ')
        .replace(/<[^>]+>/g, '')
        .replace(/\s+/g, ' ')
        .trim();
}
