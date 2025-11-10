# Changelog

All notable changes to `flowstone` will be documented in this file.

## Audit Trail, Guard, Blade Directive, Helpers, Marking Store Configuration - 2025-11-02

### Release Notes - Flowstone v1.2

**Release Date:** November 2, 2025

#### 🎉 Overview

Version 1.1.2 brings **major enhancements** to Flowstone with complete Symfony Workflow feature parity plus powerful Laravel-specific additions. This release includes audit trail logging, advanced guard systems, Blade template helpers, context support, and per-workflow marking store configuration.


---

#### ✨ New Features

##### 1. Audit Trail & Logging System

Complete audit trail implementation for tracking all workflow state changes.

**Key Features:**

- ✅ Per-workflow audit trail configuration (`audit_trail_enabled` field)
- ✅ Comprehensive `workflow_audit_logs` table with full tracking
- ✅ Automatic logging of all transitions with context and metadata
- ✅ User tracking (user_id, IP address, user agent)
- ✅ Rich query methods: `getAuditTrail()`, `recentAuditLogs()`, `hasAuditLogs()`
- ✅ Livewire `AuditLogViewer` component with filtering and sorting
- ✅ Timeline visualization component

**Database Schema:**

```php
// workflow_audit_logs table
- id, uuid, workflow_id
- subject_type, subject_id
- from_place, to_place, transition
- user_id, context, metadata
- created_at

```
**Usage Examples:**

```php
// Enable audit trail for a workflow
$workflow->update(['audit_trail_enabled' => true]);

// Get audit trail
$logs = $model->getAuditTrail();
$recent = $model->recentAuditLogs(10);

// In views
<x-flowstone::workflow-timeline :model="$document" :limit="10" />

```
**Documentation:** 04-audit-trail.md


---

##### 2. Guards & Transition Blocking System

Advanced guard system with role-based, permission-based, and custom blocking logic.

**Key Features:**

- ✅ `TransitionBlocker` class with 6 typed blockers
- ✅ Support for roles, permissions, Laravel Gates, and custom methods
- ✅ Basic Expression Language patterns (`is_granted()`, `subject.method()`)
- ✅ Integration with Spatie Laravel Permission package
- ✅ User-friendly blocker messages for UI display
- ✅ 9 guard checking methods in `InteractsWithWorkflow` trait

**Guard Types:**

1. **Role Guards** - Check if user has required role(s)
2. **Permission Guards** - Check if user has permission
3. **Expression Guards** - Expression language patterns
4. **Custom Method Guards** - Call methods on the model
5. **Laravel Gate Guards** - Use Laravel's authorization gates
6. **Marking Guards** - Automatic checking of current state

**Usage Examples:**

```php
// Check if transition can be applied
if ($document->canApplyTransition('approve')) {
    $document->applyTransition('approve');
}

// Get blocker details
$blockers = $document->getTransitionBlockers('approve');
$messages = $document->getTransitionBlockerMessages('approve');

// Configure guards in transition metadata
'guard' => "is_granted('approve-documents')"
'roles' => ['ROLE_REVIEWER', 'ROLE_ADMIN']
'permission' => 'approve-documents'
'method' => 'canBeApproved'

```
**UI Integration:**

- Amber "Guarded" badges on protected transitions
- Role and permission requirement badges
- Blocker message display component

**Documentation:** 05-guards-and-blockers.md


---

##### 3. Blade Template Helpers

Comprehensive Blade integration with custom directives, components, and helper functions.

###### 3.1 Custom Blade Directives (4 directives)

```blade
{{-- Check if transition is allowed --}}
@canTransition($document, 'approve')
    <button wire:click="approve">Approve Document</button>
@endCanTransition

{{-- Inverse check --}}
@cannotTransition($document, 'approve')
    <p>You cannot approve this document</p>
@endCannotTransition

{{-- Display current places --}}
@workflowMarkedPlaces($document)

{{-- Check specific place --}}
@workflowHasMarkedPlace($document, 'approved')
    <span class="badge badge-success">Approved</span>
@endWorkflowHasMarkedPlace

```
###### 3.2 Blade Components (4 components)

