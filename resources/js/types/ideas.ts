export interface Idea {
    id: number;
    text: string;
    description?: string;
    status: 'new' | 'in_progress' | 'completed' | 'rejected';
    priority: 0 | 1 | 2 | 3; // 0=low, 1=medium, 2=high, 3=critical
    tags?: string[];
    createdAt: string;
    completedAt?: string | null;
    groupId?: number;
    sort_order?: number;
}

export interface IdeaGroup {
    id: number;
    name: string;
    description?: string;
    sort_order: number;
    color?: string;
    ideas: Idea[];
    idea_count?: number;
    createdAt?: string;
    updatedAt?: string;
}

export interface PlanDetail {
    id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive' | 'archived';
    color?: string;
    icon?: string;
    createdAt: string;
    updatedAt: string;
    ideaGroups: IdeaGroup[];
    idea_count?: number;
    group_count?: number;
    member_count?: number;
    is_public?: boolean;
    archived_at?: string | null;
}