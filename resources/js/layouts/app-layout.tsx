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
            <main>{children}</main>
            <AppFooter />
        </>
    );
}
