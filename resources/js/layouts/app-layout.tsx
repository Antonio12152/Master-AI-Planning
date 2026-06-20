import type { BreadcrumbItem } from '@/types';
import AppFooter from '@/components/app-footer';
import AppHeader from '@/components/app-header';
export default function AppLayout({
    breadcrumbs = [],
    children,
}: {
    breadcrumbs?: BreadcrumbItem[];
    children: React.ReactNode;
}) {
    return (
        <>
            <AppHeader />
            <main className="min-h-screen bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
                {children}
            </main>
            <AppFooter />
        </>
    );
}
