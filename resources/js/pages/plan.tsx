import React, { JSX, useState, useEffect } from 'react';
import { ChevronLeft, Lightbulb, Calendar, User, MessageCircle } from 'lucide-react';
import { Link, usePage } from '@inertiajs/react';
import {
    DndContext,
    closestCenter,
    KeyboardSensor,
    PointerSensor,
    TouchSensor,
    useSensor,
    useSensors,
    DragEndEvent,
    DragOverlay,
} from '@dnd-kit/core';
import {
    arrayMove,
    SortableContext,
    sortableKeyboardCoordinates,
    horizontalListSortingStrategy,
} from '@dnd-kit/sortable';
import DraggableIdeaGroup from '@/components/dnd/DraggableIdeaGroup';
import { AddIdeaModal, ChangeStatusModal } from '@/components/modals';
import ChatModal from '@/components/modals/ChatModal';
import {
    getPlanDetails,
    fetchPlanIdeas,
    createIdea,
    updateIdea,
    deleteIdea,
    updatePlan,
} from '@/lib/api';
import type { PlanDetail, IdeaGroup } from '@/types/ideas';

// Types for drag data
interface DragDataIdea {
    type: 'Idea';
    idea: { id: number };
    fromGroupId: number;
}

interface DragDataGroup {
    type: 'IdeaGroup';
    group: { id: number };
}

type DragData = DragDataIdea | DragDataGroup;

