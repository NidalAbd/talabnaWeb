/**
 * Talabna Bootstrap
 * Minimal setup for Vue SPA — no axios (using native fetch)
 */

// CSRF token setup for native fetch
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window._csrfToken = token.content;
}
