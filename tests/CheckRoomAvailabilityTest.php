<?php


namespace App\Tests;

use App\Entity\Booking;
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

    public function dataProviderForMaxBookedTime(): array
    {
        return [
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT200M')), true],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT0M')), true],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT240M')), true],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT5000M')),
                false],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT241M')), false],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('P2D')), false],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT14401S')),
                false],
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT4H')), true],
        ];
    }

    /**
     * @dataProvider dataProviderForMaxBookedTime
     */
    public function testMaxBookedTime(\DateTime $startDate, \DateTime $endDate, bool $expectedOutput): void
    {
        $booking = new Booking($startDate, $endDate);
        $bookedTime = $booking->bookedTimeInMinutes();

        $this->assertEquals($expectedOutput, $bookedTime <= 240.00);
    }

    public function dataProviderForUserCanAffordRoom(): array
    {
        return [
            [new User(false), new Room(false, 200), false],
            [new User(false, 200), new Room(false, 200), true],
            [new User(false, 300), new Room(false, 200), true],
            [new User(false, -100), new Room(false, 200), false],
            [new User(false), new Room(false, 50), true],
            [new User(false), new Room(false, 100), true],
            [new User(false), new Room(false, 0), true],
            [new User(false), new Room(false, 150), false],
        ];
    }

    /**
     * @dataProvider dataProviderForUserCanAffordRoom
     */
    public function testUserCanAffordRoom(User $user, Room $room, bool $expectedOutput):
    void
    {
        $this->assertEquals($expectedOutput, $user->canAffordRoom($room));
    }
}