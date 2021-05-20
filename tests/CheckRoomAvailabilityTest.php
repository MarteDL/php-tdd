<?php


namespace App\Tests;

use App\Entity\Booking;
use App\Entity\Room;
use App\Entity\User;
use Monolog\Test\TestCase;
use SebastianBergmann\Comparator\Book;

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

    public function dataProviderForUserCanAffordBooking(): array
    {
        return [
            [new User(false), new Booking(new \DateTime('2021-02-02 14:00'), (new
            \DateTime('2021-02-02 18:00'))), true],
            [new User(false, 10), new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))), true],
            [new User(false, 8), new Booking(new \DateTime('2021-02-02 15:00'), (new
            \DateTime('2021-02-02 19:00'))), true],
            [new User(false, 50), new Booking(new \DateTime('2021-02-02 15:00'), (new
            \DateTime('2021-02-02 18:00'))), true],
            [new User(false, 0), new Booking(new \DateTime('2021-02-02 15:00'), (new
            \DateTime('2021-02-02 18:00'))), false],
            [new User(false, 2), new Booking(new \DateTime('2021-02-02 15:00'), (new
            \DateTime('2021-02-02 18:00'))), false],
            [new User(false, 6), new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))), true],
        ];
    }

    /**
     * @dataProvider dataProviderForUserCanAffordBooking
     */
    public function testUserCanAffordBooking(User $user, Booking $booking, bool
    $expectedOutput):
    void
    {
        $this->assertEquals($expectedOutput, $user->canAffordBooking($booking));
    }

    public function dataProviderForRoomIsAvailable(): array
    {
        return [
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 16:00'), (new \DateTime('2021-02-02 19:00'))),
                false
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                false
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-01 16:00'), (new \DateTime('2021-02-01 19:00'))),
                true
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-2 12:00'), (new \DateTime('2021-02-02 15:01'))),
                false
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 19:00'))),
                false
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 10:00'), (new \DateTime('2021-02-02 12:00'))),
                true
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 11:00'), (new \DateTime('2021-02-02 15:00'))),
                true
            ],
            [
                new Booking(new \DateTime('2021-02-02 15:00'), (new \DateTime('2021-02-02 18:00'))),
                new Booking(new \DateTime('2021-02-02 18:00'), (new \DateTime('2021-02-02 22:00'))),
                true
            ],
        ];
    }

    /**
     * @dataProvider dataProviderForRoomIsAvailable
     */
    public function testRoomIsAvailable(Booking $booking1, Booking $booking2, bool $expectedOutput): void
    {
        $room = new Room(false);
        $room->addBooking($booking1);

        $this->assertEquals($expectedOutput, $room->isAvailableForBooking($booking2));
    }
}