import { useEffect, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import {
    COOKIE_CONSENT_EVENTS,
    getCookieConsentPreferences,
    getDefaultCookieConsentPreferences,
    saveCookieConsentPreferences,
} from '@/lib/cookie-consent';
import type { CookieConsentPreferences } from '@/lib/cookie-consent';

const bannerClasses = 'fixed inset-x-0 bottom-0 z-[100] border-t border-slate-700/80 bg-slate-950/95 px-4 py-4 shadow-2xl backdrop-blur sm:px-6';

function readPreferencesState(): { preferences: CookieConsentPreferences; hasDecision: boolean } {
    const stored = getCookieConsentPreferences();

    if (!stored) {
        return {
            preferences: getDefaultCookieConsentPreferences(),
            hasDecision: false,
        };
    }

    const preferences: CookieConsentPreferences = {
        necessary: true,
        analytics: stored.analytics,
        preferences: stored.preferences,
        version: stored.version,
        timestamp: stored.timestamp,
    };

    return {
        preferences,
        hasDecision: true,
    };
}

export function CookieConsentBanner() {
    const [isOpen, setIsOpen] = useState(false);
    const [preferences, setPreferences] = useState<CookieConsentPreferences>(() => readPreferencesState().preferences);
    const [hasDecision, setHasDecision] = useState(() => readPreferencesState().hasDecision);

    useEffect(() => {
        const handleOpen = () => setIsOpen(true);
        const handleConsentChange = () => {
            const stored = getCookieConsentPreferences();

            if (stored) {
                setPreferences({
                    necessary: true,
                    analytics: stored.analytics,
                    preferences: stored.preferences,
                    version: stored.version,
                    timestamp: stored.timestamp,
                });
                setHasDecision(true);
            }
        };

        window.addEventListener(COOKIE_CONSENT_EVENTS.openPreferences, handleOpen);
        window.addEventListener(COOKIE_CONSENT_EVENTS.changed, handleConsentChange);

        return () => {
            window.removeEventListener(COOKIE_CONSENT_EVENTS.openPreferences, handleOpen);
            window.removeEventListener(COOKIE_CONSENT_EVENTS.changed, handleConsentChange);
        };
    }, []);

    const handleAcceptAll = () => {
        const next = saveCookieConsentPreferences(
            {
                necessary: true,
                analytics: true,
                preferences: true,
                version: preferences.version,
                timestamp: preferences.timestamp,
            },
            'accepted',
        );

        setPreferences({
            necessary: true,
            analytics: next.analytics,
            preferences: next.preferences,
            version: next.version,
            timestamp: next.timestamp,
        });
        setHasDecision(true);
        setIsOpen(false);
    };

    const handleDeclineAll = () => {
        const next = saveCookieConsentPreferences(
            {
                necessary: true,
                analytics: false,
                preferences: false,
                version: preferences.version,
                timestamp: preferences.timestamp,
            },
            'declined',
        );

        setPreferences({
            necessary: true,
            analytics: next.analytics,
            preferences: next.preferences,
            version: next.version,
            timestamp: next.timestamp,
        });
        setHasDecision(true);
        setIsOpen(false);
    };

    const handleSavePreferences = () => {
        const next = saveCookieConsentPreferences(preferences, 'customized');
        setPreferences({
            necessary: true,
            analytics: next.analytics,
            preferences: next.preferences,
            version: next.version,
            timestamp: next.timestamp,
        });
        setHasDecision(true);
        setIsOpen(false);
    };

    const updatePreference = (key: 'analytics' | 'preferences', value: boolean) => {
        setPreferences((current) => ({
            ...current,
            [key]: value,
        }));
    };

    const summary = useMemo(() => {
        if (!hasDecision) {
            return 'We use cookies to keep the site working and to remember your preferences.';
        }

        return preferences.analytics || preferences.preferences
            ? 'You have accepted optional cookies.'
            : 'You have declined optional cookies.';
    }, [hasDecision, preferences.analytics, preferences.preferences]);

    if (hasDecision) {
        return null;
    }

    return (
        <div className={bannerClasses}>
            <div className="mx-auto flex flex-col gap-4 md:max-w-7xl md:flex-row md:items-end md:justify-between">
                <div className="max-w-3xl space-y-2">
                    <p className="text-sm font-semibold text-white">We value your privacy</p>
                    <p className="text-sm text-slate-300">
                        {summary}{' '}
                        <span className="text-slate-400">You can review and change your choices at any time.</span>
                    </p>
                </div>

                <div className="flex flex-col gap-2 sm:flex-row">
                    <Dialog open={isOpen} onOpenChange={setIsOpen}>
                        <DialogTrigger asChild>
                            <Button variant="ghost" className="border border-slate-700 bg-slate-900/70 text-slate-100 hover:bg-slate-800">
                                Customize
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="sm:max-w-xl">
                            <DialogHeader>
                                <DialogTitle>Cookie preferences</DialogTitle>
                                <DialogDescription>
                                    Choose which optional cookies you allow. Necessary cookies are always enabled because they keep the service functioning.
                                </DialogDescription>
                            </DialogHeader>

                            <div className="space-y-4 rounded-lg border border-slate-200 p-4 dark:border-slate-800">
                                <div className="flex items-start justify-between gap-4 rounded-md bg-slate-50 p-3 dark:bg-slate-900/60">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">Necessary cookies</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Required for authentication, security, and basic site functionality.</p>
                                    </div>
                                    <Checkbox checked disabled />
                                </div>

                                <div className="flex items-start justify-between gap-4 rounded-md bg-slate-50 p-3 dark:bg-slate-900/60">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">Analytics cookies</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Help us understand feature usage and improve the experience.</p>
                                    </div>
                                    <Checkbox
                                        checked={preferences.analytics}
                                        onCheckedChange={(checked) => updatePreference('analytics', checked === true)}
                                    />
                                </div>

                                <div className="flex items-start justify-between gap-4 rounded-md bg-slate-50 p-3 dark:bg-slate-900/60">
                                    <div>
                                        <p className="text-sm font-semibold text-slate-900 dark:text-slate-100">Preference cookies</p>
                                        <p className="text-sm text-slate-600 dark:text-slate-400">Remember your theme, dashboard preferences, and other choices.</p>
                                    </div>
                                    <Checkbox
                                        checked={preferences.preferences}
                                        onCheckedChange={(checked) => updatePreference('preferences', checked === true)}
                                    />
                                </div>
                            </div>

                            <DialogFooter className="gap-2 sm:justify-between">
                                <Button variant="outline" onClick={handleDeclineAll}>
                                    Decline all
                                </Button>
                                <div className="flex gap-2">
                                    <Button variant="ghost" onClick={() => setIsOpen(false)}>
                                        Close
                                    </Button>
                                    <Button onClick={handleSavePreferences}>Save preferences</Button>
                                </div>
                            </DialogFooter>
                        </DialogContent>
                    </Dialog>

                    <Button onClick={handleAcceptAll}>Accept all</Button>
                    <Button variant="outline" onClick={handleDeclineAll}>
                        Decline all
                    </Button>
                </div>
            </div>
        </div>
    );
}
