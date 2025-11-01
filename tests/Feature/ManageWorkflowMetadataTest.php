<?php

use CleaniqueCoders\Flowstone\Livewire\ManageWorkflowMetadata;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Livewire\Livewire;

beforeEach(function () {
    $this->workflow = Workflow::factory()->create([
        'name' => 'Test Workflow',
        'type' => 'state_machine',
        'meta' => null,
    ]);
});

it('can open metadata modal', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->assertSet('showModal', false)
        ->call('openModal')
        ->assertSet('showModal', true);
});

it('can add string metadata', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'author')
        ->set('value', 'John Doe')
        ->set('type', 'string')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta)->toBeArray()
        ->and($this->workflow->meta)->toHaveKey('author')
        ->and($this->workflow->meta['author']['value'])->toBe('John Doe')
        ->and($this->workflow->meta['author']['type'])->toBe('string');
});

it('can add integer metadata', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'version')
        ->set('value', '42')
        ->set('type', 'integer')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['version']['value'])->toBe(42)
        ->and($this->workflow->meta['version']['type'])->toBe('integer');
});

it('can add numeric metadata', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'rating')
        ->set('value', '4.5')
        ->set('type', 'numeric')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['rating']['value'])->toBe(4.5)
        ->and($this->workflow->meta['rating']['type'])->toBe('numeric');
});

it('can add boolean metadata', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'is_active')
        ->set('value', '1')
        ->set('type', 'boolean')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['is_active']['value'])->toBe(true)
        ->and($this->workflow->meta['is_active']['type'])->toBe('boolean');
});

it('can add array metadata', function () {
    $jsonData = '{"name":"test","value":123}';

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'config')
        ->set('value', $jsonData)
        ->set('type', 'array')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['config']['value'])->toBeArray()
        ->and($this->workflow->meta['config']['value'])->toHaveKey('name')
        ->and($this->workflow->meta['config']['value']['name'])->toBe('test')
        ->and($this->workflow->meta['config']['type'])->toBe('array');
});

it('can add date metadata', function () {
    $date = '2024-12-15';

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'deadline')
        ->set('value', $date)
        ->set('type', 'date')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['deadline']['value'])->toBe($date)
        ->and($this->workflow->meta['deadline']['type'])->toBe('date');
});

it('validates required fields', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', '')
        ->set('value', '')
        ->call('addMetadata')
        ->assertHasErrors(['key', 'value']);
});

it('prevents duplicate keys when adding new metadata', function () {
    $this->workflow->update([
        'meta' => [
            'existing_key' => [
                'value' => 'existing value',
                'type' => 'string',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'existing_key')
        ->set('value', 'new value')
        ->set('type', 'string')
        ->call('addMetadata')
        ->assertHasErrors('key');
});

it('can edit existing metadata', function () {
    $this->workflow->update([
        'meta' => [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('editMetadata', 'author')
        ->assertSet('isEditing', true)
        ->assertSet('editingKey', 'author')
        ->assertSet('key', 'author')
        ->assertSet('value', 'John Doe')
        ->assertSet('type', 'string')
        ->set('value', 'Jane Doe')
        ->call('addMetadata')
        ->assertHasNoErrors();

    $this->workflow->refresh();

    expect($this->workflow->meta['author']['value'])->toBe('Jane Doe');
});

it('can delete metadata', function () {
    $this->workflow->update([
        'meta' => [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
            'version' => [
                'value' => 1,
                'type' => 'integer',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('deleteMetadata', 'author');

    $this->workflow->refresh();

    expect($this->workflow->meta)->not->toHaveKey('author')
        ->and($this->workflow->meta)->toHaveKey('version');
});

it('can cancel edit mode', function () {
    $this->workflow->update([
        'meta' => [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('editMetadata', 'author')
        ->assertSet('isEditing', true)
        ->call('cancelEdit')
        ->assertSet('isEditing', false)
        ->assertSet('key', '')
        ->assertSet('value', '');
});

it('can close modal', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('openModal')
        ->assertSet('showModal', true)
        ->call('closeModal')
        ->assertSet('showModal', false);
});

it('dispatches workflow-updated event after adding metadata', function () {
    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->set('key', 'author')
        ->set('value', 'John Doe')
        ->set('type', 'string')
        ->call('addMetadata')
        ->assertDispatched('workflow-updated');
});

it('dispatches workflow-updated event after deleting metadata', function () {
    $this->workflow->update([
        'meta' => [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('deleteMetadata', 'author')
        ->assertDispatched('workflow-updated');
});

it('formats array values correctly for editing', function () {
    $arrayData = ['name' => 'test', 'value' => 123];

    $this->workflow->update([
        'meta' => [
            'config' => [
                'value' => $arrayData,
                'type' => 'array',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('editMetadata', 'config')
        ->assertSet('value', json_encode($arrayData, JSON_PRETTY_PRINT));
});

it('formats boolean values correctly for editing', function () {
    $this->workflow->update([
        'meta' => [
            'is_active' => [
                'value' => true,
                'type' => 'boolean',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->call('editMetadata', 'is_active')
        ->assertSet('value', '1');
});

it('loads metadata on mount', function () {
    $this->workflow->update([
        'meta' => [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
        ],
    ]);

    Livewire::test(ManageWorkflowMetadata::class, ['workflow' => $this->workflow])
        ->assertSet('metadata', [
            'author' => [
                'value' => 'John Doe',
                'type' => 'string',
            ],
        ]);
});
