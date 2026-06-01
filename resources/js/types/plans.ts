export interface Plan {
    id: number;
    name: string;
    description: string;
    status: 'active' | 'inactive' | 'archived';
    color?: string;
    icon?: string;
    ideasCount: number;
    createdAt: string;
    updatedAt: string;
    is_public?: boolean;
    idea_count?: number;
    group_count?: number;
    member_count?: number;
    archived_at?: string | null;
}

export interface CreatePlanInput {
    name: string;
    description?: string;
    color?: string;
    icon?: string;
    is_public?: boolean;
}

export interface UpdatePlanInput {
    name?: string;
    description?: string;
    status?: 'active' | 'inactive' | 'archived';
    color?: string;
    icon?: string;
    is_public?: boolean;
}