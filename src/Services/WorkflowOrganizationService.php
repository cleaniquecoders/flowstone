<?php

namespace CleaniqueCoders\Flowstone\Services;

use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Support\Collection;

class WorkflowOrganizationService
{
    /**
     * Get all groups with workflow counts
     */
    public function getGroupsWithCounts(): Collection
    {
        return Workflow::whereNotNull('group')
            ->selectRaw('`group`, COUNT(*) as count')
            ->groupBy('group')
            ->orderBy('group')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->group => $item->count]); // @phpstan-ignore property.notFound
    }

    /**
     * Get all categories with workflow counts
     */
    public function getCategoriesWithCounts(): Collection
    {
        return Workflow::whereNotNull('category')
            ->selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->category => $item->count]); // @phpstan-ignore property.notFound
    }

    /**
     * Get all tags with usage counts
     */
    public function getTagsWithCounts(): Collection
    {
        return Workflow::get()
            ->pluck('tags')
            ->flatten()
            ->countBy()
            ->sortDesc();
    }

    /**
     * Get organization summary
     */
    public function getSummary(): array
    {
        return [
            'total_workflows' => Workflow::count(),
            'enabled_workflows' => Workflow::isEnabled()->count(),
            'groups' => $this->getGroupsWithCounts(),
            'categories' => $this->getCategoriesWithCounts(),
            'tags' => $this->getTagsWithCounts(),
        ];
    }

    /**
     * Get workflows grouped by category and group
     */
    public function getOrganizedWorkflows(): Collection
    {
        return Workflow::with(['places', 'transitions'])
            ->get()
            ->groupBy('category')
            ->map(fn ($workflows) => $workflows->groupBy('group'));
    }

    /**
     * Rename tag across all workflows
     */
    public function renameTag(string $oldTag, string $newTag): int
    {
        $count = 0;

        Workflow::whereJsonContains('tags', $oldTag)->each(function ($workflow) use ($oldTag, $newTag, &$count) {
            $tags = $workflow->tags ?? [];
            $tags = array_map(fn ($tag) => $tag === $oldTag ? $newTag : $tag, $tags);
            $workflow->update(['tags' => array_values(array_unique($tags))]);
            $count++;
        });

        return $count;
    }

    /**
     * Delete tag from all workflows
     */
    public function deleteTag(string $tag): int
    {
        $count = 0;

        Workflow::whereJsonContains('tags', $tag)->each(function ($workflow) use ($tag, &$count) {
            $workflow->removeTag($tag);
            $count++;
        });

        return $count;
    }
}
