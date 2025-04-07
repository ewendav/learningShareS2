<?php

namespace Entity;

require_once 'Session.php';

class Cours extends Session
{
    private $location_id;
    private $lesson_host_id;
    private $max_attendees;

    /**
     * Constructeur
     */
    public function __construct(
        $session_id = null,
        $start_time = null,
        $end_time = null,
        $date_session = null,
        $description = null,
        $rate_id = null,
        $skill_taught_id = null,
        $location_id = null,
        $lesson_host_id = null,
        $max_attendees = null
    ) {

        // Appel du constructeur parent
        parent::__construct($session_id, $start_time, $end_time, $date_session, $description, $rate_id, $skill_taught_id);

        // Initialisation des attributs spécifiques à Cours
        $this->location_id = $location_id;
        $this->lesson_host_id = $lesson_host_id;
        $this->max_attendees = $max_attendees;
    }

    /**
     * Getters
     */
    public function getLocationId()
    {
        return $this->location_id;
    }

    public function getLessonHostId()
    {
        return $this->lesson_host_id;
    }

    public function getMaxAttendees()
    {
        return $this->max_attendees;
    }

    /**
     * Setters
     */
    public function setLocationId($location_id)
    {
        $this->location_id = $location_id;
    }

    public function setLessonHostId($lesson_host_id)
    {
        $this->lesson_host_id = $lesson_host_id;
    }

    public function setMaxAttendees($max_attendees)
    {
        $this->max_attendees = $max_attendees;
    }
}
