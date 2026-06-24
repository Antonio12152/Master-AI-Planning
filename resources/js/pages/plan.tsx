import React, { JSX, useState, useEffect, useCallback } from 'react';
import { ChevronLeft, Lightbulb, Calendar, User, MessageCircle, Plus, Edit2, Trash2 } from 'lucide-react';
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
import { AddIdeaModal, ChangeStatusModal, IdeaGroupModal } from '@/components/modals';
import ChatModal from '@/components/modals/ChatModal';
import {
    getPlanDetails,
    createIdea,
    updateIdea,
    deleteIdea,
    updatePlan,
    moveIdea,
    createIdeaGroup,
    updateIdeaGroup,
    deleteIdeaGroup,
    reorderIdeaGroups,
    reorderIdeas,
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

export default function PlanDetailPage(): JSX.Element {
    const { auth } = usePage().props;
    const page = usePage();
    
    // Get ID from URL path
    const urlParts = page.url.split('/');
    const idFromUrl = urlParts[urlParts.length - 1];
    const planId = parseInt(idFromUrl, 10);

    const [plan, setPlan] = useState<PlanDetail | null>(null);
    const [ideaGroups, setIdeaGroups] = useState<IdeaGroup[]>([]);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState<string | null>(null);
    const [showAddIdeaModal, setShowAddIdeaModal] = useState(false);
    const [showStatusModal, setShowStatusModal] = useState(false);
    const [showAIChatModal, setShowAIChatModal] = useState(false);
    const [showGroupModal, setShowGroupModal] = useState(false);
    const [groupModalMode, setGroupModalMode] = useState<'create' | 'edit'>('create');
    const [selectedGroupForIdea, setSelectedGroupForIdea] = useState<IdeaGroup | null>(null);
    const [selectedGroupForManage, setSelectedGroupForManage] = useState<IdeaGroup | null>(null);
    const [activeId, setActiveId] = useState<string | number | null>(null);
    const [savingGroupId, setSavingGroupId] = useState<number | null>(null);

    // Load plan details and ideas on mount
    useEffect(() => {
        loadPlanData();
    }, [planId]);

    async function loadPlanData() {
        try {
            setLoading(true);
            setError(null);

            // Fetch plan details (includes nested ideaGroups and ideas)
            const response = await getPlanDetails(planId);
            const planData = response.plan;
            
            // Set plan with all data
            const convertedPlan: PlanDetail = {
                id: planData.id,
                name: planData.name,
                description: planData.description || '',
                status: planData.status || 'active',
                color: planData.color,
                icon: planData.icon,
                created_at: planData.created_at,
                updated_at: planData.updated_at,
                ideaGroups: planData.ideaGroups || [],
                idea_count: planData.idea_count,
                group_count: planData.group_count,
                member_count: planData.member_count,
                is_public: planData.is_public,
                archived_at: planData.archived_at,
                user_id: planData.user_id,
            };
            setPlan(convertedPlan);
            
            // Use nested ideas from idea groups
            const groups: IdeaGroup[] = (planData.idea_groups || []).map((group: any) => ({
                id: group.id,
                name: group.name,
                description: group.description,
                sort_order: group.sort_order,
                color: group.color,
                ideas: (group.ideas || []).map((idea: any) => ({
                    id: idea.id,
                    text: idea.text,
                    description: idea.description,
                    status: idea.status,
                    priority: idea.priority,
                    tags: idea.tags,
                    created_at: idea.created_at,
                    completed_at: idea.completed_at,
                    group_id: idea.group_id,
                    sort_order: idea.sort_order,
                    plan_id: idea.plan_id,
                })),
                idea_count: group.idea_count || group.ideas?.length || 0,
                created_at: group.created_at,
                updated_at: group.updated_at,
                plan_id: group.plan_id,
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

    const handleAddIdea = useCallback(async (groupId: number, ideaText: string) => {
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
    }, [ideaGroups]);

    const handleDeleteIdea = useCallback(async (groupId: number, ideaId: number) => {
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
    }, [ideaGroups]);

    const handleStatusChange = useCallback(async (newStatus: string) => {
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
    }, [plan]);

    const handleCreateGroup = useCallback(async (data: { name: string; description?: string; color?: string }) => {
        try {
            setSavingGroupId(-1);
            const newGroup = await createIdeaGroup(planId, data);
            setIdeaGroups([...ideaGroups, { 
                ...newGroup, 
                ideas: [],
                sort_order: ideaGroups.length,
            }]);
            setShowGroupModal(false);
        } catch (err) {
            setError('Failed to create group');
            console.error('Failed to create group:', err);
        } finally {
            setSavingGroupId(null);
        }
    }, [planId, ideaGroups]);

    const handleUpdateGroup = useCallback(async (data: { name: string; description?: string; color?: string }) => {
        if (!selectedGroupForManage) return;
        
        try {
            setSavingGroupId(selectedGroupForManage.id);
            const updatedGroup = await updateIdeaGroup(selectedGroupForManage.id, data);
            setIdeaGroups(
                ideaGroups.map((g) => g.id === selectedGroupForManage.id ? updatedGroup : g)
            );
            setShowGroupModal(false);
            setSelectedGroupForManage(null);
        } catch (err) {
            setError('Failed to update group');
            console.error('Failed to update group:', err);
        } finally {
            setSavingGroupId(null);
        }
    }, [ideaGroups, selectedGroupForManage]);

    const handleDeleteGroup = useCallback(async (groupId: number) => {
        if (!confirm('Delete this group and all its ideas? This cannot be undone.')) return;
        
        try {
            setSavingGroupId(groupId);
            await deleteIdeaGroup(groupId);
            setIdeaGroups(ideaGroups.filter((g) => g.id !== groupId));
        } catch (err) {
            setError('Failed to delete group');
            console.error('Failed to delete group:', err);
        } finally {
            setSavingGroupId(null);
        }
    }, [ideaGroups]);

    const openGroupModal = useCallback((mode: 'create' | 'edit', group?: IdeaGroup) => {
        setGroupModalMode(mode);
        if (mode === 'edit' && group) {
            setSelectedGroupForManage(group);
        } else {
            setSelectedGroupForManage(null);
        }
        setShowGroupModal(true);
    }, []);

    const handleDragStart = useCallback((event: any) => {
        setActiveId(event.active.id);
    }, []);

    const handleDragEnd = useCallback(async (event: DragEndEvent) => {
        const { active, over } = event;

        if (!over) {
            setActiveId(null);
            return;
        }

        const activeData = active.data.current as DragData | undefined;
        const overData = over.data.current as any;

        if (!activeData) {
            setActiveId(null);
            return;
        }

        // Case 1: Reorder ideas within same group (idea on idea)
        if (activeData.type === 'Idea' && overData?.type === 'Idea') {
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
                    
                    // Persist sort_order to API
                    try {
                        await reorderIdeas(updatedGroups[groupIndex].ideas);
                    } catch (err) {
                        setError('Failed to save idea order');
                        console.error('Failed to reorder ideas:', err);
                        loadPlanData();
                    }
                }
            } else {
                // Move between groups
                const idea = ideaGroups
                    .find((g) => g.id === activeGroupId)
                    ?.ideas.find((i) => i.id === ideaId);

                if (idea) {
                    try {
                        await moveIdea(ideaId, overGroupId);
                        
                        // Update UI
                        const updatedGroups = ideaGroups.map((group) => {
                            if (group.id === activeGroupId) {
                                return {
                                    ...group,
                                    ideas: group.ideas.filter((i) => i.id !== idea.id),
                                    idea_count: Math.max(0, (group.idea_count || 1) - 1),
                                };
                            }
                            if (group.id === overGroupId) {
                                return {
                                    ...group,
                                    ideas: [...group.ideas, { ...idea, group_id: overGroupId }],
                                    idea_count: (group.idea_count || 0) + 1,
                                };
                            }
                            return group;
                        });
                        setIdeaGroups(updatedGroups);
                    } catch (err) {
                        setError('Failed to move idea');
                        console.error('Failed to move idea:', err);
                    }
                }
            }
        }
        
        // Case 2: Drop idea on group drop zone (for easier cross-group moves)
        else if (activeData.type === 'Idea' && overData?.type === 'IdeaGroupDropZone') {
            const activeGroupId = (activeData as DragDataIdea).fromGroupId;
            const targetGroupId = overData.groupId;
            const ideaId = (activeData as DragDataIdea).idea.id;

            // Only process if moving to different group
            if (activeGroupId !== targetGroupId) {
                const idea = ideaGroups
                    .find((g) => g.id === activeGroupId)
                    ?.ideas.find((i) => i.id === ideaId);

                if (idea) {
                    try {
                        await moveIdea(ideaId, targetGroupId);
                        
                        // Update UI
                        const updatedGroups = ideaGroups.map((group) => {
                            if (group.id === activeGroupId) {
                                return {
                                    ...group,
                                    ideas: group.ideas.filter((i) => i.id !== idea.id),
                                    idea_count: Math.max(0, (group.idea_count || 1) - 1),
                                };
                            }
                            if (group.id === targetGroupId) {
                                return {
                                    ...group,
                                    ideas: [...group.ideas, { ...idea, group_id: targetGroupId }],
                                    idea_count: (group.idea_count || 0) + 1,
                                };
                            }
                            return group;
                        });
                        setIdeaGroups(updatedGroups);
                    } catch (err) {
                        setError('Failed to move idea between groups');
                        console.error('Failed to move idea:', err);
                    }
                }
            }
        }

        // Case 3: Moving groups - reorder persisted to backend
        if (activeData.type === 'IdeaGroup' && overData?.type === 'IdeaGroup') {
            const oldIndex = ideaGroups.findIndex(
                (g) => g.id === (activeData as DragDataGroup).group.id
            );
            const newIndex = ideaGroups.findIndex((g) => g.id === (overData as DragDataGroup).group.id);

            if (oldIndex !== -1 && newIndex !== -1 && oldIndex !== newIndex) {
                const reorderedGroups = arrayMove(ideaGroups, oldIndex, newIndex);
                setIdeaGroups(reorderedGroups);
                
                // Persist group order to backend
                try {
                    await reorderIdeaGroups(reorderedGroups);
                } catch (err) {
                    setError('Failed to save group order');
                    console.error('Failed to reorder groups:', err);
                    // Revert to original order on error
                    loadPlanData();
                }
            }
        }

        setActiveId(null);
    }, [ideaGroups, loadPlanData]);

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
                            Created {formatDate(plan.created_at)}
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

                {/* Create Group Button */}
                <div className="mb-6">
                    <button
                        onClick={() => openGroupModal('create')}
                        className="flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white font-medium transition"
                    >
                        <Plus size={18} />
                        Create Group
                    </button>
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
                                    {/* Group Header with Management Buttons */}
                                    <div className="flex items-center justify-between gap-2 mb-3">
                                        <h3 className="text-lg font-semibold text-white truncate">{group.name}</h3>
                                        <div className="flex gap-1 flex-shrink-0">
                                            <button
                                                onClick={() => openGroupModal('edit', group)}
                                                disabled={savingGroupId === group.id}
                                                className="p-1.5 rounded bg-slate-700/50 hover:bg-slate-700 text-slate-300 hover:text-white transition disabled:opacity-50"
                                                title="Edit group"
                                            >
                                                <Edit2 size={16} />
                                            </button>
                                            <button
                                                onClick={() => handleDeleteGroup(group.id)}
                                                disabled={savingGroupId === group.id}
                                                className="p-1.5 rounded bg-red-500/20 hover:bg-red-500/30 text-red-400 hover:text-red-300 transition disabled:opacity-50"
                                                title="Delete group"
                                            >
                                                <Trash2 size={16} />
                                            </button>
                                        </div>
                                    </div>
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
                              idea_count: totalIdeas,
                              status: plan.status,
                              created_at: plan.created_at,
                              updated_at: plan.updated_at,
                              user_id: plan.user_id,
                              is_public: plan.is_public,
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

            <IdeaGroupModal
                isOpen={showGroupModal}
                mode={groupModalMode}
                groupData={selectedGroupForManage}
                onClose={() => {
                    setShowGroupModal(false);
                    setSelectedGroupForManage(null);
                }}
                onSubmit={groupModalMode === 'create' ? handleCreateGroup : handleUpdateGroup}
                isLoading={savingGroupId !== null}
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