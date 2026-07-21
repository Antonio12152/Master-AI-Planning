import React, { JSX, useEffect, useState } from 'react';
import { Moon, Sun } from 'lucide-react';
import { useAppearance } from '@/hooks/use-appearance';

export default function ThemeToggle(): JSX.Element {
    const { resolvedAppearance, updateAppearance } = useAppearance();
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        setMounted(true);
    }, []);

    const toggleTheme = () => {
        const nextTheme = resolvedAppearance === 'dark' ? 'light' : 'dark';
        updateAppearance(nextTheme);
    };

    if (!mounted) {
        return (
            <button
                className="h-9 w-9 rounded-md hover:bg-slate-800 p-0 flex items-center justify-center transition"
                disabled
            >
                <Sun size={18} className="text-slate-400" />
            </button>
        );
    }

    return (
        <button
            onClick={toggleTheme}
            className="h-9 w-9 rounded-md hover:bg-slate-800 p-0 flex items-center justify-center text-slate-300 hover:text-blue-400 transition duration-200"
            aria-label={`Switch to ${resolvedAppearance === 'light' ? 'dark' : 'light'} mode`}
            title={`Current: ${resolvedAppearance} mode`}
        >
            {resolvedAppearance === 'light' ? (
                <Moon size={18} />
            ) : (
                <Sun size={18} />
            )}
        </button>
    );
}
