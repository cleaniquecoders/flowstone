<?php

namespace Workbench\Database\Seeders;

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed workflow examples
        $this->seedDocumentApprovalWorkflow();
        $this->seedEcommerceOrderWorkflow();
        $this->seedContentPublishingWorkflow();
        $this->seedBugTrackingWorkflow();
        $this->seedEmployeeOnboardingWorkflow();
    }

    /**
     * Document Approval Workflow
     */
    protected function seedDocumentApprovalWorkflow(): void
    {
        $config = [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'supports' => ['App\Models\Document'],
            'places' => [
                Status::DRAFT->value => ['metadata' => ['color' => 'gray', 'icon' => 'document']],
                'submitted' => ['metadata' => ['color' => 'blue', 'icon' => 'paper-airplane']],
                Status::UNDER_REVIEW->value => ['metadata' => ['color' => 'yellow', 'icon' => 'eye']],
                Status::APPROVED->value => ['metadata' => ['color' => 'green', 'icon' => 'check-circle']],
                Status::REJECTED->value => ['metadata' => ['color' => 'red', 'icon' => 'x-circle']],
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => 'submitted',
                    'metadata' => ['roles' => ['author'], 'label' => 'Submit for Review'],
                ],
                'start_review' => [
                    'from' => ['submitted'],
                    'to' => Status::UNDER_REVIEW->value,
                    'metadata' => ['roles' => ['reviewer', 'manager'], 'label' => 'Start Review'],
                ],
                'approve' => [
                    'from' => [Status::UNDER_REVIEW->value],
                    'to' => Status::APPROVED->value,
                    'metadata' => ['roles' => ['manager', 'admin'], 'label' => 'Approve'],
                ],
                'reject' => [
                    'from' => [Status::UNDER_REVIEW->value],
                    'to' => Status::REJECTED->value,
                    'metadata' => ['roles' => ['manager', 'admin'], 'label' => 'Reject'],
                ],
                'revise' => [
                    'from' => [Status::REJECTED->value],
                    'to' => Status::DRAFT->value,
                    'metadata' => ['roles' => ['author'], 'label' => 'Revise'],
                ],
            ],
        ];

        $workflow = Workflow::create([
            'name' => 'Document Approval',
            'description' => 'Classic document approval workflow with review and approval stages',
            'type' => 'state_machine',
            'initial_marking' => Status::DRAFT->value,
            'is_enabled' => true,
            'config' => $config,
        ]);

        // Create places
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeConfig) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => $placeConfig['metadata'] ?? null,
            ]);
        }

        // Create transitions
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionConfig) {
            foreach ($transitionConfig['from'] as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionConfig['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionConfig['metadata'] ?? null,
                ]);
            }
        }
    }

    /**
     * E-commerce Order Processing Workflow
     */
    protected function seedEcommerceOrderWorkflow(): void
    {
        $config = [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'supports' => ['App\Models\Order'],
            'places' => [
                'cart' => ['metadata' => ['color' => 'gray', 'icon' => 'shopping-cart']],
                Status::PENDING->value => ['metadata' => ['color' => 'yellow', 'icon' => 'clock']],
                'confirmed' => ['metadata' => ['color' => 'blue', 'icon' => 'check']],
                Status::IN_PROGRESS->value => ['metadata' => ['color' => 'purple', 'icon' => 'cog']],
                'shipped' => ['metadata' => ['color' => 'indigo', 'icon' => 'truck']],
                Status::COMPLETED->value => ['metadata' => ['color' => 'green', 'icon' => 'check-circle']],
                Status::CANCELLED->value => ['metadata' => ['color' => 'red', 'icon' => 'x-circle']],
                'refunded' => ['metadata' => ['color' => 'orange', 'icon' => 'refresh']],
            ],
            'transitions' => [
                'checkout' => [
                    'from' => ['cart'],
                    'to' => Status::PENDING->value,
                    'metadata' => ['roles' => ['customer'], 'label' => 'Checkout'],
                ],
                'confirm_payment' => [
                    'from' => [Status::PENDING->value],
                    'to' => 'confirmed',
                    'metadata' => ['roles' => ['system', 'admin'], 'label' => 'Confirm Payment'],
                ],
                'process' => [
                    'from' => ['confirmed'],
                    'to' => Status::IN_PROGRESS->value,
                    'metadata' => ['roles' => ['warehouse', 'admin'], 'label' => 'Start Processing'],
                ],
                'ship' => [
                    'from' => [Status::IN_PROGRESS->value],
                    'to' => 'shipped',
                    'metadata' => ['roles' => ['warehouse', 'admin'], 'label' => 'Mark as Shipped'],
                ],
                'deliver' => [
                    'from' => ['shipped'],
                    'to' => Status::COMPLETED->value,
                    'metadata' => ['roles' => ['courier', 'system'], 'label' => 'Mark as Delivered'],
                ],
                'cancel' => [
                    'from' => [Status::PENDING->value, 'confirmed', Status::IN_PROGRESS->value],
                    'to' => Status::CANCELLED->value,
                    'metadata' => ['roles' => ['customer', 'admin'], 'label' => 'Cancel Order'],
                ],
                'refund' => [
                    'from' => ['shipped', Status::COMPLETED->value],
                    'to' => 'refunded',
                    'metadata' => ['roles' => ['admin', 'support'], 'label' => 'Process Refund'],
                ],
            ],
        ];

        $workflow = Workflow::create([
            'name' => 'Order Processing',
            'description' => 'Complete e-commerce order lifecycle from cart to delivery',
            'type' => 'state_machine',
            'initial_marking' => 'cart',
            'is_enabled' => true,
            'config' => $config,
        ]);

        // Create places
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeConfig) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => $placeConfig['metadata'] ?? null,
            ]);
        }

        // Create transitions
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionConfig) {
            foreach ($transitionConfig['from'] as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionConfig['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionConfig['metadata'] ?? null,
                ]);
            }
        }
    }

    /**
     * Content Publishing Workflow
     */
    protected function seedContentPublishingWorkflow(): void
    {
        $config = [
            'type' => 'workflow',
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'supports' => ['App\Models\Article'],
            'places' => [
                Status::DRAFT->value => ['metadata' => ['color' => 'gray', 'icon' => 'document']],
                Status::UNDER_REVIEW->value => ['metadata' => ['color' => 'yellow', 'icon' => 'eye']],
                'editor_review' => ['metadata' => ['color' => 'blue', 'icon' => 'pencil']],
                'fact_check' => ['metadata' => ['color' => 'purple', 'icon' => 'shield-check']],
                Status::APPROVED->value => ['metadata' => ['color' => 'green', 'icon' => 'check']],
                'scheduled' => ['metadata' => ['color' => 'indigo', 'icon' => 'calendar']],
                'published' => ['metadata' => ['color' => 'teal', 'icon' => 'globe']],
                Status::ARCHIVED->value => ['metadata' => ['color' => 'gray', 'icon' => 'archive']],
                Status::REJECTED->value => ['metadata' => ['color' => 'red', 'icon' => 'x-circle']],
            ],
            'transitions' => [
                'submit' => [
                    'from' => [Status::DRAFT->value],
                    'to' => Status::UNDER_REVIEW->value,
                    'metadata' => ['roles' => ['author'], 'label' => 'Submit for Review'],
                ],
                'assign_editor' => [
                    'from' => [Status::UNDER_REVIEW->value],
                    'to' => 'editor_review',
                    'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Assign to Editor'],
                ],
                'request_fact_check' => [
                    'from' => ['editor_review'],
                    'to' => 'fact_check',
                    'metadata' => ['roles' => ['editor'], 'label' => 'Request Fact Check'],
                ],
                'approve_editor' => [
                    'from' => ['editor_review'],
                    'to' => Status::APPROVED->value,
                    'metadata' => ['roles' => ['editor'], 'label' => 'Approve Content'],
                ],
                'approve_fact_check' => [
                    'from' => ['fact_check'],
                    'to' => Status::APPROVED->value,
                    'metadata' => ['roles' => ['fact_checker'], 'label' => 'Verify Facts'],
                ],
                'schedule' => [
                    'from' => [Status::APPROVED->value],
                    'to' => 'scheduled',
                    'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Schedule Publication'],
                ],
                'publish' => [
                    'from' => [Status::APPROVED->value, 'scheduled'],
                    'to' => 'published',
                    'metadata' => ['roles' => ['editor', 'admin', 'system'], 'label' => 'Publish Now'],
                ],
                'archive' => [
                    'from' => ['published'],
                    'to' => Status::ARCHIVED->value,
                    'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Archive'],
                ],
                'reject' => [
                    'from' => [Status::UNDER_REVIEW->value, 'editor_review', 'fact_check'],
                    'to' => Status::REJECTED->value,
                    'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Reject'],
                ],
                'revise' => [
                    'from' => [Status::REJECTED->value],
                    'to' => Status::DRAFT->value,
                    'metadata' => ['roles' => ['author'], 'label' => 'Revise & Resubmit'],
                ],
            ],
        ];

        $workflow = Workflow::create([
            'name' => 'Content Publishing',
            'description' => 'Manage content creation, review, and publishing lifecycle',
            'type' => 'workflow',
            'initial_marking' => Status::DRAFT->value,
            'is_enabled' => true,
            'config' => $config,
        ]);

        // Create places
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeConfig) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => $placeConfig['metadata'] ?? null,
            ]);
        }

        // Create transitions
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionConfig) {
            foreach ($transitionConfig['from'] as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionConfig['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionConfig['metadata'] ?? null,
                ]);
            }
        }
    }

    /**
     * Bug Tracking Workflow
     */
    protected function seedBugTrackingWorkflow(): void
    {
        $config = [
            'type' => 'state_machine',
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'supports' => ['App\Models\Bug'],
            'places' => [
                'new' => ['metadata' => ['color' => 'blue', 'icon' => 'flag']],
                'triaged' => ['metadata' => ['color' => 'yellow', 'icon' => 'funnel']],
                Status::IN_PROGRESS->value => ['metadata' => ['color' => 'purple', 'icon' => 'code']],
                'in_testing' => ['metadata' => ['color' => 'indigo', 'icon' => 'beaker']],
                'resolved' => ['metadata' => ['color' => 'green', 'icon' => 'check-circle']],
                'closed' => ['metadata' => ['color' => 'gray', 'icon' => 'lock-closed']],
                'reopened' => ['metadata' => ['color' => 'orange', 'icon' => 'arrow-path']],
            ],
            'transitions' => [
                'triage' => [
                    'from' => ['new'],
                    'to' => 'triaged',
                    'metadata' => ['roles' => ['triage_team', 'lead'], 'label' => 'Triage'],
                ],
                'start_work' => [
                    'from' => ['triaged', 'reopened'],
                    'to' => Status::IN_PROGRESS->value,
                    'metadata' => ['roles' => ['developer'], 'label' => 'Start Work'],
                ],
                'submit_for_testing' => [
                    'from' => [Status::IN_PROGRESS->value],
                    'to' => 'in_testing',
                    'metadata' => ['roles' => ['developer'], 'label' => 'Submit for Testing'],
                ],
                'resolve' => [
                    'from' => ['in_testing'],
                    'to' => 'resolved',
                    'metadata' => ['roles' => ['tester', 'qa'], 'label' => 'Mark as Resolved'],
                ],
                'close' => [
                    'from' => ['resolved'],
                    'to' => 'closed',
                    'metadata' => ['roles' => ['lead', 'admin'], 'label' => 'Close Issue'],
                ],
                'reopen' => [
                    'from' => ['resolved', 'closed'],
                    'to' => 'reopened',
                    'metadata' => ['roles' => ['any'], 'label' => 'Reopen'],
                ],
                'fail_testing' => [
                    'from' => ['in_testing'],
                    'to' => Status::IN_PROGRESS->value,
                    'metadata' => ['roles' => ['tester', 'qa'], 'label' => 'Failed Testing'],
                ],
            ],
        ];

        $workflow = Workflow::create([
            'name' => 'Bug Tracking',
            'description' => 'Issue management workflow for software development',
            'type' => 'state_machine',
            'initial_marking' => 'new',
            'is_enabled' => true,
            'config' => $config,
        ]);

        // Create places
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeConfig) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => $placeConfig['metadata'] ?? null,
            ]);
        }

        // Create transitions
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionConfig) {
            foreach ($transitionConfig['from'] as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionConfig['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionConfig['metadata'] ?? null,
                ]);
            }
        }
    }

    /**
     * Employee Onboarding Workflow
     */
    protected function seedEmployeeOnboardingWorkflow(): void
    {
        $config = [
            'type' => 'workflow',
            'marking_store' => [
                'type' => 'method',
                'property' => 'status',
            ],
            'supports' => ['App\Models\Employee'],
            'places' => [
                'offer_sent' => ['metadata' => ['color' => 'blue', 'icon' => 'mail']],
                'offer_accepted' => ['metadata' => ['color' => 'green', 'icon' => 'hand-thumb-up']],
                'documents_pending' => ['metadata' => ['color' => 'yellow', 'icon' => 'document-text']],
                'documents_verified' => ['metadata' => ['color' => 'green', 'icon' => 'document-check']],
                'equipment_ordered' => ['metadata' => ['color' => 'purple', 'icon' => 'shopping-cart']],
                'it_setup_complete' => ['metadata' => ['color' => 'indigo', 'icon' => 'computer-desktop']],
                'training_scheduled' => ['metadata' => ['color' => 'blue', 'icon' => 'academic-cap']],
                'onboarding_complete' => ['metadata' => ['color' => 'green', 'icon' => 'check-badge']],
                'offer_declined' => ['metadata' => ['color' => 'red', 'icon' => 'x-circle']],
            ],
            'transitions' => [
                'accept_offer' => [
                    'from' => ['offer_sent'],
                    'to' => 'offer_accepted',
                    'metadata' => ['roles' => ['candidate'], 'label' => 'Accept Offer'],
                ],
                'decline_offer' => [
                    'from' => ['offer_sent'],
                    'to' => 'offer_declined',
                    'metadata' => ['roles' => ['candidate'], 'label' => 'Decline Offer'],
                ],
                'request_documents' => [
                    'from' => ['offer_accepted'],
                    'to' => 'documents_pending',
                    'metadata' => ['roles' => ['hr'], 'label' => 'Request Documents'],
                ],
                'verify_documents' => [
                    'from' => ['documents_pending'],
                    'to' => 'documents_verified',
                    'metadata' => ['roles' => ['hr'], 'label' => 'Verify Documents'],
                ],
                'order_equipment' => [
                    'from' => ['documents_verified'],
                    'to' => 'equipment_ordered',
                    'metadata' => ['roles' => ['it', 'admin'], 'label' => 'Order Equipment'],
                ],
                'complete_it_setup' => [
                    'from' => ['equipment_ordered'],
                    'to' => 'it_setup_complete',
                    'metadata' => ['roles' => ['it'], 'label' => 'Complete IT Setup'],
                ],
                'schedule_training' => [
                    'from' => ['it_setup_complete'],
                    'to' => 'training_scheduled',
                    'metadata' => ['roles' => ['hr', 'manager'], 'label' => 'Schedule Training'],
                ],
                'complete_onboarding' => [
                    'from' => ['training_scheduled'],
                    'to' => 'onboarding_complete',
                    'metadata' => ['roles' => ['hr', 'manager'], 'label' => 'Complete Onboarding'],
                ],
            ],
        ];

        $workflow = Workflow::create([
            'name' => 'Employee Onboarding',
            'description' => 'HR workflow for new employee integration process',
            'type' => 'workflow',
            'initial_marking' => 'offer_sent',
            'is_enabled' => true,
            'config' => $config,
        ]);

        // Create places
        $sortOrder = 0;
        foreach ($config['places'] as $placeName => $placeConfig) {
            $workflow->places()->create([
                'name' => $placeName,
                'sort_order' => $sortOrder++,
                'meta' => $placeConfig['metadata'] ?? null,
            ]);
        }

        // Create transitions
        $sortOrder = 0;
        foreach ($config['transitions'] as $transitionName => $transitionConfig) {
            foreach ($transitionConfig['from'] as $fromPlace) {
                $workflow->transitions()->create([
                    'name' => $transitionName,
                    'from_place' => $fromPlace,
                    'to_place' => $transitionConfig['to'],
                    'sort_order' => $sortOrder++,
                    'meta' => $transitionConfig['metadata'] ?? null,
                ]);
            }
        }
    }
}