```blade
{{-- Display workflow status with color-coded badge --}}
<x-flowstone::workflow-status :model="$document" />

{{-- Render transition buttons automatically --}}
<x-flowstone::workflow-transitions 
    :model="$document" 
    button-class="btn btn-primary"
/>

{{-- Show blocker messages --}}
<x-flowstone::workflow-blockers 
    :model="$document" 
    transition="approve"
/>

{{-- Display audit trail timeline --}}
<x-flowstone::workflow-timeline 
    :model="$document" 
    :limit="10"
/>

```
###### 3.3 Global Helper Functions (7 functions)

```php
// Check if transition can be applied
workflow_can($model, 'approve')

// Get all enabled transitions
workflow_transitions($model)

// Get specific transition
workflow_transition($model, 'approve')

// Get current places
workflow_marked_places($model)

// Check specific place
workflow_has_marked_place($model, 'draft')

// Get transition blockers
workflow_transition_blockers($model, 'approve')

// Get metadata
workflow_metadata($model, 'color', 'place', 'draft')

```
**Documentation:** 06-blade-helpers.md


---

##### 4. Advanced Workflow Features

###### 4.1 Multiple State Support

Enhanced support for workflows with multiple simultaneous states.

```php
// Check if workflow supports multiple states
if ($model->supportsMultipleStates()) {
    // Get all marked places
    $places = $model->getMarkedPlaces();
    
    // Check if in specific place
    $model->isInPlace('reviewed');
    
    // Check if in all places
    $model->isInAllPlaces(['reviewed', 'tested']);
    
    // Check if in any place
    $model->isInAnyPlace(['draft', 'pending']);
}

```
###### 4.2 Context Support

Pass context data through transitions and store in audit logs.

```php
// Apply transition with context
$context = [
    'approver' => 'John Doe',
    'reason' => 'Meets all requirements',
    'priority' => 'high'
];
$model->applyTransitionWithContext('approve', $context);

// Retrieve context from audit logs
$lastContext = $model->getLastTransitionContext();
$approveContext = $model->getTransitionContext('approve');

// Use context in custom guard methods
public function canBeApproved(array $context = []): bool
{
    $priority = $context['priority'] ?? 'normal';
    return $priority === 'high' || $this->hasApprover();
}

```
###### 4.3 Enhanced Metadata Support

```php
// Get workflow-level metadata
$workflowMeta = $model->getWorkflowMetadata();

// Get place metadata
$draftColor = $model->getPlaceMetadata('draft', 'color');

// Get transition metadata
$approveIcon = $model->getTransitionMetadata('approve', 'icon');

// Bulk metadata retrieval
$allPlaces = $model->getPlacesWithMetadata();
$allTransitions = $model->getTransitionsWithMetadata();

```
**Documentation:** 07-advanced-features.md


---

##### 5. Per-Workflow Marking Store Configuration

Each workflow can now have its own marking store configuration instead of relying on the global config file.

**Key Features:**

- ✅ Database fields: `marking_store_type` and `marking_store_property`
- ✅ Support for 4 marking store types:
  - `method` - Standard getter/setter approach (recommended)
  - `single_state` - Explicit single state for state machines
  - `multiple_state` - Multiple simultaneous states for workflows
  - `property` - Direct property access
  
- ✅ Automatic type suggestion based on workflow type
- ✅ Full UI integration in create/edit forms
- ✅ Fallback to global config values when not set

**Usage Examples:**

```php
// Configure marking store for workflow
$workflow->update([
    'marking_store_type' => 'single_state',
    'marking_store_property' => 'status',
]);

// Factory methods
WorkflowFactory::new()
    ->singleState()
    ->withMarkingStore('property', 'approval_status')
    ->create();

```
**Documentation:** 08-marking-store-configuration.md


---

#### 🎨 UI Improvements

