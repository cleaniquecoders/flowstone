<?php

namespace CleaniqueCoders\LaravelWorklfow\Concerns;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Workflow\Workflow;

trait InteractsWithWorkflow
{
    public function setWorkflow(): self
    {
        if (! empty(data_get($this, 'workflow'))) {
            return $this;
        }

        $this->update([
            'workflow' => get_workflow_config(
                $this->workflow_type,
                $this->workflow_type_field
            ),
        ]);

        $this->refresh();

        return $this;
    }

    public function workflowType(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->type,
        );
    }

    public function workflowTypeField(): Attribute
    {
        return Attribute::make(
            get: fn () => 'type',
        );
    }

    public function getWorkflow(): Workflow
    {
        if (empty($this->workflow)) {
            $this->setWorkflow();
        }

        return Cache::remember(
            $this->getWorkflowKey(),
            config('app.debug') ? 3 : config('cache.duration'), function () {
                return create_workflow($this->workflow);
            });
    }

    public function getWorkflowKey(): string
    {
        $baseKey = str(get_called_class())->lower()->replace('\\', '-')->toString();

        if (method_exists($this, 'getKeyName') && $this->{$this->getKeyName()}) {
            return $baseKey.'-'.$this->{$this->getKeyName()};
        }

        return $baseKey;
    }

    public function getMarking(): string
    {
        return $this->marking;
    }

    public function getEnabledTransitions(): array
    {
        return $this->getWorkflow()->getEnabledTransitions($this);
    }

    public function getEnabledToTransitions(): array
    {
        $trans = [];
        foreach ($this->getEnabledTransitions() as $transition) {
            $tos = $transition->getTos();
            $to = array_pop($tos);
            $trans[$to] = str($to)->headline()->toString();
        }

        return $trans;
    }

    public function hasEnabledToTransitions(): bool
    {
        return ! empty($this->getEnabledToTransitions());
    }

    public function getRolesFromTransition($marking = null, $type = 'to'): array
    {
        return get_roles_from_transition(
            $this->workflow,
            empty($marking) ? $this->getMarking() : $marking,
            $type
        );
    }

    public function getAllEnabledTransitionRoles(): array
    {
        $roles = [];
        foreach ($this->getEnabledToTransitions() as $key => $value) {
            $roles[$key] = $this->getRolesFromTransition($key);
        }

        return $roles;
    }
}
