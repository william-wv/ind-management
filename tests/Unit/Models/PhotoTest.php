<?php

namespace Tests\Unit\Models;

use App\Models\Pallet;
use App\Models\Photo;
use PDOException;
use Tests\TestCase;

class PhotoTest extends TestCase
{
    public function test_photo_and_pallet_relationships_return_related_models(): void
    {
        $pallet = new Pallet([
            'item' => 'Photo relation test',
            'width' => '81',
            'length' => '121',
            'location' => 'Unit test',
            'amount' => 5,
            'minimum_amount' => 1,
        ]);
        $this->assertTrue($pallet->save());

        /** @var Photo $photo */
        $photo = $pallet->photos()->new([
            'url' => '/assets/uploads/pallets/' . $pallet->id . '/relation.png',
            'original_name' => 'relation.png',
            'mime_type' => 'image/png',
            'size_bytes' => 123,
        ]);
        $this->assertTrue($photo->save());

        $this->assertSame($pallet->id, $photo->pallet()->get()->id);
        $this->assertSame($photo->id, $pallet->photos()->get()[0]->id);
    }

    public function test_photo_cannot_reference_a_nonexistent_pallet(): void
    {
        $photo = new Photo([
            'pallet_id' => -1,
            'url' => '/assets/uploads/pallets/-1/orphan.png',
            'original_name' => 'orphan.png',
            'mime_type' => 'image/png',
            'size_bytes' => 123,
        ]);

        $this->expectException(PDOException::class);
        $photo->save();
    }
}
