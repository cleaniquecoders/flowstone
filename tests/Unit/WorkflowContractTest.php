<?php

use CleaniqueCoders\Flowstone\Contracts\Workflow as WorkflowContract;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Database\Eloquent\Casts\Attribute;

describe('Workflow Contract', function () {
    it('defines required methods for workflow implementation', function () {
        $contract = new ReflectionClass(WorkflowContract::class);

        $expectedMethods = [
            'setWorkflow',
            'workflowType',
            'workflowTypeField',
            'getWorkflow',
            'getMarking',
            'getEnabledTransitions',
            'getEnabledToTransitions',
            'getRolesFromTransition',
            'getAllEnabledTransitionRoles',
        ];

        $actualMethods = array_map(fn ($method) => $method->getName(), $contract->getMethods());

        foreach ($expectedMethods as $method) {
            expect($actualMethods)->toContain($method);
        }
    });

    it('is implemented by workflow model', function () {
        $workflow = new Workflow;

        expect($workflow)->toBeInstanceOf(WorkflowContract::class);
    });

    it('contract methods have correct return types', function () {
        $contract = new ReflectionClass(WorkflowContract::class);

        // setWorkflow should return self
        $setWorkflowMethod = $contract->getMethod('setWorkflow');
        expect($setWorkflowMethod->getReturnType()?->getName())->toBe('self');

        // workflowType should return Attribute
        $workflowTypeMethod = $contract->getMethod('workflowType');
        expect($workflowTypeMethod->getReturnType()?->getName())->toBe(Attribute::class);

        // workflowTypeField should return Attribute
        $workflowTypeFieldMethod = $contract->getMethod('workflowTypeField');
        expect($workflowTypeFieldMethod->getReturnType()?->getName())->toBe(Attribute::class);

        // getWorkflow should return SymfonyWorkflow
        $getWorkflowMethod = $contract->getMethod('getWorkflow');
        expect($getWorkflowMethod->getReturnType()?->getName())->toBe(\Symfony\Component\Workflow\Workflow::class);

        // getMarking should return string
        $getMarkingMethod = $contract->getMethod('getMarking');
        expect($getMarkingMethod->getReturnType()?->getName())->toBe('string');

        // Array return types
        $arrayReturnMethods = [
            'getEnabledTransitions',
            'getEnabledToTransitions',
            'getRolesFromTransition',
            'getAllEnabledTransitionRoles',
        ];

        foreach ($arrayReturnMethods as $methodName) {
            $method = $contract->getMethod($methodName);
            expect($method->getReturnType()?->getName())->toBe('array');
        }
    });

    it('workflow model implements all contract methods', function () {
        $workflow = new Workflow;

        // Use reflection to get interface methods
        $contractReflection = new ReflectionClass(WorkflowContract::class);
        $contractMethods = $contractReflection->getMethods();

        foreach ($contractMethods as $method) {
            $methodName = $method->getName();
            expect(method_exists($workflow, $methodName))->toBeTrue("Workflow model should implement {$methodName} method");
        }
    });

    it('can be used for type checking', function () {
        $workflow = new Workflow;

        expect($workflow instanceof WorkflowContract)->toBeTrue();

        // Should be able to call contract methods
        expect(method_exists($workflow, 'setWorkflow'))->toBeTrue();
        expect(method_exists($workflow, 'getMarking'))->toBeTrue();
        expect(method_exists($workflow, 'getWorkflow'))->toBeTrue();
    });
});