export default function PlanDetailPage({ id }: { id: string }): JSX.Element {
    const { auth } = usePage().props;
    const planId = parseInt(id, 10);

    const [plan, setPlan] = useState<PlanDetail | null>(null);
    const [ideaGroups, setIdeaGroups] = useState<IdeaGroup[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showAddIdeaModal, setShowAddIdeaModal] = useState(false);
    const [showStatusModal, setShowStatusModal] = useState(false);
    const [showAIChatModal, setShowAIChatModal] = useState(false);
    const [selectedGroupForIdea, setSelectedGroupForIdea] = useState<IdeaGroup | null>(null);
    const [activeId, setActiveId] = useState<string | number | null>(null);

    // Load plan details and ideas on mount
    useEffect(() => {
        loadPlanData();
    }, [planId]);

    async function loadPlanData() {
        try {
            setLoading(true);
            setError(null);

            // Fetch plan details
            const planData = await getPlanDetails(planId);
            // Convert API response to PlanDetail type
            const convertedPlan: PlanDetail = {
                id: planData.id,
                name: planData.name,
                description: planData.description || '',
                status: planData.status || 'active',
                createdAt: planData.created_at,
                updatedAt: planData.updated_at,
                ideaGroups: [],
            };
            setPlan(convertedPlan);

            // Fetch ideas for the plan
            const ideasData = await fetchPlanIdeas(planId, undefined, 1000);
            const ideas = ideasData.data || [];
            
            // Group ideas by group_id
            const groupedIdeas: { [key: number]: any[] } = {};
            ideas.forEach((idea: any) => {
                const groupId = idea.group_id || 0;
                if (!groupedIdeas[groupId]) {
                    groupedIdeas[groupId] = [];
                }
                groupedIdeas[groupId].push({
                    id: idea.id,
                    text: idea.text,
                    createdAt: idea.created_at,
                    groupId: idea.group_id,
                });
            });
            
            // Convert to IdeaGroup array
            const groups: IdeaGroup[] = Object.entries(groupedIdeas).map(([groupId, ideas], index) => ({
                id: parseInt(groupId),
                name: `Group ${groupId}`,
                sort_order: index,
                ideas: ideas as any[],
            }));
            
            setIdeaGroups(groups);
        } catch (err) {
            setError('Failed to load plan details');
            console.error(err);
        } finally {
            setLoading(false);
        }
    }

    // Configure sensors for dnd-kit (touch + mouse + keyboard)
    const sensors = useSensors(
        useSensor(PointerSensor, {
            activationConstraint: {
                distance: 8,
                delay: 100,
            },
        }),
        useSensor(TouchSensor, {
            activationConstraint: {
                delay: 100,
                tolerance: 50,
            },
        }),
        useSensor(KeyboardSensor, {
            coordinateGetter: sortableKeyboardCoordinates,
        })
    );

    const handleAddIdea = async (groupId: number, ideaText: string) => {
        try {
            const newIdea = await createIdea(groupId, {
                text: ideaText,
            });

            setIdeaGroups(
                ideaGroups.map((group) =>
                    group.id === groupId
                        ? {
                              ...group,
                              ideas: [...group.ideas, newIdea],
                              idea_count: group.idea_count ? group.idea_count + 1 : 1,
                          }
                        : group
                )
            );
            setShowAddIdeaModal(false);
        } catch (err) {
            setError('Failed to create idea');
            console.error('Failed to create idea:', err);
        }
    };

    const handleDeleteIdea = async (groupId: number, ideaId: number) => {
        try {
            await deleteIdea(ideaId);
            setIdeaGroups(
                ideaGroups.map((group) =>
                    group.id === groupId
                        ? {
                              ...group,
                              ideas: group.ideas.filter((idea) => idea.id !== ideaId),
                              idea_count: Math.max(0, (group.idea_count || 1) - 1),
                          }
                        : group
                )
            );
        } catch (err) {
            setError('Failed to delete idea');
            console.error('Failed to delete idea:', err);
        }
    };

    const handleStatusChange = async (newStatus: string) => {
        if (plan) {
            try {
                const updatedPlan = await updatePlan(plan.id, {
                    status: newStatus as 'active' | 'inactive' | 'archived',
                });
                setPlan(updatedPlan as unknown as PlanDetail);
                setShowStatusModal(false);
            } catch (err) {
                setError('Failed to update status');
                console.error('Failed to update status:', err);
            }
        }
    };

    const handleDragStart = (event: any) => {
        setActiveId(event.active.id);
    };

    const handleDragEnd = (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over) {
            setActiveId(null);
            return;
        }

        const activeData = active.data.current as DragData | undefined;
        const overData = over.data.current as DragData | undefined;

        if (!activeData || !overData) {
            setActiveId(null);
            return;
        }

        // Moving ideas between groups
        if (activeData.type === 'Idea' && overData.type === 'Idea') {
            const activeGroupId = (activeData as DragDataIdea).fromGroupId;
            const overGroupId = (overData as DragDataIdea).fromGroupId;
            const ideaId = (activeData as DragDataIdea).idea.id;

            if (activeGroupId === overGroupId) {
                // Reorder within same group
                const groupIndex = ideaGroups.findIndex((g) => g.id === activeGroupId);
                if (groupIndex === -1) {
                    setActiveId(null);
                    return;
                }

                const oldIndex = ideaGroups[groupIndex].ideas.findIndex((i) => i.id === ideaId);
                const newIndex = ideaGroups[groupIndex].ideas.findIndex(
                    (i) => i.id === (overData as DragDataIdea).idea.id
                );

                if (oldIndex !== -1 && newIndex !== -1) {
                    const updatedGroups = [...ideaGroups];
                    updatedGroups[groupIndex].ideas = arrayMove(
                        updatedGroups[groupIndex].ideas,
                        oldIndex,
                        newIndex
                    );
                    setIdeaGroups(updatedGroups);
                }
            } else {
                // Move to different group
                const idea = ideaGroups
                    .find((g) => g.id === activeGroupId)
                    ?.ideas.find((i) => i.id === ideaId);

                if (idea) {
                    const updatedGroups = ideaGroups.map((group) => {
                        if (group.id === activeGroupId) {
                            return {
                                ...group,
                                ideas: group.ideas.filter((i) => i.id !== idea.id),
                            };
                        }
                        if (group.id === overGroupId) {
                            return {
                                ...group,
                                ideas: [...group.ideas, { ...idea, groupId: overGroupId }],
                            };
                        }
                        return group;
                    });
                    setIdeaGroups(updatedGroups);
                }
            }
        }

        // Moving groups
        if (activeData.type === 'IdeaGroup' && overData.type === 'IdeaGroup') {
            const oldIndex = ideaGroups.findIndex(
                (g) => g.id === (activeData as DragDataGroup).group.id
            );
            const newIndex = ideaGroups.findIndex((g) => g.id === (overData as DragDataGroup).group.id);

            if (oldIndex !== -1 && newIndex !== -1 && oldIndex !== newIndex) {
                setIdeaGroups(arrayMove(ideaGroups, oldIndex, newIndex));
            }
        }

        setActiveId(null);
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

    const totalIdeas = ideaGroups.reduce((sum, group) => sum + group.ideas.length, 0);
    const groupIds = ideaGroups.map((group) => `group-${group.id}`);

    if (!plan) {
        if (loading) {
            return (
                <div className="flex items-center justify-center min-h-screen">
                    <div className="text-center">
                        <div className="inline-block h-8 w-8 animate-spin rounded-full border-4 border-slate-700 border-t-blue-500 mb-4"></div>
                        <p className="text-slate-400">Loading plan...</p>
                    </div>
                </div>
            );
        }

        if (error) {
            return (
                <div className="mx-auto px-4 py-12 md:max-w-7xl">
                    <div className="rounded-lg bg-red-500/20 border border-red-500/50 p-4 text-red-400">
                        <p className="font-semibold mb-2">Error</p>
                        <p className="text-sm">{error}</p>
                    </div>
                    <Link href="/plans" className="mt-4 text-blue-400 hover:text-blue-300">
                        Back to Plans
                    </Link>
                </div>
            );
        }
    }

    return plan ? (
        <>
            <div className="mx-auto px-4 py-12 md:max-w-7xl">
                {/* Back Button */}
                <Link
                    href="/plans"
                    className="flex items-center gap-2 text-blue-400 hover:text-blue-300 transition mb-8"
                >
                    <ChevronLeft size={20} />
                    Back to Plans
                </Link>

                {/* Error Alert */}
                {error && (
                    <div className="mb-8 rounded-lg bg-red-500/20 border border-red-500/50 p-4 text-red-400">
                        <p className="font-semibold mb-2">Error</p>
                        <p className="text-sm">{error}</p>
                    </div>
                )}

                {/* Header Section */}
                <div className="mb-12">
                    <div className="flex items-start justify-between mb-6">
                        <div className="flex-1">
                            <h1 className="text-4xl md:text-5xl font-bold mb-2 tracking-tight">
                                {plan.name}
                            </h1>
                            <p className="text-slate-400 text-lg max-w-2xl">{plan.description}</p>
                        </div>

                        <div className="flex flex-col items-end gap-3 flex-shrink-0 ml-4">
                            <button
                                onClick={() => setShowAIChatModal(true)}
                                className="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-purple-500/20 border border-purple-500/30 text-xs font-medium text-purple-300 hover:text-purple-100 hover:bg-purple-500/30 hover:border-purple-500/50 transition"
                            >
                                <MessageCircle size={16} />
                                AI Chat
                            </button>
                            <button
                                onClick={() => setShowStatusModal(true)}
                                className={`text-sm px-3 py-1 rounded-full font-medium transition cursor-pointer hover:opacity-80 ${getStatusColor(plan.status)}`}
                            >
                                {getStatusIcon(plan.status)} {plan.status}
                            </button>
                            <button
                                onClick={() => setShowStatusModal(true)}
                                className="px-3 py-1.5 rounded-lg bg-slate-700/50 border border-slate-600/50 text-xs font-medium text-slate-300 hover:text-white hover:bg-slate-700 hover:border-slate-500 transition"
                            >
                                Change
                            </button>
                        </div>
                    </div>

                    {/* Meta Information */}
                    <div className="flex flex-wrap gap-6 text-sm text-slate-400">
                        <div className="flex items-center gap-2">
                            <Lightbulb size={18} />
                            {totalIdeas} idea{totalIdeas !== 1 ? 's' : ''}
                        </div>
                        <div className="flex items-center gap-2">
                            <Calendar size={18} />
                            Created {formatDate(plan.createdAt)}
                        </div>
                        <div className="flex items-center gap-2">
                            <User size={18} />
                            {auth.user?.name}
                        </div>
                    </div>
                </div>

                {/* Drag hint */}
                <div className="mb-8 p-4 rounded-lg bg-blue-500/10 border border-blue-500/20 text-sm text-blue-300">
                    💡 Tip: Drag ideas between groups or drag group headers to reorder them. Works on
                    mobile too!
                </div>

                {/* Idea Groups with DndContext */}
                <DndContext
                    sensors={sensors}
                    collisionDetection={closestCenter}
                    onDragStart={handleDragStart}
                    onDragEnd={handleDragEnd}
                >
                    <SortableContext items={groupIds} strategy={horizontalListSortingStrategy}>
                        <div className="flex gap-12 overflow-x-auto pb-6 px-2">
                            {ideaGroups.map((group) => (
                                <div key={group.id} className="flex-shrink-0 w-72 md:w-80 lg:w-96">
                                    <DraggableIdeaGroup
                                        group={group}
                                        onDeleteIdea={handleDeleteIdea}
                                        onAddIdea={(group) => {
                                            setSelectedGroupForIdea(group);
                                            setShowAddIdeaModal(true);
                                        }}
                                    />
                                </div>
                            ))}
                        </div>
                    </SortableContext>

                    {/* Drag Overlay for visual feedback */}
                    <DragOverlay>
                        {activeId ? <div className="opacity-50 text-slate-300">Dragging...</div> : null}
                    </DragOverlay>
                </DndContext>
            </div>

            {/* Modals */}
            <AddIdeaModal
                isOpen={showAddIdeaModal}
                groupName={selectedGroupForIdea?.name || ''}
                onClose={() => setShowAddIdeaModal(false)}
                onSubmit={(ideaText) => {
                    if (selectedGroupForIdea) {
                        handleAddIdea(selectedGroupForIdea.id, ideaText);
                    }
                }}
            />

            <ChangeStatusModal
                isOpen={showStatusModal}
                plan={
                    plan
                        ? {
                              id: plan.id,
                              name: plan.name,
                              description: plan.description,
                              ideasCount: totalIdeas,
                              status: plan.status,
                              createdAt: plan.createdAt,
                              updatedAt: plan.updatedAt,
                          }
                        : null
                }
                onClose={() => setShowStatusModal(false)}
                onSubmit={handleStatusChange}
            />

            <ChatModal
                isOpen={showAIChatModal}
                plan={plan}
                ideaGroups={ideaGroups}
                onClose={() => setShowAIChatModal(false)}
            />
        </>
    ) : <></>;
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