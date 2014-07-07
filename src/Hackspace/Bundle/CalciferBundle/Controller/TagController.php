<?php

namespace Hackspace\Bundle\CalciferBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Hackspace\Bundle\CalciferBundle\Entity\Location;
use Hackspace\Bundle\CalciferBundle\Entity\Tag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Hackspace\Bundle\CalciferBundle\Entity\Event;
use Hackspace\Bundle\CalciferBundle\Form\EventType;
use Symfony\Component\HttpFoundation\Response;
use Jsvrcek\ICS\Model\Calendar;
use Jsvrcek\ICS\Model\CalendarEvent;
use Jsvrcek\ICS\Model\Relationship\Attendee;
use Jsvrcek\ICS\Model\Relationship\Organizer;

use Jsvrcek\ICS\Utility\Formatter;
use Jsvrcek\ICS\CalendarStream;
use Jsvrcek\ICS\CalendarExport;

/**
 * Tag controller.
 *
 * @Route("/tags")
 */
class TagController extends Controller
{
    /**
     * Finds and displays a Event entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="tag_show")
     * @Method("GET")
     * @Template("CalciferBundle:Event:index.html.twig")
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Tag $tag */
        $tag = $em->getRepository('CalciferBundle:Tag')->find($id);

        if (!$tag) {
            throw $this->createNotFoundException('Unable to find tag entity.');
        }

        /** @var QueryBuilder $qb */
        $qb = $em->createQueryBuilder();
        $qb ->select(array('e'))
            ->from('CalciferBundle:Event', 'e')
            ->join('e.tags', 't', 'WITH', $qb->expr()->in('t.id', $tag->getId()))
            ->orderBy('e.startdate');
        $entities = $qb->getQuery()->execute();

        return array(
            'entities' => $entities,
            'tag' => $tag,
        );
    }

    /**
     * Finds and displays a Event entity.
     *
     * @Route("/{id}.ics", requirements={"id" = "\d+"}, name="tag_show_ics")
     * @Method("GET")
     */
    public function showActionICS($id)
    {
        $results = $this->showAction(str_replace('.ics','',$id));
        $entities = $results['entities'];

        $calendar = new Calendar();
        $calendar->setProdId('-//My Company//Cool Calendar App//EN');

        foreach($entities as $entity) {
            /** @var Event $entity */
            $event = new CalendarEvent();
            $event->setStart($entity->getStartdate());
            $event->setEnd($entity->getEnddate());
            $event->setSummary($entity->getSummary());
            $event->setDescription($entity->getDescription());
            $location = new \Jsvrcek\ICS\Model\Description\Location();
            $location->setName($entity->getLocation()->getName());
            $event->setLocations([$location]);
            $calendar->addEvent($event);
        }

        $calendarExport = new CalendarExport(new CalendarStream, new Formatter());
        $calendarExport->addCalendar($calendar);

        //output .ics formatted text
        $result =  $calendarExport->getStream();

        $response = new Response($result);
        $response->headers->set('Content-Type', 'text/calendar');

        return $response;
    }
}