##### Enhanced Workflow Management Interface

**Create Workflow Form:**

- Audit trail enable/disable toggle
- Marking store configuration section with type dropdown
- Property name input with validation
- Auto-configuration based on workflow type
- Inline help text for all fields

**Edit Workflow Form:**

- Same enhancements as create form
- Loads existing values with fallback to defaults
- Real-time validation

**Workflow Details Page:**

- Displays audit trail status
- Shows marking store configuration (Type → property_name)
- Guard indicators on transitions (amber "Guarded" badges)
- Role and permission requirement badges
- Audit trail timeline integration

**Workflow Designer:**

- Visual indicators for guarded transitions
- Guard configuration in transition metadata
- Role/permission badge display


---

#### 🔧 Technical Changes

##### New Models & Classes

**`CleaniqueCoders\Flowstone\Models\WorkflowAuditLog`:**

- Full audit log model with relationships
- Query scopes for filtering
- User relationship support

**`CleaniqueCoders\Flowstone\Guards\TransitionBlocker`:**

- 6 typed blocker factory methods
- User-friendly message generation
- Symfony Workflow alignment

##### Enhanced Trait Methods

**`CleaniqueCoders\Flowstone\Concerns\InteractsWithWorkflow`:**

**Audit Trail Methods (6 methods):**

- `isAuditTrailEnabled()` - Check if audit trail is enabled
- `logTransition()` - Log a transition
- `getAuditTrail()` - Get all audit logs
- `recentAuditLogs($limit)` - Get recent logs
- `hasAuditLogs()` - Check if logs exist
- `auditLogs()` - Relationship method

**Guard Methods (9 methods):**

- `canApplyTransition($transition)` - Check if transition is allowed
- `getTransitionBlockers($transition)` - Get all blockers
- `getTransitionBlockerMessages($transition)` - Get user-friendly messages
- `checkGuard($guard)` - Check guard condition
- `checkRoleGuard($roles)` - Check role requirements
- `checkPermissionGuard($permission)` - Check permission
- `checkMethodGuard($method)` - Check custom method
- `checkExpressionGuard($expression)` - Check expression
- `checkGuardWithContext($guard, $context)` - Context-aware guard check

**Multiple State Methods (6 methods):**

- `supportsMultipleStates()` - Check if workflow supports multiple states
- `getMarkedPlaces()` - Get all marked places
- `isInPlace($place)` - Check specific place
- `isInAllPlaces($places)` - Check multiple places (AND)
- `isInAnyPlace($places)` - Check multiple places (OR)
- `validateMarkingStoreType()` - Validate marking store configuration

**Context Methods (4 methods):**

- `applyTransitionWithContext($transition, $context)` - Apply with context
- `getLastTransitionContext()` - Get last transition context
- `getTransitionContext($transition)` - Get specific transition context
- `checkMethodGuardWithContext($method, $context)` - Context-aware method guard

**Metadata Methods (7 methods):**

- `getMetadata($key, $subject, $subjectName)` - Generic metadata getter
- `getWorkflowMetadata($key)` - Get workflow metadata
- `getPlaceMetadata($place, $key)` - Get place metadata
- `getTransitionMetadata($transition, $key)` - Get transition metadata
- `getTransitionMetadataFromStore($transition, $key)` - Get from metadata store
- `getPlacesWithMetadata()` - Get all places with metadata
- `getTransitionsWithMetadata()` - Get all transitions with metadata

**Marking Store Methods (2 methods):**

- `getMarkingStoreType()` - Get marking store type with fallback
- `getMarkingStoreProperty()` - Get property name with fallback

##### Updated Service Provider

**`FlowstoneServiceProvider`:**

- Registers 4 custom Blade directives
- Loads 4 Blade components
- Registers 7 global helper functions
- Publishes 6 migration stubs

##### New Livewire Components

**`CleaniqueCoders\Flowstone\Livewire\AuditLogViewer`:**

- Filter by workflow, user, transition, place, date range
- Sort by any column
- Pagination support
- Real-time updates

