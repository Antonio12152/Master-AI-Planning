import type { JSX } from 'react';
import { CookieConsentLink } from '@/components/cookie-consent-link';

export default function Footer(): JSX.Element {
    return (
        <footer className="border-t border-slate-700 bg-slate-900 backdrop-blur-md mt-auto">
            <div className="mx-auto px-4 py-12 md:max-w-7xl">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    {/* About Section */}
                    <div>
                        <h4 className="font-semibold mb-4 text-white text-lg">About</h4>
                        <p className="text-slate-300 text-sm leading-relaxed">
                            Master AI Planning helps you organize ideas and build actionable plans with the power of AI.
                        </p>
                    </div>

                    {/* Links Section */}
                    <div>
                        <h4 className="font-semibold mb-4 text-white text-lg">Links</h4>
                        <ul className="space-y-3 text-sm">
                            <li>
                                <a href="/" className="text-slate-300 hover:text-blue-400 transition duration-200">
                                    Home
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-slate-300 hover:text-blue-400 transition duration-200">
                                    Features
                                </a>
                            </li>
                            <li>
                                <a href="#" className="text-slate-300 hover:text-blue-400 transition duration-200">
                                    Contact
                                </a>
                            </li>
                        </ul>
                    </div>

                    {/* Contact Section */}
                    <div>
                        <h4 className="font-semibold mb-4 text-white text-lg">Contact</h4>
                        <p className="text-slate-300 text-sm">
                            <a href="mailto:info@masteraiplanning.com" className="hover:text-blue-400 transition duration-200">
                                info@masteraiplanning.com
                            </a>
                        </p>
                    </div>
                </div>

                {/* Divider */}
                <div className="border-t border-slate-700 pt-8">
                    <div className="flex flex-col items-center justify-center gap-2 text-center text-sm text-slate-400 sm:flex-row sm:gap-4">
                        <p>&copy; 2024 Master AI Planning. All rights reserved.</p>
                        <CookieConsentLink />
                    </div>
                </div>
            </div>
        </footer>
    );
}