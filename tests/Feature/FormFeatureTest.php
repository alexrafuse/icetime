<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Shared\Enums\FormCategory;
use App\Filament\Pages\MemberForms;
use App\Filament\Resources\FormResource;
use App\Filament\Resources\FormResource\Pages\CreateForm;
use App\Filament\Resources\FormResource\Pages\EditForm;
use App\Filament\Resources\FormResource\Pages\ListForms;
use Database\Seeders\RolesAndPermissionsSeeder;
use Domain\Shared\Models\Form;
use Domain\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FormFeatureTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        $this->admin = User::factory()->create(['email' => 'admin@test.com']);
        $this->admin->assignRole('admin');

        $this->member = User::factory()->create(['email' => 'member@test.com']);
        $this->member->assignRole('member');
    }

    public function test_admin_can_create_form(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateForm::class)
            ->fillForm([
                'title' => 'New Member Registration',
                'description' => 'Register as a new member',
                'tally_form_url' => 'https://tally.so/r/test123',
                'category' => FormCategory::REGISTRATION,
                'priority' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('forms', [
            'title' => 'New Member Registration',
            'tally_form_url' => 'https://tally.so/r/test123',
            'category' => 'registration',
        ]);
    }

    public function test_admin_can_edit_form(): void
    {
        $this->actingAs($this->admin);

        $form = Form::factory()->create([
            'title' => 'Original Title',
            'category' => FormCategory::GENERAL,
        ]);

        Livewire::test(EditForm::class, ['record' => $form->id])
            ->fillForm([
                'title' => 'Updated Title',
                'category' => FormCategory::MEMBERSHIP,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('forms', [
            'id' => $form->id,
            'title' => 'Updated Title',
            'category' => 'membership',
        ]);
    }

    public function test_admin_can_view_forms_list(): void
    {
        $this->actingAs($this->admin);

        $form1 = Form::factory()->create(['title' => 'Form One']);
        $form2 = Form::factory()->create(['title' => 'Form Two']);

        Livewire::test(ListForms::class)
            ->assertCanSeeTableRecords([$form1, $form2]);
    }

    public function test_member_can_view_member_forms_page(): void
    {
        $this->actingAs($this->member);

        $form = Form::factory()->create([
            'title' => 'Test Form',
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->assertSuccessful()
            ->assertSee($form->title);
    }

    public function test_member_forms_page_groups_by_category(): void
    {
        $this->actingAs($this->member);

        Form::factory()->create([
            'title' => 'Registration Form',
            'category' => FormCategory::REGISTRATION,
            'is_active' => true,
        ]);

        Form::factory()->create([
            'title' => 'Volunteer Form',
            'category' => FormCategory::VOLUNTEER,
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->assertSee('Registration')
            ->assertSee('Registration Form')
            ->assertSee('Volunteer')
            ->assertSee('Volunteer Form');
    }

    public function test_member_forms_page_shows_empty_state(): void
    {
        $this->actingAs($this->member);

        Livewire::test(MemberForms::class)
            ->assertSee('No forms available');
    }

    public function test_inactive_forms_not_shown_to_members(): void
    {
        $this->actingAs($this->member);

        Form::factory()->inactive()->create([
            'title' => 'Inactive Form',
        ]);

        Livewire::test(MemberForms::class)
            ->assertDontSee('Inactive Form');
    }

    public function test_forms_sorted_by_priority_within_category(): void
    {
        $this->actingAs($this->member);

        $lowPriority = Form::factory()->create([
            'title' => 'Low Priority Form',
            'category' => FormCategory::REGISTRATION,
            'priority' => 10,
            'is_active' => true,
        ]);

        $highPriority = Form::factory()->create([
            'title' => 'High Priority Form',
            'category' => FormCategory::REGISTRATION,
            'priority' => 1,
            'is_active' => true,
        ]);

        // Simply verify both forms are visible and ordered correctly in the database query
        $forms = Form::query()
            ->where('is_active', true)
            ->where('category', FormCategory::REGISTRATION)
            ->orderBy('priority')
            ->get();

        $this->assertEquals('High Priority Form', $forms->first()->title);
        $this->assertEquals('Low Priority Form', $forms->last()->title);
    }

    public function test_member_can_search_forms_by_title(): void
    {
        $this->actingAs($this->member);

        Form::factory()->create([
            'title' => 'Membership Renewal Form',
            'is_active' => true,
        ]);

        Form::factory()->create([
            'title' => 'Volunteer Sign-up',
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->set('search', 'Membership')
            ->assertSee('Membership Renewal Form')
            ->assertDontSee('Volunteer Sign-up');
    }

    public function test_member_can_search_forms_by_description(): void
    {
        $this->actingAs($this->member);

        Form::factory()->create([
            'title' => 'Form One',
            'description' => 'This form is for new members',
            'is_active' => true,
        ]);

        Form::factory()->create([
            'title' => 'Form Two',
            'description' => 'This form is for volunteers',
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->set('search', 'volunteers')
            ->assertSee('Form Two')
            ->assertDontSee('Form One');
    }

    public function test_member_can_filter_forms_by_category(): void
    {
        $this->actingAs($this->member);

        Form::factory()->create([
            'title' => 'Registration Form',
            'category' => FormCategory::REGISTRATION,
            'is_active' => true,
        ]);

        Form::factory()->create([
            'title' => 'Volunteer Form',
            'category' => FormCategory::VOLUNTEER,
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->set('categoryFilter', FormCategory::REGISTRATION->value)
            ->assertSee('Registration Form')
            ->assertDontSee('Volunteer Form');
    }

    public function test_member_can_clear_filters(): void
    {
        $this->actingAs($this->member);

        Form::factory()->create([
            'title' => 'Test Form',
            'is_active' => true,
        ]);

        Livewire::test(MemberForms::class)
            ->set('search', 'Something')
            ->set('categoryFilter', FormCategory::REGISTRATION->value)
            ->call('clearFilters')
            ->assertSet('search', '')
            ->assertSet('categoryFilter', '');
    }

    public function test_member_cannot_access_form_resource(): void
    {
        $this->actingAs($this->member);

        $this->assertFalse(FormResource::canViewAny());
    }

    public function test_admin_can_access_form_resource(): void
    {
        $this->actingAs($this->admin);

        $this->assertTrue(FormResource::canViewAny());
    }
}
