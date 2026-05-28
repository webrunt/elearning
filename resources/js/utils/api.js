function csrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    if (meta) {
        return meta.getAttribute('content');
    }

    const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);

    if (match) {
        return decodeURIComponent(match[1]);
    }

    return '';
}

/**
 * @param {string} url
 * @param {Record<string, unknown>} body
 * @returns {Promise<Response>}
 */
export function patchJson(url, body) {
    return fetch(url, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
}

/**
 * @param {string} url
 * @returns {Promise<Response>}
 */
export function getJson(url) {
    return fetch(url, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
    });
}

/**
 * @param {string} url
 * @param {Record<string, unknown>} body
 * @returns {Promise<Response>}
 */
export function postJson(url, body) {
    return fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        credentials: 'same-origin',
        body: JSON.stringify(body),
    });
}
