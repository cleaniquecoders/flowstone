# Content Publishing Pipeline

A blog/CMS content workflow demonstrating editorial processes with scheduling and publication management.

## Workflow: Draft → Review → Scheduled → Published

### Content Model

```php
<?php

namespace App\Models;

use CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow;
use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Content extends Model implements WorkflowContract
{
    use InteractsWithWorkflow;

    protected $fillable = [
        'title', 'slug', 'body', 'excerpt', 'status',
        'author_id', 'editor_id', 'scheduled_at', 'published_at',
        'seo_title', 'seo_description', 'featured_image'
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function workflowType(): Attribute
    {
        return Attribute::make(get: fn () => 'content-publishing');
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(get: fn () => 'workflow_type');
    }

    public function getMarking(): string
    {
        return $this->status ?? 'draft';
    }

    public function setMarking(string $marking): void
    {
        $this->status = $marking;

        if ($marking === 'published' && !$this->published_at) {
            $this->published_at = now();
        }
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                    ->where('published_at', '<=', now());
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled')
                    ->where('scheduled_at', '>', now());
    }

    // Helper methods
    public function isPublic(): bool
    {
        return $this->status === 'published' &&
               $this->published_at <= now();
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, ['draft', 'review']);
    }
}
```

### Key Features

- **Editorial workflow** with author/editor separation
- **Scheduled publishing** for content planning
- **SEO optimization** fields
- **Automatic publishing** via scheduled jobs
- **Content versioning** support

### Usage

```php
// Create content
$content = Content::create([
    'title' => 'Blog Post Title',
    'body' => 'Content...',
    'author_id' => auth()->id(),
]);

// Editorial review
$workflow = $content->getWorkflow();
$workflow->apply($content, 'submit_for_review');

// Schedule for later
$content->scheduled_at = now()->addDays(3);
$workflow->apply($content, 'schedule');

// Auto-publish scheduled content (via job)
Content::scheduled()
    ->where('scheduled_at', '<=', now())
    ->each(fn($content) =>
        $content->getWorkflow()->apply($content, 'publish')
    );
```
