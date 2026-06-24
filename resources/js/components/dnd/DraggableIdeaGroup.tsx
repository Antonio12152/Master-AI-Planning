import React, { JSX, memo } from 'react';
import { useSortable, SortableContext, verticalListSortingStrategy } from '@dnd-kit/sortable';
import { useDroppable } from '@dnd-kit/core';
import { CSS } from '@dnd-kit/utilities';
import { Trash2, Plus } from 'lucide-react';
import type { IdeaGroup, Idea } from '@/types/ideas';

interface DraggableIdeaGroupProps {
    group: IdeaGroup;
    onDeleteIdea: (groupId: number, ideaId: number) => void;
    onAddIdea: (group: IdeaGroup) => void;
}

export default memo(function DraggableIdeaGroup({
    group,
    onDeleteIdea,
    onAddIdea,
}: DraggableIdeaGroupProps): JSX.Element {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({ id: `group-${group.id}`, data: { type: 'IdeaGroup', group } });

    // Make the ideas container droppable for cross-group moves
    const { setNodeRef: setDropRef, isOver } = useDroppable({
        id: `group-${group.id}-drop`,
        data: { type: 'IdeaGroupDropZone', groupId: group.id },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={`flex flex-col h-full rounded-lg border-2 transition ${isDragging
                ? 'border-purple-500 bg-slate-800/80 shadow-2xl scale-105'
                : 'border-slate-600 bg-slate-800/50 hover:border-slate-500'
                }`}
        >
            {/* Group Header - СЕНСОРНАЯ ЗОНА */}
            <div
                {...attributes}
                {...listeners}
                className="flex items-center justify-between p-3 md:p-4 border-b border-slate-700 cursor-grab active:cursor-grabbing touch-none select-none bg-gradient-to-r from-slate-700 to-slate-800 rounded-t-md"
                style={{
                    WebkitTouchCallout: 'none',
                    WebkitUserSelect: 'none',
                    userSelect: 'none',
                }}
            >
                <h3 className="text-sm md:text-base font-semibold text-white truncate pr-2">
                    {group.name}
                </h3>
                <span className="text-xs md:text-sm font-medium px-2 py-1 rounded-full bg-slate-700/50 text-slate-300 flex-shrink-0">
                    {group.ideas.length}
                </span>
            </div>

            {/* Ideas List - АДАПТИВНОЕ МАСШТАБИРОВАНИЕ */}
            <div 
                ref={setDropRef}
                className={`flex-1 overflow-y-auto p-2 md:p-3 space-y-2 md:space-y-3 scrollbar-thin scrollbar-thumb-slate-600 scrollbar-track-transparent transition ${
                    isOver ? 'bg-slate-700/40 border-l-2 border-slate-400' : ''
                }`}
            >
                {group.ideas.length === 0 ? (
                    <div className="flex items-center justify-center h-24 text-slate-500 text-xs md:text-sm text-center px-2">
                        {isOver ? '📥 Drop idea here' : 'No ideas yet. Add one to get started! ✨'}
                    </div>
                ) : (
                    <SortableContext 
                        items={group.ideas.map((idea) => `idea-${idea.id}`)}
                        strategy={verticalListSortingStrategy}
                    >
                        {group.ideas.map((idea) => (
                            <IdeaItem
                                key={idea.id}
                                idea={idea}
                                groupId={group.id}
                                onDelete={onDeleteIdea}
                            />
                        ))}
                    </SortableContext>
                )}
            </div>

            {/* Add Button */}
            <button
                onClick={() => onAddIdea(group)}
                className="flex items-center justify-center gap-2 p-2 md:p-3 border-t border-slate-700 text-slate-300 hover:text-white hover:bg-slate-700/50 transition font-medium text-xs md:text-sm rounded-b-md"
            >
                <Plus size={16} className="md:hidden" />
                <Plus size={18} className="hidden md:block" />
                <span className="hidden sm:inline">Add Idea</span>
                <span className="sm:hidden">Add</span>
            </button>
        </div>
    );
});

const IdeaItem = memo(function IdeaItem({
    idea,
    groupId,
    onDelete,
}: {
    idea: Idea;
    groupId: number;
    onDelete: (groupId: number, ideaId: number) => void;
}): JSX.Element {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: `idea-${idea.id}`,
        data: { type: 'Idea', idea, fromGroupId: groupId },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
        opacity: isDragging ? 0.5 : 1,
    };

    return (
        <div
            ref={setNodeRef}
            {...attributes}
            {...listeners}
            className={`p-2 md:p-3 rounded-lg border-2 transition cursor-grab active:cursor-grabbing group touch-none ${isDragging
                ? 'border-purple-500 bg-purple-500/20 shadow-lg scale-105'
                : 'border-slate-600 bg-slate-700/30 hover:border-slate-500 hover:bg-slate-700/50'
                }`}
            style={{
                ...style,
                WebkitTouchCallout: 'none',
                WebkitUserSelect: 'none',
                userSelect: 'none',
            }}
        >
            {/* Text с оптимизацией для мобилей */}
            <p className="text-xs md:text-sm text-slate-100 mb-2 line-clamp-3 break-words">
                {idea.text}
            </p>

            {/* Footer с датой и кнопкой удаления */}
            <div className="flex items-center justify-between gap-2">
                <span className="text-xs text-slate-500 flex-shrink-0">
                    {formatDateShort(idea.created_at)}
                </span>
                <button
                    onClick={(e) => {
                        e.stopPropagation();
                        onDelete(groupId, idea.id);
                    }}
                    className="opacity-0 group-hover:opacity-100 md:opacity-100 p-1 rounded text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition flex-shrink-0"
                    title="Delete idea"
                >
                    <Trash2 size={14} className="md:w-4 md:h-4" />
                </button>
            </div>
        </div>
    );
});

/**
 * Форматирование даты (короткий формат для мобилей)
 */
function formatDateShort(dateString: string): string {
    const date = new Date(dateString);
    const now = new Date();
    const diffTime = Math.abs(now.getTime() - date.getTime());
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

    if (diffDays === 0) return 'today';
    if (diffDays === 1) return 'yesterday';
    if (diffDays < 7) return `${diffDays}d ago`;
    if (diffDays < 30) return `${Math.floor(diffDays / 7)}w ago`;
    if (diffDays < 365) return `${Math.floor(diffDays / 30)}m ago`;
    return `${Math.floor(diffDays / 365)}y ago`;
}