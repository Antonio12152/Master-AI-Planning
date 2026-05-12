import React, { JSX } from 'react';
import { GripVertical, Trash2 } from 'lucide-react';
import { useSortable } from '@dnd-kit/sortable';
import { CSS } from '@dnd-kit/utilities';
import type { Idea } from '@/types/ideas';

interface DraggableIdeaItemProps {
    idea: Idea;
    groupId: number;
    onDelete: (groupId: number, ideaId: number) => void;
}

export default function DraggableIdeaItem({
    idea,
    groupId,
    onDelete,
}: DraggableIdeaItemProps): JSX.Element {
    const {
        attributes,
        listeners,
        setNodeRef,
        transform,
        transition,
        isDragging,
    } = useSortable({
        id: `idea-${idea.id}`,
        data: {
            type: 'Idea',
            idea,
            fromGroupId: groupId,
        },
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
            className={`p-4 rounded-lg bg-slate-700/20 border border-slate-600/30 hover:bg-slate-700/40 hover:border-slate-500/50 transition group flex items-start justify-between ${isDragging ? 'opacity-50 border-blue-400 bg-blue-500/10 shadow-lg shadow-blue-500/20' : ''
                }`}
        >
            <div className="flex items-start gap-3 flex-1 min-w-0" {...attributes} {...listeners}>
                <GripVertical
                    size={16}
                    className="text-slate-500 flex-shrink-0 mt-1 cursor-grab active:cursor-grabbing opacity-0 group-hover:opacity-100 transition"
                />
                <div className="flex-1 min-w-0">
                    <p className="text-slate-200 mb-2 break-words">{idea.text}</p>
                    <p className="text-xs text-slate-500">{formatDate(idea.createdAt)}</p>
                </div>
            </div>
            <button
                onClick={() => onDelete(groupId, idea.id)}
                className="ml-4 p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition opacity-0 group-hover:opacity-100 flex-shrink-0"
                title="Delete idea"
            >
                <Trash2 size={18} />
            </button>
        </div>
    );
}

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