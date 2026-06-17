export interface Idea {
    id: number;
    text: string;
    description?: string;
    status: 'new' | 'in_progress' | 'completed' | 'rejected';
    priority: 0 | 1 | 2 | 3; // 0=low, 1=medium, 2=high, 3=critical
    tags?: string[];
    created_at: string;
    completed_at?: string | null;
    group_id?: number;
    sort_order?: number;
    plan_id: number;
}

export interface IdeaGroup {
    id: number;
    name: string;
    description?: string;
    sort_order: number;
    color?: string;
    ideas: Idea[];
    idea_count?: number;
    created_at?: string;
    updated_at?: string;
    plan_id: number;
}

export interface PlanDetail {
    id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive' | 'archived';
    color?: string;
    icon?: string;
    created_at: string;
    updated_at: string;
    ideaGroups: IdeaGroup[];
    idea_count?: number;
    group_count?: number;
    member_count?: number;
    is_public?: boolean;
    archived_at?: string | null;
    user_id: number;
}