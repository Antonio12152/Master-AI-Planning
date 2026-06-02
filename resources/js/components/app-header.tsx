import React, { JSX, useState } from 'react';
import { Menu, X } from 'lucide-react';
import { Link, usePage, router } from '@inertiajs/react';
import ThemeToggle from '@/components/ThemeToggle';

export default function AppHeaderLayout(): JSX.Element {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { auth } = usePage().props;
    const user = auth.user;

    return (
        <header className="border-b border-slate-700 bg-slate-900 backdrop-blur-md sticky top-0 z-50">
            <div className="mx-auto flex h-16 items-center px-4 md:max-w-7xl">

                {/* Mobile Menu Button */}
                <div className="lg:hidden">
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="mr-2 h-9 w-9 rounded-md hover:bg-slate-800 p-0 flex items-center justify-center text-slate-300 hover:text-white transition"
                        aria-label="Toggle menu"
                    >
                        {mobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
                    </button>
                </div>

                {/* Logo - Link to home */}
                <Link href="/" className="flex items-center space-x-2 hover:opacity-80 transition">
                    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center font-bold text-sm text-slate-900">
                        M
                    </div>
                    <span className="text-lg font-semibold tracking-tight text-white hidden sm:inline">
                        Master AI Planning
                    </span>
                </Link>

                {/* Desktop Navigation */}
                <nav className="ml-6 hidden h-full items-center space-x-6 lg:flex">
                    <Link
                        href="/"
                        className="text-sm font-medium text-slate-300 hover:text-blue-400 transition duration-200"
                    >
                        Home
                    </Link>
                    <a href="#" className="text-sm font-medium text-slate-300 hover:text-blue-400 transition duration-200">
                        Contact
                    </a>
                </nav>

                {/* Right Section */}
                <div className="ml-auto flex items-center space-x-4">
                    {/* Theme Toggle */}
                    <ThemeToggle />

                    {user ? (
                        // Logged in user
                        <div className="flex items-center gap-4">
                            <Link
                                href="/plans"
                                className="text-sm font-medium text-slate-300 hover:text-blue-400 transition duration-200 hidden sm:inline"
                            >
                                {user.name}
                            </Link>

                            <button
                                onClick={() => router.post('/logout')}
                                className="h-9 px-4 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition duration-200"
                            >
                                Logout
                            </button>
                        </div>
                    ) : (
                        // Not logged in
                        <>
                            <Link
                                href="/login"
                                className="h-9 px-4 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-800 transition duration-200 hidden sm:inline-flex items-center"
                            >
                                Login
                            </Link>
                            <Link
                                href="/register"
                                className="h-9 px-4 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition duration-200 font-semibold"
                            >
                                Register
                            </Link>
                        </>
                    )}
                </div>
            </div>

            {/* Mobile Menu */}
            {mobileMenuOpen && (
                <div className="border-t border-slate-700 bg-slate-800 backdrop-blur-md lg:hidden">
                    <div className="px-4 py-4 space-y-2">
                        <Link
                            href="/"
                            className="block h-9 px-3 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition duration-200 flex items-center"
                        >
                            Home
                        </Link>
                        <a href="#" className="block h-9 px-3 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition duration-200 flex items-center">
                            Contact
                        </a>
                        
                        <div className="border-t border-slate-700 mt-4 pt-4 space-y-2">
                            {user ? (
                                <>
                                    <Link
                                        href="/plans"
                                        className="block h-9 px-3 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition duration-200 flex items-center"
                                    >
                                        {user.name}
                                    </Link>
                                    <button
                                        onClick={() => router.post('/logout')}
                                        className="w-full h-9 px-3 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition duration-200"
                                    >
                                        Logout
                                    </button>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="block h-9 px-3 rounded-md text-sm font-medium text-slate-300 hover:text-white hover:bg-slate-700 transition duration-200 flex items-center"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="w-full h-9 px-3 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition duration-200 flex items-center justify-center font-semibold"
                                    >
                                        Register
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </header>
    );
}