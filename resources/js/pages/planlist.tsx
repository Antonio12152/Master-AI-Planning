import React, { JSX, useState, useMemo } from 'react';
import { Search, Plus, Folder, Clock, AlertCircle, Lightbulb, ChevronLeft, ChevronRight } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import { CreatePlanModal, ChangeStatusModal } from '@/components/modals';
import type { Plan } from '@/types/plans';

// Mock data - замени на реальные данные с backend
const MOCK_PLANS: Plan[] = [
    {
        id: 1,
        name: 'Mobile App Redesign',
        description: 'Complete redesign of the mobile application',
        ideasCount: 12,
        status: 'active',
        createdAt: '2024-01-15',
        updatedAt: '2024-01-20',
    },
    {
        id: 2,
        name: 'Marketing Campaign Q1',
        description: 'Spring marketing campaign planning',
        ideasCount: 8,
        status: 'active',
        createdAt: '2024-01-10',
        updatedAt: '2024-01-18',
    },
    {
        id: 3,
        name: 'API Documentation',
        description: 'Update API docs for v2.0',
        ideasCount: 5,
        status: 'inactive',
        createdAt: '2024-01-05',
        updatedAt: '2024-01-15',
    },
    {
        id: 4,
        name: 'Team Building Event',
        description: 'Annual team building and outing',
        ideasCount: 15,
        status: 'active',
        createdAt: '2023-12-20',
        updatedAt: '2024-01-10',
    },
    {
        id: 5,
        name: 'Database Optimization',
        description: 'Optimize database queries and indexing',
        ideasCount: 7,
        status: 'inactive',
        createdAt: '2023-12-15',
        updatedAt: '2024-01-08',
    },
    {
        id: 6,
        name: 'Security Audit',
        description: 'Complete security audit and penetration testing',
        ideasCount: 10,
        status: 'active',
        createdAt: '2024-01-01',
        updatedAt: '2024-01-19',
    },
    {
        id: 7,
        name: 'Content Strategy',
        description: 'Blog and social media content strategy',
        ideasCount: 20,
        status: 'archived',
        createdAt: '2023-11-01',
        updatedAt: '2024-01-05',
    },
    {
        id: 8,
        name: 'Website Redesign',
        description: 'Complete website redesign and migration',
        ideasCount: 18,
        status: 'active',
        createdAt: '2023-10-15',
        updatedAt: '2024-01-18',
    },
    {
        id: 9,
        name: 'Mobile App v2.0',
        description: 'Next generation of mobile application',
        ideasCount: 25,
        status: 'inactive',
        createdAt: '2023-09-01',
        updatedAt: '2024-01-12',
    },
    {
        id: 10,
        name: 'DevOps Infrastructure',
        description: 'Upgrade infrastructure and CI/CD pipelines',
        ideasCount: 14,
        status: 'active',
        createdAt: '2024-01-08',
        updatedAt: '2024-01-19',
    },
    {
        id: 11,
        name: 'Customer Research',
        description: 'Conduct market research and customer interviews',
        ideasCount: 9,
        status: 'active',
        createdAt: '2024-01-12',
        updatedAt: '2024-01-20',
    },
    {
        id: 12,
        name: 'Performance Optimization',
        description: 'Optimize app performance and reduce load times',
        ideasCount: 11,
        status: 'inactive',
        createdAt: '2023-12-01',
        updatedAt: '2024-01-14',
    },
];

const ITEMS_PER_PAGE = 6;