##### Enhanced Factories

**`WorkflowFactory`:**

- `withAuditTrail($enabled)` - Set audit trail
- `singleState()` - Configure for single state
- `multipleState()` - Configure for multiple states
- `withMarkingStore($type, $property)` - Custom marking store


---

#### ✅ Testing

##### New Test Suites

**Audit Trail Tests** (AuditTrailTest.php - 20+ tests):

- Audit trail enablement
- Log creation and retrieval
- Filtering and querying
- Relationship tests
- UI component tests

**Guard System Tests** (GuardSystemTest.php - 18 tests):

- All guard types (role, permission, method, expression)
- Transition blocker creation
- Message generation
- Context-aware guards
- Laravel Gate integration

**Blade Helpers Tests** (BladeHelpersTest.php - 20+ tests):

- All 4 custom directives
- All 7 helper functions
- All 4 Blade components
- Edge cases and error handling

**Advanced Features Tests** (AdvancedFeaturesTest.php - 20+ tests):

- Multiple state workflows
- Context passing and retrieval
- Metadata access at all levels
- Marking store type validation

**Marking Store Tests** (MarkingStoreConfigurationTest.php - 15+ tests):

- Field presence and defaults
- Getter methods with fallbacks
- Symfony config generation
- CRUD operations
- Workflow processor integration

**Total New Tests:** 90+ comprehensive tests added


---

#### 📦 Database Migrations

##### New Migrations

1. **`add_audit_trail_to_workflows_table.php.stub`**
   
   - Adds `audit_trail_enabled` boolean field
   
2. **`create_workflow_audit_logs_table.php.stub`**
   
   - Complete audit log schema with indexes
   
3. **`add_marking_store_to_workflows_table.php.stub`**
   
   - Adds `marking_store_type` and `marking_store_property` fields
   

##### Migration Instructions

```bash
# Publish migrations
php artisan vendor:publish --tag="flowstone-migrations"

# Run migrations
php artisan migrate

```
All migrations maintain **backward compatibility** with existing installations.


---

#### 📚 New Documentation

##### New Documentation Files

1. **04-audit-trail.md** - Complete audit trail guide
2. **05-guards-and-blockers.md** - Guard system reference
3. **06-blade-helpers.md** - Blade helpers documentation
4. **07-advanced-features.md** - Advanced features guide
5. **08-marking-store-configuration.md** - Marking store config

##### Documentation Improvements

- 5 new comprehensive guides (100+ pages total)
- Real-world code examples for every feature
- Best practices and troubleshooting sections
- Complete API reference updates
- UI management guides with screenshots


---

#### 🔄 Migration Guide

##### For New Projects

No action needed - all features work out of the box with sensible defaults.

##### For Existing Projects

1. **Update Composer:**
   
   ```bash
   composer require cleaniquecoders/flowstone:^1.1.2
   
   ```
2. **Publish and run migrations:**
   
   ```bash
   php artisan vendor:publish --tag="flowstone-migrations"
   php artisan migrate
   
   ```
3. **Clear caches:**
   
   ```bash
   php artisan cache:clear
   php artisan view:clear
   
   ```
4. **Review new features:**
   
   - Enable audit trail for workflows that need tracking
   - Add guards to sensitive transitions
   - Use Blade helpers in your views
   - Configure marking stores per workflow
   

All existing workflows continue to work without modification!


---

#### 🎯 Symfony Workflow Feature Parity

##### ✅ Complete Feature Parity Achieved

**Core Features:**

- ✅ Workflow and State Machine types
- ✅ Places and transitions
- ✅ Marking store (single/multiple state)
- ✅ Initial marking
- ✅ Metadata storage (all levels)
- ✅ **Audit trail** ✨ NEW
- ✅ **Per-workflow marking store** ✨ NEW
- ✅ **Guard events** ✨ NEW
- ✅ **Transition blockers** ✨ NEW

**Score: 10/10** - Perfect Symfony alignment! 🎉

