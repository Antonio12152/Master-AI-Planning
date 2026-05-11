import React, { JSX, useState } from 'react';
import { Menu, X } from 'lucide-react';
import { Link, usePage, router } from '@inertiajs/react';

export default function AppHeaderLayout(): JSX.Element {
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const { auth } = usePage().props;
    const user = auth.user;

    return (
        <header className="border-b border-slate-700/50 bg-slate-900/80 backdrop-blur-md sticky top-0 z-50">
            <div className="mx-auto flex h-16 items-center px-4 md:max-w-7xl">

                {/* Mobile Menu Button */}
                <div className="lg:hidden">
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="mr-2 h-9 w-9 rounded-md hover:bg-slate-800 p-0 flex items-center justify-center"
                    >
                        {mobileMenuOpen ? <X size={20} /> : <Menu size={20} />}
                    </button>
                </div>

                {/* Logo - Link to home */}
                <Link href="/" className="flex items-center space-x-2 hover:opacity-80 transition">
                    <div className="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-400 to-cyan-500 flex items-center justify-center font-bold text-sm">
                        P
                    </div>
                    <span className="text-lg font-semibold tracking-tight hidden sm:inline">PlanFlow</span>
                </Link>

                {/* Desktop Navigation */}
                <nav className="ml-6 hidden h-full items-center space-x-6 lg:flex">
                    <Link
                        href="/"
                        className="text-sm font-medium text-slate-400 hover:text-white transition"
                    >
                        Home
                    </Link>
                    <a href="#" className="text-sm font-medium text-slate-400 hover:text-white transition">
                        Contact
                    </a>
                </nav>

                {/* Right Section */}
                <div className="ml-auto flex items-center space-x-2">
                    {user ? (
                        // Logged in user
                        <div className="flex items-center gap-4">
                            <span className="text-sm text-slate-400 hidden sm:inline">
                                <Link
                                    href="/planlist"
                                    className="text-sm font-medium text-slate-400 hover:text-white transition"
                                >
                                    {user.name}
                                </Link>

                            </span>
                            <button
                                onClick={() => router.post('/logout')}
                                className="h-9 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition"
                            >
                                Logout
                            </button>
                        </div>
                    ) : (
                        // Not logged in
                        <>
                            <Link
                                href="/login"
                                className="h-9 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-800 transition hidden sm:inline-flex"
                            >
                                Login
                            </Link>
                            <Link
                                href="/register"
                                className="h-9 px-3 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition"
                            >
                                Register
                            </Link>
                        </>
                    )}
                </div>
            </div>

            {/* Mobile Menu */}
            {mobileMenuOpen && (
                <div className="border-t border-slate-700/50 bg-slate-800/50 backdrop-blur-md lg:hidden">
                    <div className="px-4 py-4 space-y-4">
                        <Link
                            href="/"
                            className="block text-sm font-medium text-slate-300 hover:text-white transition"
                        >
                            Home
                        </Link>
                        <a href="#" className="block text-sm font-medium text-slate-300 hover:text-white transition">
                            Contact
                        </a>
                        <div className="border-t border-slate-700/50 pt-4 space-y-2">
                            {user ? (
                                <button
                                    onClick={() => router.post('/logout')}
                                    className="w-full h-9 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700 transition text-left"
                                >
                                    Logout
                                </button>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="w-full h-9 px-3 rounded-md text-sm font-medium text-slate-400 hover:text-white hover:bg-slate-700 transition block text-left"
                                    >
                                        Login
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="w-full h-9 px-3 rounded-md text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition block text-center"
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