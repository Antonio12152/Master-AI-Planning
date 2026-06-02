import React, { JSX } from 'react';
import { Zap, Users, Lightbulb, ArrowRight } from 'lucide-react';
// import MainLayoutTemplate from '@/layouts/layout';

export default function HomePage(): JSX.Element {
    return (
        <>
            {/* Hero Section */}
            <section className="mx-auto px-4 py-20 md:max-w-7xl">
                <div className="flex flex-col items-center text-center mb-16">
                    <h1 className="text-5xl md:text-6xl font-bold mb-6 tracking-tight">
                        Organize Ideas,
                        <span className="bg-gradient-to-r from-blue-400 to-cyan-400 bg-clip-text text-transparent"> Build Plans</span>
                    </h1>
                    <p className="text-xl text-slate-400 max-w-2xl mb-8">
                        Master AI Planning helps you capture, organize, and transform your ideas into actionable plans with the power of AI.
                    </p>
                    <div className="flex flex-col sm:flex-row gap-4">
                        <button className="px-8 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                            Get Started
                        </button>
                        <button className="px-8 py-3 rounded-lg border-2 border-blue-600 hover:border-blue-700 hover:bg-blue-50 text-blue-600 hover:text-blue-700 font-medium transition flex items-center justify-center gap-2">
                            Learn More <ArrowRight size={18} />
                        </button>
                    </div>
                </div>
            </section>

            {/* Features Section */}
            <section className="mx-auto px-4 py-16 md:max-w-7xl">
                <h2 className="text-3xl md:text-4xl font-bold text-center mb-12">Why Choose Master AI Planning?</h2>

                <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                    {/* Feature 1 */}
                    <div className="p-8 rounded-xl border border-slate-700/50 bg-slate-800/30 backdrop-blur-sm">
                        <div className="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center mb-4">
                            <Lightbulb className="text-blue-400" size={24} />
                        </div>
                        <h3 className="text-xl font-semibold mb-3">Organize Ideas</h3>
                        <p className="text-slate-400">
                            Capture all your ideas in one place. Group them, categorize them, and keep everything organized.
                        </p>
                    </div>

                    {/* Feature 2 */}
                    <div className="p-8 rounded-xl border border-slate-700/50 bg-slate-800/30 backdrop-blur-sm">
                        <div className="w-12 h-12 rounded-lg bg-cyan-500/20 flex items-center justify-center mb-4">
                            <Zap className="text-cyan-400" size={24} />
                        </div>
                        <h3 className="text-xl font-semibold mb-3">AI-Powered Planning</h3>
                        <p className="text-slate-400">
                            Let AI help you refine, develop, and transform your ideas into concrete, actionable plans.
                        </p>
                    </div>

                    {/* Feature 3 */}
                    <div className="p-8 rounded-xl border border-slate-700/50 bg-slate-800/30 backdrop-blur-sm">
                        <div className="w-12 h-12 rounded-lg bg-purple-500/20 flex items-center justify-center mb-4">
                            <Users className="text-purple-400" size={24} />
                        </div>
                        <h3 className="text-xl font-semibold mb-3">Collaborate & Share</h3>
                        <p className="text-slate-400">
                            Share your plans with team members and collaborate in real-time to bring ideas to life.
                        </p>
                    </div>
                </div>
            </section>

            {/* How It Works Section */}
            <section className="mx-auto px-4 py-16 md:max-w-7xl">
                <h2 className="text-3xl md:text-4xl font-bold text-center mb-12">How It Works</h2>

                <div className="grid grid-cols-1 md:grid-cols-4 gap-6">
                    {/* Step 1 */}
                    <div className="flex flex-col items-center text-center">
                        <div className="w-16 h-16 rounded-full bg-blue-600/20 border-2 border-blue-500 flex items-center justify-center mb-4">
                            <span className="text-2xl font-bold text-blue-400">1</span>
                        </div>
                        <h3 className="font-semibold mb-2">Create Plan</h3>
                        <p className="text-sm text-slate-400">Start a new planning session</p>
                    </div>

                    {/* Arrow */}
                    <div className="hidden md:flex items-center justify-center">
                        <ArrowRight className="text-slate-600" size={24} />
                    </div>

                    {/* Step 2 */}
                    <div className="flex flex-col items-center text-center">
                        <div className="w-16 h-16 rounded-full bg-cyan-600/20 border-2 border-cyan-500 flex items-center justify-center mb-4">
                            <span className="text-2xl font-bold text-cyan-400">2</span>
                        </div>
                        <h3 className="font-semibold mb-2">Gather Ideas</h3>
                        <p className="text-sm text-slate-400">Add and organize your thoughts</p>
                    </div>

                    {/* Arrow */}
                    <div className="hidden md:flex items-center justify-center">
                        <ArrowRight className="text-slate-600" size={24} />
                    </div>

                    {/* Step 3 */}
                    <div className="flex flex-col items-center text-center">
                        <div className="w-16 h-16 rounded-full bg-purple-600/20 border-2 border-purple-500 flex items-center justify-center mb-4">
                            <span className="text-2xl font-bold text-purple-400">3</span>
                        </div>
                        <h3 className="font-semibold mb-2">Ask AI</h3>
                        <p className="text-sm text-slate-400">Get AI assistance and insights</p>
                    </div>

                    {/* Arrow */}
                    <div className="hidden md:flex items-center justify-center">
                        <ArrowRight className="text-slate-600" size={24} />
                    </div>

                    {/* Step 4 */}
                    <div className="flex flex-col items-center text-center">
                        <div className="w-16 h-16 rounded-full bg-green-600/20 border-2 border-green-500 flex items-center justify-center mb-4">
                            <span className="text-2xl font-bold text-green-400">4</span>
                        </div>
                        <h3 className="font-semibold mb-2">Execute</h3>
                        <p className="text-sm text-slate-400">Turn ideas into reality</p>
                    </div>
                </div>
            </section>

            {/* CTA Section */}
            <section className="mx-auto px-4 py-20 md:max-w-7xl">
                <div className="relative rounded-2xl border border-slate-700/50 bg-gradient-to-r from-blue-600/10 to-cyan-600/10 backdrop-blur-sm p-12 md:p-16 text-center">
                    <h2 className="text-3xl md:text-4xl font-bold mb-4">Ready to Get Started?</h2>
                    <p className="text-lg text-slate-400 max-w-2xl mx-auto mb-8">
                        Start organizing your ideas today and see how Master AI Planning can help you turn thoughts into plans.
                    </p>
                    <button className="px-8 py-3 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                        Create Your First Plan
                    </button>
                </div>
            </section>

            {/* FAQ Section */}
            <section className="mx-auto px-4 py-16 md:max-w-7xl">
                <h2 className="text-3xl md:text-4xl font-bold text-center mb-12">Frequently Asked Questions</h2>

                <div className="space-y-4 max-w-3xl mx-auto">
                    {/* FAQ Item 1 */}
                    <details className="group p-6 rounded-lg border border-slate-700/50 bg-slate-800/30 cursor-pointer">
                        <summary className="flex items-center justify-between font-semibold">
                            What is Master AI Planning?
                            <span className="transition group-open:rotate-180">
                                <ArrowRight size={20} className="text-slate-400" />
                            </span>
                        </summary>
                        <p className="text-slate-400 mt-4">
                            Master AI Planning is a platform that helps you organize ideas and turn them into actionable plans with AI assistance. It combines brainstorming, organization, and AI-powered planning in one place.
                        </p>
                    </details>

                    {/* FAQ Item 2 */}
                    <details className="group p-6 rounded-lg border border-slate-700/50 bg-slate-800/30 cursor-pointer">
                        <summary className="flex items-center justify-between font-semibold">
                            Can I use Master AI Planning for team projects?
                            <span className="transition group-open:rotate-180">
                                <ArrowRight size={20} className="text-slate-400" />
                            </span>
                        </summary>
                        <p className="text-slate-400 mt-4">
                            Yes! Master AI Planning supports collaboration features so you can work with your team members on shared plans and ideas.
                        </p>
                    </details>

                    {/* FAQ Item 3 */}
                    <details className="group p-6 rounded-lg border border-slate-700/50 bg-slate-800/30 cursor-pointer">
                        <summary className="flex items-center justify-between font-semibold">
                            How does the AI help?
                            <span className="transition group-open:rotate-180">
                                <ArrowRight size={20} className="text-slate-400" />
                            </span>
                        </summary>
                        <p className="text-slate-400 mt-4">
                            Our AI assistant helps you refine ideas, suggest improvements, organize thoughts, and develop actionable steps for your plans.
                        </p>
                    </details>

                    {/* FAQ Item 4 */}
                    <details className="group p-6 rounded-lg border border-slate-700/50 bg-slate-800/30 cursor-pointer">
                        <summary className="flex items-center justify-between font-semibold">
                            Is my data secure?
                            <span className="transition group-open:rotate-180">
                                <ArrowRight size={20} className="text-slate-400" />
                            </span>
                        </summary>
                        <p className="text-slate-400 mt-4">
                            We take data security seriously. All your plans and ideas are encrypted and stored securely. We never share your data with third parties.
                        </p>
                    </details>
                </div>
            </section>
        </>
    );
}