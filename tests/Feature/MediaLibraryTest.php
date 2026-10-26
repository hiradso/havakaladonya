<?php

namespace Tests\Feature;

use App\Filament\Resources\Media\Pages\ManageMedia;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Support\ImageOptimizer;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use RuntimeException;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    private function jpeg(int $w, int $h): string
    {
        $img = imagecreatetruecolor($w, $h);
        imagefill($img, 0, 0, imagecolorallocate($img, 30, 90, 200));
        $path = tempnam(sys_get_temp_dir(), 'hkd').'.jpg';
        imagejpeg($img, $path, 92);

        return $path;
    }

    public function test_large_photo_becomes_webp_in_several_sizes(): void
    {
        $media = ImageOptimizer::import($this->jpeg(3000, 2000), 'Harbor View.jpg');

        $this->assertSame([2400, 1600], [$media->width, $media->height]);
        $this->assertSame([480, 960, 1600], array_column($media->variants, 'w'));
        $this->assertSame('Harbor View', $media->title);
        foreach ($media->allPaths() as $path) {
            Storage::disk('public')->assertExists($path);
        }
        $this->assertStringContainsString('2400w', $media->srcset());
    }

    public function test_small_picture_is_not_enlarged(): void
    {
        $media = ImageOptimizer::import($this->jpeg(300, 200), 'small.jpg');

        $this->assertSame(300, $media->width);
        $this->assertSame([], $media->variants);
    }

    public function test_non_image_is_rejected(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'hkd');
        file_put_contents($path, '<?php echo 1;');

        $this->expectException(RuntimeException::class);
        ImageOptimizer::import($path, 'evil.jpg');
    }

    public function test_used_picture_cannot_be_deleted_and_files_go_with_record(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $used = ImageOptimizer::import($this->jpeg(800, 600), 'a.jpg');
        $unused = ImageOptimizer::import($this->jpeg(800, 600), 'b.jpg');
        Setting::set('content_about', ['team' => [['photo_media' => $used->id]]]);

        $this->assertSame(['درباره ما'], $used->usages());
        $this->assertFalse($admin->can('delete', $used));
        $this->assertTrue($admin->can('delete', $unused));

        $paths = $unused->allPaths();
        $unused->delete();
        Storage::disk('public')->assertMissing($paths[0]);
    }

    public function test_access_by_role(): void
    {
        foreach (['admin' => 200, 'writer' => 200, 'finance' => 403, 'sales_ops' => 403] as $role => $status) {
            $this->actingAs(User::factory()->create(['role' => $role]))
                ->get('/admin/media')
                ->assertStatus($status);
        }
    }

    public function test_upload_action_imports_files_and_cleans_up(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        Livewire::test(ManageMedia::class)
            ->callAction('upload', data: [
                'files' => [UploadedFile::fake()->image('port.jpg', 2000, 1200), UploadedFile::fake()->image('b.png', 640, 480)],
                'alt_fa' => 'بندر',
            ])
            ->assertHasNoActionErrors();

        $this->assertSame(2, Media::count());
        $this->assertSame('بندر', Media::first()->alt_fa);
        $this->assertSame([], Storage::disk('local')->files('media-uploads'));
    }
}
