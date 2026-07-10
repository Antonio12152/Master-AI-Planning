import { useEffect, useState } from 'react';
import { Button } from '@/components/ui/button';
import { COOKIE_CONSENT_EVENTS, getCookieConsentPreferences } from '@/lib/cookie-consent';
import type { CookieConsentState } from '@/lib/cookie-consent';

export function CookieConsentLink() {
    const [consent, setConsent] = useState<CookieConsentState | null>(null);

    useEffect(() => {
        const updateConsent = () => {
            setConsent(getCookieConsentPreferences());
        };

        updateConsent();
        window.addEventListener(COOKIE_CONSENT_EVENTS.changed, updateConsent);

        return () => window.removeEventListener(COOKIE_CONSENT_EVENTS.changed, updateConsent);
    }, []);

    const handleOpenPreferences = () => {
        window.dispatchEvent(new Event(COOKIE_CONSENT_EVENTS.openPreferences));
    };

    return (
        <Button variant="link" className="h-auto p-0 text-sm text-slate-400 underline-offset-4 hover:text-blue-400" onClick={handleOpenPreferences}>
            {consent ? 'Cookie preferences' : 'Cookie settings'}
        </Button>
    );
}