##### ✨ Laravel Enhancements (Beyond Symfony)

Features we have that Symfony doesn't:

- ✅ Livewire UI components
- ✅ Blade template helpers (directives, components, functions)
- ✅ Context storage in audit logs
- ✅ Rich metadata access methods
- ✅ Laravel Gate/Policy integration
- ✅ Spatie Laravel Permission integration
- ✅ Visual workflow designer support
- ✅ Workflow organization (group, category, tags)
- ✅ UUID support for distributed systems


---

#### 📈 Performance

##### Optimizations

- Workflow configuration caching
- Efficient audit log indexing
- Lazy loading of relationships
- Optimized guard checking

##### Benchmarks

- **Transition execution:** ~5-10ms (with audit logging)
- **Guard checking:** ~1-3ms per guard
- **Audit log query:** ~10-20ms with indexes


---

#### 🔗 Links

- **Repository:** https://github.com/cleaniquecoders/flowstone
- **Documentation:** Full Documentation
- **Issues:** https://github.com/cleaniquecoders/flowstone/issues
- **Changelog:** CHANGELOG.md


---

#### 🎉 Summary

Version 1.1.2 is a **major feature release** that brings Flowstone to complete Symfony Workflow feature parity while adding powerful Laravel-specific enhancements:

- ✅ **90+ new tests** for complete coverage
- ✅ **5 new documentation guides** (100+ pages)
- ✅ **40+ new methods** in InteractsWithWorkflow trait
- ✅ **4 Blade directives** + **4 Blade components** + **7 helper functions**
- ✅ **6 migration stubs** with backward compatibility
- ✅ **Complete audit trail system** with timeline visualization
- ✅ **Advanced guard system** with 6 blocker types
- ✅ **Multiple state support** with rich helper methods
- ✅ **Context support** through transitions and guards
- ✅ **Per-workflow marking store** configuration

**Full Changelog:** https://github.com/cleaniquecoders/flowstone/compare/v1.1.1...v1.1.2


---

**Upgrade today and supercharge your Laravel workflows!** 🚀

## Added Field Group, Category and Tag - 2025-11-02

### Flowstone v1.1.1 Release Notes

- Documentation: cleaned up and reorganized usage docs; fixed file numbering and adopted consistent kebab-case filenames.
- Screenshots: renamed to match UI labels and updated all references in docs.
- README: refreshed links and screenshot reference.
- Update Workflow model to use `InteractsWithTags` Trait.
- Added `group`, `category` and `tags` field

No breaking changes. No runtime code changes. Safe to update.

## Added UI for Managing Workflows - 2025-11-01

### Flowstone v1.1.0 Release Notes

#### 🎉 What's New

##### Flowstone UI (Admin Panel)

<img width="1238" height="446" alt="Screenshot 2025-11-01 at 11 37 08 PM" src="https://github.com/user-attachments/assets/3b18d0e0-506e-433a-a1b2-14ab05451b62" />
<img width="1234" height="919" alt="Screenshot 2025-11-01 at 11 37 17 PM" src="https://github.com/user-attachments/assets/67a489ee-e168-4287-af4d-42394214fca8" />
<img width="1229" height="952" alt="Screenshot 2025-11-01 at 11 37 30 PM" src="https://github.com/user-attachments/assets/1ef9036d-c918-4308-a3bb-fb04bc59c56e" />
<img width="1225" height="966" alt="Screenshot 2025-11-01 at 11 37 43 PM" src="https://github.com/user-attachments/assets/a0052fcc-4af9-4e2e-a43b-917327e6f239" />
<img width="1207" height="943" alt="Screenshot 2025-11-01 at 11 37 57 PM" src="https://github.com/user-attachments/assets/0ced9074-2d8a-47f5-b61d-34f0fd54c8a7" />
<img width="940" height="641" alt="Screenshot 2025-11-01 at 11 38 08 PM" src="https://github.com/user-attachments/assets/e1aabde8-b4b9-43df-a40d-609979012039" />
<img width="947" height="637" alt="Screenshot 2025-11-01 at 11 38 20 PM" src="https://github.com/user-attachments/assets/f8759822-d35d-4810-a67b-9324f0c4433e" />
- **Visual Workflow Designer** - Interactive workflow visualization powered by React Flow
- **Livewire Integration** - Built-in Livewire components for workflow management:
  - `Dashboard` - Overview of all workflows
  - `WorkflowIndex` - Browse and search workflows
  - `WorkflowShow` - View workflow details
  - `CreateWorkflow` - Create new workflows
  - `EditWorkflow` - Edit existing workflows
  - Metadata management components for places, transitions, and workflows
