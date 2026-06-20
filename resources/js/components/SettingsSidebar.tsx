import React from 'react';
import { Link } from '@inertiajs/react';
import { User, Shield, Palette } from 'lucide-react';
import { edit as profileEdit } from '@/routes/profile';
import { edit as securityEdit } from '@/routes/security';
import { edit as appearanceEdit } from '@/routes/appearance';

interface SettingsSidebarProps {
    currentPage?: 'profile' | 'security' | 'appearance';
}

export default function SettingsSidebar({ currentPage = 'profile' }: SettingsSidebarProps) {
    const navItems = [
        { name: 'Profile', href: profileEdit().url, page: 'profile', icon: User },
        { name: 'Security', href: securityEdit().url, page: 'security', icon: Shield },
        { name: 'Appearance', href: appearanceEdit().url, page: 'appearance', icon: Palette },
    ];

    return (
        <div className="lg:col-span-1">
            <nav className="rounded-xl border border-slate-700/50 bg-slate-800/30 p-4 backdrop-blur-sm sticky top-20">
                <h3 className="text-sm font-semibold text-slate-300 mb-4 px-3">Settings</h3>
                <div className="space-y-2">
                    {navItems.map((item) => {
                        const Icon = item.icon;
                        const isActive = currentPage === item.page;
                        return (
                            <Link
                                key={item.page}
                                href={item.href}
                                className={`flex items-center gap-3 px-3 py-2 rounded-lg transition ${
                                    isActive
                                        ? 'bg-blue-600/20 border border-blue-500/50 text-blue-400'
                                        : 'text-slate-400 hover:text-white hover:bg-slate-700/50'
                                }`}
                            >
                                <Icon size={18} />
                                <span className="text-sm font-medium">{item.name}</span>
                            </Link>
                        );
                    })}
                </div>
            </nav>
        </div>
    );
}
