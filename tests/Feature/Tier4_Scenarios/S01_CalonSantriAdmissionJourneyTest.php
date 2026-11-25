<?php

namespace Tests\Feature\Tier4_Scenarios;

use App\Mail\RegisterSuccessMail;
use App\Models\Informasi;
use App\Models\Kontak;
use App\Models\Pendaftaran;
use App\Models\StatusForm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\Support\CreatesPpdbFixtures;
use Tests\Support\CreatesTestUsers;
use Tests\Support\MocksFonnteGateway;
use Tests\TestCase;

/**
 * Tier 4 Scenario Test: S01 Calon Santri Admission Journey
 *
 * Source: dokum-ppdb §5.1, §5.2, §6.2, §6.4, §8.1, §8.2, Build Plan §7, §16.2
 * Verifies realistic end-to-end user journeys for prospective students (calon santri):
 * - Journey 1: Guest visits CMS -> submits registration with 10 files -> receives auto credentials & email ->
 *              logs in -> views dashboard -> downloads own files -> admin verifies 'Lulus' with WhatsApp ->
 *              applicant views 'Lulus' status.
 * - Journey 2: Registration -> Admin verifies 'Tidak Lulus' -> applicant views status ->
 *              downloads own files safely -> blocked from downloading other applicant's files (IDOR protection).
 */