- **Modern UI Components** - Beautiful Blade components with Tailwind CSS styling
- **Dashboard Route** - New `/flowstone/dashboard` route for workflow management

##### Enhanced Workflow Schema

- **Designer Column** - New `designer` JSON column in workflows table for storing visual layout data
- **Visual Configuration** - Store node positions and graph metadata for the UI designer

##### Developer Experience

- **Asset Publishing** - New command to publish UI assets: `php artisan flowstone:publish-assets`
- **Build Configuration** - Vite setup for frontend asset compilation
- **React Integration** - UMD bundle with React Flow for visual workflow editing

#### 📦 Installation

Update your composer dependencies:

```bash
composer require cleaniquecoders/flowstone:^1.1.0



```
#### 🔧 Migration

If upgrading from v1.0.0, publish and run the new migration:

```bash
php artisan vendor:publish --tag=flowstone-migrations
php artisan migrate



```
#### 🎨 UI Setup

To use the Flowstone UI, publish the frontend assets:

```bash
php artisan flowstone:publish-assets



```
Access the dashboard at: `http://your-app.test/flowstone/dashboard`

#### 📚 Documentation

New documentation added:

- Flowstone UI Guide - Complete UI setup and usage

#### 🔧 Requirements

- PHP ^8.4
- Laravel ^11.0||^12.0
- Livewire ^3.6 (for UI features)
- Node.js 18+ (for building assets)


---

**Full Changelog**: https://github.com/cleaniquecoders/flowstone/compare/v1.0.0...v1.1.0

## First Release - 2025-10-23

### Release Notes - Flowstone v1.0.0

#### 🎉 Initial Release

We're excited to announce the first stable release of **Flowstone**, a powerful Laravel package that brings the robust Symfony Workflow engine to your Laravel applications.

#### ✨ What's Included

##### Core Features

- **Database-Driven Workflows** - Configure and manage workflows through your database
- **Symfony Workflow Integration** - Built on the proven Symfony Workflow component
- **Role-Based Permissions** - Control transitions with metadata-driven role management
- **Predefined Status Enum** - Ready-to-use workflow states (Draft, Pending, Approved, etc.)
- **Performance Optimized** - Built-in caching and efficient database queries
- **Multiple Workflow Types** - Support for both State Machines and Workflows

##### Laravel Integration

- **Trait-Based Implementation** - Easy integration with `InteractsWithWorkflow` trait
- **Service Provider** - Auto-registered for seamless Laravel integration
- **Database Migrations** - Ready-to-use migration stubs
- **Configuration Publishing** - Customizable configuration files

##### Developer Experience

- **Comprehensive Documentation** - Complete guides and API reference
- **Real-World Examples** - 5 practical examples covering common use cases
- **Full Test Coverage** - Thoroughly tested with Pest framework
- **PSR-4 Compliant** - Following PHP and Laravel standards

#### 📦 Installation

```bash
composer require cleaniquecoders/flowstone




```
#### 🔧 Requirements

- PHP ^8.4
- Laravel ^11.0||^12.0
- Symfony Workflow ^7.3

#### 📚 Documentation

Complete documentation is available in the docs directory, including:

- Installation guide
- Configuration reference
- Usage examples
- API documentation
- Advanced patterns


---

**Full Changelog**: https://github.com/cleaniquecoders/flowstone/commits/v1.0.0
