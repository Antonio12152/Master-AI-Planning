import React, { useState, useEffect } from 'react';
import { Head, Link } from '@inertiajs/react';
import { Folder, Lightbulb, TrendingUp, Calendar } from 'lucide-react';
import { fetchPlans } from '@/lib/api';
import type { Plan } from '@/types/plans';

export default function Dashboard() {
    const [plans, setPlans] = useState<Plan[]>([]);
    const [loading, setLoading] = useState(true);
    const [stats, setStats] = useState({
        totalPlans: 0,
        activePlans: 0,
        totalIdeas: 0,
    });

    useEffect(() => {
        loadDashboardData();
    }, []);

    async function loadDashboardData() {
        try {
            const data = await fetchPlans(100);
            const convertedPlans = (data.data || []).map((p: any) => ({
                id: p.id,
                name: p.name,
                description: p.description || '',
                idea_count: p.idea_count || 0,
                status: p.status || 'active',
                created_at: p.created_at,
                updated_at: p.updated_at,
                user_id: p.user_id,
                is_public: p.is_public,
                group_count: p.group_count,
                member_count: p.member_count,
                archived_at: p.archived_at,
            }));

            setPlans(convertedPlans.slice(0, 5)); // Recent 5 plans
            setStats({
                totalPlans: convertedPlans.length,
                activePlans: convertedPlans.filter((p: Plan) => p.status === 'active').length,
                totalIdeas: convertedPlans.reduce((sum: number, p: Plan) => sum + (p.idea_count || 0), 0),
            });
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
        } finally {
            setLoading(false);
        }
    }

    return (
        <>
            <Head title="Dashboard" />
            <div className="mx-auto max-w-7xl px-4 py-12">
                    {/* Header */}
                    <div className="mb-12">
                        <h1 className="text-4xl font-bold tracking-tight text-white mb-2">Dashboard</h1>
                        <p className="text-slate-400">Overview of your plans and ideas</p>
                    </div>

                    {/* Stats Grid */}
                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
                        {/* Total Plans Card */}
                        <div className="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 backdrop-blur-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-slate-400 text-sm font-medium mb-1">Total Plans</p>
                                    <p className="text-3xl font-bold text-white">{stats.totalPlans}</p>
                                </div>
                                <div className="rounded-lg bg-blue-500/20 p-3">
                                    <Folder className="text-blue-400" size={24} />
                                </div>
                            </div>
                        </div>

                        {/* Active Plans Card */}
                        <div className="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 backdrop-blur-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-slate-400 text-sm font-medium mb-1">Active Plans</p>
                                    <p className="text-3xl font-bold text-green-400">{stats.activePlans}</p>
                                </div>
                                <div className="rounded-lg bg-green-500/20 p-3">
                                    <TrendingUp className="text-green-400" size={24} />
                                </div>
                            </div>
                        </div>

                        {/* Total Ideas Card */}
                        <div className="rounded-xl border border-slate-700/50 bg-slate-800/30 p-6 backdrop-blur-sm">
                            <div className="flex items-center justify-between">
                                <div>
                                    <p className="text-slate-400 text-sm font-medium mb-1">Total Ideas</p>
                                    <p className="text-3xl font-bold text-purple-400">{stats.totalIdeas}</p>
                                </div>
                                <div className="rounded-lg bg-purple-500/20 p-3">
                                    <Lightbulb className="text-purple-400" size={24} />
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Recent Plans */}
                    <div>
                        <div className="flex items-center justify-between mb-6">
                            <h2 className="text-xl font-bold text-white">Recent Plans</h2>
                            <Link href="/plans" className="text-blue-400 hover:text-blue-300 text-sm font-medium">
                                View All →
                            </Link>
                        </div>

                        {loading ? (
                            <div className="text-center py-12">
                                <div className="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-700 border-t-blue-500"></div>
                                <p className="text-slate-400 mt-3">Loading plans...</p>
                            </div>
                        ) : plans.length > 0 ? (
                            <div className="space-y-3">
                                {plans.map((plan) => (
                                    <Link
                                        key={plan.id}
                                        href={`/plans/${plan.id}`}
                                        className="block p-4 rounded-lg border border-slate-700/50 bg-slate-800/30 hover:bg-slate-700/40 hover:border-blue-500/50 transition"
                                    >
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <h3 className="text-white font-semibold">{plan.name}</h3>
                                                <p className="text-slate-400 text-sm mt-1">{plan.description}</p>
                                            </div>
                                            <div className="text-right">
                                                <p className="text-slate-300 text-sm flex items-center gap-1">
                                                    <Lightbulb size={16} />
                                                    {plan.idea_count} ideas
                                                </p>
                                                <p className={`text-xs mt-1 px-2 py-1 rounded-full ${
                                                    plan.status === 'active' ? 'bg-green-500/20 text-green-400' :
                                                    plan.status === 'inactive' ? 'bg-slate-500/20 text-slate-400' :
                                                    'bg-orange-500/20 text-orange-400'
                                                }`}>
                                                    {plan.status}
                                                </p>
                                            </div>
                                        </div>
                                    </Link>
                                ))}
                            </div>
                        ) : (
                            <div className="rounded-lg border border-slate-700/50 bg-slate-800/30 p-8 text-center">
                                <p className="text-slate-400 mb-3">No plans yet</p>
                                <Link href="/plans" className="text-blue-400 hover:text-blue-300 font-medium">
                                    Create your first plan →
                                </Link>
                            </div>
                        )}
                    </div>
                </div>
        </>
    );
}
