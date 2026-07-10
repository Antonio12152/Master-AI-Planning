import { axiosInstance } from '@/lib/http';

/**
 * Example: Fetch all plans for the current user
 */
export async function fetchPlans(perPage = 15) {
    try {
        const response = await axiosInstance.get(
            `/plans?per_page=${perPage}`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to fetch plans:', error);
        throw error;
    }
}

/**
 * Example: Create a new plan
 */
export async function createPlan(data: {
    name: string;
    description?: string;
    color?: string;
    icon?: string;
}) {
    try {
        const response = await axiosInstance.post('/plans', data);
        return response.data;
    } catch (error) {
        console.error('Failed to create plan:', error);
        throw error;
    }
}

/**
 * Example: Get plan details
 */
export async function getPlanDetails(planId: number) {
    try {
        const response = await axiosInstance.get(`/plans/${planId}`);
        return response.data;
    } catch (error) {
        console.error('Failed to fetch plan details:', error);
        throw error;
    }
}

/**
 * Example: Update plan
 */
export async function updatePlan(
    planId: number,
    data: {
        name?: string;
        description?: string;
        color?: string;
        icon?: string;
        status?: string;
    }
) {
    try {
        const response = await axiosInstance.put(`/plans/${planId}`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to update plan:', error);
        throw error;
    }
}

/**
 * Example: Fetch ideas for a plan
 */
export async function fetchPlanIdeas(
    planId: number,
    status?: string,
    perPage = 20
) {
    try {
        const params = new URLSearchParams();
        if (status) params.append('status', status);
        params.append('per_page', perPage.toString());

        const response = await axiosInstance.get(
            `/plans/${planId}/ideas?${params}`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to fetch ideas:', error);
        throw error;
    }
}

/**
 * Example: Create a new idea
 */
export async function createIdea(
    groupId: number,
    data: {
        text: string;
        description?: string;
        priority?: number;
        tags?: string[];
    }
) {
    try {
        const response = await axiosInstance.post(
            `/idea-groups/${groupId}/ideas`,
            data
        );
        return response.data;
    } catch (error) {
        console.error('Failed to create idea:', error);
        throw error;
    }
}

/**
 * Example: Update an idea
 */
export async function updateIdea(
    ideaId: number,
    data: {
        text?: string;
        description?: string;
        status?: string;
        priority?: number;
        tags?: string[];
        sort_order?: number;
    }
) {
    try {
        const response = await axiosInstance.put(`/ideas/${ideaId}`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to update idea:', error);
        throw error;
    }
}

/**
 * Example: Complete an idea
 */
export async function completeIdea(ideaId: number) {
    try {
        const response = await axiosInstance.post(
            `/ideas/${ideaId}/complete`
        );
        return response.data;
    } catch (error) {
        console.error('Failed to complete idea:', error);
        throw error;
    }
}

/**
 * Example: Delete an idea
 */
export async function deleteIdea(ideaId: number) {
    try {
        await axiosInstance.delete(`/ideas/${ideaId}`);
        return true;
    } catch (error) {
        console.error('Failed to delete idea:', error);
        throw error;
    }
}

/**
 * Example: Move idea to another group
 */
export async function moveIdea(ideaId: number, groupId: number) {
    try {
        const response = await axiosInstance.post(`/ideas/${ideaId}/move`, {
            group_id: groupId,
        });
        return response.data;
    } catch (error) {
        console.error('Failed to move idea:', error);
        throw error;
    }
}

/**
 * Example: Fetch idea groups for a plan
 */
export async function fetchIdeaGroups(planId: number) {
    try {
        const response = await axiosInstance.get(`/plans/${planId}/groups`);
        return response.data;
    } catch (error) {
        console.error('Failed to fetch groups:', error);
        throw error;
    }
}

/**
 * Example: Create new idea group
 */
export async function createIdeaGroup(planId: number, data: {
    name: string;
    description?: string;
    color?: string;
}) {
    try {
        const response = await axiosInstance.post(`/plans/${planId}/groups`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to create group:', error);
        throw error;
    }
}

/**
 * Example: Update idea group
 */
export async function updateIdeaGroup(groupId: number, data: {
    name?: string;
    description?: string;
    color?: string;
    sort_order?: number;
}) {
    try {
        const response = await axiosInstance.put(`/idea-groups/${groupId}`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to update group:', error);
        throw error;
    }
}

/**
 * Example: Delete idea group
 */
export async function deleteIdeaGroup(groupId: number) {
    try {
        await axiosInstance.delete(`/idea-groups/${groupId}`);
        return true;
    } catch (error) {
        console.error('Failed to delete group:', error);
        throw error;
    }
}

/**
 * Reorder idea groups by updating sort_order for each group
 * based on their position in the provided array
 */
export async function reorderIdeaGroups(groups: Array<{ id: number; [key: string]: any }>) {
    try {
        // Update each group with its new sort_order based on array position
        const updatePromises = groups.map((group, index) =>
            updateIdeaGroup(group.id, { sort_order: index })
        );
        
        const results = await Promise.all(updatePromises);
        return results;
    } catch (error) {
        console.error('Failed to reorder groups:', error);
        throw error;
    }
}

/**
 * Reorder ideas within a group by updating sort_order for each idea
 * based on their position in the provided array
 */
export async function reorderIdeas(ideas: Array<{ id: number; [key: string]: any }>) {
    try {
        // Update each idea with its new sort_order based on array position
        const updatePromises = ideas.map((idea, index) =>
            updateIdea(idea.id, { sort_order: index })
        );
        
        const results = await Promise.all(updatePromises);
        return results;
    } catch (error) {
        console.error('Failed to reorder ideas:', error);
        throw error;
    }
}
export async function chatWithPlanAi(planId: number, data: { message: string; selected_group_ids?: number[] }) {
    try {
        const response = await axiosInstance.post(`/plans/${planId}/ai/chat`, data);
        return response.data;
    } catch (error) {
        console.error('Failed to chat with AI:', error);
        throw error;
    }
}
