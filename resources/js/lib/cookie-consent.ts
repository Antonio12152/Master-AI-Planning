export type CookieConsentCategory = 'necessary' | 'analytics' | 'preferences';

export type CookieConsentPreferences = {
    necessary: true;
    analytics: boolean;
    preferences: boolean;
    version: string;
    timestamp: string;
};

export type CookieConsentDecision = 'accepted' | 'declined' | 'customized';

export type CookieConsentState = CookieConsentPreferences & {
    decision: CookieConsentDecision;
};

const COOKIE_NAME = 'master_ai_cookie_consent';
const COOKIE_VERSION = '1';
const COOKIE_MAX_AGE = 60 * 60 * 24 * 365;

export const COOKIE_CONSENT_EVENTS = {
    changed: 'cookie-consent-changed',
    openPreferences: 'cookie-consent-open',
} as const;

export function getDefaultCookieConsentPreferences(): CookieConsentPreferences {
    return {
        necessary: true,
        analytics: false,
        preferences: false,
        version: COOKIE_VERSION,
        timestamp: '',
    };
}

function readCookie(name: string): string | null {
    if (typeof document === 'undefined') {
        return null;
    }

    const cookieValue = document.cookie
        .split(';')
        .map((cookie) => cookie.trim())
        .find((cookie) => cookie.startsWith(`${name}=`));

    if (!cookieValue) {
        return null;
    }

    return decodeURIComponent(cookieValue.slice(name.length + 1));
}

function writeCookie(name: string, value: string, maxAge: number): void {
    if (typeof document === 'undefined') {
        return;
    }

    const secure = window.location.protocol === 'https:' ? '; Secure' : '';
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; max-age=${maxAge}; SameSite=Lax${secure}`;
}

function clearCookie(name: string): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.cookie = `${name}=; path=/; max-age=0; SameSite=Lax`;
}

export function getCookieConsentPreferences(): CookieConsentState | null {
    const cookieValue = readCookie(COOKIE_NAME);

    if (!cookieValue) {
        return null;
    }

    try {
        const parsed = JSON.parse(cookieValue) as Partial<CookieConsentState>;

        if (!parsed || typeof parsed !== 'object') {
            clearCookie(COOKIE_NAME);

            return null;
        }

        return {
            necessary: true,
            analytics: Boolean(parsed.analytics),
            preferences: Boolean(parsed.preferences),
            version: parsed.version ?? COOKIE_VERSION,
            timestamp: parsed.timestamp ?? '',
            decision: parsed.decision ?? 'customized',
        };
    } catch {
        clearCookie(COOKIE_NAME);

        return null;
    }
}

export function saveCookieConsentPreferences(
    preferences: CookieConsentPreferences,
    decision: CookieConsentDecision,
): CookieConsentState {
    const state: CookieConsentState = {
        ...preferences,
        version: COOKIE_VERSION,
        timestamp: new Date().toISOString(),
        decision,
    };

    writeCookie(COOKIE_NAME, JSON.stringify(state), COOKIE_MAX_AGE);

    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent(COOKIE_CONSENT_EVENTS.changed, { detail: state }));
    }

    return state;
}

export function clearCookieConsentPreferences(): void {
    clearCookie(COOKIE_NAME);

    if (typeof window !== 'undefined') {
        window.dispatchEvent(new CustomEvent(COOKIE_CONSENT_EVENTS.changed, { detail: null }));
    }
}

export function isOptionalCookieEnabled(preferences: CookieConsentState | null | undefined): boolean {
    return Boolean(preferences?.analytics || preferences?.preferences);
}
