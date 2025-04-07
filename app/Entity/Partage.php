<?php

namespace Entity;

require_once 'Session.php';

class Partage extends Session
{
    private $skill_requested_id;
    private $exchange_requester_id;
    private $exchange_accepter_id;

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
        $skill_requested_id = null,
        $exchange_requester_id = null,
        $exchange_accepter_id = null
    ) {

        // Appel du constructeur parent
        parent::__construct($session_id, $start_time, $end_time, $date_session, $description, $rate_id, $skill_taught_id);

        // Initialisation des attributs spécifiques à Partage
        $this->skill_requested_id = $skill_requested_id;
        $this->exchange_requester_id = $exchange_requester_id;
        $this->exchange_accepter_id = $exchange_accepter_id;
    }

    /**
     * Getters
     */
    public function getSkillRequestedId()
    {
        return $this->skill_requested_id;
    }

    public function getExchangeRequesterId()
    {
        return $this->exchange_requester_id;
    }

    public function getExchangeAccepterId()
    {
        return $this->exchange_accepter_id;
    }

    /**
     * Setters
     */
    public function setSkillRequestedId($skill_requested_id)
    {
        $this->skill_requested_id = $skill_requested_id;
    }

    public function setExchangeRequesterId($exchange_requester_id)
    {
        $this->exchange_requester_id = $exchange_requester_id;
    }

    public function setExchangeAccepterId($exchange_accepter_id)
    {
        $this->exchange_accepter_id = $exchange_accepter_id;
    }
}
