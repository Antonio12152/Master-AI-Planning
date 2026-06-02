import React, { JSX, useEffect, useState } from 'react';
import { Moon, Sun } from 'lucide-react';

export default function ThemeToggle(): JSX.Element {
    const [theme, setTheme] = useState<'light' | 'dark'>('light');
    const [mounted, setMounted] = useState(false);

    useEffect(() => {
        // Get saved theme from localStorage
        const savedTheme = localStorage.getItem('theme') as 'light' | 'dark' | null;
        
        if (savedTheme) {
            setTheme(savedTheme);
            applyTheme(savedTheme);
        } else {
            // Check system preference
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            const systemTheme = prefersDark ? 'dark' : 'light';
            setTheme(systemTheme);
            applyTheme(systemTheme);
        }

        setMounted(true);
    }, []);

    const applyTheme = (newTheme: 'light' | 'dark') => {
        const html = document.documentElement;
        
        if (newTheme === 'dark') {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }

        localStorage.setItem('theme', newTheme);
    };

    const toggleTheme = () => {
        const newTheme = theme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
        applyTheme(newTheme);
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
            aria-label={`Switch to ${theme === 'light' ? 'dark' : 'light'} mode`}
            title={`Current: ${theme} mode`}
        >
            {theme === 'light' ? (
                <Moon size={18} />
            ) : (
                <Sun size={18} />
            )}
        </button>
    );
}