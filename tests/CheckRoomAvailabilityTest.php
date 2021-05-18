<?php


namespace App\Tests;

use Monolog\Test\TestCase;

class CheckRoomAvailabilityTest extends TestCase
{
        public function testPremiumRoom(): void
        {
            $room = new Room(false);
            $user = new User(false);

            $this->assertTrue($room->canBook($user));
        }
}