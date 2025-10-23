<?php

namespace CleaniqueCoders\Flowstone\Processors;

use CleaniqueCoders\Flowstone\Enums\Status;
use Symfony\Component\Workflow\Definition;
use Symfony\Component\Workflow\MarkingStore\MethodMarkingStore;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\SupportStrategy\InstanceOfSupportStrategy;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow as SymfonyWorkflow;

class Workflow
{
    public static function getDefaultWorkflow()
    {
        $config = config('flowstone.default');

        // Auto-generate places from Status enum if not specified
        if ($config['places'] === null) {
            $config['places'] = collect(Status::cases())
                ->mapWithKeys(fn ($status) => [$status->value => null])
                ->toArray();
        }

        // Auto-generate transitions if not specified
        if ($config['transitions'] === null) {
            $config['transitions'] = static::getDefaultTransitions();
        }

        return $config;
    }

    public static function getCustomWorkflow(string $name)
    {
        $config = config("flowstone.custom.{$name}");

        if (! $config) {
            throw new \InvalidArgumentException("Custom workflow '{$name}' not found in configuration.");
        }

        // Auto-generate places from Status enum if not specified
        if (($config['places'] ?? null) === null) {
            $config['places'] = collect(Status::cases())
                ->mapWithKeys(fn ($status) => [$status->value => null])
                ->toArray();
        }

        return $config;
    }

    protected static function getDefaultTransitions(): array
    {
        return [
            Status::DRAFT->label().' to '.Status::PENDING->label() => [
                'from' => [Status::DRAFT->value],
                'to' => Status::PENDING->value,
            ],
            Status::DRAFT->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::DRAFT->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::PENDING->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::PENDING->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::PENDING->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::PENDING->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::IN_PROGRESS->label().' to '.Status::PAUSED->label() => [
                'from' => [Status::IN_PROGRESS->value],
                'to' => Status::PAUSED->value,
            ],
            Status::IN_PROGRESS->label().' to '.Status::ON_HOLD->label() => [
                'from' => [Status::IN_PROGRESS->value],
                'to' => Status::ON_HOLD->value,
            ],
            Status::IN_PROGRESS->label().' to '.Status::UNDER_REVIEW->label() => [
                'from' => [Status::IN_PROGRESS->value],
                'to' => Status::UNDER_REVIEW->value,
            ],
            Status::IN_PROGRESS->label().' to '.Status::COMPLETED->label() => [
                'from' => [Status::IN_PROGRESS->value],
                'to' => Status::COMPLETED->value,
            ],
            Status::IN_PROGRESS->label().' to '.Status::FAILED->label() => [
                'from' => [Status::IN_PROGRESS->value],
                'to' => Status::FAILED->value,
            ],
            Status::PAUSED->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::PAUSED->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::PAUSED->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::PAUSED->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::ON_HOLD->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::ON_HOLD->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::ON_HOLD->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::ON_HOLD->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::UNDER_REVIEW->label().' to '.Status::APPROVED->label() => [
                'from' => [Status::UNDER_REVIEW->value],
                'to' => Status::APPROVED->value,
            ],
            Status::UNDER_REVIEW->label().' to '.Status::REJECTED->label() => [
                'from' => [Status::UNDER_REVIEW->value],
                'to' => Status::REJECTED->value,
            ],
            Status::UNDER_REVIEW->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::UNDER_REVIEW->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::APPROVED->label().' to '.Status::COMPLETED->label() => [
                'from' => [Status::APPROVED->value],
                'to' => Status::COMPLETED->value,
            ],
            Status::REJECTED->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::REJECTED->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::REJECTED->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::REJECTED->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::FAILED->label().' to '.Status::IN_PROGRESS->label() => [
                'from' => [Status::FAILED->value],
                'to' => Status::IN_PROGRESS->value,
            ],
            Status::FAILED->label().' to '.Status::CANCELLED->label() => [
                'from' => [Status::FAILED->value],
                'to' => Status::CANCELLED->value,
            ],
            Status::COMPLETED->label().' to '.Status::ARCHIVED->label() => [
                'from' => [Status::COMPLETED->value],
                'to' => Status::ARCHIVED->value,
            ],
        ];
    }

    public static function createWorkflow(array $config, Registry $registry): SymfonyWorkflow
    {
        $places = array_keys($config['places']);

        $transitions = [];
        foreach ($config['transitions'] as $name => $transition) {
            $transitions[] = new Transition($name, $transition['from'], $transition['to']);
        }

        $definition = new Definition($places, $transitions);

        $markingStore = new MethodMarkingStore(true, $config['marking_store']['property']);

        $workflow = new SymfonyWorkflow($definition, $markingStore);

        foreach ($config['supports'] as $supportClass) {
            $supportStrategy = new InstanceOfSupportStrategy($supportClass);
            $registry->addWorkflow($workflow, $supportStrategy);
        }

        return $workflow;
    }
}
