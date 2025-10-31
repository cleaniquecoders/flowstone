<?php

namespace Workbench\Database\Seeders;

use CleaniqueCoders\Flowstone\Enums\Status;
use CleaniqueCoders\Flowstone\Models\Workflow;
use Illuminate\Database\Seeder;
use Workbench\Database\Factories\UserFactory;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create test users with different roles
        $admin = UserFactory::new()->create([
            'name' => 'Admin User',
            'email' => 'admin@flowstone.test',
        ]);

        $manager = UserFactory::new()->create([
            'name' => 'Manager User',
            'email' => 'manager@flowstone.test',
        ]);

        $reviewer = UserFactory::new()->create([
            'name' => 'Reviewer User',
            'email' => 'reviewer@flowstone.test',
        ]);

        $author = UserFactory::new()->create([
            'name' => 'Author User',
            'email' => 'author@flowstone.test',
        ]);

        $customer = UserFactory::new()->create([
            'name' => 'Customer User',
            'email' => 'customer@flowstone.test',
        ]);

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
        Workflow::create([
            'name' => 'Document Approval',
            'description' => 'Classic document approval workflow with review and approval stages',
            'type' => 'state_machine',
            'initial_marking' => Status::DRAFT->value,
            'is_enabled' => true,
            'config' => [
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
            ],
        ]);
    }

    /**
     * E-commerce Order Processing Workflow
     */
    protected function seedEcommerceOrderWorkflow(): void
    {
        Workflow::create([
            'name' => 'Order Processing',
            'description' => 'Complete e-commerce order lifecycle from cart to delivery',
            'type' => 'state_machine',
            'initial_marking' => 'cart',
            'is_enabled' => true,
            'config' => [
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
            ],
        ]);
    }

    /**
     * Content Publishing Workflow
     */
    protected function seedContentPublishingWorkflow(): void
    {
        Workflow::create([
            'name' => 'Content Publishing',
            'description' => 'Editorial workflow for blog posts and articles',
            'type' => 'state_machine',
            'initial_marking' => Status::DRAFT->value,
            'is_enabled' => true,
            'config' => [
                'type' => 'state_machine',
                'marking_store' => [
                    'type' => 'method',
                    'property' => 'status',
                ],
                'supports' => ['App\Models\Post'],
                'places' => [
                    Status::DRAFT->value => ['metadata' => ['color' => 'gray', 'icon' => 'pencil']],
                    'ready_for_review' => ['metadata' => ['color' => 'blue', 'icon' => 'document-check']],
                    'in_review' => ['metadata' => ['color' => 'yellow', 'icon' => 'eye']],
                    'scheduled' => ['metadata' => ['color' => 'purple', 'icon' => 'calendar']],
                    'published' => ['metadata' => ['color' => 'green', 'icon' => 'globe']],
                    Status::ARCHIVED->value => ['metadata' => ['color' => 'gray', 'icon' => 'archive']],
                ],
                'transitions' => [
                    'submit' => [
                        'from' => [Status::DRAFT->value],
                        'to' => 'ready_for_review',
                        'metadata' => ['roles' => ['writer'], 'label' => 'Submit for Review'],
                    ],
                    'review' => [
                        'from' => ['ready_for_review'],
                        'to' => 'in_review',
                        'metadata' => ['roles' => ['editor'], 'label' => 'Start Review'],
                    ],
                    'request_changes' => [
                        'from' => ['in_review'],
                        'to' => Status::DRAFT->value,
                        'metadata' => ['roles' => ['editor'], 'label' => 'Request Changes'],
                    ],
                    'schedule' => [
                        'from' => ['in_review'],
                        'to' => 'scheduled',
                        'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Schedule Publication'],
                    ],
                    'publish' => [
                        'from' => ['in_review', 'scheduled'],
                        'to' => 'published',
                        'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Publish Now'],
                    ],
                    'unpublish' => [
                        'from' => ['published'],
                        'to' => Status::DRAFT->value,
                        'metadata' => ['roles' => ['editor', 'admin'], 'label' => 'Unpublish'],
                    ],
                    'archive' => [
                        'from' => ['published'],
                        'to' => Status::ARCHIVED->value,
                        'metadata' => ['roles' => ['admin'], 'label' => 'Archive'],
                    ],
                ],
            ],
        ]);
    }

    /**
     * Bug Tracking Workflow
     */
    protected function seedBugTrackingWorkflow(): void
    {
        Workflow::create([
            'name' => 'Bug Tracking',
            'description' => 'Issue management workflow for software development',
            'type' => 'state_machine',
            'initial_marking' => 'new',
            'is_enabled' => true,
            'config' => [
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
            ],
        ]);
    }

    /**
     * Employee Onboarding Workflow
     */
    protected function seedEmployeeOnboardingWorkflow(): void
    {
        Workflow::create([
            'name' => 'Employee Onboarding',
            'description' => 'HR workflow for new employee integration process',
            'type' => 'workflow',
            'initial_marking' => 'offer_sent',
            'is_enabled' => true,
            'config' => [
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
            ],
        ]);
    }
}
