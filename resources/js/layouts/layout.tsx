import { AppShell } from '@/components/app-shell';
import React from 'react';
import AppFooterLayout from './app/app-footer-layout';
import AppHeaderLayout from './app/app-header-layout';

export default function MainLayoutTemplate({ children }: { children: React.ReactNode }) {
    return (
        <>
            <AppShell>
                <AppHeaderLayout />
                <main>{children}</main>
                <AppFooterLayout />
            </AppShell>
        </>
    );
};
