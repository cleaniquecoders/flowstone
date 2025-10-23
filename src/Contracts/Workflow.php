<?php

namespace CleaniqueCoders\Flowstone\Contracts;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

interface Workflow
{
    public function setWorkflow(): self;

    public function workflowType(): Attribute;

    public function workflowTypeField(): Attribute;

    public function getWorkflow(): SymfonyWorkflow;

    public function getMarking(): string;

    public function getEnabledTransitions(): array;

    public function getEnabledToTransitions(): array;

    public function getRolesFromTransition(): array;

    public function getAllEnabledTransitionRoles(): array;
}