export default function MainPlanPage(): JSX.Element {
    const { auth } = usePage().props;
    const [searchQuery, setSearchQuery] = useState<string>('');
    const [filterStatus, setFilterStatus] = useState<string>('all');
    const [currentPage, setCurrentPage] = useState<number>(1);
    const [showCreateModal, setShowCreateModal] = useState<boolean>(false);
    const [showStatusModal, setShowStatusModal] = useState<boolean>(false);
    const [selectedPlanForStatus, setSelectedPlanForStatus] = useState<Plan | null>(null);

    // Filter plans
    const filteredPlans = useMemo(() => {
        return MOCK_PLANS.filter((plan) => {
            const matchesSearch = plan.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
                plan.description.toLowerCase().includes(searchQuery.toLowerCase());

            const matchesStatus = filterStatus === 'all' || plan.status === filterStatus;

            return matchesSearch && matchesStatus;
        });
    }, [searchQuery, filterStatus]);

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

    const handleCreatePlan = (data: { name: string; description: string }) => {
        console.log('Create plan:', data);
        setShowCreateModal(false);
        // TODO: Send to backend
    };

    const handleStatusChange = (newStatus: string) => {
        if (selectedPlanForStatus) {
            console.log(`Change plan ${selectedPlanForStatus.id} status to ${newStatus}`);
            setShowStatusModal(false);
            // TODO: Send to backend
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

                {/* Search and Filter Section */}
                <div className="mb-8 space-y-4">
                    {/* Search Bar */}
                    <div className="relative">
                        <Search className="absolute left-4 top-1/2 transform -translate-y-1/2 text-slate-400" size={20} />
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
                                className={`px-4 py-2 rounded-lg text-sm font-medium transition ${filterStatus === 'all'
                                        ? 'bg-blue-600 text-white'
                                        : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                    }`}
                            >
                                All
                            </button>
                            <button
                                onClick={() => handleFilterChange('active')}
                                className={`px-4 py-2 rounded-lg text-sm font-medium transition ${filterStatus === 'active'
                                        ? 'bg-green-600 text-white'
                                        : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                    }`}
                            >
                                Active
                            </button>
                            <button
                                onClick={() => handleFilterChange('inactive')}
                                className={`px-4 py-2 rounded-lg text-sm font-medium transition ${filterStatus === 'inactive'
                                        ? 'bg-slate-600 text-white'
                                        : 'bg-slate-800/50 border border-slate-700/50 text-slate-300 hover:text-white'
                                    }`}
                            >
                                Inactive
                            </button>
                            <button
                                onClick={() => handleFilterChange('archived')}
                                className={`px-4 py-2 rounded-lg text-sm font-medium transition ${filterStatus === 'archived'
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
                            className="flex items-center justify-center gap-2 px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition whitespace-nowrap"
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
                                        href={""}
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
                                                    {plan.ideasCount} idea{plan.ideasCount !== 1 ? 's' : ''}
                                                </div>
                                                <div className="flex items-center gap-1">
                                                    <Clock size={16} />
                                                    Updated {formatDate(plan.updatedAt)}
                                                </div>
                                            </div>
                                        </div>
                                    </Link>

                                    {/* Right Section */}
                                    <div className="flex flex-col items-end gap-3 flex-shrink-0 ml-4">
                                        {/* Status Badge */}
                                        <div
                                            onClick={(e) => openStatusModal(e, plan)}
                                            className={`text-xs px-3 py-1 rounded-full font-medium transition cursor-pointer hover:opacity-80 ${getStatusColor(plan.status)}`}
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
                                            className="px-3 py-1.5 rounded-lg bg-slate-700/50 border border-slate-600/50 text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-slate-500 transition"
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
                                className="flex items-center gap-2 px-6 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition"
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
                            className={`p-2 rounded-lg border border-slate-700/50 transition ${currentPage === 1
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
                                    className={`w-10 h-10 rounded-lg text-sm font-medium transition ${currentPage === page
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
                            className={`p-2 rounded-lg border border-slate-700/50 transition ${currentPage === totalPages
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
                        Showing {startIndex + 1}-{Math.min(startIndex + ITEMS_PER_PAGE, filteredPlans.length)} of {filteredPlans.length} plan{filteredPlans.length !== 1 ? 's' : ''}
                    </div>
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