#[\PHPUnit\Framework\Attributes\Group('retired-ppdb')]
class S01_CalonSantriAdmissionJourneyTest extends TestCase
{
    use CreatesPpdbFixtures, CreatesTestUsers, MocksFonnteGateway, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Mail::fake();
        $this->mockFonnteSuccess();
        $this->createStatusForm('Buka');
    }

    /**
     * 1. Successful Calon Santri Full Lifecycle Admission Journey.
     */
    public function test_successful_calon_santri_full_lifecycle_admission_journey(): void
    {
        // 1. Guest explores public CMS portal
        Kontak::create([
            'alamat' => 'Jl. Imogiri Timur KM 10, Bantul',
            'telepon' => '081234567890',
            'email' => 'info@ibnujuraimi.sch.id',
        ]);
        Informasi::create([
            'judul' => 'Jadwal PPDB 2026/2027 Telah Dibuka',
            'isi' => 'Pendaftaran gelombang 1 dibuka mulai hari ini.',
        ]);

        $homeResponse = $this->get(route('home'));
        $homeResponse->assertStatus(200);

        $infoResponse = $this->get(route('informasi'));
        $infoResponse->assertStatus(200);
        $infoResponse->assertSee('Jadwal PPDB 2026/2027');

        $contactResponse = $this->get(route('contact'));
        $contactResponse->assertStatus(200);
        $contactResponse->assertSee('081234567890');

        // 2. Guest visits registration page
        $formPageResponse = $this->get(route('pendaftaran'));
        $formPageResponse->assertStatus(200);

        // 3. Guest submits complete registration form with 10 documents
        $applicantEmail = 'ahmad.fauzan_'.uniqid().'@example.com';
        $applicantPhone = '081211223344';
        $files = $this->fakePpdbFiles();
        $formData = $this->validPpdbFormData([
            'nama_lengkap' => 'Ahmad Fauzan Al-Ghifari',
            'email' => $applicantEmail,
            'no_hp' => $applicantPhone,
        ]);

        $submitResponse = $this->post(route('pendaftaran.store'), array_merge($formData, $files));
        $submitResponse->assertRedirect(route('register.success'));

        // 4. View one-time credentials page
        $successPageResponse = $this->get(route('register.success'));
        $successPageResponse->assertStatus(200);

        // Verify account creation in database
        $user = User::where('email', $applicantEmail)->firstOrFail();
        $this->assertTrue($user->hasRole('calon_santri'));

        // Verify Pendaftaran record created
        $pendaftaran = Pendaftaran::where('email', $applicantEmail)->firstOrFail();
        $this->assertEquals('Menunggu', $pendaftaran->status);
        $this->assertEquals($user->id, $pendaftaran->user_id);

        // 5. Verify credential delivery email
        Mail::assertSent(RegisterSuccessMail::class, function ($mail) use ($applicantEmail) {
            return $mail->hasTo($applicantEmail);
        });

        // 6. Applicant logs into student portal
        $dashboardResponse = $this->actingAs($user)->get(route('user.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Ahmad Fauzan Al-Ghifari');
        $dashboardResponse->assertSee('Menunggu');

        // 7. Applicant downloads their own uploaded file
        $fileDownloadResponse = $this->actingAs($user)->get(
            route('user.file', ['id' => $pendaftaran->id, 'field' => 'pas_photo'])
        );
        $fileDownloadResponse->assertStatus(200);

        // 8. Admin reviews documents and updates status to 'Lulus'
        $admin = $this->createAdmin();
        $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftaran->id), [
            'status' => 'Lulus',
            'catatan' => 'Selamat, Anda dinyatakan Lulus Seleksi Tahfidz.',
        ])->assertSessionHasNoErrors();

        // Verify WhatsApp notification was sent to applicant
        $this->assertFonnteSent($applicantPhone, 'Lulus');

        // 9. Applicant returns to dashboard and confirms 'Lulus' status
        $updatedDashboardResponse = $this->actingAs($user)->get(route('user.dashboard'));
        $updatedDashboardResponse->assertStatus(200);
        $updatedDashboardResponse->assertSee('Lulus');
    }

    /**
     * 2. Rejected Calon Santri Lifecycle with Document Retrieval and IDOR Protection.
     */
    public function test_rejected_calon_santri_lifecycle_with_document_retrieval_and_idor_protection(): void
    {
        $admin = $this->createAdmin();

        // Create applicant A
        $userA = $this->createCalonSantri(['name' => 'Calon A']);
        Storage::disk('local')->put('pendaftaran/pas_photo/a.jpg', 'doc-content-a');
        $pendaftarA = $this->createPendaftaran([
            'nama_lengkap' => 'Calon A',
            'no_hp' => '081299990001',
            'pas_photo' => 'pendaftaran/pas_photo/a.jpg',
        ], $userA);

        // Create applicant B
        $userB = $this->createCalonSantri(['name' => 'Calon B']);
        Storage::disk('local')->put('pendaftaran/pas_photo/b.jpg', 'doc-content-b');
        $pendaftarB = $this->createPendaftaran([
            'nama_lengkap' => 'Calon B',
            'pas_photo' => 'pendaftaran/pas_photo/b.jpg',
        ], $userB);

        // Admin verifies Applicant A as 'Tidak Lulus'
        $this->actingAs($admin)->post(route('admin.verifikasi', $pendaftarA->id), [
            'status' => 'Tidak Lulus',
            'catatan' => 'Mohon maaf, kuota penerimaan telah terpenuhi.',
        ])->assertSessionHasNoErrors();

        // Assert notification sent
        $this->assertFonnteSent('081299990001', 'Tidak Lulus');

        // Applicant A logs in and sees 'Tidak Lulus'
        $dashboardResponse = $this->actingAs($userA)->get(route('user.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Tidak Lulus');

        // Applicant A can still download their own documents
        $ownDocResponse = $this->actingAs($userA)->get(
            route('user.file', ['id' => $pendaftarA->id, 'field' => 'pas_photo'])
        );
        $ownDocResponse->assertStatus(200);

        // Applicant A attempts IDOR attack on Applicant B's documents
        $attackResponse = $this->actingAs($userA)->get(
            route('user.file', ['id' => $pendaftarB->id, 'field' => 'pas_photo'])
        );
        $attackResponse->assertForbidden();
    }

    /**
     * 3. Calon Santri Closed Registration Schedule Guard and Reopening Journey.
     *
     * Verifies end-to-end journey when an applicant encounters a closed registration
     * window, gets blocked from form access and direct POST bypass, and successfully
     * registers once the admin reopens the registration schedule.
     */
    public function test_calon_santri_closed_registration_schedule_guard_and_reopening_journey(): void
    {
        // 1. PPDB Registration window is closed by administration
        $statusForm = StatusForm::first();
        if ($statusForm) {
            $statusForm->update(['status' => 'Tutup']);
        } else {
            $this->createStatusForm('Tutup');
        }

        // 2. Guest visits registration page while closed -> blocked by middleware
        $closedPageResponse = $this->get(route('pendaftaran'));
        $closedPageResponse->assertRedirect(route('home'));

        // 3. Guest attempts direct POST bypass -> blocked by middleware
        $applicantEmail = 'late.applicant_'.uniqid().'@example.com';
        $formData = $this->validPpdbFormData(['email' => $applicantEmail]);
        $files = $this->fakePpdbFiles();

        $bypassAttempt = $this->post(route('pendaftaran.store'), array_merge($formData, $files));
        $bypassAttempt->assertRedirect(route('home'));
        $this->assertDatabaseMissing('users', ['email' => $applicantEmail]);
        $this->assertDatabaseMissing('pendaftaran', ['email' => $applicantEmail]);

        // 4. Admin reopens the registration schedule
        $admin = $this->createAdmin();
        $this->actingAs($admin);
        StatusForm::first()->update(['status' => 'Buka']);
        $this->post(route('logout'));

        // 5. Guest revisits registration page -> accessible
        $openPageResponse = $this->get(route('pendaftaran'));
        $openPageResponse->assertStatus(200);

        // 6. Guest completes registration submission
        $submitResponse = $this->post(route('pendaftaran.store'), array_merge($formData, $files));
        $submitResponse->assertRedirect(route('register.success'));

        // 7. Verify account and application creation
        $user = User::where('email', $applicantEmail)->firstOrFail();
        $this->assertTrue($user->hasRole('calon_santri'));
        $this->assertDatabaseHas('pendaftaran', [
            'email' => $applicantEmail,
            'status' => 'Menunggu',
            'user_id' => $user->id,
        ]);

        // 8. Applicant logs into student dashboard
        $dashboardResponse = $this->actingAs($user)->get(route('user.dashboard'));
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee('Menunggu');
    }
}
