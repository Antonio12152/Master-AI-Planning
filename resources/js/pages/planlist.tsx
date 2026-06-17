import React, { JSX, useState, useMemo, useEffect } from 'react';
import { Search, Plus, Folder, Clock, AlertCircle, Lightbulb, ChevronLeft, ChevronRight } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { CreatePlanModal, ChangeStatusModal } from '@/components/modals';
import { fetchPlans, createPlan, updatePlan } from '@/lib/api';
import type { Plan, CreatePlanInput, UpdatePlanInput } from '@/types/plans';

const ITEMS_PER_PAGE = 6;

export default function MainPlanPage(): JSX.Element {
    const { auth } = usePage().props;
    const [plans, setPlans] = useState<Plan[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [filterStatus, setFilterStatus] = useState<string>('all');
    const [currentPage, setCurrentPage] = useState<number>(1);
    const [showCreateModal, setShowCreateModal] = useState<boolean>(false);
    const [showStatusModal, setShowStatusModal] = useState<boolean>(false);
    const [selectedPlanForStatus, setSelectedPlanForStatus] = useState<Plan | null>(null);
    const [creatingPlan, setCreatingPlan] = useState(false);
    const [updatingPlan, setUpdatingPlan] = useState<number | null>(null);

    // Load plans on mount
    useEffect(() => {
        loadPlans();
    }, []);

    async function loadPlans() {
        try {
            setLoading(true);
            setError(null);
            const data = await fetchPlans(100);
            setPlans(data.data || []);
        } catch (err) {
            setError('Failed to load plans');
            console.error(err);
        } finally {
            setLoading(false);
        }
    }

    // Filter plans
    const filteredPlans = useMemo(() => {
        return plans.filter((plan) => {
            const matchesSearch =
                plan.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                plan.description.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesStatus = filterStatus === 'all' || plan.status === filterStatus;

            return matchesSearch && matchesStatus;
        });
    }, [plans, searchQuery, filterStatus]);

    // Pagination
    const totalPages = Math.ceil(filteredPlans.length / ITEMS_PER_PAGE);
    const startIndex = (currentPage - 1) * ITEMS_PER_PAGE;
    const paginatedPlans = filteredPlans.slice(startIndex, startIndex + ITEMS_PER_PAGE);

    // Reset to first page when search changes
    const handleSearch = (query: string) => {
        setSearchQuery(query);
        setCurrentPage(1);
    };

    const handleFilterChange = (status: string) => {
        setFilterStatus(status);
        setCurrentPage(1);
    };

    const handleCreatePlan = async (data: CreatePlanInput) => {
        try {
            setCreatingPlan(true);
            setError(null);
            await createPlan(data);
            setShowCreateModal(false);
            await loadPlans();
        } catch (err) {
            setError('Failed to create plan');
            console.error(err);
        } finally {
            setCreatingPlan(false);
        }
    };

    const handleStatusChange = async (newStatus: string) => {
        if (selectedPlanForStatus) {
            try {
                setUpdatingPlan(selectedPlanForStatus.id);
                setError(null);
                const updateInput: UpdatePlanInput = {
                    status: newStatus as 'active' | 'inactive' | 'archived',
                };
                await updatePlan(selectedPlanForStatus.id, updateInput);
                setShowStatusModal(false);
                await loadPlans();
            } catch (err) {
                setError('Failed to update plan status');
                console.error(err);
            } finally {
                setUpdatingPlan(null);
            }
        }
    };

    const openStatusModal = (e: React.MouseEvent, plan: Plan) => {
        e.preventDefault();
        e.stopPropagation();
        setSelectedPlanForStatus(plan);
        setShowStatusModal(true);
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'active':
                return 'bg-green-500/20 text-green-400';
            case 'inactive':
                return 'bg-slate-500/20 text-slate-400';
            case 'archived':
                return 'bg-orange-500/20 text-orange-400';
            default:
                return 'bg-slate-500/20 text-slate-400';
        }
    };

    const getStatusIcon = (status: string) => {
        switch (status) {
            case 'active':
                return '🟢';
            case 'inactive':
                return '⚫';
            case 'archived':
                return '📦';
            default:
                return '•';
        }
    };

    return (
        <>
            <div className="mx-auto px-4 py-12 md:max-w-7xl">
                {/* Header */}
                <div className="mb-12">
                    <h1 className="text-4xl md:text-5xl font-bold mb-2 tracking-tight">Your Plans</h1>
                    <p className="text-slate-400 text-lg">
                        Manage all your planning projects and ideas
                    </p>
                </div>

                {/* Error Message */}
                {error && (
                    <div className="mb-8 rounded-lg bg-red-500/20 border border-red-500/50 p-4 text-red-400 flex items-start gap-3">
                        <AlertCircle size={20} className="flex-shrink-0 mt-0.5" />
                        <div>
                            <p className="font-semibold">Error</p>
                            <p className="text-sm">{error}</p>
                        </div>
                    </div>
                )}

                {/* Loading State */}
                {loading ? (
                    <div className="flex items-center justify-center py-20">
                        <div className="text-center">
                            <div className="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-700 border-t-blue-500 mb-4"></div>
                            <p className="text-slate-400">Loading your plans...</p>
                        </div>
                    </div>
                ) : (
                    <>
                        {/* Search and Filter Section */}
                        <div className="mb-8 space-y-4">
                            {/* Search Bar */}
                            <div className="relative">
                                <Search
                                    className="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400"
                                    size={20}
                                />
                                <input
                                    type="text"
                                    placeholder="Search plans by name or description..."
                                    value={searchQuery}
                                    onChange={(e) => handleSearch(e.target.value)}
                                    className="w-full pl-12 pr-4 py-3 rounded-lg bg-slate-800/50 border border-slate-700/50 text-white placeholder-slate-500 focus:outline-none focus:border-blue-500/50 transition"
                                />
                            </div>

                            {/* Filter and Action Buttons */}
                            <div className="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center justify-between">
                                <div className="flex gap-2 flex-wrap">
                                    <button
                                        onClick={() => handleFilterChange('all')}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                            filterStatus === 'all'
                                                ? 'bg-blue-600 text-white'
                                                : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                        }`}
                                    >
                                        All
                                    </button>
                                    <button
                                        onClick={() => handleFilterChange('active')}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                            filterStatus === 'active'
                                                ? 'bg-green-600 text-white'
                                                : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                        }`}
                                    >
                                        Active
                                    </button>
                                    <button
                                        onClick={() => handleFilterChange('inactive')}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                            filterStatus === 'inactive'
                                                ? 'bg-slate-600 text-white'
                                                : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                        }`}
                                    >
                                        Inactive
                                    </button>
                                    <button
                                        onClick={() => handleFilterChange('archived')}
                                        className={`px-4 py-2 rounded-lg text-sm font-medium transition ${
                                            filterStatus === 'archived'
                                                ? 'bg-orange-600 text-white'
                                                : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                        }`}
                                    >
                                        Archived
                                    </button>
                                </div>

                                {/* Create Plan Button */}
                                <button
                                    onClick={() => setShowCreateModal(true)}
                                    className="flex items-center justify-center gap-2 px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition whitespace-nowrap disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled={creatingPlan}
                                >
                                    <Plus size={20} />
                                    Create Plan
                                </button>
                            </div>
                        </div>

                        {/* Plans List */}
                        <div className="space-y-4 mb-8">
                            {paginatedPlans.length > 0 ? (
                                paginatedPlans.map((plan) => (
                                    <div
                                        key={plan.id}
                                        className="group p-6 rounded-xl border border-slate-700/50 bg-slate-800/30 hover:bg-slate-700/40 hover:border-blue-500/50 transition backdrop-blur-sm relative"
                                    >
                                        <div className="flex items-start justify-between mb-4">
                                            <Link
                                                href={`/plans/${plan.id}`}
                                                className="flex items-start gap-4 flex-1 cursor-pointer"
                                            >
                                                {/* Icon */}
                                                <div className="w-12 h-12 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0 group-hover:bg-blue-500/30 transition">
                                                    <Folder className="text-blue-400" size={24} />
                                                </div>

                                                {/* Content */}
                                                <div className="flex-1 min-w-0">
                                                    <div className="flex items-center gap-3 mb-2 flex-wrap">
                                                        <h3 className="text-lg font-semibold group-hover:text-blue-400 transition">
                                                            {plan.name}
                                                        </h3>
                                                    </div>
                                                    <p className="text-slate-400 text-sm mb-3 line-clamp-2">
                                                        {plan.description}
                                                    </p>

                                                    {/* Meta Information */}
                                                    <div className="flex items-center gap-4 text-sm text-slate-500">
                                                        <div className="flex items-center gap-1">
                                                            <Lightbulb size={16} />
                                                            {plan.idea_count} idea
                                                            {plan.idea_count !== 1 ? 's' : ''}
                                                        </div>
                                                        <div className="flex items-center gap-1">
                                                            <Clock size={16} />
                                                            Updated {formatDate(plan.updated_at)}
                                                        </div>
                                                    </div>
                                                </div>
                                            </Link>

                                            {/* Right Section */}
                                            <div className="flex flex-col items-end gap-3 flex-shrink-0 ml-4">
                                                {/* Status Badge */}
                                                <div
                                                    onClick={(e) => openStatusModal(e, plan)}
                                                    className={`text-xs px-3 py-1 rounded-full font-medium transition cursor-pointer hover:opacity-80 ${getStatusColor(
                                                        plan.status
                                                    )}`}
                                                >
                                                    {getStatusIcon(plan.status)} {plan.status}
                                                </div>

                                                {/* Change Status Button */}
                                                <button
                                                    onClick={(e) => {
                                                        e.preventDefault();
                                                        e.stopPropagation();
                                                        openStatusModal(e, plan);
                                                    }}
                                                    className="px-3 py-1.5 rounded-lg bg-slate-700/50 border border-slate-600/50 text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-slate-500 transition disabled:opacity-50"
                                                    disabled={updatingPlan === plan.id}
                                                >
                                                    Change
                                                </button>

                                                {/* Arrow */}
                                                <div className="text-slate-500 group-hover:text-blue-400 transition text-lg mt-1">
                                                    →
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                ))
                            ) : (
                                // Empty State
                                <div className="flex flex-col items-center justify-center py-12 px-4 rounded-xl border border-dashed border-slate-700/50 bg-slate-800/10">
                                    <AlertCircle className="text-slate-500 mb-4" size={48} />
                                    <h3 className="text-lg font-semibold text-slate-300 mb-2">
                                        No plans found
                                    </h3>
                                    <p className="text-slate-400 text-center mb-6 max-w-md">
                                        {searchQuery
                                            ? `No plans match your search for "${searchQuery}"`
                                            : 'Create your first plan to get started'}
                                    </p>
                                    <button
                                        onClick={() => setShowCreateModal(true)}
                                        className="flex items-center gap-2 px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition disabled:opacity-50"
                                        disabled={creatingPlan}
                                    >
                                        <Plus size={20} />
                                        Create Plan
                                    </button>
                                </div>
                            )}
                        </div>

                        {/* Pagination */}
                        {totalPages > 1 && (
                            <div className="flex items-center justify-center gap-4">
                                <button
                                    onClick={() => setCurrentPage(Math.max(1, currentPage - 1))}
                                    disabled={currentPage === 1}
                                    className={`p-2 rounded-lg border border-slate-700/50 transition ${
                                        currentPage === 1
                                            ? 'opacity-50 cursor-not-allowed text-slate-500'
                                            : 'text-slate-400 hover:text-white hover:border-slate-600'
                                    }`}
                                >
                                    <ChevronLeft size={20} />
                                </button>

                                <div className="flex items-center gap-2">
                                    {Array.from({ length: totalPages }, (_, i) => i + 1).map((page) => (
                                        <button
                                            key={page}
                                            onClick={() => setCurrentPage(page)}
                                            className={`w-10 h-10 rounded-lg text-sm font-medium transition ${
                                                currentPage === page
                                                    ? 'bg-blue-600 text-white'
                                                    : 'bg-slate-800/50 border border-slate-700/50 text-slate-400 hover:text-white hover:border-slate-600'
                                            }`}
                                        >
                                            {page}
                                        </button>
                                    ))}
                                </div>

                                <button
                                    onClick={() => setCurrentPage(Math.min(totalPages, currentPage + 1))}
                                    disabled={currentPage === totalPages}
                                    className={`p-2 rounded-lg border border-slate-700/50 transition ${
                                        currentPage === totalPages
                                            ? 'opacity-50 cursor-not-allowed text-slate-500'
                                            : 'text-slate-400 hover:text-white hover:border-slate-600'
                                    }`}
                                >
                                    <ChevronRight size={20} />
                                </button>
                            </div>
                        )}

                        {/* Results Count */}
                        {filteredPlans.length > 0 && (
                            <div className="mt-8 text-sm text-slate-400 text-center">
                                Showing {startIndex + 1}-{Math.min(startIndex + ITEMS_PER_PAGE, filteredPlans.length)}{' '}
                                of {filteredPlans.length} plan{filteredPlans.length !== 1 ? 's' : ''}
                            </div>
                        )}
                    </>
                )}
            </div>

            {/* Modals */}
            <CreatePlanModal
                isOpen={showCreateModal}
                onClose={() => setShowCreateModal(false)}
                onSubmit={handleCreatePlan}
            />

            <ChangeStatusModal
                isOpen={showStatusModal}
                plan={selectedPlanForStatus}
                onClose={() => setShowStatusModal(false)}
                onSubmit={handleStatusChange}
            />
        </>
    );
}

/**
 * Format date helper
 */
function formatDate(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'today';
    if (diffDays === 1) return 'yesterday';
    if (diffDays < 7) return `${diffDays} days ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)} weeks ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)} months ago`;
    return `${Math.floor(diffDays / 365)} years ago`;
}