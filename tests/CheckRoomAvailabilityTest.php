<?php


namespace App\Tests;

use App\Entity\Room;
use App\Entity\User;
use Monolog\Test\TestCase;

class CheckRoomAvailabilityTest extends TestCase
{
    public function dataProviderForPremiumRoom(): array
    {
        return [
            [true, true, true],
            [false, false, true],
            [false, true, true],
            [true, false, false]
        ];
    }

    /**
     * @dataProvider dataProviderForPremiumRoom
     */
    public function testPremiumRoom(bool $roomVar, bool $userVar, bool $expectedOutput):
    void
    {
        $room = new Room($roomVar);
        $user = new User($userVar);

        $this->assertEquals($expectedOutput, $room->canBook($user));
    }
}