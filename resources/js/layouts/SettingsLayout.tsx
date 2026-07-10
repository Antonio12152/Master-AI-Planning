import React from 'react';
import { Settings } from 'lucide-react';
import SettingsSidebar from '@/components/SettingsSidebar';

interface SettingsLayoutProps {
    children: React.ReactNode;
    currentPage?: 'profile' | 'security' | 'appearance' | 'ai';
}

export default function SettingsLayout({ children, currentPage = 'profile' }: SettingsLayoutProps) {
    return (
        <div className="mx-auto max-w-7xl px-4 py-12">
            {/* Header */}
            <div className="mb-12 flex items-center gap-3">
                <Settings className="text-blue-400" size={32} />
                <div>
                    <h1 className="text-3xl font-bold text-white">Settings</h1>
                    <p className="text-slate-400">Manage your account and preferences</p>
                </div>
            </div>

            {/* Settings Grid */}
            <div className="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <SettingsSidebar currentPage={currentPage} />
                <div className="lg:col-span-3">{children}</div>
            </div>
        </div>
    );
}
