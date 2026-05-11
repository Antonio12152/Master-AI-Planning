import React, { JSX } from 'react';

export default function Footer(): JSX.Element {
    return (
        <footer className="border-t border-slate-700/50 bg-slate-900/50 backdrop-blur-md mt-auto">
            <div className="mx-auto px-4 py-8 md:max-w-7xl">
                <div className="grid grid-cols-1 md:grid-cols-3 gap-8 mb-8">
                    <div>
                        <h4 className="font-semibold mb-4 text-white">About</h4>
                        <p className="text-slate-400 text-sm">
                            PlanFlow helps you organize ideas and build actionable plans.
                        </p>
                    </div>

                    <div>
                        <h4 className="font-semibold mb-4 text-white">Links</h4>
                        <ul className="space-y-2 text-sm text-slate-400">
                            <li><a href="#" className="hover:text-white transition">Home</a></li>
                            <li><a href="#" className="hover:text-white transition">Plans</a></li>
                            <li><a href="#" className="hover:text-white transition">Contact</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 className="font-semibold mb-4 text-white">Contact</h4>
                        <p className="text-slate-400 text-sm">
                            info@planflow.com
                        </p>
                    </div>
                </div>

                <div className="border-t border-slate-700/50 pt-8 text-center text-slate-500 text-sm">
                    <p>&copy; 2024 PlanFlow. All rights reserved.</p>
                </div>
            </div>
        </footer>
    );
}