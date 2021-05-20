<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Room;
use App\Form\BookingType;
use App\Repository\BookingsRepository;
use App\Repository\RoomRepository;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/booking')]
class BookingController extends AbstractController
{
    #[Route('/', name: 'booking_index', methods: ['GET'])]
    public function index(BookingsRepository $bookingsRepository): Response
    {
        return $this->render('booking/index.html.twig', [
            'bookings' => $bookingsRepository->findAll(),
        ]);
    }

    #[Route('/new/{room}', name: 'booking_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Room $room):
    Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        $user = $this->getUser();

        if(!$room->canBook($user)) {
            throw new Exception('The '.$room.' room is for premium members only');
        }

        $booking = new Booking(new \DateTime(), (new \DateTime())->add(new
        \DateInterval('PT240M')));
        $booking->setRoom($room);

        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$room->isAvailableForBooking($booking)) {
                throw new Exception('The '.$room.' room is not available during this timeslot');
            }

            if (!($booking->bookedTimeInMinutes() <= 240)) {
                throw new Exception('You can book a room for 4 hours maximum');
            }

            if (!$user->canAffordBooking($booking)) {
                throw new Exception('The cost of this booking is '
                    .$booking->calculateCost().' but you only have '
                    .$user->getCredit().' euros left in your account so first top up your credit before you make this booking.');
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($booking);
            $entityManager->flush();

            return $this->redirectToRoute('booking_show', ['id' => $booking->getId()]);
        }

        return $this->render('booking/new.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'booking_show', methods: ['GET'])]
    public function show(Booking $booking): Response
    {
        return $this->render('booking/show.html.twig', [
            'booking' => $booking,
        ]);
    }

    #[Route('/{id}/edit', name: 'booking_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Booking $booking): Response
    {
        $form = $this->createForm(BookingType::class, $booking);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('booking_index');
        }

        return $this->render('booking/edit.html.twig', [
            'booking' => $booking,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'booking_delete', methods: ['POST'])]
    public function delete(Request $request, Booking $booking): Response
    {
        if ($this->isCsrfTokenValid('delete' . $booking->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($booking);
            $entityManager->flush();
        }

        return $this->redirectToRoute('booking_index');
    }
}
