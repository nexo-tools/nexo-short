<?php

use App\Models\Click;
use App\Models\Link;
use Illuminate\Support\Facades\Schema;

it('has the expected clicks columns', function () {
    expect(Schema::hasColumns('clicks', [
        'id', 'link_id', 'visitor_hash', 'referrer_host', 'device', 'country', 'created_at',
    ]))->toBeTrue();
});

it('AC-22: stores no raw IP or User-Agent (privacy by design)', function () {
    expect(Schema::hasColumn('clicks', 'ip'))->toBeFalse();
    expect(Schema::hasColumn('clicks', 'ip_address'))->toBeFalse();
    expect(Schema::hasColumn('clicks', 'user_agent'))->toBeFalse();
});

it('belongs to a link and cascades on link delete', function () {
    $link = Link::factory()->create();
    $click = Click::factory()->for($link)->create();

    expect($click->link->is($link))->toBeTrue();

    $link->delete();
    expect(Click::find($click->id))->toBeNull();
});
