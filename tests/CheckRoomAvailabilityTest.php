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
            [new \DateTime(), (new \DateTime())->add(new \DateInterval('PT2S')), true],
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

        $this->assertEquals($expectedOutput, $bookedTime <= 240);
    }
}