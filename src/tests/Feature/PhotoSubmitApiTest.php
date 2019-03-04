<?php

namespace Tests\Feature;

use App\Photo;
use App\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PhotoSubmitApiTest extends TestCase
{
    use RefreshDatabase;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(User::class)->create();
    }

    /**
     * @test
     */
    public function should_ファイルをアップロードできる()
    {
        // テスト用ストレージを利用
        // -> Storage/framework/testing
        Storage::fake("s3");

        $response = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                // ダミーファイルを作成して送信している
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        // レスポンスが201であること
        $response->assertStatus(201);

        $photo = Photo::first();
        // 写真のIDが12桁のランダムな文字列であること
        $this->assertRegExp("/^[0-9a-zA-Z-_]{12}$/", $photo->id);

        // DBに挿入されたファイル名のファイルがストレージにほぞんされていること
        Storage::cloud()->assertExists($photo->filename);
    }

    /**
     * @test
     */
    public function should_データベースエラーの場合はファイルを保存しない()
    {
        Schema::drop("photos");

        Storage::fake("s3");

        $response = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        // レスポンスが500であること
        $response->assertStatus(500);

        // ストレージにファイルが保存されていないこと
        $this->assertEquals(0, count(Storage::cloud()->files()));
    }

    /**
     * @test
     */
    public function should_ファイル保存エラーの場合はDBへの挿入はしない()
    {
        // ストレージをモックにして保存時にエラーを起こさせる
        Storage::shouldReceive("cloud")
            ->once()
            ->andReturnNull();

        $response = $this->actingAs($this->user)
            ->json("POST", route("photo.create"), [
                "photo" => UploadedFile::fake()->image("photo.jpg"),
            ]);

        // レスポンスが500であること
        $response->assertStatus(500);

        // データベースになにも挿入されていないこと
        $this->assertEmpty(Photo::all());
    }

}
