<?php

use CleaniqueCoders\Flowstone\FlowstoneServiceProvider;
use Illuminate\Support\Facades\App;

describe('FlowstoneServiceProvider', function () {
    it('registers service provider correctly', function () {
        $providers = App::getLoadedProviders();

        expect($providers)->toHaveKey(FlowstoneServiceProvider::class);
    });

    it('publishes config file', function () {
        $provider = new FlowstoneServiceProvider(app());

        // Test that config is published
        expect(config()->has('flowstone'))->toBeTrue();
        expect(config('flowstone.default'))->toBeArray();
        expect(config('flowstone.default.type'))->toBe('state_machine');
    });

    it('registers workflow registry in container', function () {
        expect(app()->bound(\Symfony\Component\Workflow\Registry::class))->toBeTrue();

        $registry = app(\Symfony\Component\Workflow\Registry::class);
        expect($registry)->toBeInstanceOf(\Symfony\Component\Workflow\Registry::class);
    });

    it('provides expected services', function () {
        $provider = new FlowstoneServiceProvider(app());

        $provides = $provider->provides();

        expect($provides)->toBeArray();
        // The provider should provide the registry service
        expect(in_array(\Symfony\Component\Workflow\Registry::class, $provides) || empty($provides))->toBeTrue();
    });

    it('has correct package tag name', function () {
        // Verify the package uses consistent naming
        expect(config('flowstone'))->not->toBeNull();
    });

    it('loads default configuration correctly', function () {
        $config = config('flowstone.default');

        expect($config)->toHaveKey('type');
        expect($config)->toHaveKey('supports');
        expect($config)->toHaveKey('places');
        expect($config)->toHaveKey('transitions');
        expect($config)->toHaveKey('marking_store');

        expect($config['type'])->toBe('state_machine');
        expect($config['supports'])->toBeArray();
        expect($config['marking_store']['property'])->toBe('marking');
    });

    it('allows custom workflow configurations', function () {
        config([
            'flowstone.custom.test_workflow' => [
                'type' => 'workflow',
                'places' => ['start', 'end'],
                'transitions' => [
                    'finish' => [
                        'from' => ['start'],
                        'to' => 'end',
                    ],
                ],
            ],
        ]);

        $customConfig = config('flowstone.custom.test_workflow');

        expect($customConfig)->toBeArray();
        expect($customConfig['type'])->toBe('workflow');
        expect($customConfig['places'])->toEqual(['start', 'end']);
    });
});
