import React, { JSX } from 'react';
import { GripVertical, Plus } from 'lucide-react';
import {
    useSortable,
    SortableContext,
    verticalListSortingStrategy,
} from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import DraggableIdeaItem from './DraggableIdeaItem';
import type { IdeaGroup } from '@/types/ideas';

interface DraggableIdeaGroupProps {
    group: IdeaGroup;
    onDeleteIdea: (groupId: number, ideaId: number) => void;
    onAddIdea: (group: IdeaGroup) => void;
}

export default function DraggableIdeaGroup({
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
    } = useSortable({
        id: `group-${group.id}`,
        data: {
            type: 'IdeaGroup',
            group,
        },
    });

    const style = {
        transform: CSS.Transform.toString(transform),
        transition,
    };

    const ideaIds = group.ideas.map((idea) => `idea-${idea.id}`);

    return (
        <div
            ref={setNodeRef}
            style={style}
            className={`border rounded-xl bg-slate-800/30 backdrop-blur-sm overflow-hidden transition ${isDragging ? 'opacity-50 border-blue-500 shadow-lg shadow-blue-500/20' : 'border-slate-700/50'
                }`}
        >
            {/* Group Header */}
            <div
                className="p-6 border-b border-slate-700/30 bg-gradient-to-r from-slate-700/20 to-transparent flex items-center justify-between hover:bg-slate-700/30 transition cursor-grab active:cursor-grabbing"
                {...attributes}
                {...listeners}
            >
                <div className="flex items-center gap-3 flex-1 min-w-0">
                    <GripVertical size={20} className="text-slate-500 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                        <h2 className="text-2xl font-semibold truncate">{group.name}</h2>
                        <p className="text-sm text-slate-400">{group.ideas.length} idea{group.ideas.length !== 1 ? 's' : ''}</p>
                    </div>
                </div>
            </div>

            {/* Ideas Drop Zone */}
            <div className="p-6">
                {group.ideas.length > 0 ? (
                    <SortableContext items={ideaIds} strategy={verticalListSortingStrategy}>
                        <div className="space-y-3 mb-6">
                            {group.ideas.map((idea) => (
                                <DraggableIdeaItem
                                    key={idea.id}
                                    idea={idea}
                                    groupId={group.id}
                                    onDelete={onDeleteIdea}
                                />
                            ))}
                        </div>
                    </SortableContext>
                ) : (
                    <div className="py-8 text-center">
                        <p className="text-sm text-slate-500 italic mb-4">No ideas yet</p>
                        <p className="text-xs text-slate-600">Drag ideas here or add a new one</p>
                    </div>
                )}

                {/* Add Idea Button */}
                <button
                    onClick={() => onAddIdea(group)}
                    className="w-full py-2 px-4 rounded-lg border border-slate-600/50 text-slate-300 hover:text-white hover:bg-slate-700/50 transition text-sm font-medium flex items-center justify-center gap-2"
                >
                    <Plus size={18} />
                    Add Idea
                </button>
            </div>
        </div>
    );
}